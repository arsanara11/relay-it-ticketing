<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHandover extends Model
{
    protected $fillable = [
        'ticket_id',
        'handed_over_by',
        'received_by',
        'notes',
        'handed_over_at',
    ];

    protected function casts(): array
    {
        return [
            'handed_over_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }
}
