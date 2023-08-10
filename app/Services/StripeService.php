<?php
namespace App\Services;

use File;
use Carbon\Carbon;
use Stripe\Charge;
use Stripe\Person;
use Stripe\Stripe;
use Stripe\Account;
use App\Models\User;
use Stripe\Transfer;
use App\Models\BookChef;
use Stripe\StripeClient;
use App\Models\TimeSlots;
use App\Models\UserAccount;
use App\Models\UserPayment;
use Laravel\Passport\Token;
use Illuminate\Http\Request;
use Stripe\AccountExternalAccount;
use PeterPetrus\Auth\PassportToken;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;

use Stripe\Token as stripeToken;

class StripeService{

  public function charge($request)
  {
      try
      {
     
       
          $validatedData = Validator::make($request->all(), [
              // "number"        =>'required',
              // "amount"        =>'required',
              // "cvc"           =>'required',
              // "exp_month"     =>'required',
              // "exp_year"      =>'required',
              // "description"   =>'required',
              // "email"         =>'required',
              // "name"          =>'required',
              "booking_id"    =>'required',
              "token"    =>'required',
          ]);
          if ($validatedData->fails())
          {
              return response()->json([
                  'code' => 422,
                  'error' => $validatedData->errors()->first(),
              ]);
          }
          
          $stripe = new StripeClient(env('STRIPE_SECRET'));
        
          $user = auth()->user();
         
          if($user->is_chef == 1)
          {
              return response()->json([
                  'code' => 401,
                  'message' => "You can not charge amount becouse you are chef",
              ]);
          }
          $bookChef =BookChef::where(['id' => $request->booking_id,'status' =>'paid'])->first();
        
          if($bookChef){
            return response()->json([
              'code' => 200,
              'message' => 'This is booking is not available anymore. Another user has paid first.',
          ]);
          }
        
          $bookChef =BookChef::where(['id' => $request->booking_id ])->first();
          if($bookChef)
          {
              // $result=$stripe->tokens->create([
              //     'card' => [
              //         'number' => $request->number,
              //         'exp_month' => $request->exp_month,
              //         'exp_year' => $request->exp_year,
              //         'cvc' => $request->cvc,
              //     ],
              // ]);

              Stripe::setApiKey(env('STRIPE_SECRET'));
              $token = $request->token;
              $charge_amount = 5;
              $deduction_amount = $charge_amount * 0.1;
              $transfer_amount = $charge_amount - $deduction_amount;


              $charges = $stripe->charges->create([
                  'amount' => $charge_amount * 100,
                  'currency' => 'usd',
                  'source' => $token,
                  'description' => "Charging fee from User",
              ]);


              $transfer = Transfer::create([
                  'amount' => $deduction_amount *100,  // amount in cents
                  'currency' => $charges->currency,
                  'destination' =>  config('global.AppAccount'),
                  'source_transaction' =>$charges->id,
                  'description' => 'Application fee transfer for Charge ID: ' . $charges->id,
                  'application_fee' => $charges->application_fee_amount,
              ]);

              
              if($charges->id)
              {  
                
                  $time_id=$bookChef->time_slot_id;

                  // return $time_id;
                  $time_slots =TimeSlots::where('id',$time_id)->latest()->first();

                  $time_slots->status= 'unavailable';
                  $time_slots->update();
                  $time_slots->update(['status' =>'unavailable']);
                  $get_time_slot = $time_slots->latest()->first();
                  $reminder_time = $get_time_slot->date . ' ' . $get_time_slot->from_time;
                  $from_time = Carbon::parse($reminder_time);
                  $reminder_time = $from_time->subMinutes(30)->format('Y-m-d H:i:s');
                  
                  $bookChef->status = 'paid';
                  $bookChef->reminder_time = $reminder_time;
                  $bookChef->update();

                  $userMessage = 'Notification cannot be sent due to technichal error';

                  $messaging = app(Messaging::class);
                
                  $chef = User::find($bookChef->chef_id);
                  $user = User::find($bookChef->user_id);

                  $messageText = $user->first_name . ' ' . $user->last_name . ' has booked your time slot';
                  $chefNotification = Notification::create('LiveChef', $messageText);
                
                  $check1 = $chef->deviceToken;
                 
                  if($check1){
                    $userMessage = CloudMessage::fromArray([
                        'token' => $chef->deviceToken->device_token,
                        'notification' => $chefNotification,
                        'data' => [
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'notification_type' => 'reminder',
                        ],
                    ]);
                    $userMessage = $messaging->send($userMessage);
                   
                  }
                  
                  
                  $payment= UserPayment::create([
                      'token_id' => $charges->id,
                      'user_id' => $user->id,
                      'booking_id' => $request->booking_id,
                      'email' => $user->email,
                      'amount' => $request->amount,
                  ]);
                  return response()->json([
                      'code' => 200,
                      // 'time' => $time_slots,
                      'data' => $charges,
                      'notification' => $userMessage,
                      'message' => 'Amount successfully transferred',
                  ]);

              }
              else
              {
                  return response()->json([
                      'code' => 401,
                      'message' => 'transiction failed',
                  ]);
              }
              
          }
          else
          {
              return response()->json([
                  'code' => 401,
                  'message' => 'This booking is not available',
              ]); 
          }
          
      }
      catch (\Throwable $th) {
        // dd($th);
       return response()->json(['code' => 500, 'message' => $th->getMessage()]);
      }
      
  }

