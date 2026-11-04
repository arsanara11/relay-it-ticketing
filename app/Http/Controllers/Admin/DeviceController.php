<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $devices = Device::query()
            ->with('customer')
            ->withCount('tickets')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.devices.index', compact(
            'devices',
            'search'
        ));
    }

    public function create(): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('admin.devices.create', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Device::create($validated);

        return redirect()
            ->route('admin.devices.index')
            ->with('success', 'Device registered successfully.');
    }

    public function show(Device $device): View
    {
        $device->load([
            'customer',
            'tickets' => fn ($query) => $query->latest(),
        ]);

        return view('admin.devices.show', compact('device'));
    }

    public function edit(Device $device): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('admin.devices.edit', compact(
            'device',
            'customers'
        ));
    }

    public function update(
        Request $request,
        Device $device
    ): RedirectResponse {
        $validated = $request->validate($this->rules());

        // A device with existing tickets must retain its owner.
        if (
            $device->tickets()->exists() &&
            (int) $validated['customer_id'] !== (int) $device->customer_id
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Cannot change the owner of a device with service tickets.'
                );
        }

        $device->update($validated);

        return redirect()
            ->route('admin.devices.index')
            ->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        if ($device->tickets()->exists()) {
            return back()->with(
                'error',
                'Device cannot be deleted while linked to service tickets.'
            );
        }

        $device->delete();

        return redirect()
            ->route('admin.devices.index')
            ->with('success', 'Device deleted successfully.');
    }

    private function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id'),
            ],
            'type' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
