<?php
namespace App\Traits;

use App\Models\Notification;
use Ladumor\OneSignal\OneSignal;

trait NotifictionTrait {
  
    public function pushNotification($title,$device_token, $description) {

        $notificationMsg['contents']= ['en' => "English Message"];
       $notificationMsg['include_player_ids'] = [$device_token];
       $notificationMsg['data'] = array(
        "title" => $title,
        "description" =>  $description,
        );
    
        Notification::create([
            'user_id' => auth()->id(),
            'title' => $title,
            'message' => $description,
        ]);
    $val=OneSignal::sendPush($notificationMsg);
        if(isset($val['errors']))
        {
            return response()->json([
                'code' => "invalid player id",
            ]);
        }
    return OneSignal::getNotifications($val['id']);

  
    }
  
}