<?php

namespace App\Providers;

use App\Services\Localization\LocalizationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModularProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $modules = config('modular.modules');
        $path = config('modular.path');

        if($modules) {
            Route::group([
                'prefix' => LocalizationService::locale()
            ], function() use ($modules, $path) {

                foreach ($modules as $mod => $submodules) {

                    foreach($submodules as $key => $sub) {

                        $relativePath = "/$mod/$sub";

                        Route::middleware('web')
                            ->group(function () use ($mod, $sub, $relativePath, $path) {
                                $this->getWebRoutes($mod, $sub, $relativePath, $path);
                            });

                        if (in_array($sub, config('modular.noAuth'))) {

                            Route::prefix('api')
                                ->middleware('api')
                                ->group(function () use ($mod, $sub, $relativePath, $path) {
                                    $this->getApiRoutes($mod, $sub, $relativePath, $path);
                                });
                        } else {
                           Route::prefix('api')
                                ->middleware('auth:api')
                                ->group(function () use ($mod, $sub, $relativePath, $path) {
                                    $this->getApiRoutes($mod, $sub, $relativePath, $path);
                                });
                        }
                    }

                }
            });
        }
        $this->app['view']->addNamespace('Pub', base_path().'/resources/views/Pub');
        $this->app['view']->addNamespace('Admin', base_path().'/resources/views/Admin');
    }

    private function getWebRoutes($mod, $sub, $relativePath, $path)
    {
        $routesPath = $path.$relativePath.'/Routes/web.php';
        if(file_exists($routesPath)) {

            if($mod != config('modular.groupWithoutPrefix')) {
                Route::group(
                    [
                        'prefix' => strtolower($mod),
                        'middleware' => $this->getMiddleware($mod),
                    ],
                    function() use ($mod, $sub, $routesPath) {
                        Route::namespace("App\Modules\\$mod\\$sub\Controllers")->
                        group($routesPath);

                    }
                );
            } else {
                Route::namespace("App\Modules\\$mod\\$sub\Controllers")->
                    middleware($this->getMiddleware($mod))->
                    group($routesPath);
            }
        }
    }

    private function getApiRoutes($mod, $sub, string $relativePath, $path)
    {
        $routesPath = $path.$relativePath.'/Routes/api.php';
//        dd($routesPath);
        if(file_exists($routesPath)) {
            Route::group(
                [
                    'prefix' => strtolower($mod),
                    'middleware' => $this->getMiddleware($mod, 'api'),
                ],
                function() use ($mod, $sub, $routesPath) {
                    if (in_array($sub, config('modular.noAuth'))) {
//                        dd($sub);
                        Route::middleware(['guest'])->namespace("App\Modules\\$mod\\$sub\Controllers")->
                        group($routesPath);
                    } else {
//                        Route::middleware(['auth:api'])->namespace("App\Modules\\$mod\\$sub\Controllers")->
//                        group($routesPath);
                        Route::namespace("App\Modules\\$mod\\$sub\Controllers")->
                        group($routesPath);
                    }
//                    Route::namespace("App\Modules\\$mod\\$sub\Controllers")->
//                    group($routesPath);

                }
            );
        }
    }

    private function getMiddleware($mod, $key='web'): array
    {
        $middleware = [];

        $config = config('modular.groupMiddleware');

        if(isset($config[$mod])) {
            if(array_key_exists($mod, $config[$mod])) {
                $middleware = array_merge($middleware, $config[$mod][$key]);
            }
        }

        return $middleware;
    }
}
