<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpRequest extends Model
{
    protected $fillable = ['requester_id', 'helper_id', 'title', 'category', 'description', 'person', 'initial', 'place', 'status'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function helper()
    {
        return $this->belongsTo(User::class, 'helper_id');
    }
}
