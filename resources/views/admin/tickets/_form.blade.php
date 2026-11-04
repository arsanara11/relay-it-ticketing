@csrf

<div
    x-data="{
        customerId: @js((string) old('customer_id', $ticket->customer_id ?? '')),
        deviceId: @js((string) old('device_id', $ticket->device_id ?? '')),
        devices: @js($devices->map(fn ($device) => [
            'id' => (string) $device->id,
            'customer_id' => (string) $device->customer_id,
            'label' => trim($device->brand . ' ' . $device->model)
        ])->values()),
        get availableDevices() {
            return this.devices.filter(
                device => device.customer_id === this.customerId
            );
        }
    }"
>
    <div class="relay-form-grid">

        <div class="relay-form-field">
            <label for="customer_id" class="relay-form-label">
                Customer <span class="relay-required">*</span>
            </label>

            <select
                id="customer_id"
                name="customer_id"
                class="relay-input"
                x-model="customerId"
                @change="deviceId = ''"
                required
            >
                <option value="">Select customer</option>

                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>

            @error('customer_id')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field">
            <label for="device_id" class="relay-form-label">
                Device <span class="relay-required">*</span>
            </label>

            <select
                id="device_id"
                name="device_id"
                class="relay-input"
                x-model="deviceId"
                :disabled="!customerId"
                required
            >
                <option value="">Select device</option>

                <template x-for="device in availableDevices" :key="device.id">
                    <option :value="device.id" x-text="device.label"></option>
                </template>
            </select>

            @error('device_id')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field">
            <label for="ticket_category_id" class="relay-form-label">
                Service Category <span class="relay-required">*</span>
            </label>

            <select
                id="ticket_category_id"
                name="ticket_category_id"
                class="relay-input"
                required
            >
                <option value="">Select category</option>

                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected(old('ticket_category_id', $ticket->ticket_category_id ?? '') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            @error('ticket_category_id')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field">
            <label for="technician_id" class="relay-form-label">
                Assigned Technician
            </label>

            <select
                id="technician_id"
                name="technician_id"
                class="relay-input"
            >
                <option value="">Unassigned</option>

                @foreach ($technicians as $technician)
                    <option value="{{ $technician->id }}"
                        @selected(old('technician_id', $ticket->technician_id ?? '') == $technician->id)>
                        {{ $technician->name }}
                    </option>
                @endforeach
            </select>

            @error('technician_id')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field">
            <label for="priority" class="relay-form-label">
                Priority <span class="relay-required">*</span>
            </label>

            <select
                id="priority"
                name="priority"
                class="relay-input"
                required
            >
                @foreach (\App\Models\Ticket::PRIORITIES as $priority)
                    <option value="{{ $priority }}"
                        @selected(old('priority', $ticket->priority ?? 'medium') === $priority)>
                        {{ ucfirst($priority) }}
                    </option>
                @endforeach
            </select>

            @error('priority')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field full">
            <label for="subject" class="relay-form-label">
                Issue Subject <span class="relay-required">*</span>
            </label>

            <input
                id="subject"
                type="text"
                name="subject"
                class="relay-input"
                value="{{ old('subject', $ticket->subject ?? '') }}"
                maxlength="255"
                placeholder="e.g. Laptop won't turn on"
                required
            >

            @error('subject')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field full">
            <label for="description" class="relay-form-label">
                Issue Description <span class="relay-required">*</span>
            </label>

            <textarea
                id="description"
                name="description"
                class="relay-input"
                rows="4"
                maxlength="5000"
                required
                placeholder="Describe the issue reported by the customer..."
            >{{ old('description', $ticket->description ?? '') }}</textarea>

            @error('description')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field full">
            <label for="initial_condition" class="relay-form-label">
                Initial Device Condition
            </label>

            <textarea
                id="initial_condition"
                name="initial_condition"
                class="relay-input"
                rows="3"
                maxlength="5000"
                placeholder="Record the device's physical condition on arrival..."
            >{{ old('initial_condition', $ticket->initial_condition ?? '') }}</textarea>

            @error('initial_condition')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="relay-form-field full">
            <label for="accessories" class="relay-form-label">
                Included Accessories
            </label>

            <textarea
                id="accessories"
                name="accessories"
                class="relay-input"
                rows="3"
                maxlength="2000"
                placeholder="e.g. Charger, laptop bag, power cable..."
            >{{ old('accessories', $ticket->accessories ?? '') }}</textarea>

            @error('accessories')
                <span class="relay-form-error">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <div class="relay-form-actions">
        <a href="{{ route('admin.tickets.index') }}"
           class="relay-action-link">
            Cancel
        </a>

        <button type="submit" class="relay-button">
            Save Service Ticket
        </button>
    </div>
</div>
