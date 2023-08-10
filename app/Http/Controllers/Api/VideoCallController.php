<?php

namespace App\Http\Controllers\Api;

use OpenTok\Role;
use App\Models\User;
use OpenTok\OpenTok;
use OpenTok\MediaMode;
use App\Models\VideoCall;
use App\Models\DeviceToken;
use Laravel\Passport\Token;
use App\Models\BookChef;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Ladumor\OneSignal\OneSignal;
use PeterPetrus\Auth\PassportToken;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\Notification;

use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\MessagingException;


class VideoCallController extends Controller
{
    // public $projectId = 'livechef-4c94d';
    public $messaging;
    public function __construct(Messaging $messaging)
    {
       
        $this->messaging = $messaging;
        // dd($messaging);
    }
    
    public function createSeesion(Request $request)
    {
        try{

            $validatedData = Validator::make($request->all(), [
                "booking_id"    =>'required',
            ]);
            
            if ($validatedData->fails())
            {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }

            $booking = BookChef::where('id', $request->booking_id)->first();

            if(!$booking){
                return response()->json(['code' => 500, 'message' => 'Invalid Booking']);

            }

            $sender = auth()->user();

            $toUser = $booking->user_id;

            if(!$sender->is_chef){
                $toUser = $booking->chef_id;
            }

            $toUserDevice = DeviceToken::where('user_id', $toUser)->first();

            if($toUserDevice->device_type == 0){
                $toUser = User::find($toUserDevice->user_id);

                if(!$toUser){
                    return response()->json([
                        'code' => 405,
                        'message' => "No active user found"
                    ]); 
                }
                if($toUser->one_signal_player_id)
                {
                    // Instantiate a new OpenTok object with our api key & secret
                    $opentok = new OpenTok(env('VONAGE_API_KEY'), env('VONAGE_API_SECRET'));
                    // Creates a new session (Stored in the Vonage API cloud)
                    $session = $opentok->createSession(array('mediaMode' => MediaMode::ROUTED));
                    // Create a new virtual class that would be stored in db
                    // Store the unique ID of the session
                    $sessionId = $session->getSessionId();
        
                    // dd($sessionId);
                        // Generate a Token from just a sessionId (fetched from a database)
                    // Set some options in a token
                    $token = $session->generateToken(array(
                        'role'       => Role::MODERATOR,
                        'expireTime' =>  time() + 60*60 , // in one hour
                        'data'       => $sender->first_name .' '.$sender->last_name,
                        'initialLayoutClassList' => array('focus')
                    ));
    
                    // dd($token);
                    if(VideoCall::where('user_id',$sender->id)->exists())
                    {
                        VideoCall::where('user_id',$sender->id)->update([
                            'session_id' => $sessionId,
                            'vonage_token' => $token,
                        ]);
                    }
                    else
                    {
                        VideoCall::where('user_id',$sender->id)->create([
                            'user_id' => $sender->id,
                            'session_id' => $sessionId,
                            'vonage_token' => $token,
                        ]);
                    }
    
            
                    $contents= ['en' => "English Message",];
                    $notificationMsg['contents']= $contents;
                    // $notificationMsg['name'] = $user->first_name .' '.$user->last_name;
                    $notificationMsg['apns_push_type_override'] = 'voip';
                    $notificationMsg['include_player_ids'] = [$toUser->one_signal_player_id];
                    $notificationMsg['data'] = array(
                        "name" => $sender->first_name .' '.$sender->last_name,
                        'session_id' => $sessionId,
                        'publish_token' => $token
                    );
                        
                        // $notificationMsg['content_available'] = true; // Setting content_available to true makes it a silent notification
            
                    $onsignal_data=OneSignal::sendPush($notificationMsg);
            
                    if(isset($onsignal_data['errors']))
                    {
                        return response()->json([
                            'code' => 500,
                            'error' => "invalide player id",
                        ]);
                    }
                    return response()->json([
                        'code' => 200,
                        'message' => 'session created',
                        'data' => [
                            'session_id' =>  $sessionId,
                            'publish_token' =>  $token,
                            'one Signal' =>$onsignal_data,
                            // 'session' =>$sessionStatus
                        ],
                    ]);
            
                }
            }

            $toUserDevice = DeviceToken::where('user_id', $toUser)->first();

            $deviceToken = $toUserDevice->device_token;

            $user = User::find($toUserDevice->user_id);
		    $sender = User::find(auth()->id());
            if(!$toUserDevice){
                return response()->json(['code' => 500, 'message' => 'Wrong Player ID or unknown Error']);
            }

            $opentok = new OpenTok(env('VONAGE_API_KEY'), env('VONAGE_API_SECRET'));

            // Creates a new session (Stored in the Vonage API cloud)
            $session = $opentok->createSession(array('mediaMode' => MediaMode::ROUTED));
            // Create a new virtual class that would be stored in db
            // Store the unique ID of the session
            $sessionId = $session->getSessionId();
            // return $sessionId;

            // dd($sessionId);
                // Generate a Token from just a sessionId (fetched from a database)
            // Set some options in a token
            $token = $session->generateToken(array(
                'role'       => Role::MODERATOR,
                'expireTime' =>  time() + 60*60 , // in one hour
                'data'       => $user->first_name .' '.$user->last_name,
                'initialLayoutClassList' => array('focus')
            ));

            // return ($token);
            if(VideoCall::where('user_id',$user->id)->exists())
            {
                VideoCall::where('user_id',$user->id)->update([
                    'session_id' => $sessionId,
                    'vonage_token' => $token,
                ]);
            }
            else
            {
                VideoCall::where('user_id',$user->id)->create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'vonage_token' => $token,
                ]);
            }

