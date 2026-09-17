{{--
    "This author is a student" — the delegate-category tick shown against each author.

    Shared by the registration form and by Papers > Submit / Edit so the three forms
    cannot drift apart in wording or appearance. The card styling below is the same
    treatment the registration form uses for its other choices.

    $name     form field name, e.g. "co_authors[{index}][is_student]"
    $id       unique element id for the label to point at
    $checked  whether it starts ticked (default false)
    $label    caption; co-authors on the public form read "This co-author is a student"
    $sub      the small grey line under the caption
--}}
@php
    $checked = $checked ?? false;
    $label = $label ?? 'This author is a student';
    $sub = $sub ?? 'The student delegate rate applies to them';
@endphp

<label class="custom-check-card" for="{{ $id }}">
    <input type="hidden" name="{{ $name }}" value="0">
    {{-- co-author-student is what the forms' JavaScript reads when it restores a row. --}}
    <input type="checkbox" class="co-author-student" id="{{ $id }}" name="{{ $name }}" value="1"
           {{ $checked ? 'checked' : '' }}>
    <span class="custom-check-box"></span>
    <span class="custom-check-content">
        <span class="custom-check-title">{{ $label }}</span>
        <span class="custom-check-sub">{{ $sub }}</span>
    </span>
</label>

@once
    @push('style')
        <style>
            /* ========== Check card ========== */
            .custom-check-card {
                display: flex;
                align-items: flex-start;
                gap: 14px;
                padding: 14px 16px;
                border: 1.5px solid #dee2e6;
                border-radius: 10px;
                cursor: pointer;
                background: #fff;
                transition: all 0.18s;
                margin-bottom: 0;
                width: 100%;
            }
            .custom-check-card:hover {
                border-color: #0055A0;
                background: #f0f7ff;
            }
            .custom-check-card input[type="checkbox"] {
                display: none;
            }
            .custom-check-box {
                width: 22px;
                height: 22px;
                min-width: 22px;
                border: 2px solid #ccc;
                border-radius: 6px;
                margin-top: 1px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.18s;
                background: #fff;
            }
            .custom-check-card input[type="checkbox"]:checked ~ .custom-check-box {
                background: #0055A0;
                border-color: #0055A0;
            }
            .custom-check-card input[type="checkbox"]:checked ~ .custom-check-box::after {
                content: '';
                display: block;
                width: 5px;
                height: 10px;
                border: 2px solid #fff;
                border-top: none;
                border-left: none;
                transform: rotate(45deg) translate(-1px, -1px);
            }
            .custom-check-card input[type="checkbox"]:checked ~ .custom-check-content .custom-check-title {
                color: #0056cc;
            }
            .custom-check-card:has(input:checked) {
                border-color: #0055A0;
                background: #f0f7ff;
            }
            .custom-check-content {
                display: flex;
                flex-direction: column;
            }
            .custom-check-title {
                font-size: 14px;
                font-weight: 600;
                color: #333;
                line-height: 1.3;
            }
            .custom-check-sub {
                font-size: 12px;
                color: #888;
                margin-top: 2px;
            }
        </style>
    @endpush
@endonce
