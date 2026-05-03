@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-gray-700 flex items-center gap-2']) }}>
        <flux:icon.check-circle class="size-5 text-gray-600" />
        {{ $status }}
    </div>
@endif
