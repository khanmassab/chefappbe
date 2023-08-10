<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChefController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\VideoCallController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\SQLController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-passwd', [AuthController::class, 'resetPassword']);
Route::get('/picture/{filename}', [ProfileController::class, 'getProfilePicture']);
Route::get('/recipe_videos/{filename}', [ProfileController::class, 'getRecipeVideo']);
Route::post('/login_with_social', [AuthController::class, 'loginwithSocial']);
Route::get('/perform_sql', [SQLController::class, 'index']);
Route::group(['middleware' => 'guest'], function(){
   
    Route::group(['middleware' => 'auth:api'], function(){
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::post('/logout', [AuthController::class, 'logout']);
        //Chef Specific
        Route::post('/chef/chef_info', [ProfileController::class, 'postChefInfo']);
        Route::post('/chef/update_chef_info', [ProfileController::class, 'updateChefInfo']);
    
        Route::post('/chef/recipe', [ChefController::class, 'storeRecipe']);
        Route::post('/chef/recipe/{id}', [ChefController::class, 'updateRecipe']);
        Route::delete('/chef/recipe/{id}', [ChefController::class, 'deleteRecipe']);
        
        Route::get('/view_recipe/{id}', [ChefController::class, 'viewRecipe']);
        Route::post('/favorite_recipe/{recipeId}', [ProfileController::class, 'favouriteRecipe']);
        Route::get('/get_favorite_recipe', [ProfileController::class, 'getFavouriteRecipe']);
    
        Route::post('/add_time_slots', [ChefController::class, 'addTimeSlot']);
        Route::get('/time_slots', [ChefController::class, 'getTimeSlot']);
        Route::post('/time_slots/{id}', [ChefController::class, 'updateTimeSlot']);
        Route::post('/delete_time_slots', [ChefController::class, 'deleteTimeSlot']);
        
        Route::get('/get_added_recipes', [ChefController::class, 'getAddedRecipies']);
        Route::get('/get_drafted_recipies', [ChefController::class, 'getDraftedRecipies']);
        
        //All Users
        Route::post('/update_passwrd', [AuthController::class, 'updatePassword']);
        Route::post('/upload_profile_picture', [ProfileController::class, 'uploadProfilePicture']);
        Route::get('/get_profile', [ProfileController::class, 'getProfile']);
        Route::delete('/delete_certificate/{id}', [ProfileController::class, 'deleteCertificate']);
        Route::post('/add_message_to_support', [ProfileController::class, 'addMessageToSupport']);
       
        
        Route::post('/post_strype', [ProfileController::class, 'postStrype']);
        Route::post('/complete_stripe_amount', [ProfileController::class, 'CompleteStripeAmount']);
        Route::post('/update_user_profile', [ProfileController::class, 'updateUserProfile']);
        Route::post('/user_device_token', [AuthController::class, 'UserDeviceToken']);
        Route::get('/get_user_notification', [ProfileController::class, 'getUserNotification']);
        Route::post('/update_user_interest', [ProfileController::class, 'updateInterests']);
    
        //video call Api
        Route::post('/create_seesion', [VideoCallController::class, 'createSeesion']);
        //stripe integration
    
        Route::post('/charge_amount', 'App\Http\Controllers\Api\StripePaymentController@chargeAmount');
        Route::post('/transfer_amount', 'App\Http\Controllers\Api\StripePaymentController@transferAmount');
        Route::post('/refund_charge', 'App\Http\Controllers\Api\StripePaymentController@RefundCharge');
        Route::post('/create_account', 'App\Http\Controllers\Api\StripePaymentController@createAccount');
        Route::post('/verifiy_account', 'App\Http\Controllers\Api\StripePaymentController@verifyCompany');
        Route::post('/verify_personal_info', 'App\Http\Controllers\Api\StripePaymentController@verifyPersonalInfo');
    
        //convert to chef 
    
        Route::post('/converted_to_chef', [AuthController::class, 'convertedToChef']);
       
        // Route::post('/refund_charge', 'App\Http\Controllers\Api\StripePaymentController@RefundCharge');   
    
        // Route::get('/explore_chefs_and_recipes', [ProfileController::class, 'exploreChefsAndRecipes']);
        // Route::get('/search_recepies', [ProfileController::class, 'searchRecepies']);
        // Route::get('/search_chefs', [ProfileController::class, 'searchChef']);
        // Route::post('/filter_chefs', [ProfileController::class, 'exploreChefsByCookingStyles']);
        // Route::post('/explore_chef', [ProfileController::class, 'exploreChef']);
        // Route::get('/cooking_style', [ProfileController::class, 'cookingStyle']);
        // Route::post('/book_chef', [ProfileController::class, 'bookChef']);  
        // Route::get('/get_chefs', [ProfileController::class, 'getChefs']);
        // Route::get('/get_recipes', [ProfileController::class, 'getRecipes']);
        Route::post('/book_chef', [ProfileController::class, 'bookChef']);  
        Route::get('/get_user_bookings', [ProfileController::class, 'getUserBookings']);  
        Route::get('/get_chef_bookings', [ProfileController::class, 'getChefBookings']);  
        Route::post('/get_booking_details', [ProfileController::class, 'getBookingDetails']);  
        // Route::get('/get_chefs', [ProfileController::class, 'getChefs']);

        //chefCharge amount
        Route::post('/chef_payment', [ChefController::class, 'chefCharge']);  

        
        //fcm token
        Route::post('/add_fcm_token', [AuthController::class, 'addFcmToken']);
        Route::post('/call_end_request', [VideoCallController::class, 'sendSilentNotification']);
        
        Route::post('/send_voip_adnroid', [VideoCallController::class, 'sendVoipAndroid']);

        //complete session api
        Route::post('/complete_session', [VideoCallController::class, 'completeSession']);
        
    });
    
    Route::get('/generate_token', [App\Http\Controllers\Api\StripePaymentController::class, 'generateToken']);
    Route::get('/get_chefs', [ProfileController::class, 'getChefs']);
    Route::post('/chef_time_availibality', [ProfileController::class, 'ChefTimeAvailibality']);
    Route::post('/notify', [NotificationController::class, 'sendNotification']);
    Route::get('/explore_chefs_and_recipes', [ProfileController::class, 'exploreChefsAndRecipes']);
    Route::get('/search_recepies', [ProfileController::class, 'searchRecepies']);
    Route::get('/search_chefs', [ProfileController::class, 'searchChef']);

    Route::post('/filter_chefs', [ProfileController::class, 'exploreChefsByCookingStyles']);
    Route::post('/filter_recipes', [ProfileController::class, 'exploreRecipesByCookingStyles']);

    Route::post('/explore_chef', [ProfileController::class, 'exploreChef']);
    Route::get('/cooking_style', [ProfileController::class, 'cookingStyle']);
    Route::get('/get_recipes', [ProfileController::class, 'getRecipes']);

    //fcm token
    Route::post('/add_fcm_token', [AuthController::class, 'addFcmToken']);
    Route::post('/send_fcm_notification', [VideoCallController::class, 'fcmNotifcation']);
    Route::post('/send_voip_adnroid', [VideoCallController::class, 'sendVoipAndroid']);


    Route::get('/generate_token', [App\Http\Controllers\Api\StripePaymentController::class, 'generateToken']);
    
});

Route::post('/refreshToken', [AuthController::class, 'refreshToken']);

