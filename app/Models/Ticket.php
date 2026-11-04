<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_id',
        'device_id',
        'ticket_category_id',
        'technician_id',
        'created_by',
        'subject',
        'description',
        'priority',
        'status',
        'initial_condition',
        'accessories',
        'received_at',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class);
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(TicketDiagnosis::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TicketNote::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(TicketCost::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TicketPayment::class);
    }
}
