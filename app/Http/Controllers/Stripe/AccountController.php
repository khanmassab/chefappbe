<?php

namespace App\Http\Controllers\Stripe;

use App\Models\User;
use GuzzleHttp\Client;
use Stripe\CountrySpec;
use App\Models\UserAccount;
use Laravel\Passport\Token;
use Illuminate\Http\Request;
use App\Services\StripeService;
use PeterPetrus\Auth\PassportToken;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Stripe\StripeClient;

class AccountController extends Controller
{
    public function createAccount($user_id)
    {
     
      
    $id = decrypt($user_id);
    
      
        $user_account= UserAccount::where('user_id',  $id)->first();
     
        if($user_account)
        {
          $stripe = new \Stripe\StripeClient('sk_test_51KGQvyD0VrSNKQlKY0ypUrxmbJDQaGU6sU4rQnvozHa5y8ceak3V0YsKuRg7gHiDhRV5rKmpF92c6Uof5FkgtHvE006hn5byBz');
          $check_account=$stripe->accounts->retrieve($user_account->acc_token);
          $requirements = $check_account->requirements['currently_due'];
          
          if(empty($requirements))
          {
            $user_account->status = true;
            $user_account->update();
          }
           
        
       
         $accountLink = $stripe->accountLinks->create(
         [
           'account' => $user_account->acc_token,
           'refresh_url' => route('create_account',$user_id),
           'return_url' => route('create_account',$user_id),
           'type' => 'account_onboarding',
         ]);
          
        }
        else
        {
          $stripe = new StripeClient('sk_test_51KGQvyD0VrSNKQlKY0ypUrxmbJDQaGU6sU4rQnvozHa5y8ceak3V0YsKuRg7gHiDhRV5rKmpF92c6Uof5FkgtHvE006hn5byBz');
         $account=$stripe->accounts->create(['type' => 'express']);
         $accountLink= $stripe->accountLinks->create(
          [
            'account' =>$account->id,
            'refresh_url' => route('create_account',$user_id),
            'return_url' => route('create_account',$user_id),
            'type' => 'account_onboarding',
          ]);
          $stripe = new \Stripe\StripeClient('sk_test_51KGQvyD0VrSNKQlKY0ypUrxmbJDQaGU6sU4rQnvozHa5y8ceak3V0YsKuRg7gHiDhRV5rKmpF92c6Uof5FkgtHvE006hn5byBz');
          $accoutn_create=UserAccount::create([
              'user_id' => $id,
              'acc_token' =>  $account->id,
            ]);
        }
      
         
    
        return view('stripe_account.create_account',compact('accountLink'));
      
    }
  

    public function storeAccount(Request $request)
    {

      $transfer_amount = new StripeService();
      return $transfer_amount->addAccount($request);
    }

    public function createCompany()
    {
        return view('stripe_account.verify_company');
    }

    public function verifyCompany(Request $request)
    {
      $transfer_amount = new StripeService();
      return $transfer_amount->verifyCompany($request);
    }

    public function personalInfo()
    {
      return view('stripe_account.personal_info');
    }

      public function VerifyPersonalInfo(Request $request)
    {
     
      $transfer_amount = new StripeService();
      return $transfer_amount->verifyPersonalInfo($request);
    }
}
