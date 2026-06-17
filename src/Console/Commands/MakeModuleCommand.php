<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
        {name : Module name, e.g. Product}
        {--model=      : Custom Eloquent model name (defaults to the module name)}
        {--controller= : Custom controller class name}
        {--only=       : Comma-separated list of components to generate (all others skipped)}
        {--except=     : Comma-separated list of components to skip}
        {--no-model      : Skip Eloquent model generation}
        {--no-migration  : Skip migration generation}
        {--no-enum       : Skip status Enum}
        {--no-filter     : Skip Filters class}
        {--no-service    : Skip Service class}
        {--no-request    : Skip Store/Update Form Requests}
        {--no-resource   : Skip API Resource and ResourceCollection}
        {--no-policy     : Skip Policy class}
        {--no-controller : Skip Controller class}
        {--no-test       : Skip Feature and Unit test stubs}
        {--provider      : Create / update RepositoryServiceProvider with the interface binding}
        {--force         : Overwrite files that already exist}';

    protected $description = 'Generate a complete module (Model, Migration, Enum, Filter, Interface, Repository, Service, Requests, Resource, Policy, Controller, Tests)';

    /**
     * Maps component names to their per-component --no-* option.
     * Components not listed here (interface, repository) have no dedicated flag.
     *
     * @var array<string, string>
     */
    private const COMPONENT_FLAGS = [
        'model' => 'no-model',
        'migration' => 'no-migration',
        'enum' => 'no-enum',
        'filter' => 'no-filter',
        'service' => 'no-service',
        'request' => 'no-request',
        'resource' => 'no-resource',
        'policy' => 'no-policy',
        'controller' => 'no-controller',
        'test' => 'no-test',
    ];

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $class = Str::studly((string) $this->argument('name'));
        $modelOption = $this->option('model');
        $model = Str::studly(is_string($modelOption) && $modelOption !== '' ? $modelOption : $class);
        $rootNamespace = $this->laravel->getNamespace();
        $modelNs = $rootNamespace.'Models\\'.$model;
        $modelVariable = Str::camel($model);
        $routeName = Str::kebab(Str::pluralStudly($class));

        $ctrlOption = $this->option('controller');
        $controller = Str::studly(is_string($ctrlOption) && $ctrlOption !== '' ? $ctrlOption : "{$class}Controller");
        if (! Str::endsWith($controller, 'Controller')) {
            $controller .= 'Controller';
        }

        /** @var array<string, string> $replacements */
        $replacements = [
            '{{ class }}' => $class,
            '{{ rootNamespace }}' => $rootNamespace,
            '{{ model }}' => $model,
            '{{ modelNamespace }}' => $modelNs,
            '{{ modelVariable }}' => $modelVariable,
            '{{ controller }}' => $controller,
            '{{ routeName }}' => $routeName,
        ];

        // ── Interface ────────────────────────────────────────────────────────
        if ($this->shouldGenerate('interface')) {
            $this->generate(
                'interface.stub',
                app_path("Interfaces/{$class}RepositoryInterface.php"),
                $replacements,
                'Interface'
            );
        }

        // ── Repository ───────────────────────────────────────────────────────
        if ($this->shouldGenerate('repository')) {
            $this->generate(
                'repository.stub',
                app_path("Repositories/{$class}Repository.php"),
                $replacements,
                'Repository'
            );
        }

        // ── Model ────────────────────────────────────────────────────────────
        if ($this->shouldGenerate('model')) {
            $this->makeModel($model, $replacements);
        }

        // ── Migration ────────────────────────────────────────────────────────
        if ($this->shouldGenerate('migration')) {
            $this->makeMigration($class);
        }

        // ── Enum ─────────────────────────────────────────────────────────────
        if ($this->shouldGenerate('enum')) {
            $this->generate(
                'enum.stub',
                app_path("Enums/{$class}Status.php"),
                $replacements,
                'Enum'
            );
        }

        // ── Filters ──────────────────────────────────────────────────────────
        if ($this->shouldGenerate('filter')) {
            $this->generate(
                'filter.stub',
                app_path("Filters/{$class}Filters.php"),
                $replacements,
                'Filter'
            );
        }

        // ── Service ──────────────────────────────────────────────────────────
        if ($this->shouldGenerate('service')) {
            $stub = $this->shouldGenerate('filter') ? 'module-service.stub' : 'service.stub';
            $this->generate($stub, app_path("Services/{$class}Service.php"), $replacements, 'Service');
        }

        // ── Form Requests ────────────────────────────────────────────────────
        if ($this->shouldGenerate('request')) {
            foreach (["Store{$class}Request", "Update{$class}Request"] as $req) {
                $this->generate(
                    'request.stub',
                    app_path("Http/Requests/{$class}/{$req}.php"),
                    array_merge($replacements, ['{{ request }}' => $req]),
                    'Request'
                );
            }
        }

        // ── API Resource + Collection ─────────────────────────────────────────
        if ($this->shouldGenerate('resource')) {
            $this->generate(
                'resource.stub',
                app_path("Http/Resources/{$class}Resource.php"),
                $replacements,
                'Resource'
            );
            $this->generate(
                'resource-collection.stub',
                app_path("Http/Resources/{$class}ResourceCollection.php"),
                $replacements,
                'ResourceCollection'
            );
        }

        // ── Policy ───────────────────────────────────────────────────────────
        if ($this->shouldGenerate('policy')) {
            $this->generate(
                'policy.stub',
                app_path("Policies/{$class}Policy.php"),
                $replacements,
                'Policy'
            );
        }

        // ── Controller ───────────────────────────────────────────────────────
        if ($this->shouldGenerate('controller')) {
            $this->generate(
                $this->pickControllerStub(),
                app_path("Http/Controllers/{$controller}.php"),
                $replacements,
                'Controller'
            );
        }

        // ── Tests ────────────────────────────────────────────────────────────
        if ($this->shouldGenerate('test')) {
            $this->generate(
                'test-feature.stub',
                base_path("tests/Feature/{$class}Test.php"),
                $replacements,
                'Feature Test'
            );
            $this->generate(
                'test-unit.stub',
                base_path("tests/Unit/{$class}ServiceTest.php"),
                $replacements,
                'Unit Test'
            );
        }

        // ── RepositoryServiceProvider (opt-in) ───────────────────────────────
        if ($this->option('provider')) {
            $this->ensureProvider($class);
        }

        $this->newLine();
        $this->info("✔  {$class} module generated successfully.");

        if ($this->option('provider')) {
            $this->line('   Binding written to RepositoryServiceProvider.');
            if (config('base.auto_bind', true)) {
                $this->comment('   Tip: set auto_bind => false in config/base.php to use the provider as sole binding.');
            }
        } elseif (config('base.auto_bind', true)) {
            $this->line('   The interface is auto-bound to the repository — no manual binding needed.');
        } else {
            $this->warn('   Register the binding in config/base.php:');
            $this->line("   \\App\\Interfaces\\{$class}RepositoryInterface::class => \\App\\Repositories\\{$class}Repository::class,");
        }

        return self::SUCCESS;
    }

    // =========================================================================
    // Component generation helpers
    // =========================================================================

    /**
     * Determine which controller stub to use based on enabled components.
     * Falls back to simpler stubs when resource/policy/filter are disabled.
     */
    protected function pickControllerStub(): string
    {
        $fullModule = $this->shouldGenerate('resource')
            && $this->shouldGenerate('request')
            && $this->shouldGenerate('filter')
            && $this->shouldGenerate('policy');

        if ($fullModule) {
            return 'module-controller.stub';
        }

        return $this->shouldGenerate('request') ? 'controller.stub' : 'controller.plain.stub';
    }

    /**
     * Create the Eloquent model from a stub. Never overwrites an existing model,
     * even with --force; use --no-model to skip generation entirely.
     *
     * @param  array<string, string>  $replacements
     */
    protected function makeModel(string $model, array $replacements): void
    {
        $modelPath = app_path("Models/{$model}.php");

        if ($this->files->exists($modelPath)) {
            $this->line('•  Model already exists, kept: '.$this->relative($modelPath));

            return;
        }

        $stub = $this->shouldGenerate('enum') ? 'module-model.stub' : 'model.stub';

        $this->files->ensureDirectoryExists(dirname($modelPath));
        $contents = strtr($this->files->get(__DIR__.'/../Stubs/'.$stub), $replacements);
        $this->files->put($modelPath, $contents);
        $this->line('•  Model created: '.$this->relative($modelPath));
    }

    /**
     * Generate a driver-aware migration for the module.
     * Skips if a migration for the same table already exists (unless --force).
     */
    protected function makeMigration(string $class): void
    {
        $table = Str::snake(Str::pluralStudly($class));
        $existing = glob(database_path("migrations/*_create_{$table}_table.php")) ?: [];

        if (! empty($existing) && ! $this->option('force')) {
            $this->warn("•  Migration for table '{$table}' already exists, skipped.");

            return;
        }

        $driver = $this->detectDatabaseDriver();
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_{$table}_table.php";
        $target = database_path("migrations/{$filename}");

        $metadataLine = match ($driver) {
            'mysql', 'pgsql' => "            \$table->json('metadata')->nullable();",
            default => "            \$table->text('metadata')->nullable(); // {$driver}: no native JSON — stored as TEXT",
        };

        $this->generate(
            'migration.stub',
            $target,
            [
                '{{ table }}' => $table,
                '{{ metadataColumn }}' => $metadataLine,
            ],
            'Migration'
        );

        if (! in_array($driver, ['mysql', 'pgsql'], true)) {
            $this->comment("   Notice: driver '{$driver}' detected — portable migration generated (json() → text()).");
        }
    }

    /**
     * Detect the configured database driver at runtime from the app config.
     */
    protected function detectDatabaseDriver(): string
    {
        $connection = config('database.default');
        $connection = is_string($connection) && $connection !== '' ? $connection : 'mysql';

        $driver = config("database.connections.{$connection}.driver");

        return is_string($driver) && $driver !== '' ? $driver : 'mysql';
    }

    // =========================================================================
    // RepositoryServiceProvider
    // =========================================================================

    /**
     * Ensure RepositoryServiceProvider exists and contains a binding for $class.
     */
    protected function ensureProvider(string $class): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (! $this->files->exists($providerPath)) {
            $this->files->ensureDirectoryExists(dirname($providerPath));
            $stub = $this->files->get(__DIR__.'/../Stubs/repository-service-provider.stub');
            $this->files->put($providerPath, $stub);
            $this->line('•  RepositoryServiceProvider created: '.$this->relative($providerPath));
            $this->informProviderRegistration();
        }

        $this->addBindingToProvider($providerPath, $class);
    }

    /**
     * Insert a $this->app->bind(…) call into the provider's register() method.
     * Idempotent: skips if the interface FQCN is already present.
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

        if (! str_contains($content, $marker)) {
            $this->warn("•  Could not insert binding: marker '{$marker}' not found in RepositoryServiceProvider.");
            $this->warn('   Add the binding manually inside register():');
            $this->line("   \$this->app->bind(\\App\\Interfaces\\{$class}RepositoryInterface::class, \\App\\Repositories\\{$class}Repository::class);");

            return;
        }

        $binding = "\$this->app->bind(\n"
            ."            \\App\\Interfaces\\{$class}RepositoryInterface::class,\n"
            ."            \\App\\Repositories\\{$class}Repository::class,\n"
            ."        );\n"
            ."        {$marker}";

        $this->files->put($providerPath, str_replace($marker, $binding, $content));
        $this->line("•  Binding for {$class} added to RepositoryServiceProvider.");
    }

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

    // =========================================================================
    // Shared generation primitive
    // =========================================================================

    /**
     * Write a single file from a stub, replacing all placeholders.
     * Skips if file already exists and --force is not set.
     *
     * @param  array<string, string>  $replacements
     */
    protected function generate(string $stub, string $target, array $replacements, string $label): void
    {
        if ($this->files->exists($target) && ! $this->option('force')) {
            $this->warn('•  '.$label.' already exists, skipped: '.$this->relative($target));

            return;
        }

        $this->files->ensureDirectoryExists(dirname($target));

        $contents = strtr(
            $this->files->get(__DIR__.'/../Stubs/'.$stub),
            $replacements
        );

        $this->files->put($target, $contents);
        $this->line('•  '.$label.' created: '.$this->relative($target));
    }

    /**
     * Return true if the given component should be generated given the current options.
     *
     * Resolution order:
     *   1. --only=<list>  → include only listed components
     *   2. --except=<list> → exclude listed components
     *   3. --no-<component> flag for components that have one
     *   4. Default: generate
     */
    protected function shouldGenerate(string $component): bool
    {
        $only = trim((string) ($this->option('only') ?: ''));
        $except = trim((string) ($this->option('except') ?: ''));

        if ($only !== '') {
            return in_array($component, array_map('trim', explode(',', $only)), true);
        }

        if ($except !== '') {
            if (in_array($component, array_map('trim', explode(',', $except)), true)) {
                return false;
            }
        }

        $flag = self::COMPONENT_FLAGS[$component] ?? null;
        if ($flag !== null) {
            return ! (bool) $this->option($flag);
        }

        return true;
    }

    /**
     * Make a path relative to the project base for cleaner output lines.
     */
    protected function relative(string $path): string
    {
        return str_replace($this->laravel->basePath().DIRECTORY_SEPARATOR, '', $path);
    }
}
