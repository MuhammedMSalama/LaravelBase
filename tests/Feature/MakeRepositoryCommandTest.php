<?php

namespace MuhammedSalama\Base\Tests\Feature;

use Illuminate\Support\Facades\File;
use MuhammedSalama\Base\Tests\TestCase;

class MakeRepositoryCommandTest extends TestCase
{
    /** Paths created by the command during a test — cleaned up in tearDown. */
    private array $createdPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->createdPaths as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Original tests
    // -------------------------------------------------------------------------

    public function test_command_generates_interface_and_repository(): void
    {
        $interfacePath  = app_path('Interfaces/WidgetRepositoryInterface.php');
        $repositoryPath = app_path('Repositories/WidgetRepository.php');
        $modelPath      = app_path('Models/Widget.php');

        $this->createdPaths = [$interfacePath, $repositoryPath, $modelPath];

        $this->artisan('make:repository', [
            'name'            => 'Widget',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
        ])->assertSuccessful();

        $this->assertFileExists($interfacePath);
        $this->assertFileExists($repositoryPath);

        $this->assertStringContainsString('WidgetRepositoryInterface', File::get($interfacePath));
        $this->assertStringContainsString('WidgetRepository', File::get($repositoryPath));
    }

    public function test_command_generates_service_when_not_skipped(): void
    {
        $servicePath = app_path('Services/OrderService.php');

        $this->createdPaths = [
            app_path('Interfaces/OrderRepositoryInterface.php'),
            app_path('Repositories/OrderRepository.php'),
            app_path('Models/Order.php'),
            $servicePath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Order',
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
        ])->assertSuccessful();

