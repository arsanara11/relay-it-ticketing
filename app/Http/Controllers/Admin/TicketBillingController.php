<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TicketBillingController extends Controller
{
    private function requireReady(Ticket $ticket): void
    {
        if ($ticket->status !== 'ready_for_pickup') {
            throw ValidationException::withMessages([
                'billing' => 'Billing and handover are available only when the ticket is Ready for Pickup.',
            ]);
        }

        if ($ticket->handover()->exists()) {
            throw ValidationException::withMessages([
                'billing' => 'The device has already been handed over.',
            ]);
        }
    }

    private function lockedTicket(Ticket $ticket): Ticket
    {
        return Ticket::query()->lockForUpdate()->findOrFail($ticket->id);
    }

    private function totals(Ticket $ticket): array
    {
        // Use integer cents to avoid floating-point comparison errors.
        $total = 0;

        foreach ($ticket->costs()->get(['quantity', 'unit_price']) as $cost) {
            $total += (int) $cost->quantity
                * (int) round((float) $cost->unit_price * 100);
        }

        $paid = 0;

        foreach ($ticket->payments()->get(['amount']) as $payment) {
            $paid += (int) round((float) $payment->amount * 100);
        }

        return [$total, $paid];
    }

    public function storeCost(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['service', 'part', 'other'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'unit_price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
                'decimal:0,2',
            ],
        ]);

        DB::transaction(function () use ($request, $ticket, $data) {
            $locked = $this->lockedTicket($ticket);
            $this->requireReady($locked);

            if ($locked->payments()->exists()) {
                throw ValidationException::withMessages([
                    'billing' => 'Costs cannot be changed after the first payment.',
                ]);
            }

            $cost = $locked->costs()->create($data);

            $locked->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'cost_added',
                'old_status' => $locked->status,
                'new_status' => $locked->status,
                'description' => 'Added service cost: '
                    .$cost->description
                    .' (quantity '.$cost->quantity.').',
            ]);
        });

        return back()->with('success', 'Service cost added.');
    }

    public function destroyCost(
        Request $request,
        Ticket $ticket,
        int $cost
    ): RedirectResponse {
        DB::transaction(function () use ($request, $ticket, $cost) {
            $locked = $this->lockedTicket($ticket);
            $this->requireReady($locked);

            if ($locked->payments()->exists()) {
                throw ValidationException::withMessages([
                    'billing' => 'Costs cannot be deleted after the first payment.',
                ]);
            }

            $item = $locked->costs()->findOrFail($cost);
            $description = $item->description;

            $item->delete();

            $locked->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'cost_removed',
                'old_status' => $locked->status,
                'new_status' => $locked->status,
                'description' => 'Removed service cost: '.$description.'.',
            ]);
        });

        return back()->with('success', 'Service cost removed.');
    }

    public function storePayment(
        Request $request,
        Ticket $ticket
    ): RedirectResponse {
        $data = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999.99',
                'decimal:0,2',
            ],
            'payment_method' => [
                'required',
                Rule::in([
                    'cash',
                    'bank_transfer',
                    'qris',
                    'debit_card',
                    'other',
                ]),
            ],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $ticket, $data) {
            $locked = $this->lockedTicket($ticket);
            $this->requireReady($locked);

            [$total, $paid] = $this->totals($locked);

            $amount = (int) round((float) $data['amount'] * 100);

            if ($total <= 0 || $amount > $total - $paid) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment must not exceed the remaining positive balance.',
                ]);
            }

            $locked->payments()->create([
                ...$data,
                'recorded_by' => $request->user()->id,
                'paid_at' => now(),
            ]);

            $locked->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'payment_recorded',
                'old_status' => $locked->status,
                'new_status' => $locked->status,
                'description' => 'Recorded payment of Rp '
                    .number_format($amount / 100, 2, ',', '.').'.',
            ]);
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function storeHandover(
        Request $request,
        Ticket $ticket
    ): RedirectResponse {
        $data = $request->validate([
            'received_by' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $ticket, $data) {
            $locked = $this->lockedTicket($ticket);
            $this->requireReady($locked);

            [$total, $paid] = $this->totals($locked);

            if ($paid !== $total) {
                throw ValidationException::withMessages([
                    'billing' => 'All outstanding charges must be paid before handover.',
                ]);
            }

            $locked->handover()->create([
                ...$data,
                'handed_over_by' => $request->user()->id,
                'handed_over_at' => now(),
            ]);

            $locked->update([
                'status' => 'completed',
                'resolved_at' => $locked->resolved_at ?? now(),
                'closed_at' => now(),
            ]);

            $locked->activities()->create([
                'user_id' => $request->user()->id,
                'action' => 'device_handed_over',
                'old_status' => 'ready_for_pickup',
                'new_status' => 'completed',
                'description' => 'Device handed over to '
                    .$data['received_by']
                    .'; ticket completed.',
            ]);
        });

        return back()->with(
            'success',
            'Device handed over and ticket completed.'
        );
    }
}
