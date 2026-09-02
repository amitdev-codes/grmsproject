<?php

namespace Modules\Grievance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundSms extends Model
{
    protected $table = 'inbound_sms';

    protected $fillable = ['from_number', 'raw_message', 'gateway_message_id', 'status', 'grievance_id', 'registered_by'];

    public function grievance(): BelongsTo
    {
        return $this->belongsTo(Grievance::class);
    }
}
