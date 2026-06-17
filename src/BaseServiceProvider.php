<?php

declare(strict_types=1);

namespace MuhammedSalama\Base;

use Illuminate\Support\ServiceProvider;
use MuhammedSalama\Base\Console\Commands\CreateDatabaseCommand;
use MuhammedSalama\Base\Console\Commands\MakeModuleCommand;
use MuhammedSalama\Base\Console\Commands\MakeRepositoryCommand;

class BaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/base.php',
            'base'
        );

        foreach ((array) config('base.bindings', []) as $interface => $implementation) {
            $this->app->bind((string) $interface, is_string($implementation) ? $implementation : null);
        }

        if (config('base.auto_bind', true)) {
            $this->autoBindRepositories();
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/base.php' => config_path('base.php'),
            ], 'base-config');

            $this->publishes([
                __DIR__.'/../stubs/app/Helpers/ApiResponse.php' => app_path('Helpers/ApiResponse.php'),
            ], 'base-helpers');

            $this->publishes([
                __DIR__.'/../stubs/app/Traits/ApiResponseTrait.php' => app_path('Traits/ApiResponseTrait.php'),
                __DIR__.'/../stubs/app/Traits/ImageUploadTrait.php' => app_path('Traits/ImageUploadTrait.php'),
            ], 'base-traits');

            $this->commands([
                MakeModuleCommand::class,
                MakeRepositoryCommand::class,
                CreateDatabaseCommand::class,
            ]);
        }
    }

    /**
     * Automatically bind every *RepositoryInterface in app/Interfaces
     * to its matching *Repository in app/Repositories by naming convention.
     */
    protected function autoBindRepositories(): void
    {
        $interfacePath = app_path('Interfaces');

        if (! is_dir($interfacePath)) {
            return;
        }

        $appNamespace = $this->app->getNamespace();

        foreach ((glob($interfacePath.'/*RepositoryInterface.php') ?: []) as $file) {
            $base = basename($file, '.php');                       // ProductRepositoryInterface
            $interface = $appNamespace.'Interfaces\\'.$base;        // App\Interfaces\ProductRepositoryInterface
            $implementation = $appNamespace.'Repositories\\'.str_replace('Interface', '', $base); // App\Repositories\ProductRepository

            if (interface_exists($interface) && class_exists($implementation)) {
                $this->app->bind($interface, $implementation);
            }
        }
    }
}
