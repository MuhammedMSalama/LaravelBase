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

    public function test_command_generates_interface_and_repository(): void
    {
        $interfacePath = app_path('Interfaces/WidgetRepositoryInterface.php');
        $repositoryPath = app_path('Repositories/WidgetRepository.php');

        $this->createdPaths = [$interfacePath, $repositoryPath];

        $this->artisan('make:repository', [
            'name' => 'Widget',
            '--no-service' => true,
            '--no-controller' => true,
            '--no-request' => true,
            '--no-migration' => true,
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
            $servicePath,
        ];

        $this->artisan('make:repository', [
            'name' => 'Order',
            '--no-controller' => true,
            '--no-request' => true,
            '--no-migration' => true,
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
        ];

        $this->artisan('make:repository', [
            'name' => 'Gadget',
            '--no-service' => true,
            '--no-controller' => true,
            '--no-request' => true,
            '--no-migration' => true,
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
        ];

        $this->artisan('make:repository', [
            'name' => 'Gizmo',
            '--no-service' => true,
            '--no-controller' => true,
            '--no-request' => true,
            '--no-migration' => true,
            '--force' => true,
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
            app_path('Services/GadgetService.php'),
            app_path('Http/Requests/Gadget/StoreGadgetRequest.php'),
            app_path('Http/Requests/Gadget/UpdateGadgetRequest.php'),
            $controllerPath,
        ];

        $this->artisan('make:repository', [
            'name' => 'Gadget',
            '--controller' => 'GadgetApiController',
            '--no-migration' => true,
        ])->assertSuccessful();

        $this->assertFileExists($controllerPath);
        $this->assertStringContainsString('GadgetApiController', File::get($controllerPath));
    }
}
