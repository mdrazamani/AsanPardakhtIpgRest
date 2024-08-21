<?php 

namespace mdrazamani\AsanPardakhtIpgRest;

use Illuminate\Support\ServiceProvider;

class AsanPardakhtIpgRestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/asanpardakht.php', 'asanpardakht');
        
        $this->app->singleton('asanpardakht', function ($app) {
            return new AsanPardakhtIpgRest(config('asanpardakht'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/asanpardakht.php' => config_path('asanpardakht.php'),
        ]);
    }
}
