<?php

namespace App\Http\Controllers\Api;

use File;
use Carbon\Carbon;
use Stripe\Charge;
use Stripe\Person;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\Transfer;
use App\Models\BookChef;
use Stripe\StripeClient;
use App\Models\UserAccount;
use App\Models\UserPayment;
use Illuminate\Http\Request;
use App\Services\StripeService;
use Stripe\AccountExternalAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



class StripePaymentController extends Controller
{
  public function chargeAmount(Request $request)
  {
    $charge_amount = new StripeService();
    return $charge_amount->charge($request);
  }

  public function transferAmount(Request $request)
  {

    $transfer_amount = new StripeService();
    return $transfer_amount->transferAmountToChef($request);
  }
 
  public function RefundCharge(Request $request)
  {
    $transfer_amount = new StripeService();
    return $transfer_amount->refundcharge($request);
  }

  public function createAccount(Request $request)
  {
    $transfer_amount = new StripeService();
    return $transfer_amount->addAccount($request);
  }
 
  public function verifyCompany(Request $request)
  {
    $transfer_amount = new StripeService();
    return $transfer_amount->verifyCompany($request);
  }
  
  public function VerifyPersonalInfo(Request $request)
  {
   
    $transfer_amount = new StripeService();
    return $transfer_amount->verifyPersonalInfo($request);
  }
  
  public function generateToken()
  {
   
    $token = new StripeService();
    return $token->generateToken();
  }
   
}
