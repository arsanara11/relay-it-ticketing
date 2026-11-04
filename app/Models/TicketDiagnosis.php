<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketDiagnosis extends Model
{
    protected $fillable = [
        'ticket_id',
        'technician_id',
        'findings',
        'recommended_action',
        'estimated_cost',
        'approval_status',
        'approved_at',
        'diagnosed_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'approved_at' => 'datetime',
            'diagnosed_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
