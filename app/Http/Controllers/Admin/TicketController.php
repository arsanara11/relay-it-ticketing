<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $tickets = Ticket::query()
            ->with(['customer', 'device', 'category', 'technician'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customer) =>
                            $customer->where('name', 'like', "%{$search}%")
                        )
                        ->orWhereHas('device', fn ($device) =>
                            $device->where('brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%")
                        );
                });
            })
            ->when(
                in_array($status, Ticket::STATUSES, true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalTickets = Ticket::count();

        $activeTickets = Ticket::whereNotIn('status', [
            'completed',
            'cancelled',
        ])->count();

        $completedTickets = Ticket::where('status', 'completed')->count();

        return view('admin.tickets.index', compact(
            'tickets',
            'search',
            'status',
            'totalTickets',
            'activeTickets',
            'completedTickets'
        ));
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();

        $devices = Device::with('customer')
            ->orderBy('brand')
            ->get();

        $categories = TicketCategory::orderBy('name')->get();

        $technicians = User::where('role', 'technician')
            ->orderBy('name')
            ->get();

        return view('admin.tickets.create', compact(
            'customers',
            'devices',
            'categories',
            'technicians'
        ));
    }

    public function store(
        Request $request,
        TicketNumberService $numberService
    ): RedirectResponse {
        $validated = $request->validate($this->rules());

        $ticket = DB::transaction(function () use (
            $validated,
            $request,
            $numberService
        ) {
            $ticket = Ticket::create([
                ...$validated,
                'ticket_number' => $numberService->generate(),
                'created_by' => $request->user()->id,
                'status' => 'received',
                'received_at' => now(),
            ]);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'action' => 'created',
                'old_status' => null,
                'new_status' => 'received',
                'description' => 'Service ticket created.',
            ]);

            return $ticket;
        });

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('success', 'Service ticket created successfully.');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load([
            'customer',
            'device',
            'category',
            'technician',
            'creator',
            'activities' => fn ($query) =>
                $query->with('user')->latest(),
            'diagnoses' => fn ($query) =>
                $query->with('technician')->latest('id'),
            'costs' => fn ($query) => $query->latest('id'),
            'payments' => fn ($query) => $query->with('recorder')->latest('id'),
            'handover' => fn ($query) => $query->with('staff'),
        ]);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket): View
    {
        if (in_array($ticket->status, ['completed', 'cancelled'], true)) {
            abort(403, 'Closed tickets cannot be edited.');
        }

        $customers = Customer::orderBy('name')->get();

        $devices = Device::with('customer')
            ->orderBy('brand')
            ->get();

        $categories = TicketCategory::orderBy('name')->get();

        $technicians = User::where('role', 'technician')
            ->orderBy('name')
            ->get();

        return view('admin.tickets.edit', compact(
            'ticket',
            'customers',
            'devices',
            'categories',
            'technicians'
        ));
    }

    public function update(
        Request $request,
        Ticket $ticket
    ): RedirectResponse {
        if (in_array($ticket->status, ['completed', 'cancelled'], true)) {
            abort(403, 'Closed tickets cannot be edited.');
        }

        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($ticket, $validated, $request) {
            $oldTechnician = $ticket->technician_id;

            $ticket->update($validated);

            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'action' => 'updated',
                'old_status' => $ticket->status,
                'new_status' => $ticket->status,
                'description' => 'Service ticket information updated.',
            ]);

            if ($oldTechnician != $ticket->technician_id) {
                TicketActivity::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $request->user()->id,
                    'action' => 'technician_assigned',
                    'old_status' => $ticket->status,
                    'new_status' => $ticket->status,
                    'description' => 'Technician assignment changed.',
                ]);
            }
        });

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('success', 'Service ticket updated successfully.');
    }

    public function destroy(Request $request, Ticket $ticket): RedirectResponse
    {
        DB::transaction(function () use ($ticket, $request) {
            $locked = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);

            if (in_array($locked->status, ['completed', 'cancelled'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'ticket' => 'This ticket is already closed.',
                ]);
            }

            if ($locked->payments()->exists() || $locked->handover()->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'ticket' => 'Tickets with payments or handover records cannot be cancelled.',
                ]);
            }

            $oldStatus = $locked->status;

            $locked->update([
                'status' => 'cancelled',
                'closed_at' => now(),
            ]);

            $locked->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'cancelled',
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'description' => 'Service ticket cancelled by administrator.',
            ]);
        });

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Service ticket cancelled.');
    }

    public function decideDiagnosis(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'decision_note' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $ticket, $validated) {
            $locked = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);

            if ($locked->status !== 'awaiting_approval') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'decision' => 'Only tickets awaiting approval can be reviewed.',
                ]);
            }

            $diagnosis = $locked->diagnoses()
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $diagnosis || $diagnosis->approval_status !== 'pending'
                || (float) $diagnosis->estimated_cost <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'decision' => 'A pending paid diagnosis is required.',
                ]);
            }

            $decision = $validated['decision'];

            $diagnosis->update([
                'approval_status' => $decision,
                'approved_at' => $decision === 'approved' ? now() : null,
            ]);

            $oldStatus = $locked->status;
            $newStatus = $decision === 'approved'
                ? 'awaiting_approval'
                : 'on_hold';

            if ($decision === 'rejected') {
                $locked->update(['status' => 'on_hold']);
            }

            $locked->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'diagnosis_'.$decision,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'description' => 'Customer decision recorded by admin: '
                    .ucfirst($decision)
                    .'. Note: '.$validated['decision_note'],
            ]);
        });

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Diagnosis decision recorded successfully.');
    }

    private function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id'),
            ],
            'device_id' => [
                'required',
                'integer',
                Rule::exists('devices', 'id')->where(
                    'customer_id',
                    request()->input('customer_id')
                ),
            ],
            'ticket_category_id' => [
                'required',
                'integer',
                Rule::exists('ticket_categories', 'id'),
            ],
            'technician_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'technician'),
            ],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => [
                'required',
                Rule::in(Ticket::PRIORITIES),
            ],
            'initial_condition' => ['nullable', 'string', 'max:5000'],
            'accessories' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
