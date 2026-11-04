@csrf

@if (session('error'))
    <div class="relay-alert error" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="relay-form-grid">

    <div class="relay-form-field full">
        <label for="customer_id" class="relay-form-label">
            Customer <span class="relay-required">*</span>
        </label>

        <select
            id="customer_id"
            name="customer_id"
            class="relay-input"
            required
        >
            <option value="">Select a customer</option>

            @foreach ($customers as $customer)
                <option
                    value="{{ $customer->id }}"
                    @selected(
                        old(
                            'customer_id',
                            $device->customer_id ?? ''
                        ) == $customer->id
                    )
                >
                    {{ $customer->name }} — {{ $customer->phone }}
                </option>
            @endforeach
        </select>

        @error('customer_id')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror

        <span class="relay-device-form-hint">
            Select the customer who owns this device.
        </span>
    </div>

    <div class="relay-form-field">
        <label for="type" class="relay-form-label">
            Device Type <span class="relay-required">*</span>
        </label>

        <select
            id="type"
            name="type"
            class="relay-input"
            required
        >
            <option value="">Select device type</option>

            @foreach ([
                'Laptop',
                'Desktop PC',
                'Printer',
                'Monitor',
                'Networking',
                'Other'
            ] as $type)
                <option
                    value="{{ $type }}"
                    @selected(old('type', $device->type ?? '') === $type)
                >
                    {{ $type }}
                </option>
            @endforeach
        </select>

        @error('type')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field">
        <label for="brand" class="relay-form-label">
            Brand <span class="relay-required">*</span>
        </label>

        <input
            id="brand"
            type="text"
            name="brand"
            class="relay-input"
            value="{{ old('brand', $device->brand ?? '') }}"
            placeholder="e.g. ASUS, Lenovo, Epson"
            maxlength="150"
            required
        >

        @error('brand')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field">
        <label for="model" class="relay-form-label">
            Model
        </label>

        <input
            id="model"
            type="text"
            name="model"
            class="relay-input"
            value="{{ old('model', $device->model ?? '') }}"
            placeholder="e.g. Vivobook 14"
            maxlength="150"
        >

        @error('model')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field">
        <label for="serial_number" class="relay-form-label">
            Serial Number
        </label>

        <input
            id="serial_number"
            type="text"
            name="serial_number"
            class="relay-input"
            value="{{ old('serial_number', $device->serial_number ?? '') }}"
            placeholder="Enter device serial number"
            maxlength="255"
        >

        @error('serial_number')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="relay-form-field full">
        <label for="notes" class="relay-form-label">
            Additional Notes
        </label>

        <textarea
            id="notes"
            name="notes"
            class="relay-input"
            rows="4"
            maxlength="5000"
            placeholder="Physical condition, specifications, or additional details..."
        >{{ old('notes', $device->notes ?? '') }}</textarea>

        @error('notes')
            <span class="relay-form-error">{{ $message }}</span>
        @enderror
    </div>

</div>

<div class="relay-form-actions">

    <a href="{{ route('admin.devices.index') }}"
       class="relay-action-link">
        Cancel
    </a>

    <button type="submit" class="relay-button">
        Save Device
    </button>

</div>
