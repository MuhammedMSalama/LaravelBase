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
        {--no-service : Skip generating the Service class}
        {--force : Overwrite the files if they already exist}';

    /**
     * @var string
     */
    protected $description = 'Generate an Interface, Repository (and Service) using the Laravel Base structure';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $class = Str::studly($this->argument('name'));
        $model = Str::studly($this->option('model') ?: $class);

        $rootNamespace  = $this->laravel->getNamespace();          // e.g. "App\"
        $modelNamespace = $rootNamespace . 'Models\\' . $model;     // e.g. "App\Models\Product"

        $replacements = [
            '{{ class }}'          => $class,
            '{{ rootNamespace }}'  => $rootNamespace,
            '{{ model }}'          => $model,
            '{{ modelNamespace }}' => $modelNamespace,
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

        // Service (optional)
        if (! $this->option('no-service')) {
            $this->generate(
                'service.stub',
                app_path("Services/{$class}Service.php"),
                $replacements,
                'Service'
            );
        }

        $this->newLine();
        $this->info("✔  {$class} structure generated successfully.");

        if (config('base.auto_bind', true)) {
            $this->line("   The interface is auto-bound to the repository — no manual binding needed.");
        } else {
            $this->warn("   Remember to register the binding in config/base.php:");
            $this->line("   \\App\\Interfaces\\{$class}RepositoryInterface::class => \\App\\Repositories\\{$class}Repository::class,");
        }

        return self::SUCCESS;
    }

    /**
     * Create a file from a stub, replacing the placeholders.
     */
    protected function generate(string $stub, string $target, array $replacements, string $label): void
    {
        if ($this->files->exists($target) && ! $this->option('force')) {
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
