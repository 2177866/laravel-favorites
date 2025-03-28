<?php
namespace Alyakin\Favorites\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Post extends Model
{
    use HasUuids;

    protected $guarded = [];
    public $timestamps = false;
    public $table = 'test_posts';
}