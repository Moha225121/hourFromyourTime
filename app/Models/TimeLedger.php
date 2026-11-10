<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeLedger extends Model
{
    protected $table = 'time_ledger';

    protected $fillable = ['member_id', 'booking_id', 'minutes', 'entry_type', 'description', 'approved_by'];

    public function member()
    {
        return $this->belongsTo(CommunityMember::class);
    }

    public function booking()
    {
        return $this->belongsTo(ServiceBooking::class);
    }
}
