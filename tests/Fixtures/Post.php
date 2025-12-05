<?php

namespace PlusInfoLab\Search\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use PlusInfoLab\Search\Traits\Searchable;

class Post extends Model
{
    use Searchable;

    protected $fillable = ['title', 'content'];

    protected $searchable = ['title', 'content'];

    protected $searchWeights = [
        'title' => 10,
        'content' => 5,
    ];
}
