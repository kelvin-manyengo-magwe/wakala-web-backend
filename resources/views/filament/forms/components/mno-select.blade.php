{{-- resources/views/components/forms/mno-select-option.blade.php --}}
@props([
    'value' => '',
    'label' => '',
    'logoUrl' => '',
    'isSelected' => false,
])
<div {{ $attributes->class([
        'flex items-center space-x-3 rtl:space-x-reverse p-2 rounded-md cursor-pointer',
        'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700',
        'bg-primary-100 dark:bg-primary-700 text-primary-600 dark:text-primary-300' => $isSelected,
    ])
}}>
    @if($logoUrl)
        <img src="{{ $logoUrl }}"
             alt="{{ $label }} Logo"
             class="h-6 w-6 object-contain shrink-0"
        >
    @else
        <div class="h-6 w-6 shrink-0 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
            <x-heroicon-s-question-mark-circle class="h-4 w-4 text-gray-500 dark:text-gray-400"/>
        </div>
    @endif
    <span>{{ $label }}</span>
</div>
