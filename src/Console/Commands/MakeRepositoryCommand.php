<?php

namespace MuhammedSalama\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRepositoryCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:repository
        {name : The base name, e.g. Product}
        {--model= : The Eloquent model to wrap (defaults to the name)}
        {--controller= : Custom controller name (defaults to {Name}Controller)}
        {--no-model : Skip generating the Eloquent model}
        {--no-service : Skip generating the Service class}
        {--no-controller : Skip generating the Controller class}
        {--no-request : Skip generating the Form Request classes}
        {--no-migration : Skip generating the migration}
        {--provider : Create / update a RepositoryServiceProvider with the interface binding}
        {--force : Overwrite the files if they already exist}';

    /**
     * @var string
     */
    protected $description = 'Generate an Interface, Repository, Service, Form Requests, Controller, migration and optional model/provider using the Laravel Base structure';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $class = Str::studly((string)$this->argument('name'));
        $model = Str::studly((string)($this->option('model') ?: $class));

        $rootNamespace = $this->laravel->getNamespace();
        $modelNamespace = $rootNamespace . 'Models\\' . $model;

        $controller = (string)($this->option('controller') ?: "{$class}Controller");
        $controller = Str::studly($controller);
        if (!Str::endsWith($controller, 'Controller')) {
            $controller .= 'Controller';
        }

        $replacements = [
            '{{ class }}' => $class,
            '{{ rootNamespace }}' => $rootNamespace,
            '{{ model }}' => $model,
            '{{ modelNamespace }}' => $modelNamespace,
            '{{ controller }}' => $controller,
        ];

        // Interface
        $this->generate(
            'interface.stub',
            app_path("Interfaces/{$class}RepositoryInterface.php"),
            $replacements,
            'Interface'
        );

        // Repository
        $this->generate(
            'repository.stub',
            app_path("Repositories/{$class}Repository.php"),
            $replacements,
            'Repository'
        );

        // Model — never overwritten even with --force; use --no-model to skip.
        if (!$this->option('no-model')) {
            $this->makeModel($model, $rootNamespace);
        }

        // Service (optional)
        if (!$this->option('no-service')) {
            $this->generate(
                'service.stub',
                app_path("Services/{$class}Service.php"),
                $replacements,
                'Service'
            );
        }

        // Form Requests (optional)
        $withRequests = !$this->option('no-request');
        if ($withRequests) {
            foreach (["Store{$class}Request", "Update{$class}Request"] as $request) {
                $this->generate(
                    'request.stub',
                    app_path("Http/Requests/{$class}/{$request}.php"),
                    array_merge($replacements, ['{{ request }}' => $request]),
                    'Request'
                );
            }
        }

        // Controller (optional)
        if (!$this->option('no-controller')) {
            $this->generate(
                $withRequests ? 'controller.stub' : 'controller.plain.stub',
                app_path("Http/Controllers/{$controller}.php"),
                $replacements,
                'Controller'
            );
        }

        // Migration (optional)
        if (!$this->option('no-migration')) {
            $this->makeMigration($class);
        }

        // RepositoryServiceProvider (opt-in via --provider)
        if ($this->option('provider')) {
            $this->ensureProvider($class);
        }

        $this->newLine();
        $this->info("✔  {$class} structure generated successfully.");

        if ($this->option('provider')) {
            if (config('base.auto_bind', true)) {
                $this->line('   Binding written to RepositoryServiceProvider.');
                $this->comment('   Tip: set auto_bind => false in config/base.php to make the provider the sole binding mechanism.');
            } else {
                $this->line('   Binding written to RepositoryServiceProvider.');
            }
        } elseif (config('base.auto_bind', true)) {
            $this->line('   The interface is auto-bound to the repository — no manual binding needed.');
        } else {
            $this->warn('   Remember to register the binding in config/base.php:');
            $this->line("   \\App\\Interfaces\\{$class}RepositoryInterface::class => \\App\\Repositories\\{$class}Repository::class,");
        }

        return self::SUCCESS;
    }

    /**
     * Generate the Eloquent model if it does not exist.
     * Never overwrites an existing model, even when --force is used.
     */
    protected function makeModel(string $model, string $rootNamespace): void
    {
        $modelPath = app_path("Models/{$model}.php");

        if ($this->files->exists($modelPath)) {
            $this->line('•  Model already exists, kept: ' . $this->relative($modelPath));
            return;
        }

        $this->files->ensureDirectoryExists(dirname($modelPath));

        $contents = strtr(
            $this->files->get(__DIR__ . '/../Stubs/model.stub'),
            [
                '{{ rootNamespace }}' => $rootNamespace,
                '{{ model }}' => $model,
            ]
        );

        $this->files->put($modelPath, $contents);
        $this->line('•  Model created: ' . $this->relative($modelPath));
    }

    /**
     * Ensure RepositoryServiceProvider exists and contains a binding for $class.
     * The provider is created once and never overwritten; bindings are appended.
     */
    protected function ensureProvider(string $class): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (!$this->files->exists($providerPath)) {
            $this->files->ensureDirectoryExists(dirname($providerPath));
            $stub = $this->files->get(__DIR__ . '/../Stubs/repository-service-provider.stub');
            $this->files->put($providerPath, $stub);
            $this->line('•  RepositoryServiceProvider created: ' . $this->relative($providerPath));
            $this->informProviderRegistration();
        }

        $this->addBindingToProvider($providerPath, $class);
    }

    /**
     * Insert a $this->app->bind(…) call into the provider's register() method.
     * Idempotent: skips if the interface FQCN is already present in the file.
     * Uses the persistent `// {{ bindings }}` marker as the insertion point.
     */
    protected function addBindingToProvider(string $providerPath, string $class): void
    {
        $content = $this->files->get($providerPath);
        $needle = "\\App\\Interfaces\\{$class}RepositoryInterface::class";
        $marker = '// {{ bindings }}';

        if (str_contains($content, $needle)) {
            $this->line("•  Binding for {$class} already present in RepositoryServiceProvider, skipped.");
            return;
        }

        if (!str_contains($content, $marker)) {
            $this->warn("•  Could not insert binding: marker '{$marker}' not found in RepositoryServiceProvider.");
            $this->warn('   Add the binding manually inside register():');
            $this->line("   \$this->app->bind(\\App\\Interfaces\\{$class}RepositoryInterface::class, \\App\\Repositories\\{$class}Repository::class);");
            return;
        }

        // Replace the marker with the new binding followed by the marker again,
        // so subsequent calls can keep inserting above the marker.
        $binding = "\$this->app->bind(\n"
            . "            \\App\\Interfaces\\{$class}RepositoryInterface::class,\n"
            . "            \\App\\Repositories\\{$class}Repository::class,\n"
            . "        );\n"
            . "        {$marker}";

        $this->files->put($providerPath, str_replace($marker, $binding, $content));
        $this->line("•  Binding for {$class} added to RepositoryServiceProvider.");
    }

    /**
     * Print the correct instruction to register RepositoryServiceProvider
     * depending on which Laravel bootstrap format is in use.
     */
    protected function informProviderRegistration(): void
    {
        $bootstrapProviders = base_path('bootstrap/providers.php');

        if ($this->files->exists($bootstrapProviders)) {
            $this->comment('   Add to bootstrap/providers.php:');
        } else {
            $this->comment('   Add to the providers array in config/app.php:');
        }

        $this->line('   App\\Providers\\RepositoryServiceProvider::class,');
    }

    /**
     * Create a "create_{table}_table" migration unless one already exists.
     */
    protected function makeMigration(string $class): void
    {
        $table = Str::snake(Str::pluralStudly($class));

        $existing = glob(database_path("migrations/*_create_{$table}_table.php"));
        if (!empty($existing) && !$this->option('force')) {
            $this->warn("•  Migration for table '{$table}' already exists, skipped.");
            return;
        }

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    /**
     * Create a file from a stub, replacing the placeholders.
     * Respects --force for overwriting.
     *
     * @param array<string, string> $replacements
     */
    protected function generate(string $stub, string $target, array $replacements, string $label): void
    {
        if ($this->files->exists($target) && !$this->option('force')) {
            $this->warn('•  ' . $label . ' already exists, skipped: ' . $this->relative($target));
            return;
        }

        $this->files->ensureDirectoryExists(dirname($target));

        $contents = strtr(
            $this->files->get(__DIR__ . '/../Stubs/' . $stub),
            $replacements
        );

        $this->files->put($target, $contents);
        $this->line('•  ' . $label . ' created: ' . $this->relative($target));
    }

    /**
     * Make a path relative to the project base for cleaner output.
     */
    protected function relative(string $path): string
    {
        return str_replace($this->laravel->basePath() . DIRECTORY_SEPARATOR, '', $path);
    }
}
