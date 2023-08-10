<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Recipe;
use App\Models\BookChef;
use App\Models\ChefPayment;
use App\Models\UserPayment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
  
     return view('admin.dashboard');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    //
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getChefs(){
        $usersQuery = User::query();
        $chefs = $usersQuery->paginate(10);
        return view('pages.user-management', compact('chefs'));
    }

    public function archiveUser(User $user){
        if($user->is_archived){
            $user->is_archived=0;
        }else{
            $user->is_archived = 1;
        }
        $user->save();

        return back();
    }
    
    public function getBookings(Request $request){

        $query = BookChef::query();
        
        $query->orderBy('created_at', 'desc');
        
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate(10);
        $bookings->appends(['status' => $request->input('status')]);


        return view('pages.user-bookings', compact('bookings'));
    }

    public function getChefPayments(Request $request){

        $query = ChefPayment::query();
        
        $query->orderBy('created_at', 'desc');

        $payments = $query->paginate(10);


        return view('pages.chef-payment', compact('payments'));
    }

    public function getUserPayments(Request $request){

        $query = UserPayment::query();
        
        $query->orderBy('created_at', 'desc');
        

        $payments = $query->paginate(10);


        return view('pages.user-paymemts', compact('payments'));
    }

    public function getAllPayments(Request $request)
    {
        $query = null;
        $paymentType = $request->input('type');
        $sortOrder = $request->input('sort');
        
        if ($paymentType == 'chef') {
            $query = ChefPayment::query();
        } elseif ($paymentType == 'user') {
            $query = UserPayment::query();
        } else {
            $query = UserPayment::query();
        }
        
        if ($sortOrder == 'asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $payments = $query->paginate(10);

        return view('pages.payments', compact('payments'));
    }
    
    public function getAllRecipes(Request $request)
    {
        $query = null;
        $sortOrder = $request->input('sort');
        $query = Recipe::query();
        
        if ($sortOrder == 'asc') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'asc');
        }
        
        $payments = $query->paginate(10);

        return view('pages.recipes', compact('payments'));
    }

    public function showRecipe(Recipe $recipe){
        return view('pages.recipe-show', compact('recipe'));
    }
    
    public function deleteRecipe(Recipe $recipe){
        $recipe->delete();
        return back();
    }

}
