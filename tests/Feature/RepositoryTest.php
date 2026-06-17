<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MuhammedSalama\Base\Tests\Fixtures\Post;
use MuhammedSalama\Base\Tests\Fixtures\PostRepository;
use MuhammedSalama\Base\Tests\TestCase;

class RepositoryTest extends TestCase
{
    protected PostRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        $this->repository = new PostRepository(new Post);
    }

    public function test_it_creates_and_finds_a_record(): void
    {
        /** @var Post $post */
        $post = $this->repository->create(['title' => 'Hello']);

        $this->assertDatabaseHas('posts', ['title' => 'Hello']);

        /** @var Post $found */
        $found = $this->repository->find($post->id);
        $this->assertSame('Hello', $found->title);
    }

    public function test_it_updates_a_record(): void
    {
        /** @var Post $post */
        $post = $this->repository->create(['title' => 'Old']);

        /** @var Post $updated */
        $updated = $this->repository->update($post->id, ['title' => 'New']);
        $this->assertSame('New', $updated->title);
    }

    public function test_it_deletes_a_record(): void
    {
        /** @var Post $post */
        $post = $this->repository->create(['title' => 'Bye']);

        $this->assertTrue($this->repository->delete($post->id));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_it_returns_all_records(): void
    {
        $this->repository->create(['title' => 'A']);
        $this->repository->create(['title' => 'B']);

        $this->assertCount(2, $this->repository->all());
    }
}
