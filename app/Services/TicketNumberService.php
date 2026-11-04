<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TicketNumberService
{
    public function generate(): string
    {
        $year = (int) now()->format('Y');

        DB::table('ticket_sequences')->insertOrIgnore([
            'year' => $year,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('ticket_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $nextNumber = $sequence->last_number + 1;

        DB::table('ticket_sequences')
            ->where('year', $year)
            ->update([
                'last_number' => $nextNumber,
                'updated_at' => now(),
            ]);

        return sprintf('RLY-%d-%04d', $year, $nextNumber);
    }
}
