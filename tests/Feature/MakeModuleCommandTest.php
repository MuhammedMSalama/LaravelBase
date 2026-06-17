<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Tests\Feature;

use Illuminate\Support\Facades\File;
use MuhammedSalama\Base\Tests\TestCase;

class MakeModuleCommandTest extends TestCase
{
    /** @var list<string> Paths created during a test — cleaned up in tearDown. */
    private array $createdPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->createdPaths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            } elseif (File::exists($path)) {
                File::delete($path);
            }
        }
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Full module generation
    // -------------------------------------------------------------------------

    public function test_full_module_generates_all_components(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/BoxRepositoryInterface.php'),
            app_path('Repositories/BoxRepository.php'),
            app_path('Models/Box.php'),
            app_path('Enums/BoxStatus.php'),
            app_path('Filters/BoxFilters.php'),
            app_path('Services/BoxService.php'),
            app_path('Http/Requests/Box'),
            app_path('Http/Resources/BoxResource.php'),
            app_path('Http/Resources/BoxResourceCollection.php'),
            app_path('Policies/BoxPolicy.php'),
            app_path('Http/Controllers/BoxController.php'),
            base_path('tests/Feature/BoxTest.php'),
            base_path('tests/Unit/BoxServiceTest.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Box',
            '--no-migration' => true,
        ])->assertSuccessful();

        $this->assertFileExists(app_path('Interfaces/BoxRepositoryInterface.php'));
        $this->assertFileExists(app_path('Repositories/BoxRepository.php'));
        $this->assertFileExists(app_path('Models/Box.php'));
        $this->assertFileExists(app_path('Enums/BoxStatus.php'));
        $this->assertFileExists(app_path('Filters/BoxFilters.php'));
        $this->assertFileExists(app_path('Services/BoxService.php'));
        $this->assertFileExists(app_path('Http/Requests/Box/StoreBoxRequest.php'));
        $this->assertFileExists(app_path('Http/Requests/Box/UpdateBoxRequest.php'));
        $this->assertFileExists(app_path('Http/Resources/BoxResource.php'));
        $this->assertFileExists(app_path('Http/Resources/BoxResourceCollection.php'));
        $this->assertFileExists(app_path('Policies/BoxPolicy.php'));
        $this->assertFileExists(app_path('Http/Controllers/BoxController.php'));
        $this->assertFileExists(base_path('tests/Feature/BoxTest.php'));
        $this->assertFileExists(base_path('tests/Unit/BoxServiceTest.php'));
    }

    public function test_full_module_controller_uses_api_response_and_resources(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/LampRepositoryInterface.php'),
            app_path('Repositories/LampRepository.php'),
            app_path('Models/Lamp.php'),
            app_path('Enums/LampStatus.php'),
            app_path('Filters/LampFilters.php'),
            app_path('Services/LampService.php'),
            app_path('Http/Requests/Lamp'),
            app_path('Http/Resources/LampResource.php'),
            app_path('Http/Resources/LampResourceCollection.php'),
            app_path('Policies/LampPolicy.php'),
            app_path('Http/Controllers/LampController.php'),
            base_path('tests/Feature/LampTest.php'),
            base_path('tests/Unit/LampServiceTest.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Lamp',
            '--no-migration' => true,
        ])->assertSuccessful();

        $controllerContent = File::get(app_path('Http/Controllers/LampController.php'));
        $this->assertStringContainsString('ApiResponse::paginated', $controllerContent);
        $this->assertStringContainsString('LampResource', $controllerContent);
        $this->assertStringContainsString('@OA\Get', $controllerContent);
        $this->assertStringContainsString('$this->authorize', $controllerContent);
        $this->assertStringContainsString('filter($request)->paginate()', $controllerContent);
    }

    public function test_full_module_service_contains_filter_method(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/PenRepositoryInterface.php'),
            app_path('Repositories/PenRepository.php'),
            app_path('Models/Pen.php'),
            app_path('Enums/PenStatus.php'),
            app_path('Filters/PenFilters.php'),
            app_path('Services/PenService.php'),
            app_path('Http/Requests/Pen'),
            app_path('Http/Resources/PenResource.php'),
            app_path('Http/Resources/PenResourceCollection.php'),
            app_path('Policies/PenPolicy.php'),
            app_path('Http/Controllers/PenController.php'),
            base_path('tests/Feature/PenTest.php'),
            base_path('tests/Unit/PenServiceTest.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Pen',
            '--no-migration' => true,
        ])->assertSuccessful();

        $serviceContent = File::get(app_path('Services/PenService.php'));
        $this->assertStringContainsString('public function filter(Request $request): PenFilters', $serviceContent);
        $this->assertStringContainsString('$this->repository->query()', $serviceContent);
    }

    public function test_module_model_includes_enum_cast_when_enum_is_generated(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/CupRepositoryInterface.php'),
            app_path('Repositories/CupRepository.php'),
            app_path('Models/Cup.php'),
            app_path('Enums/CupStatus.php'),
            app_path('Filters/CupFilters.php'),
            app_path('Services/CupService.php'),
            app_path('Http/Requests/Cup'),
            app_path('Http/Resources/CupResource.php'),
            app_path('Http/Resources/CupResourceCollection.php'),
            app_path('Policies/CupPolicy.php'),
            app_path('Http/Controllers/CupController.php'),
            base_path('tests/Feature/CupTest.php'),
            base_path('tests/Unit/CupServiceTest.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Cup',
            '--no-migration' => true,
        ])->assertSuccessful();

        $modelContent = File::get(app_path('Models/Cup.php'));
        $this->assertStringContainsString('CupStatus::class', $modelContent);
        $this->assertStringContainsString('$casts', $modelContent);
    }

    // -------------------------------------------------------------------------
    // --only= option
    // -------------------------------------------------------------------------

    public function test_only_model_generates_only_model(): void
    {
        $modelPath = app_path('Models/Desk.php');
        $this->createdPaths = [$modelPath];

        $this->artisan('make:module', [
            'name' => 'Desk',
            '--only' => 'model',
        ])->assertSuccessful();

        $this->assertFileExists($modelPath);
        $this->assertFileDoesNotExist(app_path('Interfaces/DeskRepositoryInterface.php'));
        $this->assertFileDoesNotExist(app_path('Services/DeskService.php'));
        $this->assertFileDoesNotExist(app_path('Enums/DeskStatus.php'));
    }

    public function test_only_interface_and_repository(): void
    {
        $interfacePath = app_path('Interfaces/ChairRepositoryInterface.php');
        $repositoryPath = app_path('Repositories/ChairRepository.php');
        $this->createdPaths = [$interfacePath, $repositoryPath];

        $this->artisan('make:module', [
            'name' => 'Chair',
            '--only' => 'interface,repository',
        ])->assertSuccessful();

        $this->assertFileExists($interfacePath);
        $this->assertFileExists($repositoryPath);
        $this->assertFileDoesNotExist(app_path('Models/Chair.php'));
        $this->assertFileDoesNotExist(app_path('Services/ChairService.php'));
    }

    // -------------------------------------------------------------------------
    // --except= option
    // -------------------------------------------------------------------------

    public function test_except_skips_listed_components(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/TrayRepositoryInterface.php'),
            app_path('Repositories/TrayRepository.php'),
            app_path('Models/Tray.php'),
            app_path('Services/TrayService.php'),
            app_path('Http/Requests/Tray'),
            app_path('Http/Controllers/TrayController.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Tray',
            '--except' => 'enum,filter,resource,policy,test',
            '--no-migration' => true,
        ])->assertSuccessful();

        // Should exist
        $this->assertFileExists(app_path('Interfaces/TrayRepositoryInterface.php'));
        $this->assertFileExists(app_path('Services/TrayService.php'));

        // Should not exist
        $this->assertFileDoesNotExist(app_path('Enums/TrayStatus.php'));
        $this->assertFileDoesNotExist(app_path('Filters/TrayFilters.php'));
        $this->assertFileDoesNotExist(app_path('Http/Resources/TrayResource.php'));
        $this->assertFileDoesNotExist(app_path('Policies/TrayPolicy.php'));
        $this->assertFileDoesNotExist(base_path('tests/Feature/TrayTest.php'));
    }

    // -------------------------------------------------------------------------
    // --no-* individual flags
    // -------------------------------------------------------------------------

    public function test_no_enum_skips_enum_and_uses_plain_model(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/BookRepositoryInterface.php'),
            app_path('Repositories/BookRepository.php'),
            app_path('Models/Book.php'),
            app_path('Filters/BookFilters.php'),
            app_path('Services/BookService.php'),
            app_path('Http/Requests/Book'),
            app_path('Http/Resources/BookResource.php'),
            app_path('Http/Resources/BookResourceCollection.php'),
            app_path('Policies/BookPolicy.php'),
            app_path('Http/Controllers/BookController.php'),
            base_path('tests/Feature/BookTest.php'),
            base_path('tests/Unit/BookServiceTest.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Book',
            '--no-enum' => true,
            '--no-migration' => true,
        ])->assertSuccessful();

        $this->assertFileDoesNotExist(app_path('Enums/BookStatus.php'));

        // Model should use plain stub (no enum cast)
        $modelContent = File::get(app_path('Models/Book.php'));
        $this->assertStringNotContainsString('BookStatus', $modelContent);
    }

    public function test_no_filter_uses_plain_service_and_simple_controller(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/NoteRepositoryInterface.php'),
            app_path('Repositories/NoteRepository.php'),
            app_path('Models/Note.php'),
            app_path('Enums/NoteStatus.php'),
            app_path('Services/NoteService.php'),
            app_path('Http/Requests/Note'),
            app_path('Http/Resources/NoteResource.php'),
            app_path('Http/Resources/NoteResourceCollection.php'),
            app_path('Policies/NotePolicy.php'),
            app_path('Http/Controllers/NoteController.php'),
            base_path('tests/Feature/NoteTest.php'),
            base_path('tests/Unit/NoteServiceTest.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Note',
            '--no-filter' => true,
            '--no-migration' => true,
        ])->assertSuccessful();

        $this->assertFileDoesNotExist(app_path('Filters/NoteFilters.php'));

        // Service should use plain stub (no filter method)
        $serviceContent = File::get(app_path('Services/NoteService.php'));
        $this->assertStringNotContainsString('filter(', $serviceContent);

        // Controller should use controller.stub (no full module controller)
        $controllerContent = File::get(app_path('Http/Controllers/NoteController.php'));
        $this->assertStringNotContainsString('@OA\Get', $controllerContent);
    }

    public function test_no_resource_uses_simple_controller_with_requests(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/TagRepositoryInterface.php'),
            app_path('Repositories/TagRepository.php'),
            app_path('Models/Tag.php'),
            app_path('Enums/TagStatus.php'),
            app_path('Filters/TagFilters.php'),
            app_path('Services/TagService.php'),
            app_path('Http/Requests/Tag'),
            app_path('Http/Controllers/TagController.php'),
            base_path('tests/Feature/TagTest.php'),
            base_path('tests/Unit/TagServiceTest.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Tag',
            '--no-resource' => true,
            '--no-policy' => true,
            '--no-migration' => true,
        ])->assertSuccessful();

        $this->assertFileDoesNotExist(app_path('Http/Resources/TagResource.php'));

        $controllerContent = File::get(app_path('Http/Controllers/TagController.php'));
        // simple controller.stub uses ApiResponseTrait, not ApiResponse::
        $this->assertStringContainsString('ApiResponseTrait', $controllerContent);
    }

    // -------------------------------------------------------------------------
    // Idempotency / --force
    // -------------------------------------------------------------------------

    public function test_existing_files_are_skipped_without_force(): void
    {
        $interfacePath = app_path('Interfaces/ForkRepositoryInterface.php');
        File::ensureDirectoryExists(dirname($interfacePath));
        File::put($interfacePath, '<?php // existing');

        $this->createdPaths = [
            $interfacePath,
            app_path('Repositories/ForkRepository.php'),
            app_path('Models/Fork.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Fork',
            '--only' => 'interface,repository,model',
        ])->assertSuccessful();

        $this->assertStringContainsString('existing', File::get($interfacePath));
    }

    public function test_force_overwrites_existing_files(): void
    {
        $interfacePath = app_path('Interfaces/KnifeRepositoryInterface.php');
        File::ensureDirectoryExists(dirname($interfacePath));
        File::put($interfacePath, '<?php // old');

        $this->createdPaths = [
            $interfacePath,
            app_path('Repositories/KnifeRepository.php'),
            app_path('Models/Knife.php'),
        ];

        $this->artisan('make:module', [
            'name' => 'Knife',
            '--only' => 'interface,repository,model',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('KnifeRepositoryInterface', File::get($interfacePath));
        $this->assertStringNotContainsString('old', File::get($interfacePath));
    }

    public function test_force_does_not_overwrite_existing_model(): void
    {
        $modelPath = app_path('Models/Spoon.php');
        File::ensureDirectoryExists(dirname($modelPath));
        File::put($modelPath, '<?php // hand-crafted');

        $this->createdPaths = [
            app_path('Interfaces/SpoonRepositoryInterface.php'),
            app_path('Repositories/SpoonRepository.php'),
            $modelPath,
        ];

        $this->artisan('make:module', [
            'name' => 'Spoon',
            '--only' => 'interface,repository,model',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertStringContainsString('hand-crafted', File::get($modelPath));
    }

    // -------------------------------------------------------------------------
    // Backward-compatible make:repository alias
    // -------------------------------------------------------------------------

    public function test_make_repository_alias_generates_classic_components(): void
    {
        $interfacePath = app_path('Interfaces/DishRepositoryInterface.php');
        $repositoryPath = app_path('Repositories/DishRepository.php');
        $modelPath = app_path('Models/Dish.php');
        $servicePath = app_path('Services/DishService.php');

        $this->createdPaths = [
            $interfacePath, $repositoryPath, $modelPath, $servicePath,
        ];

        $this->artisan('make:repository', [
            'name' => 'Dish',
            '--no-controller' => true,
            '--no-request' => true,
            '--no-migration' => true,
        ])->assertSuccessful();

        $this->assertFileExists($interfacePath);
        $this->assertFileExists($repositoryPath);
        $this->assertFileExists($modelPath);
        $this->assertFileExists($servicePath);
        // New components must NOT be generated by the alias
        $this->assertFileDoesNotExist(app_path('Enums/DishStatus.php'));
        $this->assertFileDoesNotExist(app_path('Filters/DishFilters.php'));
        $this->assertFileDoesNotExist(app_path('Policies/DishPolicy.php'));
        $this->assertFileDoesNotExist(base_path('tests/Feature/DishTest.php'));
    }

    public function test_make_repository_alias_service_has_no_filter_method(): void
    {
        $this->createdPaths = [
            app_path('Interfaces/PlatRepositoryInterface.php'),
            app_path('Repositories/PlatRepository.php'),
            app_path('Models/Plat.php'),
            app_path('Services/PlatService.php'),
        ];

        $this->artisan('make:repository', [
            'name' => 'Plat',
            '--no-controller' => true,
            '--no-request' => true,
            '--no-migration' => true,
        ])->assertSuccessful();

        $serviceContent = File::get(app_path('Services/PlatService.php'));
        $this->assertStringNotContainsString('filter(', $serviceContent);
    }

    // -------------------------------------------------------------------------
    // Enum content
    // -------------------------------------------------------------------------

    public function test_enum_is_backed_string_enum_with_active_case(): void
    {
        $enumPath = app_path('Enums/RingStatus.php');

        $this->createdPaths = [
            app_path('Interfaces/RingRepositoryInterface.php'),
            app_path('Repositories/RingRepository.php'),
            app_path('Models/Ring.php'),
            $enumPath,
        ];

        $this->artisan('make:module', [
            'name' => 'Ring',
            '--only' => 'interface,repository,model,enum',
        ])->assertSuccessful();

        $content = File::get($enumPath);
        $this->assertStringContainsString('enum RingStatus: string', $content);
        $this->assertStringContainsString("case Active   = 'active';", $content);
        $this->assertStringContainsString('public function label(): string', $content);
        $this->assertStringContainsString('public static function values(): array', $content);
    }

    // -------------------------------------------------------------------------
    // Provider support (delegated to MakeModuleCommand from make:repository tests)
    // -------------------------------------------------------------------------

    public function test_provider_flag_creates_service_provider_with_binding(): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        $this->createdPaths = [
            app_path('Interfaces/BoltRepositoryInterface.php'),
            app_path('Repositories/BoltRepository.php'),
            app_path('Models/Bolt.php'),
            $providerPath,
        ];

        $this->artisan('make:module', [
            'name' => 'Bolt',
            '--only' => 'interface,repository,model',
            '--provider' => true,
        ])->assertSuccessful();

        $this->assertFileExists($providerPath);
        $content = File::get($providerPath);
        $this->assertStringContainsString('BoltRepositoryInterface::class', $content);
        $this->assertStringContainsString('BoltRepository::class', $content);
    }
}
