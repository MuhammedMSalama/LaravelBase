<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Console\Commands;

use Illuminate\Console\Command;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository
        {name : The base name, e.g. Product}
        {--model=      : The Eloquent model to wrap (defaults to the name)}
        {--controller= : Custom controller name (defaults to {Name}Controller)}
        {--no-model      : Skip generating the Eloquent model}
        {--no-service    : Skip generating the Service class}
        {--no-controller : Skip generating the Controller class}
        {--no-request    : Skip generating the Form Request classes}
        {--no-migration  : Skip generating the migration}
        {--provider      : Create / update a RepositoryServiceProvider with the interface binding}
        {--force         : Overwrite the files if they already exist}';

    protected $description = '[Deprecated: use make:module] Generate an Interface, Repository, Service, Form Requests, Controller, migration and optional model/provider';

    public function handle(): int
    {
        $this->comment('⚠  make:repository is deprecated. Please use `php artisan make:module` instead.');
        $this->newLine();

        /** @var array<string, mixed> $args */
        $args = [
            'name' => $this->argument('name'),
            // Skip all components added in make:module to preserve legacy output.
            '--no-resource' => true,
            '--no-policy' => true,
            '--no-test' => true,
            '--no-enum' => true,
            '--no-filter' => true,
            // Forward boolean flags.
            '--no-model' => (bool) $this->option('no-model'),
            '--no-service' => (bool) $this->option('no-service'),
            '--no-controller' => (bool) $this->option('no-controller'),
            '--no-request' => (bool) $this->option('no-request'),
            '--no-migration' => (bool) $this->option('no-migration'),
            '--provider' => (bool) $this->option('provider'),
            '--force' => (bool) $this->option('force'),
        ];

        $modelOption = $this->option('model');
        if (is_string($modelOption) && $modelOption !== '') {
            $args['--model'] = $modelOption;
        }

        $ctrlOption = $this->option('controller');
        if (is_string($ctrlOption) && $ctrlOption !== '') {
            $args['--controller'] = $ctrlOption;
        }

        return $this->call('make:module', $args);
    }
}
