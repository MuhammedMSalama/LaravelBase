<?php

namespace MuhammedSalama\Base;

use Illuminate\Support\ServiceProvider;
use MuhammedSalama\Base\Console\Commands\MakeRepositoryCommand;
use MuhammedSalama\Base\Console\Commands\CreateDatabaseCommand;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge package config so users can override values.
        $this->mergeConfigFrom(
            __DIR__ . '/../config/base.php',
            'base'
        );

        // Explicit interface => implementation bindings from config.
        foreach ((array) config('base.bindings', []) as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }

        // Convention-based auto binding:
        // App\Interfaces\XRepositoryInterface => App\Repositories\XRepository
        if (config('base.auto_bind', true)) {
            $this->autoBindRepositories();
        }
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/base.php' => config_path('base.php'),
            ], 'base-config');

            // Publish an editable copy of the ApiResponse helper into the app.
            $this->publishes([
                __DIR__ . '/../stubs/app/Helpers/ApiResponse.php' => app_path('Helpers/ApiResponse.php'),
            ], 'base-helpers');

            // Publish editable copies of the traits into the app.
            $this->publishes([
                __DIR__ . '/../stubs/app/Traits/ApiResponseTrait.php' => app_path('Traits/ApiResponseTrait.php'),
                __DIR__ . '/../stubs/app/Traits/ImageUploadTrait.php' => app_path('Traits/ImageUploadTrait.php'),
            ], 'base-traits');

            $this->commands([
                MakeRepositoryCommand::class,
                CreateDatabaseCommand::class,
            ]);
        }
    }

    /**
     * Automatically bind every *RepositoryInterface in app/Interfaces
     * to its matching *Repository in app/Repositories.
     *
     * @return void
     */
    protected function autoBindRepositories(): void
    {
        $interfacePath = app_path('Interfaces');

        if (! is_dir($interfacePath)) {
            return;
        }

        $appNamespace = $this->app->getNamespace(); // "App\"

        foreach ((glob($interfacePath . '/*RepositoryInterface.php') ?: []) as $file) {
            $base           = basename($file, '.php');                       // ProductRepositoryInterface
            $interface      = $appNamespace . 'Interfaces\\' . $base;        // App\Interfaces\ProductRepositoryInterface
            $implementation = $appNamespace . 'Repositories\\' . str_replace('Interface', '', $base); // App\Repositories\ProductRepository

            if (interface_exists($interface) && class_exists($implementation)) {
                $this->app->bind($interface, $implementation);
            }
        }
    }
}