        $this->assertFileExists($servicePath);
        $this->assertStringContainsString('OrderService', File::get($servicePath));
    }

    public function test_command_skips_existing_files_without_force(): void
    {
        $interfacePath = app_path('Interfaces/GadgetRepositoryInterface.php');

        File::ensureDirectoryExists(dirname($interfacePath));
        File::put($interfacePath, '<?php // existing');

        $this->createdPaths = [
            $interfacePath,
            app_path('Repositories/GadgetRepository.php'),
            app_path('Models/Gadget.php'),
        ];

        $this->artisan('make:repository', [
            'name'            => 'Gadget',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('existing', File::get($interfacePath));
    }

    public function test_force_flag_overwrites_existing_files(): void
    {
        $interfacePath = app_path('Interfaces/GizmoRepositoryInterface.php');

        File::ensureDirectoryExists(dirname($interfacePath));
        File::put($interfacePath, '<?php // old content');

        $this->createdPaths = [
            $interfacePath,
            app_path('Repositories/GizmoRepository.php'),
            app_path('Models/Gizmo.php'),
        ];

        $this->artisan('make:repository', [
            'name'            => 'Gizmo',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--force'         => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('GizmoRepositoryInterface', File::get($interfacePath));
        $this->assertStringNotContainsString('old content', File::get($interfacePath));
    }

    public function test_custom_controller_name_is_respected(): void
    {
        $controllerPath = app_path('Http/Controllers/GadgetApiController.php');

        $this->createdPaths = [
            app_path('Interfaces/GadgetRepositoryInterface.php'),
            app_path('Repositories/GadgetRepository.php'),
            app_path('Models/Gadget.php'),
            app_path('Services/GadgetService.php'),
            app_path('Http/Requests/Gadget/StoreGadgetRequest.php'),
            app_path('Http/Requests/Gadget/UpdateGadgetRequest.php'),
            $controllerPath,
        ];

        $this->artisan('make:repository', [
            'name'           => 'Gadget',
            '--controller'   => 'GadgetApiController',
            '--no-migration' => true,
        ])->assertSuccessful();

        $this->assertFileExists($controllerPath);
        $this->assertStringContainsString('GadgetApiController', File::get($controllerPath));
    }

    // -------------------------------------------------------------------------
    // Feature 1: Model generation
    // -------------------------------------------------------------------------

    public function test_model_is_created_when_missing(): void
    {
        $modelPath = app_path('Models/Rocket.php');

        $this->createdPaths = [
            app_path('Interfaces/RocketRepositoryInterface.php'),
            app_path('Repositories/RocketRepository.php'),
            $modelPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Rocket',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
        ])->assertSuccessful();

        $this->assertFileExists($modelPath);
        $content = File::get($modelPath);
        $this->assertStringContainsString('class Rocket extends Model', $content);
        $this->assertStringContainsString('$guarded', $content);
    }

    public function test_model_is_left_intact_when_it_already_exists(): void
    {
        $modelPath = app_path('Models/Shuttle.php');

        File::ensureDirectoryExists(dirname($modelPath));
        File::put($modelPath, '<?php // hand-crafted model — do not touch');

        $this->createdPaths = [
            app_path('Interfaces/ShuttleRepositoryInterface.php'),
            app_path('Repositories/ShuttleRepository.php'),
            $modelPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Shuttle',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('hand-crafted', File::get($modelPath));
    }

    public function test_force_flag_does_not_overwrite_existing_model(): void
    {
        $modelPath = app_path('Models/Probe.php');

        File::ensureDirectoryExists(dirname($modelPath));
        File::put($modelPath, '<?php // keep me');

        $this->createdPaths = [
            app_path('Interfaces/ProbeRepositoryInterface.php'),
            app_path('Repositories/ProbeRepository.php'),
            $modelPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Probe',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--force'         => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('keep me', File::get($modelPath));
    }

    public function test_no_model_option_skips_model_creation(): void
    {
        $modelPath = app_path('Models/Comet.php');

        $this->createdPaths = [
            app_path('Interfaces/CometRepositoryInterface.php'),
            app_path('Repositories/CometRepository.php'),
        ];

        $this->artisan('make:repository', [
            'name'            => 'Comet',
            '--no-model'      => true,
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
        ])->assertSuccessful();

        $this->assertFileDoesNotExist($modelPath);
    }

    public function test_model_uses_custom_model_option(): void
    {
        $modelPath = app_path('Models/Planet.php');

        $this->createdPaths = [
            app_path('Interfaces/StarRepositoryInterface.php'),
            app_path('Repositories/StarRepository.php'),
            $modelPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Star',
            '--model'         => 'Planet',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
        ])->assertSuccessful();

        $this->assertFileExists($modelPath);
        $this->assertStringContainsString('class Planet extends Model', File::get($modelPath));
        $this->assertFileDoesNotExist(app_path('Models/Star.php'));
    }

    // -------------------------------------------------------------------------
    // Feature 2: RepositoryServiceProvider
    // -------------------------------------------------------------------------

    public function test_provider_is_created_on_first_run_with_provider_flag(): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        $this->createdPaths = [
            app_path('Interfaces/AlphaRepositoryInterface.php'),
            app_path('Repositories/AlphaRepository.php'),
            app_path('Models/Alpha.php'),
            $providerPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Alpha',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--provider'      => true,
        ])->assertSuccessful();

        $this->assertFileExists($providerPath);
        $content = File::get($providerPath);
        $this->assertStringContainsString('class RepositoryServiceProvider', $content);
        $this->assertStringContainsString('AlphaRepositoryInterface::class', $content);
        $this->assertStringContainsString('AlphaRepository::class', $content);
    }

    public function test_binding_is_appended_on_subsequent_runs(): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        $this->createdPaths = [
            app_path('Interfaces/BetaRepositoryInterface.php'),
            app_path('Repositories/BetaRepository.php'),
            app_path('Models/Beta.php'),
            app_path('Interfaces/GammaRepositoryInterface.php'),
            app_path('Repositories/GammaRepository.php'),
            app_path('Models/Gamma.php'),
            $providerPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Beta',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--provider'      => true,
        ])->assertSuccessful();

        $this->artisan('make:repository', [
            'name'            => 'Gamma',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--provider'      => true,
        ])->assertSuccessful();

        $content = File::get($providerPath);
        $this->assertStringContainsString('BetaRepositoryInterface::class', $content);
        $this->assertStringContainsString('GammaRepositoryInterface::class', $content);
        $this->assertStringContainsString('// {{ bindings }}', $content);
    }

    public function test_no_duplicate_bindings_on_repeated_runs(): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        $this->createdPaths = [
            app_path('Interfaces/DeltaRepositoryInterface.php'),
            app_path('Repositories/DeltaRepository.php'),
            app_path('Models/Delta.php'),
            $providerPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Delta',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--provider'      => true,
        ])->assertSuccessful();

        $this->artisan('make:repository', [
            'name'            => 'Delta',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--provider'      => true,
            '--force'         => true,
        ])->assertSuccessful();

        $content = File::get($providerPath);
        $this->assertSame(
            1,
            substr_count($content, 'DeltaRepositoryInterface::class'),
            'DeltaRepositoryInterface::class must appear exactly once in the provider.'
        );
    }

    public function test_existing_provider_is_not_overwritten_but_binding_is_added(): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        File::ensureDirectoryExists(dirname($providerPath));
        File::put($providerPath, <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // existing-custom-code
        // {{ bindings }}
    }
}
PHP);

        $this->createdPaths = [
            app_path('Interfaces/EpsilonRepositoryInterface.php'),
            app_path('Repositories/EpsilonRepository.php'),
            app_path('Models/Epsilon.php'),
            $providerPath,
        ];

        $this->artisan('make:repository', [
            'name'            => 'Epsilon',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            '--provider'      => true,
        ])->assertSuccessful();

        $content = File::get($providerPath);
        $this->assertStringContainsString('existing-custom-code', $content);
        $this->assertStringContainsString('EpsilonRepositoryInterface::class', $content);
    }

    public function test_no_provider_option_does_not_create_provider(): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        $this->createdPaths = [
            app_path('Interfaces/ZetaRepositoryInterface.php'),
            app_path('Repositories/ZetaRepository.php'),
            app_path('Models/Zeta.php'),
        ];

        $this->artisan('make:repository', [
            'name'            => 'Zeta',
            '--no-service'    => true,
            '--no-controller' => true,
            '--no-request'    => true,
            '--no-migration'  => true,
            // No --provider flag.
        ])->assertSuccessful();

        $this->assertFileDoesNotExist($providerPath);
    }
}
