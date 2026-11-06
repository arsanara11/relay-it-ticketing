<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $technicianId = $request->user()->id;

        $search = trim((string) $request->query('search', ''));

        $status = (string) $request->query('status', '');

        $tickets = Ticket::query()
            ->where('technician_id', $technicianId)
            ->with([
                'customer',
                'device',
                'category',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );
                        })
                        ->orWhereHas('device', function ($deviceQuery) use ($search) {
                            $deviceQuery
                                ->where('brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                in_array($status, Ticket::STATUSES, true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalAssigned = Ticket::where(
            'technician_id',
            $technicianId
        )->count();

        return view('technician.tickets.index', compact(
            'tickets',
            'search',
            'status',
            'totalAssigned'
        ));
    }

    public function show(Request $request, Ticket $ticket): View
    {
        if ($ticket->technician_id !== $request->user()->id) {
            abort(403, 'You are not assigned to this ticket.');
        }

        $ticket->load([
            'customer',
            'device',
            'category',
            'technician',
            'creator',
            'activities' => fn ($query) =>
                $query->with('user')->latest(),
        ]);

        return view('technician.tickets.show', compact('ticket'));
    }
}
