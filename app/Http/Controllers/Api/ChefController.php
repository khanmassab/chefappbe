<?php

namespace App\Http\Controllers\Api;

use Stripe\Charge;
use Stripe\Stripe;
use App\Models\User;
use Aws\S3\S3Client;
use Stripe\Transfer;
use App\Models\Recipe;
use App\Models\ChefInfo;
use Stripe\StripeClient;
use App\Models\TimeSlots;
use App\Models\ChefPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


use Stripe\PaymentIntent;

class ChefController extends Controller
{
    public function storeRecipe(Request $request)
    {
        $user = Auth::user();
        if (!$user->is_chef) {
            return response()->json(['code'=> 401, 'message' => 'Unauthorized']);
        }

        $validator = Validator::make($request->all(), [
            'recipe_name' => 'required|string|max:255',
            'recipe_requirements' => 'required|string',
            'recipe_video' => 'required',
            'image' =>'required|image',
            'cooking_style_id' => 'required|string',
            'is_draft' => 'required|string'
        ]);
    
     
        if ($validator->fails()) {
            return response()->json(['code' => 422, 'message' => $validator->errors()->first()]);
        }
        if(!auth()->user()->chefInfo)
        {
            return response()->json([
                'status' =>403,
                'error' => 'please first add information',
            ]);
        }
        $recipeData = [
            'chef_info_id' => auth()->user()->chefInfo->user_id,
            'recipe_name' => $request->input('recipe_name'),
            'recipe_requirements' => $request->input('recipe_requirements'),
            'chef_info_id' => $user->id,
            'is_draft' => intval($request->is_draft),
            'cooking_style_id' => intval($request->cooking_style_id)
        ];

        
        if ($request->hasFile('recipe_video')) {
            
            try {
                $filename = (time()+ random_int(100, 1000));
                $extension = $request->recipe_video->getClientOriginalExtension();
                $filename = $filename . '.' . $extension;

                $filePath = 'recipe_videos/' . $filename;
                $path = Storage::disk('s3')->put($filePath, file_get_contents($request->recipe_video));
                $path = Storage::disk('s3')->url($filePath);
                $recipeData['recipe_video']  = $path;
                // dd($recipeData['recipe_video'] );

            } catch (Exception $e) {
                dd('Error storing recipe video: ' . $e->getMessage());
            }
        } else {
            dd('No recipe video found in request');
        }

        if($request->has('image')){
            $filename = (time()+ random_int(100, 1000));
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $filename . '.' . $extension;

            $filePath = 'recipe_images/' . $filename;
            $path = Storage::disk('s3')->put($filePath, file_get_contents($request->file('image')));
            $path = Storage::disk('s3')->url($filePath);

            $recipeData['image'] = $path;
        }

        
       $recipe = Recipe::create($recipeData);
        if($recipe){
            return response()->json(['code'=> 200, 'message' => 'Recipe created successfully', 'recipe' => $recipeData]);
        }

        return response()->json(['code'=> 500, 'message' => 'Recipe cannot be added']);
    }

    public function updateRecipe(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->is_chef) {
            return response()->json(['code'=> 401, 'message' => 'Unauthorized']);
        }

        $recipe = Recipe::where('id', $id)->where('chef_info_id', auth()->user()->chefInfo->user_id)->first();
        if (!$recipe) {
            return response()->json(['code'=> 404, 'message' => 'Recipe not found']);
        }

       
        if(!auth()->user()->chefInfo)
        {
            return response()->json([
                'status' =>403,
                'error' => 'please first add information',
            ]);
        }
        
        $recipeData = [
            'chef_info_id' => auth()->user()->chefInfo->user_id,
            'recipe_name' => $request->input('recipe_name', $recipe->recipe_name),
            'recipe_requirements' => $request->input('recipe_requirements', $recipe->recipe_requirements),
            'is_draft' => intval($request->input('is_draft', $recipe->is_draft)),
            'cooking_style_id' =>   intval($request->input('cooking_style_id', $recipe->cooking_style_id))
        ];

