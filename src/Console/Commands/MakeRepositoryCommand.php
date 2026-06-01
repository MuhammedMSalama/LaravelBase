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
        {--no-service : Skip generating the Service class}
        {--no-controller : Skip generating the Controller class}
        {--no-request : Skip generating the Form Request classes}
        {--no-migration : Skip generating the migration}
        {--force : Overwrite the files if they already exist}';

    /**
     * @var string
     */
    protected $description = 'Generate an Interface, Repository, Service, Form Requests, Controller and migration using the Laravel Base structure';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $class = Str::studly((string)$this->argument('name'));
        $model = Str::studly((string)($this->option('model') ?: $class));

        $rootNamespace = $this->laravel->getNamespace();          // e.g. "App\"
        $modelNamespace = $rootNamespace . 'Models\\' . $model;     // e.g. "App\Models\Product"

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

        $this->generate(
            'interface.stub',
            app_path("Interfaces/{$class}RepositoryInterface.php"),
            $replacements,
            'Interface'
        );

        $this->generate(
            'repository.stub',
            app_path("Repositories/{$class}Repository.php"),
            $replacements,
            'Repository'
        );

        if (!$this->option('no-service')) {
            $this->generate(
                'service.stub',
                app_path("Services/{$class}Service.php"),
                $replacements,
                'Service'
            );
        }

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

        if (!$this->option('no-controller')) {
            $this->generate(
                $withRequests ? 'controller.stub' : 'controller.plain.stub',
                app_path("Http/Controllers/{$controller}.php"),
                $replacements,
                'Controller'
            );
        }

        if (!$this->option('no-migration')) {
            $this->makeMigration($class);
        }

        $this->newLine();
        $this->info("✔  {$class} structure generated successfully.");

        if (config('base.auto_bind', true)) {
            $this->line('   The interface is auto-bound to the repository — no manual binding needed.');
        } else {
            $this->warn('   Remember to register the binding in config/base.php:');
            $this->line("   \\App\\Interfaces\\{$class}RepositoryInterface::class => \\App\\Repositories\\{$class}Repository::class,");
        }

        return self::SUCCESS;
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
     */
    protected function generate(string $stub, string $target, array $replacements, string $label): void
    {
        if ($this->files->exists($target) && !$this->option('force')) {
            $this->warn("•  {$label} already exists, skipped: " . $this->relative($target));
            return;
        }

        $this->files->ensureDirectoryExists(dirname($target));

        $contents = strtr(
            $this->files->get(__DIR__ . '/../Stubs/' . $stub),
            $replacements
        );

        $this->files->put($target, $contents);

        $this->line("•  {$label} created: " . $this->relative($target));
    }

    /**
     * Make a path relative to the project base for cleaner output.
     */
    protected function relative(string $path): string
    {
        return str_replace($this->laravel->basePath() . DIRECTORY_SEPARATOR, '', $path);
    }
}