            // return $deviceToken;
            // $tokenD = $toUserDevice->device_token;

            
            
            $messaging = app(Messaging::class);

            $tokenD = $toUserDevice->device_token;

            $chefNotification = [
                'title' => 'Incoming Call - LiveChef',
                'body' => $user->first_name .' '.$user->last_name . ' is calling..',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Optional: Customize the click action
                'sound' => 'default', // Optional: Customize the notification sound
                // Additional VoIP-specific fields as needed
            ];
        
            $message = CloudMessage::fromArray([
                'token' => $tokenD,
                'data' => [
                    "name" => $sender->first_name .' '.$sender->last_name,
		            "picture" => $sender->profile_picture,
                    'session_id' => $sessionId,
                    'publish_token' => $token,
                    'notification_type' => 'voip',
                ],
                'apns' => [
                    'headers' => [
                        'apns-push-type' => 'voip',
                        'apns-priority' => '10',
                        // Additional APNs headers as needed
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $chefNotification['title'],
                                'body' => $chefNotification['body'],
                            ],
                            'mutable-content' => 1,
                            'sound' => $chefNotification['sound'],
                            // Additional APS fields as needed
                        ],
                        // Additional payload fields as needed
                    ],
                ],
                
            ]);
        
            $messaging->send($message);
            
            // $messaging = app(Messaging::class);


            // $chefNotification = [
            //     'title' => 'Incoming Call - LiveChef',
            //     'body' => $user->first_name .' '.$user->last_name . ' is calling..',
            //     'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Optional: Customize the click action
            //     'sound' => 'default', // Optional: Customize the notification sound
            //     // Additional VoIP-specific fields as needed
            // ];
        
            // $message = CloudMessage::fromArray([
            //     'token' => $deviceToken,
            //     'data' => [
            //         "name" => $user->first_name .' '.$user->last_name,
            //         'session_id' => $sessionId,
            //         'publish_token' => $token,
            //         'notification_type' => 'voip',
            //     ],
            //     'apns' => [
            //         'headers' => [
            //             'apns-push-type' => 'voip',
            //             'apns-priority' => '10',
            //             // Additional APNs headers as needed
            //         ],
            //         'payload' => [
            //             'aps' => [
            //                 'alert' => [
            //                     'title' => $chefNotification['title'],
            //                     'body' => $chefNotification['body'],
            //                 ],
            //                 'mutable-content' => 1,
            //                 'sound' => $chefNotification['sound'],
            //                 // Additional APS fields as needed
            //             ],
            //             // Additional payload fields as needed
            //         ],
            //     ],
            //     'notification' => $chefNotification,
            // ]);

            
            // $messaging->send($message);
            
            return response()->json([
                'code' => 200,
                'message' => 'session created',
                'data' => [
                    'session_id' =>  $sessionId,
                    'publish_token' =>  $token,
                    'fcm' =>$chefNotification
                ],
                'priority' => 'high'
            ]);

        } catch (\Throwable $th) {

            return $th->getMessage();
            
        }
    }

    public function sendSilentNotification(Request $request)
    {
        try{

            $validatedData = Validator::make($request->all(), [
                "booking_id"    =>'required',
            ]);
            
            if ($validatedData->fails())
            {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }

            $booking = BookChef::where('id', $request->booking_id)->first();

            $sender = auth()->user();

            $toUser = $booking->user_id;

            if(!$sender->is_chef){
                $toUser = $booking->chef_id;
            }

            $toUserDevice = DeviceToken::where('user_id', $toUser)->first();

            $messaging = app(Messaging::class);

            $userNotification = Notification::create('Reminder', 'Hello.');

            $chefNotification = [
                'title' => 'Call End Request - LiveChef',
                'body' => $sender->first_name .' '.$sender->last_name . ' is ending the call..',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Optional: Customize the click action
                'sound' => 'default', // Optional: Customize the notification sound
                // Additional VoIP-specific fields as needed
            ];
        
            $message = CloudMessage::fromArray([
                'token' => $toUserDevice->device_token,
                'data' => [
                    "name" => $sender->first_name .' '.$sender->last_name,
		            "picture" => $sender->profile_picture,
                    // 'session_id' => $sessionId,
                    'publish_token' => $toUserDevice->device_token,
                    'notification_type' => 'call_end',
                ],
                'apns' => [
                    'headers' => [
                        'apns-push-type' => 'voip',
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $chefNotification['title'],
                                'body' => $chefNotification['body'],
                            ],
                            'mutable-content' => 1,
                            'sound' => $chefNotification['sound'],
                        ],
                    ],
                ],
                
            ]);
        
            $messaging->send($message);
            
            return response()->json([
                'code' => 200,
                'message' => 'Notification sent',
                'data' => $message
            ]);

        } catch (\Throwable $th) {
            dd ($th);
        }
    }
    

    public function completeSession(Request $request)
    {
        try{
          

            $validatedData = Validator::make($request->all(), [
                'booking_id' => 'required',
            ]);
    
            if ($validatedData->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }  

            $user = auth()->user();

           $complete_booking = BookChef::where('id',$request->booking_id)->where('status','paid')->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
               
            })->orWhere(function ($q) use ($user) {
                $q->where('chef_id', $user->id);
            })->first();
            

            if($complete_booking)
            {
                $complete_booking->status = 'completed';
                $complete_booking->save();

                return response()->json([
                    'code' => 200,
                    'message' => 'Successfully completed your session',
                ]);
            }
            else
            {
                return response()->json([
                    'code' => 401,
                    'error' => 'you can not update status',
                ],401); 
            }

            
        }
        catch(\Throwable $th)
        {
            dd($th);
            return response()->json(['code' =>500, 'error' => 'Internal error',]);
        }
       



    }
}


