<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeActivity extends Model
{
    protected $fillable = ['member_id', 'title', 'hours', 'activity_date'];

    public function member()
    {
        return $this->belongsTo(CommunityMember::class, 'member_id');
    }
}