    public function transferAmountToChef($request)
    {
        try
        {
            $validatedData = Validator::make($request->all(), [
                "booking_id"        =>'required',
            ]);
            if ($validatedData->fails())
            {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }
            $user = auth()->user();
            if($user->is_chef == 1)
            {
                return response()->json([
                    'code' => 401,
                    'error' => "You can not charge amount becouse you are chef",
                ]);
            }
            $userpay= UserPayment::where(['booking_id' => $request->booking_id,'recived_to_chef' =>false ])->with('bookingChef')->first();
        
            if($userpay)
            {
                $stripe=Stripe::setApiKey(env('STRIPE_SECRET'));
                 $charge_id = $userpay->token_id; 
                 $charge = Charge::retrieve($charge_id);
                
                 $actual_amount = ($charge->amount/100);
                 $deduction_amount = $actual_amount * 0.1;
                 $stripe_percentage = ( $actual_amount * 0.029) + 0.30;
                 $net_amount =  $actual_amount  - $stripe_percentage;
                $net_amount -=$deduction_amount;
               
                $app_connected_account_id = $userpay['bookingChef']['ChefAccount']['acc_token']; //chef accounts for
                $transfer = Transfer::create([
                    'amount'=> $net_amount*100,
                    'currency' => 'usd', 
                    'source_transaction' => $charge_id, 
                    'destination' => $app_connected_account_id,   
                
                ],);
                if($transfer)
                {
                    $userpay->recived_to_chef = true;
                    $userpay->update();
                    
                   return response()->json([
                        'code' => 200,
                        'message'=>'Successfully transfere to your account',
                        'data' => $transfer,
                    ]);
                  

                }
                else
                {
                    return response()->json([
                        'code' => 200,
                        'message' => 'Successfully transfere this account is not available',
                     ]);
                }
            }
            else
            {
                return response()->json([
                    'code' => 401,
                    'error' => 'Boking is not fount or already transfered',
                ]);
            }
        }
        catch(\Throwable $th)
        {
        return response()->json(['code' =>500, 'error' => $th->getMessage()]);
        }
    }
    public function refundcharge($request)
    {
        try{

            $validatedData = Validator::make($request->all(), [
                "booking_id"        =>'required',
            ]);
            if ($validatedData->fails())
            {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }

            $stripe = new StripeClient(env('STRIPE_SECRET'));  
            $user= auth()->user();
    
            $userPayment=UserPayment::where(['user_id' => $user->id,'booking_id' => $request->booking_id ,'recived_to_chef' =>  false])->first();
            if($userPayment)
            {
                $actual_amount = 5;
                $deduction_amount = $actual_amount * 0.1;
                $stripe_percentage = ($actual_amount * 0.029) + 0.30;
                $net_amount =  $actual_amount  - $stripe_percentage;
               $net_amount -=$deduction_amount;
                $charge_token = $userPayment->token_id;
                $refund = $stripe->refunds->create([
                    'charge' =>  $charge_token,
                    'amount' => (int) $net_amount*100,
                  ]);
                   if($refund)
                   {
                        return response()->json([
                            'code' => 200,
                            'message' => 'Successfully refunded',
                            'data' => $refund,
                        ]);
                    }
                   else
                   {
                      return response()->json([
                          'code' => 401,
                          'message' => 'Refund failed',
                          'data' => $refund,
                      ]);
                   }
            }
            else
            {
                return response()->json([
                    'code' => 401,
                    'message' => 'The booking refund is not longer define',
                ]);
            }
            
             
        }
        catch(\Throwable $th)
        {
           return response()->json(['code' =>500, 'error' => $th->getMessage()]);
        }
    }
    
    public function addAccount(Request $request)
    {
        try
        {
          // $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
          // $account=$stripe->accounts->retrieve('acct_1N5qoZRWdYasSAB8');
          // $requirements = $account->requirements['currently_due'];

          $token= $request->bearerToken();
          $tokenDec = new PassportToken($token);
          $user_token= Token::where('id', $tokenDec->token_id)->first();
       if($user_token)
       {
      
        if($user_token)
        {
          $user= User::where('id',$user_token->user_id)->select('id')->first();
          $id = encrypt($user->id);
          
            $route = route('create_account',$id);
          return response()->json([
            'code'=>200,
            'stripe_url' =>$route,
          ]);
        }
          
         
       }
       else
       {
        return response()->json([
          'code' =>404,
          'error' => "Token not exists",
        ]);
       }
      }
        catch(\Throwable $th)
        {

          dd($th);  
          return response()->json(['code' =>500, 'error' => $th->getMessage()]);
        }
    }