        if ($request->hasFile('recipe_video')) {
            $recipeVideo = $request->file('recipe_video');
            $oldVideo = $recipe->recipe_video;

            try {
                $filename = (time()+ random_int(100, 1000));
                $extension = $request->recipe_video->getClientOriginalExtension();
                $filename = $filename . '.' . $extension;
                $filePath = 'recipe_videos/' . $filename;

                $path = Storage::disk('s3')->put($filePath, file_get_contents($request->recipe_video));
                $path = Storage::disk('s3')->url($filePath);
                $recipeData['recipe_video']  = $path;

                // $recipeData['recipe_video'] = Storage::putFile('/recipe_videos', $recipeVideo);
                if ($oldVideo) {
                    Storage::delete($oldVideo);
                }
            } catch (Exception $e) {
                return response()->json(['code' => 500, 'message' => 'Error storing recipe video']);
            }
        }

        if ($request->hasFile('image')) {
            $filename = (time()+ random_int(100, 1000));
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $filename . '.' . $extension;

            $filePath = 'recipe_images/' . $filename;
            $path = Storage::disk('s3')->put($filePath, file_get_contents($request->file('image')));
            $path = Storage::disk('s3')->url($filePath);

            $recipeData['image'] = $path;
        }
        

        $recipe->update($recipeData);

