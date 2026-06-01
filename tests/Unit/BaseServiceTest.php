<?php

namespace MuhammedSalama\Base\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MuhammedSalama\Base\Services\BaseService;
use MuhammedSalama\Base\Tests\Fixtures\Post;
use MuhammedSalama\Base\Tests\Fixtures\PostRepository;
use MuhammedSalama\Base\Tests\TestCase;

class BaseServiceTest extends TestCase
{
    private PostRepository $repository;
    private BaseService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        $this->repository = new PostRepository(new Post());

        $this->service = new class($this->repository) extends BaseService {
        };
    }

    public function test_store_creates_a_record(): void
    {
        $model = $this->service->store(['title' => 'Hello']);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertDatabaseHas('posts', ['title' => 'Hello']);
    }

    public function test_find_returns_model_or_throws(): void
    {
        $created = $this->service->store(['title' => 'Find me']);

        $found = $this->service->find($created->id);

        $this->assertSame($created->id, $found->id);
    }

    public function test_all_returns_collection_of_all_records(): void
    {
        $this->service->store(['title' => 'A']);
        $this->service->store(['title' => 'B']);

        $results = $this->service->all();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(2, $results);
    }

    public function test_update_changes_the_record(): void
    {
        $model = $this->service->store(['title' => 'Old']);

        $updated = $this->service->update($model->id, ['title' => 'New']);

        $this->assertSame('New', $updated->title);
        $this->assertDatabaseHas('posts', ['id' => $model->id, 'title' => 'New']);
    }

    public function test_destroy_deletes_the_record(): void
    {
        $model = $this->service->store(['title' => 'Bye']);

        $result = $this->service->destroy($model->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('posts', ['id' => $model->id]);
    }

    public function test_repository_accessor_returns_the_repository(): void
    {
        $this->assertSame($this->repository, $this->service->repository());
    }

    public function test_paginate_returns_paginator(): void
    {
        $this->service->store(['title' => 'Page 1']);

        $paginator = $this->service->paginate(10);

        $this->assertSame(1, $paginator->total());
    }
}
