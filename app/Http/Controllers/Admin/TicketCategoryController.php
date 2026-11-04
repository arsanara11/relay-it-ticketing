<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $categories = TicketCategory::query()
            ->withCount('tickets')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalCategories = TicketCategory::count();
        $usedCategories = TicketCategory::has('tickets')->count();
        $totalTickets = \App\Models\Ticket::count();

        return view('admin.ticket-categories.index', compact(
            'categories',
            'search',
            'totalCategories',
            'usedCategories',
            'totalTickets'
        ));
    }

    public function create(): View
    {
        return view('admin.ticket-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('ticket_categories', 'name'),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        TicketCategory::create($validated);

        return redirect()
            ->route('admin.ticket-categories.index')
            ->with('success', 'Ticket category created successfully.');
    }

    public function show(TicketCategory $ticketCategory): View
    {
        $ticketCategory->load([
            'tickets' => fn ($query) => $query->latest()->limit(10),
        ]);

        $ticketCount = $ticketCategory->tickets()->count();

        return view('admin.ticket-categories.show', compact(
            'ticketCategory',
            'ticketCount'
        ));
    }

    public function edit(TicketCategory $ticketCategory): View
    {
        return view('admin.ticket-categories.edit', compact(
            'ticketCategory'
        ));
    }

    public function update(
        Request $request,
        TicketCategory $ticketCategory
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('ticket_categories', 'name')
                    ->ignore($ticketCategory->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $ticketCategory->update($validated);

        return redirect()
            ->route('admin.ticket-categories.index')
            ->with('success', 'Ticket category updated successfully.');
    }

    public function destroy(TicketCategory $ticketCategory): RedirectResponse
    {
        if ($ticketCategory->tickets()->exists()) {
            return back()->with(
                'error',
                'This category cannot be deleted because it is linked to service tickets.'
            );
        }

        $ticketCategory->delete();

        return redirect()
            ->route('admin.ticket-categories.index')
            ->with('success', 'Ticket category deleted successfully.');
    }
}
