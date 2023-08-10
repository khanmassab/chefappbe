<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\BookChef;

use App\Models\DeviceToken;
use Illuminate\Console\Command;
use Ladumor\OneSignal\OneSignal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;

class NotificationAlert extends Command
{
   public $messaging;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    { 
        // $user = User::where('email', 'testu@gmail.com')->first();

        // // dd ($user);
        // $messaging = app(Messaging::class);

        // dd ($user);
        // $messaging = app(Messaging::class);

        // $userNotification = Notification::create('Reminder','Your Session is about to start in 20 minutes.');

 
        // $userMessage = CloudMessage::fromArray([
        //     'token' => $user->deviceToken->device_token,
        //     'notification' => $userNotification,
        //     'data' => [
        //         'name' => $user->first_name . ' ' . $user->last_name,
        //     ],
        // ]);
        
        // $userMessage = $messaging->send($userMessage);

        // dd($userMessage);

        $now =  Carbon::now()->format('Y-m-d H:i:s'); 
        $user=[];
            
            // Get all bookings that require a reminder to be sent
            $bookings = DB::table('book_chefs')
                ->where('reminder_sent', false)
                ->whereNotNull('reminder_time')
                ->where('reminder_time', '<=', $now)
                ->where('status', 'paid')
                ->get();

                // dd(Carbon::now());
        if($bookings->count() > 0)
        {
            foreach ($bookings as $booking) {

                $chef = User::find($booking->chef_id);
                $user = User::find($booking->user_id);
                
                $messaging = app(Messaging::class);

                $chefNotification = Notification::create('Session Reminder','Please be ready for today session'.' '. $user['first_name']. ' '.  $user['last_name']);

                // $chefNotification = Notification::create('Session alert', 'Please be ready for today\'s session');
                $userNotification = Notification::create('Session Reminder','Please be ready for today session'.' '. $chef['first_name']. ' '.  $chef['last_name']);

                $deviceToken = $chef->deviceToken;
                $cdeviceToken = $user->deviceToken;

                if($deviceToken && $deviceToken->device_token){
                    $chefMessage = CloudMessage::fromArray([
                        'token' => $chef->deviceToken->device_token,
                        'notification' => $chefNotification,
                        'data' => [
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'notification_type' => 'reminder',
                        ],
                    ]);
                    $chefResult = $messaging->send($chefMessage);

                    DB::table('notifications')->insert([
                    [
                        'user_id' => $booking->chef_id,
                        'title' => 'Upcoming Session',
                        'message' => 'please be ready for today session with'.' '. $user['first_name']. ' '.  $user['last_name'],
                        'created_at' =>now(),
                        'updated_at' => now(),
                    ]]);

                    DB::table('book_chefs')
                    ->where('id', $booking->id)
                    ->update(['reminder_sent' => true]);
                }

                if($cdeviceToken && $cdeviceToken->device_token)
                {
                    $userMessage = CloudMessage::fromArray([
                        'token' => $user->deviceToken->device_token,
                        'notification' => $userNotification,
                        'data' => [
                            'name' => $chef->first_name . ' ' . $chef->last_name,
                            'notification_type' => 'reminder',
                        ],
                    ]);

                    $userResult = $messaging->send($userMessage);

                    DB::table('notifications')->insert([
                    [
                        'user_id' => $booking->user_id,
                        'title' => 'Upcoming Session',
                        'message' => 'please be ready for today session'.' '. $chef['first_name']. ' '.  $chef['last_name'],
                        'created_at' =>now(),
                        'updated_at' => now(),
                    ]]);

                    DB::table('book_chefs')
                    ->where('id', $booking->id)
                    ->update(['reminder_sent' => true]);
                }
                
                
                
                

             // Update reminder_sent column
               
            }
            $this->info('Successfully sent Notification to every one.'); 
        }
        else
        {
            $this->info('There is no booking'); 
        }
           
 

         


    }
}
