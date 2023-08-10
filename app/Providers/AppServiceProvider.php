<?php

namespace App\Providers;

use App\Types\EnumType;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserProvider::class, GuestUserProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Type::addType(EnumType::ENUM, 'App\Types\EnumType');
    }
}
