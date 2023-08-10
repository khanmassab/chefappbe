<?php

namespace App\Providers;
        
use Laravel\Passport\Passport;
use App\Providers\GuestUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
    

        $this->registerPolicies();
        // Passport::routes();
        Passport::tokensExpireIn(now()->addWeeks(1));
        Passport::refreshTokensExpireIn(now()->addWeeks(1));
        Passport::personalAccessTokensExpireIn(now()->addWeeks(1));
        // Auth::provider('guest', function ($app, array $config) {
        //     return new GuestUserProvider();
        // }); 
    }
}
