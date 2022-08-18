<div class="mb-2">
    <label for="addressTypeCode" class="form-label">Address Type</label>
    <select id="addressTypeCode" name="addressTypeCode" required class="form-select">
        @foreach($addressTypes as $addressType)
            <option
                {{ $addressType->id === ($address->atp_id ?? null) ? 'selected' : '' }}
                value="{{ $addressType->code }}"
                data-id="{{ $addressType->id }}"
            >{{ $addressType->name }}</option>
        @endforeach
    </select>
</div>
@foreach ($addressObjectTypes as $addressObjectType)
    <div class="mb-3 address-type-{{ $addressObjectType->atp_id }}">
        <label for="address{{ $addressObjectType->code }}"
               class="form-label">{{ $addressObjectType->name }}</label>
        <input
            type="text"
            class="form-control"
            id="address{{ $addressObjectType->code }}"
            name="address[{{ $addressObjectType->code }}]"
            value="{{ $addressValues[$addressObjectType->code]->value ?? '' }}"
        >
    </div>
@endforeach
