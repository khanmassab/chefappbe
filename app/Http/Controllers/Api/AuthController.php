<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Stripe\Charge;
use Stripe\Stripe;
use App\Models\User;
use Stripe\Transfer;
use Stripe\StripeClient;

use App\Events\UserEvent;
use App\Models\ChefPayment;
use App\Models\DeviceToken;
use App\Models\UserAccount;
use Illuminate\Support\Str;
use Laravel\Passport\Token;
use App\Models\CookingStyle;
use Illuminate\Http\Request;
use App\Traits\NotifictionTrait;
use Ladumor\OneSignal\OneSignal;
use Illuminate\Support\Facades\DB;
use PeterPetrus\Auth\PassportToken;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\TokenRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Auth\Authenticatable;


class AuthController extends Controller
{
    use NotifictionTrait;

public function register(Request $request)
{
  
    $validatedData = Validator::make($request->all(), [
        'first_name' => 'nullable|string',
        'last_name' => 'nullable|string',
        'email' => 'required|email',
        'password' => 'required|min:8',
        'is_chef' => 'required|boolean',
        'interest*' => 'array',
        'interest.*' => 'nullable',
    ]);

    if ($validatedData->fails()) {
        return response()->json([
            'code' => 422,
            'error' => $validatedData->errors()->first(),
        ]);
    }

    if(User::where('email', $request->email)->whereNotNull('password')->exists())
    {
        return response()->json([
            'code' => 422,
            'error' => 'This email is already exists',
        ]);
    }
    $interest = implode(',' , $request->interest);

    $user = User::create([
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
        'is_chef' => $request->input('is_chef'),
        'interest' => $interest,
    ]);

    $user['interest'] = json_encode($interest);

    $tokenResult = $user->createToken('Personal Access Token');
    $token = $tokenResult->token;
    $token->expires_at = Carbon::now()->addWeeks(1);
    $token->save();
    if($user->is_chef == 1)
    {
     $user->load(['chefPayment'=> function($q){
             $q->select(['id', 'user_id', 'amount', 'due']);
         }]);
     }
    if($user){
        return response()->json([
            'code' => 200,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
            ],
        ]);
    }

