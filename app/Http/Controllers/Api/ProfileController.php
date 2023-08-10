<?php

namespace App\Http\Controllers\Api;

use File;
use Carbon\Carbon;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Account;
use App\Models\User;
use Stripe\Customer;
use Stripe\Transfer;

use App\Models\Recipe;
use App\Models\BookChef;
use App\Models\ChefInfo;
use Stripe\StripeClient;
use App\Events\UserEvent;
use App\Models\TimeSlots;
use App\Models\UserAccount;
use App\Models\UserPayment;
use App\Models\CookingStyle;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Certification;
use App\Models\SupportCenter;
use App\Models\FavoriteRecipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;


class ProfileController extends Controller
{
    public function postChefInfo(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'nationality' => 'required|string',
            'cooking_style' => 'required|string',
            'about' => 'required|string',
            'city' => 'required|string',
            'number_of_years_experience' => 'required',
            'certifications' => 'required|array',
            'certifications.*' => 'file|mimes:png,jpeg',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
        }

        $user = Auth::user();

        $chefInfo = $user->chefInfo()->updateOrCreate(['user_id' => $user->id], [
            'nationality' => $request->input('nationality'),
            'cooking_style_id' => (int)$request->input('cooking_style'),
            'about' => $request->input('about'),
            'city' => $request->input('city'),
            'number_of_years_experience' => (int)$request->input('number_of_years_experience'),
        ]);


        if ($request->hasFile('certifications')) {
            foreach ($request->file('certifications') as $certification) {
                $filename = time() . '_' . uniqid();
                $extension = $certification->getClientOriginalExtension();
                $filename = $filename . '.' . $extension;
            
                $filePath = 'certifications/' . $filename;
                $path = Storage::disk('s3')->put($filePath, file_get_contents($certification));
                $path = Storage::disk('s3')->url($filePath);
             
                $cert = new Certification;
                $cert->certification_proof = $filename;
                $cert->orignal_name = $path;
                $cert->chef_info_id = auth()->id();
                $cert->save();
            }            

            // dd($cert);
        }
            $chefInfo['certifications'] = $chefInfo->certifications;
        if($chefInfo){
            return response()->json(['code'=> 200, 'message' => 'Cheff Info Updated Successfully', 'data' => ['chef_info' => $chefInfo]]);
        }

