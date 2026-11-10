<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOffer extends Model
{
    protected $fillable = ['member_id', 'category_id', 'title', 'description', 'duration_minutes', 'delivery_mode', 'city', 'availability', 'status'];

    protected $casts = ['duration_minutes' => 'integer'];

    public function member()
    {
        return $this->belongsTo(CommunityMember::class);
    }

    public function category()
    {
        return $this->belongsTo(SkillCategory::class);
    }

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class, 'offer_id');
    }
}
