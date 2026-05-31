<?php

namespace MuhammedSalama\Base\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property string $title
 */
class Post extends Model
{
    protected $guarded = [];
}
