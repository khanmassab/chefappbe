<?php

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Stripe\AccountController;
use App\Http\Controllers\Admin\AdminController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();
Route::get('/', function(){
  if(auth()->check()){
    return view('admin.dashboard');
  }

  else{
    return redirect()->route('login');
  }
})->name('home');

// Password Reset Routes
Route::group(['middleware' => ['auth']], function () {
  
  Route::post('/chef/verify/{id}', function($id){
    $get_chef = User::findOrFail($id);
    
    if($get_chef){
      $get_chef->verified_by_admin = 1;
      $get_chef->save();
      
      return back();
    }
  })->name('/chef/verify');
  
  
  Route::get('/user-management',[AdminController::class,'getChefs'])->name('user-management');
  Route::get('/user-bookings',[AdminController::class,'getBookings'])->name('user-bookings');
  Route::get('/payments', [AdminController::class,'getAllPayments'])->name('payments.index');
  Route::get('/recipes', [AdminController::class,'getAllRecipes'])->name('recipes.index');
  Route::get('/recipes/{recipe}', [AdminController::class, 'showRecipe'])->name('recipes.show');
  Route::get('/archive/user/{user}', [AdminController::class, 'archiveUser'])->name('archive.user');
  Route::delete('/recipe/{recipe}', [AdminController::class, 'deleteRecipe'])->name('delete.recipe');
  Route::post('/logout',[LoginController::class,'logout'])->name('logout');
});

Route::get('forgot-password', 'App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('forgot-password', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('reset-password/{token}', 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('reset-password', 'App\Http\Controllers\Auth\ResetPasswordController@reset')->name('password.update');


Route::post('stripe-account-verify', 'app/Http/Controllers/Api/WebHookController@handleStripeWebhook');

// Auth::routes();

//stripe account

Route::get('create_account/{id}',[AccountController::class,'createAccount'])->name('create_account');
Route::post('store_account',[AccountController::class,'storeAccount']);
Route::get('create_company',[AccountController::class,'createCompany']);
Route::get('personal_info',[AccountController::class,'personalInfo']);
Route::get('personal_info',[AccountController::class,'personalInfo']);