    public function verifyCompany($request)
    {
        try
        {
            
          $validatedData = Validator::make($request->all(), [
            'mcc' => 'required',
            // 'url' => 'required',
            'city' => 'required',
            'line1' => 'required',
            'postal_code' => 'required',
            'state' => 'required',
            'name' => 'required',      //company_name
            'phone' => 'required',    
          ]);
          if($validatedData->fails())
          {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
          }
          
          $user  = auth()->user();
          $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET')); //acct_1MrNfkRiTZvaxTZ6
          $user_account = UserAccount::where('user_id', $user->id)->first();
          if($user_account)
          {
            $account_update= $stripe->accounts->update(
              $user_account->acc_token,
              [
                'business_profile' => ['mcc' => $request->mcc, 'url' =>$request->url],
                'company' => [
                  'address' => [
                    'city' => $request->city,
                    'line1' =>$request->line1,
                    'postal_code' =>$request->postal_code,
                    'state' => $request->state,
                  ],
                  'tax_id' => $request->tax_id,
                  'name' => $request->name,
                  'phone' => $request->phone,
                ],
                'company' => ['owners_provided' => true]
              ]
            );
            
            $account = $stripe->accounts->retrieve($user_account->acc_token);
            $requirements = $account->requirements;
            
            if(!empty($requirements['currently_due']))
            {
              return response()->json([
                'code' => 401,
                'message' => 'company added successfully please verify personal Information' ,
                'required' => $requirements['currently_due'][0],
              ]);
            } 
          }
          else
          {
            return response()->json([
              'code' => 401,
              'message' => 'You have no account please first create account',
            ]);
          }
          
        }
        catch(\Throwable $th)
        {
           return response()->json(['code' =>500, 'error' => $th->getMessage()]);
        }
    }

    public function verifyPersonalInfo($request)
    {
        try
        {
          
          $validatedData = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'city' => 'required',
            'line1' => 'required',
            'postal_code' => 'required',
            'state' => 'required',
            'dob.*' => 'required',
            'ssn_last_4' => 'required',
            'phone' => 'required',
            'email' => 'required',
          ]);
    
          if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
          }
         
          // $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET')); //acct_1MrNfkRiTZvaxTZ6
          $user  = auth()->user();
          $user_account = UserAccount::where('user_id', $user->id)->first();
          $dob=Carbon::parse($request->dob)->format('d/m/Y');
          $dob = explode('/',$request->dob);
          $account = $stripe->accounts->retrieve($user_account->acc_token);
    
          if($user_account->person_id == null)
          {
            $person_info = $stripe->accounts->createPerson(
              $user_account->acc_token,
              [
                'first_name' =>$request->first_name,
                'last_name' => $request->last_name,
                'relationship' => ['representative' => true,'owner' => true,  'title' => $request->title],
                'address' => [
                  'city' => $request->city,
                  'line1' => $request->line1,
                  'state' => $request->state,
                  'postal_code' => $request->postal_code,
                
                ],
                'dob' => ['day' => $dob[0], 'month' => $dob[1], 'year' => $dob[2]],
                'ssn_last_4' => $request->ssn_last_4,
                'phone' => $request->phone,
                'email' => $request->email,
              ]
            );
            $user_account->person_id = $person_info->id;
          }
          else
          {
          
            $stripe->accounts->updatePerson(
            $user_account->acc_token,
            $user_account->person_id,
            [
              'first_name' =>$request->first_name,
              'last_name' => $request->last_name,
              'relationship' => ['representative' => true,'owner' => true,  'title' => $request->title],
              'address' => [
                'city' => $request->city,
                'line1' => $request->line1,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
              
              ],
              'dob' => ['day' => $dob[0], 'month' => $dob[1], 'year' => $dob[2]],
              'ssn_last_4' => $request->ssn_last_4,
              'phone' => $request->phone,
              'email' => $request->email,
            ]);  
          }
          
          $account = $stripe->accounts->retrieve($user_account->acc_token);
          $requirements = $account->requirements;
         
          if(!empty($requirements['currently_due']))
          {
            return response()->json([
              'code' => 401,
              'message' => 'You hava already accounts please verify the account' ,
              'required' => $requirements['currently_due'],
            ]);
          }
          else
          {
            $user_account->status = true;
             $user_account->update();
             return response()->json([
              'code' => 200,
              'message' => 'Your account is successfully verified' ,
              'required' => $requirements['currently_due'],
            ]);
          }
        }  
        catch(\Throwable $th)
        {
         return response()->json(['code' =>500, 'error' => $th->getMessage()]);
        }
    }

    public function generateToken()
    {
      // Set your test secret key
      $stripe = new StripeClient(env('STRIPE_SECRET'));
      $token = $stripe->tokens->create([
          'card' => [
              'number' => '4000000000000002', 
              'exp_month' => 12,
              'exp_year' => 2023,
              'cvc' => '123',
          ],
      ]);

      return [ 'token' => $token->id];
    }

}