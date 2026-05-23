@props(['disabled' => false, 'label' => '', 'id' => '', 'value' => ''])

<div class="relative">
    <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'peer block w-full px-3 pt-6 pb-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm placeholder-transparent']) !!} placeholder="{{ $label }}" value="{{ $value }}">
    <label for="{{ $id }}" class="absolute left-3 top-2 text-xs text-gray-600 dark:text-gray-400 transition-all peer-placeholder-shown:text-base peer-placeholder-shown:top-4 peer-focus:top-2 peer-focus:text-xs">
        {{ $label }}
    </label>
</div>
