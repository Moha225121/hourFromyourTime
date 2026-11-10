<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityMember extends Model
{
    protected $fillable = ['user_id', 'name', 'email', 'city', 'skills', 'needs', 'initial', 'bio', 'rating', 'hours_contributed', 'available_hours', 'skill', 'category', 'description', 'place', 'times'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activities()
    {
        return $this->hasMany(TimeActivity::class, 'member_id');
    }

    public function offers()
    {
        return $this->hasMany(ServiceOffer::class, 'member_id');
    }

    public function ledger()
    {
        return $this->hasMany(TimeLedger::class, 'member_id');
    }

    public function getLedgerMinutesAttribute(): int
    {
        return (int) $this->ledger()->sum('minutes');
    }
}