    return response()->json([
        'code' => 500,
        'message' => 'User could not be created',
    ]);
    }

    public function login(Request $request)
    {
        try{

            $credentials = $request->only('email', 'password');
            $user= User::where('email', $request->email)->whereNotNull('password')->first();
            if ($user && Hash::check($request->password , $user->password) ) {
            $response = ['user' => $user];
           if($user->is_chef == 1)
           {    
                $check = $user->chefInfo;
                if(!$check){

                    $response['info'] = false;
                }
                
                $response['info'] = true;

                $user->load(['chefPayment'=> function($q){
                        $q->select(['id', 'user_id', 'amount', 'due']);
                    }])->load(['chefAccount' => function($query){
                        $query->select('status');
                    }]);

                $user_account = UserAccount::where('user_id', $user->id)->first();

                if($user_account)
                {
                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));


                    $account = $stripe->accounts->retrieve($user_account->acc_token);
                    $requirements = $account->requirements;

                    if (!empty($requirements['currently_due'])) {
                        $account_status = 0;
                        $reason = $requirements['currently_due'][0];
                    } else {
                     $account_status = 1;
                     $user_account->status = true;
                        $user_account->save();
                        $reason = '';
                    }

                    $response['chef_stripe'] = [ 'account_status' => $account_status, 'reason' => $reason ];
                }

                if(!$user_account){
                    $account_status = 0;
                    $reason = "You haven't added your Stripe account yet";

                    $response['chef_stripe'] = [ 'account_status' => $account_status, 'reason' => $reason ];

                }
                    
            }
      
              if($user->Tokens())
              {
            
                $accessToken = Token::where('user_id', $user->id)->latest()->first();
                if($accessToken)
                {
                    $accessToken->revoked = true;
                    $accessToken->save();
                }
               
               
                 
              }
             
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();
                $title = $user->first_name . ' ' .$user->last_name;
                $device_token =$user->one_signal_player_id; 
                // $notification = $this->pushNotification($title,$device_token, 'you are login');
                $authToken = ''; // Replace this with the actual auth token obtained from the API

                Session::put('auth_token', $authToken);

                $accessToken = Token::where('user_id', $user->id)->first();
                $accessToken->revoked = false;
                $accessToken->save();


                if($user->is_chef == 1){
                    return response()->json([
                        'code' => 200,
                        'message' => 'User logged in successfully',
                        'data' => [
                            'user' => $response['user'],
                            'chef_stripe' => $response['chef_stripe'],
                            'access_token' => $tokenResult->accessToken,
                            'token_type' => 'Bearer',
                            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
                            // 'notification' =>  $notification,
                        ],
                    ]);
                }

                

                return response()->json([
                    'code' => 200,
                    'message' => 'User logged in successfully',
                    'data' => [
                        'user' => $response['user'],
                        'access_token' => $tokenResult->accessToken,
                        'token_type' => 'Bearer',
                        'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
                        // 'notification' =>  $notification,
                    ],
                ]);
                
            }
          
    
            return response()->json([
                'code' => 401,
                'error' => 'Invalid credentials',
            ]);
        }
        catch (\Throwable $th) {
            dd($th);
            return response()->json(['code' => 500, 'message' => 'enternal server error.']);
        }
      
    }
    

    public function forgotPassword(Request $request)
    {
    												   
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'error' => $validator->errors()->first(),
            ]);
        }

        $otp = random_int(100000, 999999);;
        
            if(User::where('email', $request->email)->exists())
            {  
                
                 Mail::send('auth.forgotPassword', ['otp' => $otp], function($message) use($request){
                    $message->to($request->email);
                    $message->subject('Reset Password');
                });
               
                    
                $user= DB::table('password_resets')->where('email',$request->email)->first();
                if($user)
                {
                    
                    DB::table('password_resets')->where('email',$request->email)->update([
                        'otp' => $otp, 
                        'created_at' => Carbon::now(),
                        'expires_at' => Carbon::now()->addMinute(2),
                      ]);
                }
                else
                {
                    DB::table('password_resets')->insert([
                        'email' => $request->email, 
                        'otp' => $otp, 
                        'token' => 'withouttoken',
                        'created_at' => Carbon::now(),
                        'expires_at' => Carbon::now()->addMinute(2),
                      ]);
                }

                return response([
                "code" => 200, 
                "message" => "OTP sent successfully",
                'otp' => $otp,
               ]);

            }
            else
            {
                return response(["status" => 500, 
                 "message" => "Invalid Email",
                ]); 
            }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors()->first(),
            ]);
        }

        $user = DB::table('password_resets')->where('email', $request->email)->first();
        if($user->expires_at < Carbon::now()->subMinute(2))
        {
            return response()->json([
                'code' => 400,
                'error' => 'OTP Expired',
            ]);
        }

        if ($user->otp != $request->otp) {
            return response()->json([
                'code' => 400,
                'error' => 'Invalid OTP',
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'OTP verified successfully',
            'user' => $user,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|confirmed:new_password_confirmation|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors()->first(),
            ]);
        }

         $user = User::where('email',$request->email)->update([
            'password' => Hash::make($request->new_password),
        ]);
        return response()->json([
            'code' => 200,
            'message' => 'Your password updated Successfully ',
        ]);
       
    }

    public function updatePassword(Request $req){
        try{
            $validator = Validator::make($req->all(),
            [
                'new_password' => 'required|confirmed:new_password_confirmation',
            ]);

            if($validator->fails()){
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }
           
            $pass = User::find(Auth::user()->id)->update([
                'password' => Hash::make($req->new_password)
            ]);

            if($pass){
                return response()->json(['code' => 200, 'message' => 'Password Updated']);
            }

            } catch (Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

   
    public function loginwithSocial(Request $request)
    {
        try {
           
            
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'token' => 'required', //social token
                'login_type' => 'required',
            ]);
          
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first(),
                ]);
            }
           
          if($request->token !=null && $request->login_type)
          {
        
             $finduser = User::where('email',$request->email)->where('social_token', '!=', null)->first();
            //  dd($finduser);
            if($finduser){
                if($finduser->is_chef == 1)
                {
                 $finduser->load(['chefPayment'=> function($q){
                         $q->select(['id', 'user_id', 'amount', 'due']);
                     }])->load(['chefAccount' => function($query){
                        $query->select('status');
                     }]);
                     

                 }
                
                if($finduser->Tokens())
                {
                  $accessToken = Token::where('user_id', $finduser->id)->latest()->first();
                  if($accessToken)
                  {
                      $accessToken->revoked = true;
                      $accessToken->save();
                  }
                 
                  // dd($accessToken);
                   
                }

                Auth::login($finduser);
                $tokenResult = auth()->user()->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();
                return response()->json([
                    'code' =>200,
                    'message' => 'Successfully  login through social',
                    'data' => [
                        'user' => $finduser,
                        'access_token' => $tokenResult->accessToken,
                        'token_type'   => 'Bearer',
                        'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
                    ],
                ]);
            }
            else{
               
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first(),
                ]);
            }

            $newUser = User::create([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'social_token' => $request->token,
                        'login_type' =>(int) $request->login_type,
                        'profile_picture' => $request->profile_picture, //link of social profile
                        'is_chef' => false,
                        
                    ]);
         
            Auth::login($newUser);
        
            $tokenResult = auth()->user()->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->expires_at = Carbon::now()->addWeeks(1);
            $token->save();
            return response()->json([
                    'code' =>200,
                    'message' => 'Successfully  created user through social',
                        '   data' => [
                        'user' => $newUser,
                        'access_token' => $tokenResult->accessToken,
                        'token_type'   => 'Bearer',
                        'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
                    ],
                    
                ]);
            }

          }
          else
          {
            return response()->json([
                'code' =>200,
                'message' => 'please give correct credintial',
                
            ]);
          }
           
        
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['code' => 500, 'message' => 'internal error']);
        }
    }

    public function UserDeviceToken(Request $request)
    {
        try{
         
            $validator = Validator::make($request->all(),
            [
                'identifier' => 'required',
            ]);
    
            if($validator->fails()){
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            } 
            $fields = [
                'device_type'  => $request->device_type,
                'identifier'   => $request->identifier,
                'test_type'    => $request->test_type,
            ];
           
            // $val=OneSignal::getDevices();   

          $val=OneSignal::addDevice($fields);

            $user = User::find(auth()->id());
            $user->one_signal_player_id = $val['id'];
            $user->save();
            
           
            //  dd($val['id'], $user->one_signal_player_id);

             return response()->json([
                'code' =>200,
                'message' => 'Successfully created device token',
                'device_token' => $val
            ]);

          
        }catch (\Throwable $th) {
            throw $th;
            return response()->json(['code' => 500, 'message' => 'internal error']);
        }
        
    }


    public function convertedToChef(Request $request)
    {
        $user= auth()->user();

        if($user->is_chef == false)
        { 
           $user->update(['is_chef'=> $request->is_chef]);
           
           if($user->is_chef == 1)
           {
            $user->load(['chefPayment'=> function($q){
                    $q->select(['id', 'user_id', 'amount', 'due']);
                }]);
            }
            
            return response()->json([
             'code' => 200,
             'message' => 'You are converted to Chef',
             'data' => $user
            ]);

        }
        else
        {
            return response()->json([
                'code' => 401,
                'error' => 'You are already chef'
               ]);
        }
      
    }
    public function logout(Authenticatable $user)
    {
        
        $user->token()->revoke();
        $user->one_signal_player_id = '';
        $user->save();
        // $user->token()->delete();
        // auth()->guard('api')->logout();
      
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refreshToken(Request $request)
    {
        
        $token = $request->bearerToken();

            $tokenDec = new PassportToken(
                $token
            );
           
            $accessToken = Token::where(['id' => $tokenDec->token_id, 'revoked' =>0 ])->first();
            if($accessToken) {
               
                   $expire = Carbon::parse($accessToken->expires_at);
                    $currentDatetime = Carbon::now();
                   if($expire->lessThan($currentDatetime)) {

                            $user=User::where('id',$accessToken->user_id)->first();
                            $tokenResult = $user->createToken('Personal Access Token');
                            $token = $tokenResult->token;
                            $token->save();
                            if($user){
                                return response()->json([
                                    'code' => 200,
                                    'message' => 'Your token is refreshed',
                                    'data' => [
                                        'user' => $user,
                                        'access_token' => $tokenResult->accessToken,
                                        'token_type' => 'Bearer',
                                        'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
                                    ],
                                ]);
                            }
                   }
                   else
                   {
                    return response()->json([
                        'code' => 401,
                        'message' => 'This token is not expired.'
                    ]);
                   }
            }
            else
            {
                return response()->json([
                    'code' => 401,
                    'message' => 'Invalid Token.'
                ]);
            }
    }

    public function addFcmToken(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),
            [
                'device_token' => 'required',
                'device_type' =>'required',
            ]);
    
            if($validator->fails()){
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            } 
            $user = auth('api')->user();

            if(!$user){
                return response()->json(['code' => 401, 'message' => 'unauthorized']);
            }

            $dvice_toeken = DeviceToken::where('user_id', $user->id)->first();
            if($dvice_toeken)
            {
                $dvice_toeken->update([
                    'device_token' => $request->device_token,
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Device token successfully updated',
                ]);
            }
            else
            {
                $create = DeviceToken::create([
                    'user_id' => $user->id,
                    'device_token' => $request->device_token,
                    'device_type' => $request->device_type,
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Device token successfully registered',
                ]);
            }
        }
        catch (\Throwable $th) {
            throw $th->getMessage();
            return response()->json(['code' => 500, 'message' => 'internal error']);
        }
        
    }
   
}
