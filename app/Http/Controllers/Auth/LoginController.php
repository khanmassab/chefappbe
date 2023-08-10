<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Session;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function attemptLogin(Request $request) {
        try {
            
        
            $validation = Validator::make( $request->all(), [
                'email'    => 'required|email',
                'password' => 'required'
            ] );
    
            if ($validation->fails() ) {
                return Redirect::back()->withErrors( $validation )->withInput();
            }
    
            $email = $request->email;
            // $check_user = User::where('email', $email)->where('is_chef','2')->first();
            $check_user = User::where('email', $email)->first();
            

        
            if($check_user && Hash::check($request['password'], $check_user->password)) {
                if ($user = Auth::attempt($validation->validated())) {
                    return redirect(route('home'));
                }
                return back();
            } else {
               
                // Set the 'error' session data and return to the login page
                Session::put(['error' => 'Your provided credentials do not match in our records.', 'count' => 1]);
                return back();
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function logout(Request $request) {
        Auth::logout();
        return redirect('/login');
    }
}
