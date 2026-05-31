<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Binding
    |--------------------------------------------------------------------------
    |
    | When enabled, every App\Interfaces\{Name}RepositoryInterface is bound
    | automatically to App\Repositories\{Name}Repository by naming convention,
    | so you don't have to register bindings manually.
    |
    */

    'auto_bind' => true,

    /*
    |--------------------------------------------------------------------------
    | Manual Bindings
    |--------------------------------------------------------------------------
    |
    | Use this for any binding that does not follow the default convention
    | (e.g. a differently named implementation). These are always registered.
    |
    | Example:
    |   \App\Interfaces\ProductRepositoryInterface::class
    |       => \App\Repositories\EloquentProductRepository::class,
    |
    */

    'bindings' => [
        //
    ],

];
