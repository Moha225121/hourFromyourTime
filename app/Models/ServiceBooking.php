<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    protected $fillable = ['offer_id', 'requester_id', 'provider_id', 'requested_minutes', 'actual_minutes', 'scheduled_at', 'status', 'requester_confirmed', 'provider_confirmed', 'settled_at', 'notes'];

    protected $casts = ['scheduled_at' => 'datetime', 'settled_at' => 'datetime', 'requester_confirmed' => 'boolean', 'provider_confirmed' => 'boolean'];

    public function offer()
    {
        return $this->belongsTo(ServiceOffer::class, 'offer_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
