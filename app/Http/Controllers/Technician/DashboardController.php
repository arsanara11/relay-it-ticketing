<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $technicianId = $request->user()->id;

        $baseQuery = Ticket::query()
            ->where('technician_id', $technicianId);

        $totalAssigned = (clone $baseQuery)->count();

        $activeTickets = (clone $baseQuery)
            ->whereNotIn('status', [
                'completed',
                'cancelled',
            ])
            ->count();

        $completedTickets = (clone $baseQuery)
            ->where('status', 'completed')
            ->count();

        $recentTickets = (clone $baseQuery)
            ->with([
                'customer',
                'device',
                'category',
            ])
            ->whereNotIn('status', [
                'completed',
                'cancelled',
            ])
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        return view('technician.dashboard', compact(
            'totalAssigned',
            'activeTickets',
            'completedTickets',
            'recentTickets'
        ));
    }
}
