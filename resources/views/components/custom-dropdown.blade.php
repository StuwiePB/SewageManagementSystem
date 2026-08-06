@props([
    'id' => null,
    'value' => null,
    'options' => [],
    'ariaLabel' => null,
])

<select
    @if ($id) id="{{ $id }}" @endif
    @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    {{ $attributes->merge([
        'class' => 'custom-dropdown-select',
    ]) }}
    style="
        flex-shrink: 0;
        max-width: 42%;
        min-width: 108px;
        height: 32px;
        padding: 0 28px 0 10px;
        border-radius: 8px;
        border: 0.7px solid rgba(255, 255, 255, 0.21);
        background: rgba(66, 106, 120, 0.35) url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23ffffff' fill-opacity='0.65' d='M1 1l4 4 4-4'/%3E%3C/svg%3E\") no-repeat right 10px center;
        color: rgba(255, 255, 255, 0.9);
        font-family: Poppins, sans-serif;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        outline: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    "
>
    @foreach ($options as $optionValue => $optionLabel)
        <option
            value="{{ $optionValue }}"
            @selected((string) $value === (string) $optionValue)
            style="background: #060a16; color: #fff;"
        >
            {{ $optionLabel }}
        </option>
    @endforeach
</select>
