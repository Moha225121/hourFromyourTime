<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityStory extends Model
{
    protected $fillable = ['quote', 'name', 'detail', 'initial', 'published'];

    protected $casts = ['published' => 'boolean'];
}
