<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillCategory extends Model
{
    protected $fillable = ['name', 'icon', 'examples', 'sort_order'];

    protected $casts = ['examples' => 'array'];

    public function offers()
    {
        return $this->hasMany(ServiceOffer::class, 'category_id');
    }
}
