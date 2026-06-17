<?php

declare(strict_types=1);

namespace MuhammedSalama\Base\Tests\Fixtures;

use MuhammedSalama\Base\Repositories\BaseRepository;

class PostRepository extends BaseRepository
{
    public function __construct(Post $post)
    {
        parent::__construct($post);
    }
}
