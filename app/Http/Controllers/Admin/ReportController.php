<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCost;
use App\Models\TicketPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function filters(Request $request): array
    {
        $data = Validator::make($request->all(), [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
        ])->validate();

        $start = Carbon::parse(
            $data['start_date'] ?? now()->startOfMonth()->subMonths(5)->toDateString()
        )->startOfDay();

        $end = Carbon::parse(
            $data['end_date'] ?? now()->toDateString()
        )->endOfDay();

        if ($start->gt($end)) {
            abort(422, 'Start date must not be later than end date.');
        }

        if ($start->diffInDays($end) > 3660) {
            abort(422, 'Maximum report range is 10 years.');
        }

        $technicianId = $data['technician_id'] ?? null;

        if ($technicianId && ! User::where('id', $technicianId)
            ->where('role', 'technician')->exists()) {
            abort(422, 'Invalid technician.');
        }

        return [$start, $end, $technicianId];
    }

    private function ticketScope($technicianId)
    {
        return Ticket::query()
            ->when($technicianId, fn ($q) =>
                $q->where('technician_id', $technicianId)
            );
    }

    private function reportData(
        Carbon $start,
        Carbon $end,
        ?int $technicianId
    ): array {
        $tickets = $this->ticketScope($technicianId)
            ->whereBetween('created_at', [$start, $end]);

        $totalTickets = (clone $tickets)->count();
        $completedTickets = (clone $tickets)
            ->where('status', 'completed')->count();
        $cancelledTickets = (clone $tickets)
            ->where('status', 'cancelled')->count();
        $activeTickets = $totalTickets - $completedTickets - $cancelledTickets;

        $statusCounts = (clone $tickets)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $ticketRows = (clone $tickets)
            ->select('created_at')
            ->get();

        $paymentRows = TicketPayment::query()
            ->when($technicianId, fn ($q) =>
                $q->whereHas('ticket', fn ($t) =>
                    $t->where('technician_id', $technicianId)
                )
            )
            ->whereBetween('paid_at', [$start, $end])
            ->get(['amount', 'paid_at']);

        $revenue = $paymentRows->sum(fn ($p) => (float) $p->amount);

        $monthly = [];
        $cursor = $start->copy()->startOfMonth();
        $lastMonth = $end->copy()->startOfMonth();

        while ($cursor->lte($lastMonth)) {
            $key = $cursor->format('Y-m');

            $monthly[$key] = [
                'month' => $cursor->format('M Y'),
                'tickets' => 0,
                'revenue' => 0,
            ];

            $cursor->addMonth();
        }

        foreach ($ticketRows as $ticket) {
            $key = Carbon::parse($ticket->created_at)->format('Y-m');
            if (isset($monthly[$key])) {
                $monthly[$key]['tickets']++;
            }
        }

        foreach ($paymentRows as $payment) {
            $key = Carbon::parse($payment->paid_at)->format('Y-m');
            if (isset($monthly[$key])) {
                $monthly[$key]['revenue'] += (float) $payment->amount;
            }
        }

        // Outstanding is a balance as of the selected end date.
        // It is not restricted to tickets created during the period.
        $costs = TicketCost::query()
            ->whereHas('ticket', function ($q) use ($technicianId) {
                $q->where('status', '!=', 'cancelled')
                    ->when($technicianId, fn ($t) =>
                        $t->where('technician_id', $technicianId)
                    );
            })
            ->where('created_at', '<=', $end)
            ->get(['quantity', 'unit_price']);

        $allPayments = TicketPayment::query()
            ->whereHas('ticket', function ($q) use ($technicianId) {
                $q->where('status', '!=', 'cancelled')
                    ->when($technicianId, fn ($t) =>
                        $t->where('technician_id', $technicianId)
                    );
            })
            ->where('paid_at', '<=', $end)
            ->get(['amount']);

        $chargesCents = $costs->sum(fn ($c) =>
            (int) $c->quantity
            * (int) round((float) $c->unit_price * 100)
        );

        $paidCents = $allPayments->sum(fn ($p) =>
            (int) round((float) $p->amount * 100)
        );

        $outstanding = max(0, ($chargesCents - $paidCents) / 100);

        $technicians = User::query()
            ->where('role', 'technician')
            ->when($technicianId, fn ($q) =>
                $q->whereKey($technicianId)
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        $performance = $technicians->map(function ($technician) use ($start, $end) {
            $query = Ticket::query()
                ->where('technician_id', $technician->id)
                ->whereBetween('created_at', [$start, $end]);

            $assigned = (clone $query)->count();
            $completed = (clone $query)
                ->where('status', 'completed')->count();

            return [
                'name' => $technician->name,
                'assigned' => $assigned,
                'completed' => $completed,
                'rate' => $assigned > 0
                    ? round($completed / $assigned * 100, 1)
                    : 0,
            ];
        })->sortByDesc('completed')->values();

        return compact(
            'totalTickets',
            'completedTickets',
            'cancelledTickets',
            'activeTickets',
            'statusCounts',
            'revenue',
            'outstanding',
            'monthly',
            'performance'
        );
    }

    public function index(Request $request): View
    {
        [$start, $end, $technicianId] = $this->filters($request);

        $data = $this->reportData($start, $end, $technicianId);

        $technicians = User::where('role', 'technician')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.reports.index', [
            ...$data,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'technicianId' => $technicianId,
            'technicians' => $technicians,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$start, $end, $technicianId] = $this->filters($request);

        $data = $this->reportData($start, $end, $technicianId);

        $filename = 'relay-report-'
            .$start->format('Ymd').'-'
            .$end->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($data, $start, $end) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility.
            fwrite($output, "\xEF\xBB\xBF");

            $write = function (array $row) use ($output) {
                // Prevent spreadsheet formula injection.
                $safe = array_map(function ($value) {
                    $value = (string) $value;

                    if (preg_match('/^[\s]*[=+\-@]/u', $value)) {
                        return "'".$value;
                    }

                    return $value;
                }, $row);

                fputcsv($output, $safe);
            };

            $write(['Relay Reports & Analytics']);
            $write(['Start Date', $start->toDateString()]);
            $write(['End Date', $end->toDateString()]);
            $write([]);

            $write(['Metric', 'Value']);
            foreach ([
                'Total Tickets' => $data['totalTickets'],
                'Active Tickets' => $data['activeTickets'],
                'Completed Tickets' => $data['completedTickets'],
                'Cancelled Tickets' => $data['cancelledTickets'],
                'Revenue (IDR)' => $data['revenue'],
                'Outstanding (IDR)' => $data['outstanding'],
            ] as $label => $value) {
                $write([$label, $value]);
            }

            $write([]);
            $write(['Month', 'Tickets Created', 'Revenue (IDR)']);

            foreach ($data['monthly'] as $month) {
                $write([
                    $month['month'],
                    $month['tickets'],
                    $month['revenue'],
                ]);
            }

            $write([]);
            $write(['Status', 'Total']);

            foreach (Ticket::STATUSES as $status) {
                $write([
                    $status,
                    $data['statusCounts'][$status] ?? 0,
                ]);
            }

            $write([]);
            $write(['Technician', 'Assigned', 'Completed', 'Completion Rate (%)']);

            foreach ($data['performance'] as $row) {
                $write([
                    $row['name'],
                    $row['assigned'],
                    $row['completed'],
                    $row['rate'],
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