        return response()->json(['code'=> 200, 'message' => 'Recipe updated successfully', 'recipe' => $recipe->fresh()]);
    }


    public function deleteRecipe($id)
    {
        $user = Auth::user();
        if(!$user->chefInfo)
        {
            return response()->json([
                'status' =>403,
                'error' => 'please first add information',
            ]);
        }
        $recipe = $user->chefInfo->recipes()->find($id);

        // return $recipe;

        if (!$recipe) {
            return response()->json(['code'=> 401, 'message' => 'Unauthorized']);
        }

        if($recipe){
            if($recipe->recipe_video){
                Storage::delete($recipe->recipe_video);
            }
            $recipe->delete();
            return response()->json(['code'=> 200, 'message' => 'Recipe deleted successfully']);
        }

        return response()->json(['code'=> 200, 'message' => 'Recipe not found']);
    }

    public function getTimeSlot(){
        $user = auth()->user();
        if($user->is_chef == 1)
        {
            if($user->chefInfo)
            {
                $timeSlot = $user->chefInfo->timeSlots;
                return response()->json([
                    'code' => 200,
                    'message' => 'success',
                    'time_slots' => $timeSlot
                ]);
            }
            else
            {
                return response()->json([
                    'code' => 200,
                    'message' => 'Please  first complete your profile'
                ]);
            }
            
        }
        else
        {
            return response()->json([
                'code' => 200,
                'message' => 'you are not chef'
            ]);
        }
       
    }
    public function addTimeSlot(Request $request)
    {  

        $from_date = $request->date;

        if($request->to_time == '00:00' || $request->to_time == '24:00'){
            $request->to_time = '23:59';
        }

        $validatedData = Validator::make($request->all(), [
            'from_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($from_date) {
                    $fromDateTime = Carbon::createFromFormat('Y-m-d H:i', $from_date.' '.$value);
        
                    $nowDateTime = Carbon::now();
                    if ($fromDateTime->gt($nowDateTime)) {
                        // Time is in the future, validation passes
                        return;
                    }
                    $fail('The time has passed');
                }
            ],
            'to_time' => 'required|date_format:H:i|after:from_time',
            'date'   => 'required|after_or_equal:today|date_format:Y-m-d',
        ]);
        
        
        if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
        }
        $user = auth()->user();
        if($user->is_chef == 0)
        {
            return response()->json(['code' => 404, 'error' => 'you are not chef']);
        }
        
        $from_time = $request->from_time;
        $to_time = $request->to_time;
           
        $time_slot=TimeSlots::where(['chef_info_id' => auth()->user()->chefInfo->user_id, 'date'=> $request->date])->get();
        foreach($time_slot as $time)
        {
            $from_time_db=Carbon::parse($time['from_time'])->format('H:i');
            $to_time_db=Carbon::parse($time['to_time'])->format('H:i');
        
            if($from_time <= $from_time_db &&  $to_time >= $from_time_db)
            {
            return response()->json([
                    'code' => 404,
                    'error' => 'this time is not available',
            ]);
            }
            elseif($from_time <= $to_time_db && $to_time_db <= $to_time)
            {
            return response()->json([
                    'code' => 404,
                    'error' => 'this time is not available',
            ]);
            }
        }

        if($user->chefInfo)
        {
            $timeSlot = TimeSlots::create([
                'chef_info_id' => $user->chefInfo->user_id,
                'from_time' =>$request->from_time,
                'to_time' =>   $request->to_time,
                'date' => $request->date,
            ]);
    
            return response()->json(['code' => 200, 'message' => 'Time Slot created Successfully','data' =>  $timeSlot]);
        }
        else
        {
            return response()->json(['code' => 404, 'error' => 'ChefInfo is required to be filled']);  
        }
    }

    public function updateTimeSlot(Request $request, $id){

        $validatedData = Validator::make($request->all(), [
            'from_time' => 'required|date_format:H:i',
            'to_time' => 'required|date_format:H:i|after:from_time',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
        }
        $user = auth()->user();
     
        $check_time_slote=TimeSlots::where('chef_info_id' ,$user->id)->where('id','!=',$id)->get(); 

        foreach($check_time_slote as $time)
        {
            $from_time_db=Carbon::parse($time['from_time'])->format('H:i');
            $to_time_db=Carbon::parse($time['to_time'])->format('H:i');
           
            if($request->from_time <= $from_time_db &&  $request->to_time >= $from_time_db)
            {
               return response()->json([
                    'code' => 200,
                    'message' => 'This time is not available',
               ]);
            }
            elseif($request->from_time <= $to_time_db && $to_time_db <=  $request->to_time)
            {
               return response()->json([
                    'code' => 200,
                    'message' => 'This time is not available',
               ]);
            }
        }

        $chef_info=$user->chefInfo;
        $time_availible=TimeSlots::where(['id' => $id,'chef_info_id' => $chef_info->user_id])->first();
          if(!$time_availible && $time_availible->available == 'unavailable')
            {
                return response()->json(['code' => 200, 'You can not update this time becouse this date and time are booked']);
            }

        $timeSlot = TimeSlots::where(['id' => $id, 'chef_info_id' => $chef_info->user_id])->update([
            'chef_info_id' => $chef_info->id,
            'from_time' => $request->input('from_time'),
            'to_time' => $request->input('to_time')
        ]);
       
        $timeSlots= $time_availible;
        
        if(!$timeSlots){
            return response()->json(['code' =>  200, 'message' => 'You are not authorized to update the Time Slot']);
        }

     

        return response()->json(['code' =>  200, 'Time Slot Updated Successfully', 'data' => $timeSlots]);
    }    
    
    public function deleteTimeSlot(Request $request){

        try{
            $user = auth()->user();
            $chef_info=$user->chefInfo;
            if($chef_info)
            {
                $time_availible=TimeSlots::where(['id' => $request->id, 'chef_info_id' => $chef_info->user_id])->first();
                if($time_availible)
                {
                    if($time_availible->available == 'unavailable')
                    {
                        return response()->json(['code' => 200, 'You can not delete this time becouse this date and time are booked']);
                    }
                    else
                    {
                      $time_availible->delete();
                      return response()->json(['code' =>  200, 'message' => 'Time Slot Deleted Successfully', 'data' => $time_availible]);
                    }
                }
                else
                {
                    return response()->json(['code' =>  200, 'message' => 'no data found']);
                }
            }
            else
            {
                return response()->json(['code' =>  200, 'message' => 'You have no chef information']);
            }
        } catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'enternal server error.']);
        }
        
      
    }

    public function viewRecipe($id)
    {
        // $recipe = Recipe::findOrFail($id);
        // $chefInfo = $recipe->chefInfo;

        $recipe = Recipe::find($id);
        $chefInfo = $recipe->chefInfo;

        if($recipe){
            return response()->json([
                'code' => 200,
                'data' => $recipe,
            ]);
        }

        return response()->json(['code' => 200, 'message' => 'Either the Recipe has been deleted or is not available']);
    }
    
    public function getAddedRecipies()
    {
    // dd(auth()->user()->chefInfo);
        if(auth()->user()->chefInfo)
        {
            $recipes = Recipe::where('chef_info_id', auth()->user()->chefInfo->user_id)->where('is_draft', 0)->with('chefInfo.user','chefInfo.cookingStyle')->get();
        }
        else
        {
            return response()->json([
                'status' =>403,
                'error' => 'please first add information',
            ]);
        }
       

        return response()->json([
            'code' => 200,
            'message' => 'added recipes',
            'chef_recipes' => $recipes,
        ]);
    }
     
     public function getDraftedRecipies()
    {    
        if(auth()->user()->chefInfo)
        {
        $added_receipes = Recipe::where('chef_info_id', auth()->user()->chefInfo->user_id)->where('is_draft', 1)->with('chefInfo.user','cookingStyle')->get();
        }
        else
        {
            return response()->json([
                'status' =>403,
                'error' => 'please first add information',
            ]);
        }
        return response()->json([
        'code' => 200,
            'message' => 'added recipes',
            'chef_recipes' => $added_receipes,
        ]);
    }


    public function chefCharge(Request $request)
    {
        try
        {
            $validatedData = Validator::make($request->all(), [
                "token"        =>'required',
            ]);
            
            if ($validatedData->fails())
            {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }
            

            $stripe = new StripeClient(env('STRIPE_SECRET'));
            $user = User::find(auth()->id());
            if($user)
            {
                if($user->is_chef == 0)
                {
                    return response()->json([
                        'code' => 401,
                        'error' => "You can not charge amount becouse you are user",
                    ]);
                }
                $payment =ChefPayment::where('user_id' ,$user->id)->where('due', 1)->first();
                if(!$payment)
                {
                    Stripe::setApiKey(env('STRIPE_SECRET'));
                   
                    $token = $request->token;
                    $charge_amount = 10;
    
                    $charges = $stripe->charges->create([
                        'amount' => $charge_amount * 100,
                        'currency' => 'usd',
                        'source' => $token,
                        'description' => "Charging Fee from Chef",
                    ]);

    
                    $amount = ($charges->amount/100);
                    $stripe_percentage = ( $amount * 0.029) + 0.30;
                    $net_amount = $amount-$stripe_percentage;
                   
                    $transfer = Transfer::create([
                        'amount' => $net_amount *100,  // amount in cents
                        'currency' => $charges->currency,
                        'destination' =>  config('global.AppAccount'),
                        'source_transaction' =>$charges->id,
                        'description' => 'Application fee transfer for Charge ID: ' . $charges->id,
                    ]);
                    
    
                    if($charges->id)
                    {  
                       $payment= ChefPayment::create([
                            'charge_id' => $charges->id,
                            'user_id' => $user->id,
                            'amount' => $request->amount,
                            'due' => 0,
    
                        ]);

                        return response()->json([
                            'code' => 200,
                            'data' => $payment,
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
                        'code' => 409,
                        'message' => 'You are already charged',
                    ]); 
                }

            }
            else
            {
                return response()->json([
                    'code' => 404,
                    'error' => 'This User Does not exists'
                ]);
            }
        }
        catch (\Throwable $th) {
            // throw $th;
            return response()->json(['code' => 500, 'message' => 'Something Went Wrong']);
        }
    }
}
