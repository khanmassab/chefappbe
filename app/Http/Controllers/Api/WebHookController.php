<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAccount;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Validator;

use App\Mail\StripeActivationMail;

class WebHookController extends Controller
{
   public $messaging;

    public function handleStripeWebhook(Request $request)
    {
        // return 'Hello World';
        // \Log::info('Stripe webhook received');

        // Parse the incoming webhook payload
        $payload = json_decode($request->getContent(), true);
    
        // Log the payload for debugging
        // \Log::debug('Webhook payload:', $payload);
    
        // Retrieve the relevant data from the payload
        $accountId = $payload['data']['object']['id'];
    
        // Log the account ID for debugging
        // \Log::debug('Account ID:', ['id' => $accountId]);

        // Get the corresponding user account based on the account ID
        $userAccount = UserAccount::where('acc_token', $accountId)->first();

        // Check if the user account exists and update its status dynamically
        if ($userAccount) {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $account = $stripe->accounts->retrieve($userAccount->acc_token);
            $requirements = $account->requirements;

            if (!empty($requirements['currently_due'])) {
                $account_status = 0;
                $reason = $requirements['currently_due'][0] . ' is not yet verified.';
            } else {
                $account_status = 1;
                $userAccount->status = true;
                $userAccount->save();
                $reason = '';

                $user = User::find($userAccount->user_id);

                $messaging = app(Messaging::class);
                
                $greeting = 'Congrats!' . ' ' .  $user->first_name . ' ' . $user->last_name;

                $chefNotification = Notification::create($greeting, 'Your Account has been verified by Stripe.');

                $check1 = $user->deviceToken;
                if($check1){
                $userMessage = CloudMessage::fromArray([
                    'token' => $user->deviceToken->device_token,
                    'notification' => $chefNotification,
                    'data' => [
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'notification_type' => 'reminder',
                    ],
                ]);
                $userMessage = $messaging->send($userMessage);
                }

                Mail::to($user->email)->send(new StripeActivationMail());

                // \Log::info($mail);

            }

            $response['chef_stripe'] = ['account_status' => $account_status, 'reason' => $reason];
        }

        return response()->json(['success' => true]);
    }
}
