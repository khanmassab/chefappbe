<?php

namespace App\Listeners;

use App\Mail\NotifyMail;
use App\Events\UserEvent;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\JsonResponse;

class UserNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserEvent $event)
    {
    
        $contents= [
            'en' => "English Message",
           ];
         $notificationMsg['apns_push_type_override'] = 'voip';
        $notificationMsg['include_player_ids'] = [$event->data['device_token']];
        //    $notificationMsg['data'] = array(
        //     "name" => $user->first_name .' '.$user->last_name,
        //     );
        //   $onsignal_data=OneSignal::sendPush($notificationMsg);
    }

}
