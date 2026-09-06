@props(['provinces', 'id' => 'province', 'label' => 'Province or territory'])

<div>
    <label for="{{ $id }}" class="mb-2 block text-sm font-semibold">{{ $label }}</label>
    <select id="{{ $id }}" {{ $attributes->class('input-field') }}>
        <option value="">Choose a province or territory</option>
        @foreach ($provinces as $option)
            <option value="{{ $option->value }}">{{ $option->name() }}</option>
        @endforeach
    </select>
    {{ $slot }}
</div>