        return response()->json(['code'=> 500, 'message' => 'Cheff Info update failed']);        
    }

    public function updateChefInfo(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'nationality' => 'nullable|string',
            'cooking_style' => 'nullable|string',
            'about' => 'nullable|string',
            'city' => 'nullable|string',
            'number_of_years_experience' => 'nullable',
            'certifications' => 'nullable|array',
            'certifications.*' => 'file|mimes:png,jpeg',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
        }   

        $user = Auth::user();
        $chefInfo = $user->chefInfo;

        if (!$chefInfo) {
            return response()->json(['code' => 404, 'message' => 'Chef Info not found']);
        }

        if ($request->has('nationality')) {
            $chefInfo->nationality = $request->input('nationality');
        }

        if ($request->has('cooking_style')) {
            $chefInfo->cooking_style_id = (int)$request->input('cooking_style');
        }

        if ($request->has('about')) {
            $chefInfo->about = $request->input('about');
        }
        
        if ($request->has('city')) {
            $chefInfo->city = $request->input('city');
        }
        if ($request->has('number_of_years_experience')) {
            $chefInfo->number_of_years_experience = (int)$request->input('number_of_years_experience');
        }

        $updated = $chefInfo->save();

        if ($updated && $request->hasFile('certifications')) {
            foreach ($request->file('certifications') as $certification) {
                $filename = time() . '_' . uniqid();
                $extension = $certification->getClientOriginalExtension();
                $filename = $filename . '.' . $extension;
            
                $filePath = 'certifications/' . $filename;
                $path = Storage::disk('s3')->put($filePath, file_get_contents($certification));
                $path = Storage::disk('s3')->url($filePath);

                $cert = new Certification;
                $cert->certification_proof = $filename;
                $cert->orignal_name = $path;
                $cert->chef_info_id = auth()->id();
                $cert->save();
            }
        }

        $chefInfo->refresh();

        return response()->json([
            'code' => $updated ? 200 : 500,
            'message' => $updated ? 'Chef Info Updated Successfully' : 'Chef Info update failed',
            'data' => ['chef_info' => $chefInfo]
        ]);
    }



    public function deleteCertificate($id)
    {
        try{
            $chefinfo_id=ChefInfo::where('user_id', auth()->id())->first()->id;
            // dd($chefinfo_id);
            $certifecate= Certification::where(['id' => $id, 'chef_info_id' => auth()->id()]);
            if($certifecate)
            {
                $certifecate->delete();
                return response()->json(['code' => 200, 'message' => 'Certificate Deleted Succefully' ]);
            }
            else
            {
                return response()->json(['code' => 200, 'message' => 'Certificate Not Exists' ]);
            }
        }
        catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'enternal server error.']);
        }
    }
    
    public function uploadProfilePicture(Request $request)
    {
        try {
            $user = Auth::user();
            if($request->hasFile('profile_picture')) {

                $filename = (time()+ random_int(100, 1000));
                $extension = $request->file('profile_picture')->getClientOriginalExtension();
                $filename = $filename . '.' . $extension;

                $filePath = 'profile_pictures/' . $filename;
                $path = Storage::disk('s3')->put($filePath, file_get_contents($request->file('profile_picture')));
                $path = Storage::disk('s3')->url($filePath);

                $user->profile_picture = $path;
                $user->save();

                if($path && $user){
                    return response()->json(['code'=> 200, 'message' => 'Profile picture uploaded successfully', 'data' => ['profile_picture' => $path]]);
                }
            } 
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function updateUserProfile(Request $request)
    {
        $validatedData = Validator::make($request->all(),[
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
        } 
        $user = Auth::user();
        
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        if($user){
            return response()->json(['code'=> 200, 'message' => 'Profile picture uploaded successfully', 'data' => ['user_profile' => $user]]);
        }
    }

    public function getProfilePicture($filename)
    {
        $path = storage_path('app/profile_pictures/' . $filename);

        if (!File::exists($path)) {
            $path = storage_path('app/public/recipe_image/' . $filename);

            if (!File::exists($path)) {
                return response()->json(['code' => '404', 'message' => 'Image Not Found. or You are not authorized to access this image.']);
                abort(404);
            }
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
    
    public function getRecipeVideo($filename)
    {
        $path = storage_path('app/recipe_vidoes/' . $filename);

        if (!File::exists($path)) {
            return response()->json(['code' => '404', 'message' => 'Video Not Found. or You are not authorized to access this image.']);
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
    
        $response->header('Accept-Ranges', 'bytes');    
        return $response;
    }

    public function getProfile(Request $request)
    {
        // $user = $request->session()->get('user');
        $user = Auth::user();
        $user->load(['chefPayment'=> function($q){
            $q->select(['id', 'user_id', 'amount', 'due']);
        }]);

        
        $response = ['user' => $user];
        
        if($user->is_chef == 1)
        {
            $chefInfo = $user->chefInfo;
            if(!$chefInfo){
                return response()->json(['code' => 412, 'error' => 'ChefInfo is required to be filled']);
            }

            $chef_info = $user->chefInfo ? $user->chefInfo->certifications : [];
            $cooking_style = $user->chefInfo ? $user->chefInfo->cookingStyle : [];
            $user['chef_info'] = $user;

            $user_account = UserAccount::where('user_id', $user->id)->first();

            if($user_account)
            {
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));


                $account = $stripe->accounts->retrieve($user_account->acc_token);
                $requirements = $account->requirements;

                if (!empty($requirements['currently_due'])) {
                    $account_status = 0;
                    $reason = $requirements['currently_due'][0] . ' is not yet verified.';
                    $response['chef_stripe'] = [ 'account_status' => $account_status, 'reason' => $reason, 'stripe_info' => null ];
                } else {
                    $account_status = 1;
                    $user_account->status = true;
                    $user_account->save();
                    $reason = '';
                    $stripe_info=$account->external_accounts->data;
                    
                    $response['chef_stripe'] = [ 'account_status' => $account_status, 'reason' => $reason , 'stripe_info' => (object)$stripe_info[0]];
                }
            }

            else{
                $account_status = 0;
                $reason = "You haven't added your Stripe account yet";

                $response['chef_stripe'] = [ 'account_status' => $account_status, 'reason' => $reason , 'stripe_info' => null];

            }

                
        }
        
        return response()->json([
            'code' => 200,
            'message' => 'User profile',
            'data' =>  $response
        ]);
    }


    public function getChefs(Request $request)
    {
        try{

            if(auth('api')->check()){
                $user = auth('api')->user();
               
                if($user->interest == null && $user->is_chef ==0)
                {
                    $user_interest = ['1', '2' , '3', '4', '5', '6', '7'];
                }
                else
                {
                $user_interest = explode(',', $user->interest);
                $user_interest = array_map('intval', $user_interest);
                }
               
                // dd($user);
                if($user->is_chef == 1)
                {
                    $user_interest = ['1', '2' , '3', '4', '5', '6', '7'];
                }
            }
            

            if(!auth('api')->check()){
                $user_interest = ['1', '2' , '3', '4', '5', '6', '7'];
            }


          
            // dd($user_interest);  
            $chefs = User::where('is_chef', 1)
            ->whereHas('chefInfo', function($query) use($user_interest) {
                $query->whereIn('cooking_style_id', $user_interest);
                $query->where('user_id', '<>' ,auth()->id());
            })
            ->with(['chefInfo' => function($q) {
                $q->with('cookingStyle')
                  ->with(['timeSlots' => function($query) {
                      $query->where('status', '=', 'available')->latest();
                  }]);
            }])
           
            ->whereHas('chefInfo')
            ->where('id', '<>', auth()->id())
            ->whereHas('chefAccount',function($q){
                $q->where('status',true);
            })
            ->paginate($request->page_limit)
            ->toArray();

            // $chefs = User::where('is_chef', 1)
            //     ->whereHas('chefInfo', function($query) use($user_interest) {
            //         $query->whereIn('cooking_style_id', $user_interest);
            //         $query->where('user_id', '<>', auth()->id());
            //     })
            //     ->with(['chefInfo' => function($q) {
            //         $q->with('cookingStyle')
            //             ->with(['timeSlots' => function($query) {
            //                 $query->where('status', '=', 'available')->latest();
            //             }]);
            //     }])
            //     ->whereHas('chefInfo')
            //     ->where('id', '<>', auth()->id())
            //     ->whereHas('userAccount', function($query) {
            //         $query->where('status', true);
            //     })
            //     ->paginate($request->page_limit)
            //     ->toArray();
        
            return response()->json(['code' => 200,
             'message' => 'data fetched successfully', 
             'data' => [
                'current_page' => $chefs['current_page'],
                'chefs' => $chefs['data'],
                'next_page_url' => $chefs['next_page_url'],
                'total' => $chefs['total'],
                ]
            ]);
        }
        catch (\Throwable $th) {
            dd($th);
            return response()->json(['code' => 500, 'message' => 'internal server error.']);
        }
     }

     public function getRecipes(Request $request)
     {
        if(auth('api')->check()){
            $user = auth('api')->user();
            if($user->interest  == null && $user->is_chef == 0)
            {
                $user_interest = ['1', '2' , '3', '4', '5', '6', '7'];
            }
            else
            {
                $user_interest = explode(',', $user->interest);
                $user_interest = array_map('intval', $user_interest);
             
            }
            
           
            if($user->is_chef == 1)
            {
                $user_interest = ['1', '2' , '3', '4', '5', '6', '7'];
            }
        }
        

        if(!auth('api')->check()){
            $user_interest = ['1', '2' , '3', '4', '5', '6', '7'];
        }
        $recepies = Recipe::withCount(['is_favorited' => function ($q) use ($user_interest) {

            $q->select(DB::raw("if(count(*) > 0, 1, 0)"));
        }])
        ->whereIn('cooking_style_id', $user_interest)
        ->with(['chefInfo' => function ($q) {
            $q->with('user', 'cookingStyle');
        }])
        ->whereHas('chefAccount',function($q){
            $q->where('status',true);
        })
        ->paginate($request->page_limit)
        ->toArray();

    
    return response()->json([
        'code' => 200,
        'message' => 'data fetched successfully',
        'data' => [
            'current_page' => $recepies['current_page'],
            'recipes' => $recepies['data'],
            'next_page_url' => $recepies['next_page_url'],
            'total' => $recepies['total'],
            ]
    ]);
    
          
     }
    public function exploreChefsAndRecipes(Request $request)
    {

        $users = User::where('is_chef', 1)->where('id', '<>', auth()->id())->get();
    
        $chefs = [];
        $chefs_paginate = [];
        $recipes = [];
    
        foreach ($users as $user) {
            $chefInfo = $user->chefInfo;
            $cookingStyle = $chefInfo ? $chefInfo->cookingStyle : null;
            $recipeChef = $chefInfo ? $chefInfo->recipes : null;
            $certifications = $chefInfo ? $chefInfo->certifications : null;
    
            $chef_recipes = $chefInfo ? $chefInfo->recipes : null;
            $chef_recipes = $chefInfo ? $chefInfo->recipes()->with('chefInfo.user')->get() : null;
                
            if($chefInfo)
            {
               
                 if($request->page_limit != null)
                {
                    // $users = $this->paginate($user,$request->page_limit)->toArray();
                    $chefs[] = $user;
                    $chefs_paginate = $this->paginate($chefs,$request->page_limit)->toArray();
                } 
                else
                {
                    $chefs[] = $user;
                }   
            }

            if($chef_recipes){
                foreach ($chef_recipes as $recipe) {
                    $recipe['is_favorited'] = false;
                    if (auth('api')->check() && auth('api')->user()->favoriteRecipes->contains($recipe['id'])) {
                        $recipe['is_favorited'] = true;
                    }
                }
                if($request->page_limit != null)
                {
                    $recipes = array_merge($recipes, $chef_recipes->toArray());
                    $recipes_paginate  =$this->paginate($recipes,$request->page_limit)->toArray();
                }
                else
                {
                    $recipes = array_merge($recipes, $chef_recipes->toArray());
                }
            }
        }
   
        if($request->page_limit != null)
        {
            $data = [
                'current_page' => $chefs_paginate['current_page'], 
                'recipes' => $recipes_paginate['data'],
                'chefs' => $chefs_paginate['data'],
                'next_page_url' => $chefs_paginate['next_page_url'], 
                'total' => $chefs_paginate['total'],
            ];
        }
        else
        {
            $data = [
                'recipes' => $recipes,
                'chefs' => $chefs,
            ];
        }
        
        return response()->json(['code' => 200, 'message' => 'data fetched successfully', 'data' => $data]);
    }

    
    public function favouriteRecipe($recipeId)
    {
        $user = Auth::user();
        $recipe = $user->favoriteRecipes()->where('recipe_id', $recipeId)->first();
                
        if($recipe){
            $recipe->defavourite();
            return response()->json(['code' => 200, 'favorite' => false, 'message' => 'Recipe unfavorited.', 'data' => $recipe]);
        }

        $recipe = Recipe::find($recipeId);
        if(!$recipe){
            return response()->json(['code' => 200, 'message' => 'Recipe not found.']);
        }

        $recipe->favourite();
        return response()->json(['code' => 200, 'favorite' => true, 'message' => 'Recipe favorited.', 'data' => $recipe]);
    }

    public function exploreChefsByCookingStyles(Request $request)
    {
        try{
            $validatedData = Validator::make($request->all(), [
                "cooking_style_ids"        =>'required',
            ]);
    
        if ($validatedData->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }
        $cookingStyleIds = $request->input('cooking_style_ids');

        $chefs = User::whereHas('chefInfo',function($q) use ($cookingStyleIds){
            $q->whereHas('cookingStyle', function($query) use ($cookingStyleIds){
                $query->whereIn('id', $cookingStyleIds);
            });
        })
        ->with('chefInfo',function($q){
            $q->with('certifications', 'recipes','cookingStyle');
        })
        ->get();
        
        // $chefs = ChefInfo::whereHas('cookingStyle', function ($query) use ($cookingStyleIds) {
        //     $query->whereIn('id', $cookingStyleIds);
        // })->with('user', 'certifications', 'recipes')->get();

        return response()->json(['code' => 200,
         'message' => 'Data fetched successfully',
          'data' =>[
            'chefs' => $chefs,
        ]]);
        }
        catch (\Throwable $th) {
            dd($th);
            return response()->json(['code' => 500, 'message' => 'internal error.']);
        }
    }

    public function exploreRecipesByCookingStyles(Request $request)
    {
        try{
            $validatedData = Validator::make($request->all(), [
                "cooking_style_ids"        =>'required',
            ]);
    
        if ($validatedData->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }
        $cookingStyleIds = $request->input('cooking_style_ids');

        $recepies=Recipe::whereIn('cooking_style_id',$cookingStyleIds)
        ->with('chefInfo.user')
        ->take(10)->get();


        return response()->json(['code' => 200,
         'message' => 'Data fetched successfully',
          'data' =>[
            'recipes' => $recepies
        ],]);
        }
        catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'internal error.']);
        }
    }

    public function getFavouriteRecipe(Request $request){
        try {
            // $userId = $request->query('userId');
            
            $userId = auth()->id();
            $user = User::find($userId);
            $favoriteRecipes = $user->favoriteRecipes;
            
            if($favoriteRecipes){
                return response()->json(['code' => 200, 'message' => 'Favorite Recipes', 'data' => $favoriteRecipes]);
            }
            return response()->json(['code' => 500, 'message' => 'No recipe found.']);
        } catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'No recipe found.']);
        }
    }


    public function searchRecepies(Request $request)
    {
        $recipes=[];
        $recepes =[];
        $query = $request->input('query');
        $recipe = Recipe::withCount(['is_favorited' => function ($q){

            $q->select(DB::raw("if(count(*) > 0, 1, 0)"));
        }])
        ->Orwhere(DB::raw('lower(recipe_name)'),'Like', '%' . strtolower($query) . '%')
        // ->orWhere(DB::raw('lower(recipe_requirements)'),'Like', '%' . strtolower($query) . '%') 
            ->with('cookingStyle', 'chefInfo.user')->with('chefInfo', function($q){
                $q->with('certifications', 'cookingStyle');
            });
        if($request->page_limit !=null && !$request->page_limit==0)
        { 
          $recepes = $recipe->paginate($request->page_limit)->toArray();
        }
        else
        {
            $recepes = $recipe->get();
        }
        if($request->page_limit !=null  && !$request->page_limit==0)
        {
            return response()->json([
                'code' => 200,
                'message' => 'Search results',
                'data' => [
                    'current_page' => $recepes['current_page'] ?? null,
                    'recipes' => $recepes['data'] ?? [],
                    'next_page_url' => $recepes['next_page_url'] ?? null,
                    'total' => $recepes['total'] ?? null,
                    ]
            ]);
        }
        else
        {
            return response()->json([
                'code' => 200,
                'message' => 'Search results',
                'data' => [
                    'recipes' => $recepes ?? [],
                    ]
            ]);
        }   
       
    }
    
    public function searchChef(Request $request)
    { 
        $chefs=[];
        $chef_paginate=[];
        $query = $request->input('query');

        $chef = User::where('is_chef',1)->where(function($q) use ($query)
        {
            $q->orWhere(DB::raw('lower(first_name)'),'Like', '%' . $query . '%');
            $q->orWhere(DB::raw('lower(last_name)'),'Like', '%' . $query . '%');
            $q->orWhere(DB::raw("CONCAT(lower(first_name), ' ', lower(last_name))"),'Like', '%' . $query . '%');
            // $q->orWhere(DB::raw("(lower(first_name), ' ', lower(last_name))"),'Like', '%' . $query . '%');
        })
        ->where('id', '<>', auth('api')->id())
        ->whereHas('chefInfo')
        ->with('chefInfo',function($q){
            $q->with('certifications','cookingStyle');
        }); 
            
        if($request->page_limit != null && !$request->page_limit== 0 )
        {     
            $chef_paginate= $chef->paginate($request->page_limit)->toArray();    
        }
        else
        {
            $chefs_data = $chef->get();
        }

        if($request->page_limit != null  && !$request->page_limit==0 )
        {
            return response()->json([
                'code' => 200,
                'message' => 'Search results',
                'data' => [
                    'current_page' => $chef_paginate['current_page'] ?? null, 
                    'chefs' => $chef_paginate['data'] ?? [],
                    'next_page_url' => $chef_paginate['next_page_url'] ?? null, 
                    'total' => $chef_paginate['total'] ?? null,
                    ]
            ]);
        }  
        else
        {
            
            return response()->json([
                'code' => 200,
                'message' => 'Search results',
                'data' => [
                        'chefs' => $chefs_data,
                    ]
            ]);
        }
   }

    public function exploreChef(Request $request)
    {
        try{
            $id= $request->id;
            $chef = User::where(['id' => $id , 'is_chef' => 1])->with('chefInfo', function($q){
                $q->with('cookingStyle');
                $q->with('timeSlots',function($query){
                     $query->where('status','=','available')->latest();
                });
            })->first();
            // dd($chef);
           return response()->json([
            'code' => 200,
            'message' => 'Chef information',
            'data' =>  [
                'chefs' =>$chef,
            ],
        ]);
        } 
        catch (\Throwable $th) {
            dd($th);
            return response()->json(['code' => 500, 'message' => 'Chef not exist']);
        }
    }

    public function cookingStyle()
    {
        
        $cookingStyle=CookingStyle::all();
        return response()->json([
            'code' => 200,
            'message' => 'cooking Style',
            'data' =>  [
            'cookingStyle' =>$cookingStyle,
          ],
        ]);
    }
    public function paginate($items, $perPage, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
    

    public function ChefTimeAvailibality(Request $request)
    {
       try{
        $validatedData = Validator::make($request->all(),[
            'date' => 'required',
            'chef_id' =>'required',
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
        }

        // $user = User::latest()->first();
        // return $user;

        $date= $request->date;

        $now = now()->format('H:i');
        $chef_availibility = TimeSlots::where(['chef_info_id' => $request->chef_id , 'date' => $request->date])->get();
        
        // return $chef_availibility;
        if($request->status){
            $status = $request->input('status');
            $chef_availibility = TimeSlots::where(['chef_info_id' => $request->chef_id , 'date' => $request->date, 'status' => $status])->get();
            // $chef_availibility = TimeSlots::where(['chef_info_id' => $request->chef_id , 'date' => $request->date, 'status' => $status])->where('to_time', '<=', $now)->get();
        }
            return response()->json([
                'code' => 200,
                'message' => 'chef time slots',
                'availibility' => $chef_availibility,
            ]);
        } 
         catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'internal error']);
        }
    }
    
    public function bookChef(Request $request)
    {
        try{
            $validatedData = Validator::make($request->all(), [
                'chef_id' => 'required',
                'time_slot_id' => 'required',
            ]);
    
            if ($validatedData->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }
                $chef_id = $request->chef_id;
                $time_slot_id = $request->time_slot_id;
            $user = auth()->user();
            if($user->is_chef == 0)
            {
                if(!User::where(['id' => $chef_id, 'is_chef' => 1])->exists())
                {
                    return response()->json(['code' => 404, 'message' => 'Chef not exist']);
                }
                if(!TimeSlots::where(['id' => $time_slot_id , 'status' => 'available'])->exists())
                {
                    return response()->json(['code' =>404 , 'message' => 'This time is not available']);
                }
                if(BookChef::where(['user_id' => auth()->id(), 'time_slot_id'=> $time_slot_id , 'chef_id' => $chef_id])->exists())
                {
                    return response()->json(['code' => 409, 'message' => 'Booking is already exists']);
                }
                $bookchef = BookChef::create([
                    'user_id' => auth()->id(),
                    'chef_id' => $chef_id,
                    'time_slot_id' => $time_slot_id,
                ]); 

                $response = [
                    'booking_id' => $bookchef->id,
                    'time_slot_id' => (int)$time_slot_id,
                    'updated_at' => $bookchef->updated_at,
                    'created_at' => $bookchef->created_at,
                ];
    
                if($bookchef){
                    return response()->json([
                        'code' => 200,
                        'message' => 'Your booking is in pending please charge amount',
                       'bookchef' =>$response,
                    ]);
                }
            }
            else
            {
                return response()->json(['code' => 404, 'message' =>'Only user Can book']);
            }
           
        }
        catch (\Throwable $th) {
            return response()->json(['code' => 500, 'message' => 'internal error']);
        }
       
    }

    public function addMessageToSupport(Request $request)
    {
        try{
            $validatedData = Validator::make($request->all(), [
                'name' => 'required',
                'message' => 'required',
                'email' => 'required',
            ]);
    
            if ($validatedData->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validatedData->errors()->first(),
                ]);
            }
            $user=auth()->user();
            if($user->email == $request->email)
            {
                $support_center= SupportCenter::create([
                    'name' => $request->name,
                    'user_id' => $user->id,
                    'message' => $request->message,
                ]);
                return response()->json([
                    'code' => 200,
                    'message' => 'Message sent successfully',
                  ]);
    
            }
            else
            {
                return response()->json([
                    'code' => 500,
                    'error' => 'email not exists',
                  ]); 
            }

            
        }
        catch (\Throwable $th) {
            // dd($th);
            return response()->json(['code' => 500, 'message' => 'internal error']);
        }
    }
    
  
    public function getUserNotification()
    {
        try{

            $notification = Notification::where('user_id',auth()->id())->get();

            return response()->json([
                'code' => 200,
                'message' => 'Notifications Fetched',
                'notifications' => $notification
            ]);

        } catch (\Throwable $th) {
        
                return response()->json(['code' => 500, 'message' => 'user not exists']);
            }
    }

    

    public function getUserBookings()
    {
        $user = auth()->user();
        $upcoming = BookChef::where(['user_id' => $user->id])
        ->with('chefInfo.user', 'timeSlot')
        ->whereHas('timeSlot', function ($q) {
            $q->whereRaw('CONCAT(date, " ", to_time) >= ?', [Carbon::now()->format('Y-m-d H:i')]);
        })
        ->latest()->get();
    
    $previous = BookChef::where(['user_id' => $user->id])
        ->with('chefInfo.user', 'timeSlot')
        ->whereHas('timeSlot', function ($q) {
            $q->whereRaw('CONCAT(date, " ", to_time) <= ?', [Carbon::now()->format('Y-m-d H:i')]);
        })
        ->latest()->get();

        
      return response()->json([
        'code' => 200,
        'message' => 'successfully fetched',
        'userBooking' =>[
            'upCommingBooking' => $upcoming,
            'previousBooking' => $previous,
        ]
        
      ]);
    }
   
    public function getChefBookings()
    {
    
        $user = auth()->user();
        if($user->is_chef == 0)
        {
            return response()->json([
                'code' => 401,
                'error' => 'You are not chef'
            ]);
        }
        $upcomming =  BookChef::where(['chef_id' => $user->id])
        ->with('user','timeSlot')
        ->whereHas('timeSlot' , function($q){
            $q->whereRaw('CONCAT(date, " ", from_time) >= ?', [Carbon::now()->format('Y-m-d H:i')]);
        })
        ->latest()->get();

        $previous =  BookChef::where(['chef_id' => $user->id])
        ->with('user', 'timeSlot')
        ->whereHas('timeSlot' , function($q){
            $q->whereRaw('CONCAT(date, " ", to_time) < ?', [Carbon::now()->format('Y-m-d H:i')]);
        })
        ->latest()->get();


     return response()->json([
       'code' => 200,
       'message' => 'successfully fetched',
       'chefBooking' =>[
           'upCommingBooking' => $upcomming,
           'previousBooking' => $previous,
       ]
       
     ]);
     
    }
    public function getBookingDetails(Request $request)
    {

        try{
            $validatedData = Validator::make($request->all(), [
                'booking_id' => 'required',
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
                $BookingDetail =  BookChef::where(['id' => $request->booking_id,'user_id' => $user->id])->with('chefInfo.user','timeSlot')->first();
           
               
            }
            else{

                $BookingDetail =  BookChef::where(['id' => $request->booking_id,'chef_id' => $user->id])->with('user','timeSlot')->first();
            }
            
            return response()->json([
                'code' => 200,
                'message' => 'successfully fetched',
                'bookingDetail' =>$BookingDetail
            ]);
        }
        catch (\Throwable $th) {
            return response()->json(['code' => 500, 'error' => 'internal error']);
        }
        
        
    }

    public function updateInterests(Request $request){
        $validatedData = Validator::make($request->all(), [
            'interest*' => 'array',
            'interest.*' => 'nullable',
        ]);
    
        if ($validatedData->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validatedData->errors()->first(),
            ]);
        }
        $user = User::find(auth()->id());
        if($user->is_chef){
            return response()->json(['code' => 401, 'message' => 'Chefs are not authorized to perform this task.']);
        }
        
        $interest = implode(',' , $request->interest);
    
        $updateInterest = User::find($user->id)->update([
            'interest' => $interest,
        ]);

        if($updateInterest){
            $updateInterest = json_encode($interest);

            return response()->json(['code' => 200, 'message' => 'Interest has been updated', 'data' => ['interest' => $updateInterest]]);
        }
    }

}