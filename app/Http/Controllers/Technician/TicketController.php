<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TicketController extends Controller
{
    private const STATUS_TRANSITIONS = [
        'received' => ['diagnosing', 'on_hold'],
        'diagnosing' => ['awaiting_approval', 'repairing', 'on_hold'],
        'awaiting_approval' => [],
        'repairing' => ['ready_for_pickup', 'on_hold'],
        'on_hold' => ['diagnosing', 'repairing'],
        'ready_for_pickup' => [],
        'completed' => [],
        'cancelled' => [],
    ];

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
                            $customerQuery->where('name', 'like', "%{$search}%");
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
        $this->authorizeAssigned($request, $ticket);

        $ticket->load([
            'customer',
            'device',
            'category',
            'technician',
            'creator',
            'diagnoses' => fn ($query) => $query
                ->with('technician')
                ->latest('id'),
            'notes' => fn ($query) => $query
                ->with('user')
                ->latest(),
            'activities' => fn ($query) => $query
                ->with('user')
                ->latest(),
        ]);

        $availableStatuses = self::STATUS_TRANSITIONS[$ticket->status] ?? [];

        return view('technician.tickets.show', compact(
            'ticket',
            'availableStatuses'
        ));
    }

    public function storeDiagnosis(
        Request $request,
        Ticket $ticket
    ): RedirectResponse {
        $this->authorizeAssigned($request, $ticket);

        $validated = $request->validate([
            'findings' => ['required', 'string', 'max:5000'],
            'recommended_action' => ['required', 'string', 'max:5000'],
            'estimated_cost' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ]);

        DB::transaction(function () use ($request, $ticket, $validated) {
            $lockedTicket = $this->lockAssignedTicket($request, $ticket);

            if (! in_array($lockedTicket->status, ['received', 'diagnosing'], true)) {
                throw ValidationException::withMessages([
                    'findings' => 'Diagnosis can only be recorded while the ticket is received or diagnosing.',
                ]);
            }

            $existingDiagnosis = $lockedTicket->diagnoses()
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($existingDiagnosis && $existingDiagnosis->approval_status !== 'pending') {
                throw ValidationException::withMessages([
                    'findings' => 'An approved or rejected diagnosis cannot be edited here.',
                ]);
            }

            $data = [
                'technician_id' => $request->user()->id,
                'findings' => $validated['findings'],
                'recommended_action' => $validated['recommended_action'],
                'estimated_cost' => $validated['estimated_cost'],
                'approval_status' => 'pending',
                'approved_at' => null,
                'diagnosed_at' => now(),
            ];

            if ($existingDiagnosis) {
                $existingDiagnosis->update($data);
            } else {
                $lockedTicket->diagnoses()->create($data);
            }

            $lockedTicket->activities()->create([
                'user_id' => $request->user()->id,
                'action' => $existingDiagnosis
                    ? 'diagnosis_updated'
                    : 'diagnosis_recorded',
                'old_status' => $lockedTicket->status,
                'new_status' => $lockedTicket->status,
                'description' => $existingDiagnosis
                    ? 'Technician updated the diagnosis and cost estimate.'
                    : 'Technician recorded the initial diagnosis and cost estimate.',
            ]);
        });

        return back()->with('success', 'Diagnosis saved successfully.');
    }

    public function storeNote(
        Request $request,
        Ticket $ticket
    ): RedirectResponse {
        $this->authorizeAssigned($request, $ticket);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $ticket, $validated) {
            $lockedTicket = $this->lockAssignedTicket($request, $ticket);

            if (in_array($lockedTicket->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'content' => 'Notes cannot be added to a closed or cancelled ticket.',
                ]);
            }

            $lockedTicket->notes()->create([
                'user_id' => $request->user()->id,
                'content' => $validated['content'],
                'is_internal' => true,
            ]);

            $lockedTicket->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'note_added',
                'old_status' => $lockedTicket->status,
                'new_status' => $lockedTicket->status,
                'description' => 'Technician added an internal service note.',
            ]);
        });

        return back()->with('success', 'Internal note added successfully.');
    }

    public function updateStatus(
        Request $request,
        Ticket $ticket
    ): RedirectResponse {
        $this->authorizeAssigned($request, $ticket);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(Ticket::STATUSES),
            ],
        ]);

        DB::transaction(function () use ($request, $ticket, $validated) {
            $lockedTicket = $this->lockAssignedTicket($request, $ticket);

            $oldStatus = $lockedTicket->status;
            $newStatus = $validated['status'];

            $allowed = self::STATUS_TRANSITIONS[$oldStatus] ?? [];

            if (! in_array($newStatus, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => 'This status transition is not allowed.',
                ]);
            }

            if (in_array($newStatus, ['awaiting_approval', 'repairing'], true)) {
                $diagnosis = $lockedTicket->diagnoses()
                    ->latest('id')
                    ->first();

                if (! $diagnosis) {
                    throw ValidationException::withMessages([
                        'status' => 'Record a diagnosis before continuing.',
                    ]);
                }

                if ($newStatus === 'awaiting_approval'
                    && (float) $diagnosis->estimated_cost <= 0) {
                    throw ValidationException::withMessages([
                        'status' => 'Approval is only required when the estimated cost is greater than zero.',
                    ]);
                }

                if ($newStatus === 'repairing'
                    && (float) $diagnosis->estimated_cost > 0
                    && $diagnosis->approval_status !== 'approved') {
                    throw ValidationException::withMessages([
                        'status' => 'Customer approval is required before starting a paid repair.',
                    ]);
                }
            }

            $lockedTicket->update([
                'status' => $newStatus,
            ]);

            $lockedTicket->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'status_changed',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => sprintf(
                    'Technician changed the ticket status from %s to %s.',
                    str_replace('_', ' ', $oldStatus),
                    str_replace('_', ' ', $newStatus)
                ),
            ]);
        });

        return back()->with('success', 'Ticket status updated successfully.');
    }

    private function authorizeAssigned(
        Request $request,
        Ticket $ticket
    ): void {
        if ((int) $ticket->technician_id !== (int) $request->user()->id) {
            abort(403, 'You are not assigned to this ticket.');
        }
    }

    private function lockAssignedTicket(
        Request $request,
        Ticket $ticket
    ): Ticket {
        $lockedTicket = Ticket::query()
            ->lockForUpdate()
            ->findOrFail($ticket->id);

        $this->authorizeAssigned($request, $lockedTicket);

        return $lockedTicket;
    }
}
