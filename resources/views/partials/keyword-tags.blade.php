@php
    /**
     * Keyword entry as removable chips. The hidden input still carries the
     * comma-joined string the server validates and stores, so this is only an editor.
     *
     * @param string|array|null $value  current keywords, either shape
     * @param string|null $labelClass  extra classes for the label
     */
    // The models hold an array; the form posts a comma-joined string.
    $keywordValue = implode(', ', \App\Services\SubmissionRules::splitKeywords($value ?? old('keywords')));
    $keywordsMin = \App\Services\SubmissionRules::keywordsMin();
    $keywordsMax = \App\Services\SubmissionRules::keywordsMax();
@endphp

<label for="keyword_input" class="{{ $labelClass ?? '' }}"><strong>Keywords ({{ $keywordsMin }}-{{ $keywordsMax }})*</strong></label>
<div id="keyword_tags" class="form-control keyword-tag-field">
    {{-- data-skip-required keeps this out of any sweep that marks every field in a
         section required. It is only the editor: it is emptied after each chip, so a
         required attribute here would block the form for ever. The real value sits in
         the hidden input below, and the count is enforced server-side. --}}
    <input type="text" id="keyword_input" class="keyword-tag-input" data-skip-required
           placeholder="Type a keyword and press Enter" autocomplete="off">
</div>
<input type="hidden" id="keywords" name="keywords" value="{{ $keywordValue }}">
<div id="keyword_count_display" class="small mt-1 text-muted">Keywords: <span id="keyword_count">0</span> / {{ $keywordsMax }}</div>
@error('keywords') <span class="text-danger small"><strong>{{ $message }}</strong></span> @enderror

@once
    @push('style')
        <style>
            .keyword-tag-field {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 6px;
                min-height: 46px;
                height: auto;
                padding: 6px 8px;
                cursor: text;
            }
            .keyword-tag-field:focus-within {
                border-color: #0055A0;
                box-shadow: 0 0 0 0.2rem rgba(0, 85, 160, 0.15);
            }
            .keyword-tag {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: rgba(0, 85, 160, 0.1);
                color: #0055A0;
                border: 1px solid rgba(0, 85, 160, 0.25);
                border-radius: 14px;
                padding: 2px 6px 2px 10px;
                font-size: 13px;
                line-height: 1.6;
                white-space: nowrap;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .keyword-tag-remove {
                border: 0;
                background: transparent;
                color: #0055A0;
                font-size: 16px;
                line-height: 1;
                padding: 0 2px;
                cursor: pointer;
                opacity: 0.65;
            }
            .keyword-tag-remove:hover { opacity: 1; }
            .keyword-tag-input {
                flex: 1 1 140px;
                min-width: 140px;
                border: 0;
                outline: 0;
                padding: 4px 2px;
                font-size: 14px;
                background: transparent;
            }
            .keyword-tag-input:disabled { cursor: not-allowed; }
        </style>
    @endpush

    @push('script')
        <script>
            const KEYWORD_MIN = {{ \App\Services\SubmissionRules::keywordsMin() }};
            const KEYWORD_MAX = {{ \App\Services\SubmissionRules::keywordsMax() }};
            let keywordList = [];

            function renderKeywordTags() {
                const field = document.getElementById('keyword_tags');
                const input = document.getElementById('keyword_input');
                const hidden = document.getElementById('keywords');
                const counter = document.getElementById('keyword_count');
                const display = document.getElementById('keyword_count_display');
                if (!field || !input || !hidden) return;

                field.querySelectorAll('.keyword-tag').forEach(tag => tag.remove());

                keywordList.forEach((word, index) => {
                    const tag = document.createElement('span');
                    tag.className = 'keyword-tag';
                    tag.textContent = word;

                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'keyword-tag-remove';
                    remove.setAttribute('aria-label', 'Remove ' + word);
                    remove.textContent = '×';
                    remove.addEventListener('click', () => {
                        keywordList.splice(index, 1);
                        renderKeywordTags();
                        input.focus();
                    });

                    tag.appendChild(remove);
                    field.insertBefore(tag, input);
                });

                hidden.value = keywordList.join(', ');
                input.placeholder = keywordList.length >= KEYWORD_MAX
                    ? 'Maximum ' + KEYWORD_MAX + ' keywords reached'
                    : 'Type a keyword and press Enter';
                input.disabled = keywordList.length >= KEYWORD_MAX;

                if (counter) counter.innerText = keywordList.length;
                if (display) {
                    // Keywords are mandatory, so an empty field is flagged at once.
                    const ok = keywordList.length > 0;
                    display.classList.remove('text-muted');
                    display.classList.toggle('text-success', ok);
                    display.classList.toggle('text-danger', !ok);
                    display.classList.toggle('font-weight-bold', !ok);
                }
            }

            function addKeyword(raw) {
                const word = raw.trim().replace(/,+$/, '').trim();
                if (!word || keywordList.length >= KEYWORD_MAX) return;
                // Case-insensitive duplicate check so "AI" and "ai" don't both land.
                if (keywordList.some(existing => existing.toLowerCase() === word.toLowerCase())) return;
                keywordList.push(word);
                renderKeywordTags();
            }

            function initKeywordTags() {
                const input = document.getElementById('keyword_input');
                const hidden = document.getElementById('keywords');
                const field = document.getElementById('keyword_tags');
                if (!input || !hidden || !field) return;

                keywordList = (hidden.value || '').split(',').map(k => k.trim()).filter(k => k !== '').slice(0, KEYWORD_MAX);

                input.addEventListener('keydown', event => {
                    if (event.key === 'Enter' || event.key === ',') {
                        // Enter must not submit the form while keywords are being entered.
                        event.preventDefault();
                        addKeyword(input.value);
                        input.value = '';
                        return;
                    }
                    if (event.key === 'Backspace' && input.value === '' && keywordList.length) {
                        keywordList.pop();
                        renderKeywordTags();
                    }
                });

                // Pasting "a, b, c" should become three chips, not one.
                input.addEventListener('paste', event => {
                    const text = (event.clipboardData || window.clipboardData).getData('text');
                    if (!text.includes(',')) return;
                    event.preventDefault();
                    text.split(',').forEach(addKeyword);
                    input.value = '';
                });

                input.addEventListener('blur', () => {
                    addKeyword(input.value);
                    input.value = '';
                });

                field.addEventListener('click', () => { if (!input.disabled) input.focus(); });

                renderKeywordTags();
            }

            document.addEventListener('DOMContentLoaded', initKeywordTags);
        </script>
    @endpush
@endonce
