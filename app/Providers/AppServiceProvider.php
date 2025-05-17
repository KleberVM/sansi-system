<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
 
    
    public function boot()
    {
        // Forzar HTTPS en todos los entornos
        //URL::forceScheme('https');
        /*if ($this->app->environment('local')) {
            URL::forceScheme('https');
        }*/
        View::composer('layouts.sidebar', function ($view) {
            $iusIds = collect();
    
            if ($user = Auth::user()) {
                $iusIds = $user->roles->flatMap(function ($rol) {
                    return $rol->funciones->flatMap(function ($funcion) {
                        return $funcion->ius->pluck('idIu');
                    });
                })->unique()->values();
            }
    
            $view->with('iusIds', $iusIds);
        });
    }
    
}
