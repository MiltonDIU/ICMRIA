@php
    /**
     * Live "amount payable" panel. Mirrors App\Services\PricingService::calculatePaperCost
     * so the figure shown is the figure charged.
     *
     * Expects $priceTable (id => name/category/currency/early_bird/regular) and
     * $currentStage from the controller.
     *
     * It adapts to whichever form it sits in: when a #price_id select exists (the
     * registration form) that is the registrant and the .co-author-entry rows are
     * co-authors; on the paper form there is no such select and the first author row
     * is the registrant.
     */
@endphp

<div id="fee_summary" class="mt-4 p-3 rounded" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0" style="color: #003366;">
            <i class="fa fa-calculator text-primary mr-1"></i> Amount Payable
        </h6>
        <span id="fee_stage_badge" class="badge badge-primary"></span>
    </div>
    <div id="fee_breakdown" style="font-size: 13px;"></div>
    <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
        <strong style="color: #003366;">Total</strong>
        <strong id="fee_total" style="font-size: 20px; color: #0055A0;">—</strong>
    </div>
    <small id="fee_note" class="form-text text-muted mt-2"></small>
</div>

@once
    @push('script')
        <script>
            const priceTable = @json($priceTable);
            const currentStage = @json($currentStage);

            function priceAmount(priceId) {
                const row = priceTable[priceId];
                if (!row) return null;
                return { ...row, amount: currentStage === 'early_bird' ? row.early_bird : row.regular };
            }

            function money(currency, amount) {
                const symbol = currency === 'USD' ? 'US$ ' : '৳ ';
                return symbol + amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }

            function collectFeeRows() {
                const selfSelect = document.getElementById('price_id');
                const authorToggle = document.querySelector('input[name="is_author"]:checked');
                const countAuthors = !authorToggle || authorToggle.value === '1';
                const entries = countAuthors ? Array.from(document.querySelectorAll('.co-author-entry')) : [];

                // A paper is invoiced in one currency: the registrant's.
                const main = selfSelect
                    ? priceAmount(selfSelect.value)
                    : priceAmount(entries[0]?.querySelector('.delegate-category-select')?.value);

                if (!main) return null;

                const rows = [];
                if (selfSelect) {
                    rows.push({ label: 'You — ' + main.name, amount: main.amount, adjusted: false });
                }

                entries.forEach((entry, index) => {
                    const author = priceAmount(entry.querySelector('.delegate-category-select')?.value);
                    if (!author) return;

                    const sameCurrency = author.currency === main.currency;
                    const isRegistrantRow = !selfSelect && index === 0;

                    rows.push({
                        label: isRegistrantRow
                            ? 'You — ' + author.name
                            : 'Co-Author #' + (selfSelect ? index + 1 : index) + ' — ' + (sameCurrency ? author.name : main.name),
                        amount: sameCurrency ? author.amount : main.amount,
                        adjusted: !sameCurrency && !isRegistrantRow,
                    });
                });

                return { currency: main.currency, rows };
            }

            function renderFeeSummary() {
                const breakdown = document.getElementById('fee_breakdown');
                const totalEl = document.getElementById('fee_total');
                const stageEl = document.getElementById('fee_stage_badge');
                const noteEl = document.getElementById('fee_note');
                if (!breakdown || !totalEl) return;

                stageEl.textContent = currentStage === 'early_bird' ? 'Early Bird Rate' : 'Regular Rate';

                const result = collectFeeRows();
                if (!result) {
                    breakdown.innerHTML = '<span class="text-muted">Choose the country and delegate category to see the amount.</span>';
                    totalEl.textContent = '—';
                    noteEl.textContent = '';
                    return;
                }

                let total = 0;
                breakdown.innerHTML = result.rows.map(row => {
                    total += row.amount;
                    const flag = row.adjusted
                        ? ' <span class="badge badge-warning" title="Billed at the registrant\'s rate because the paper is invoiced in one currency">rate adjusted</span>'
                        : '';
                    return '<div class="d-flex justify-content-between py-1">'
                        + '<span class="text-muted">' + row.label + flag + '</span>'
                        + '<span>' + money(result.currency, row.amount) + '</span>'
                        + '</div>';
                }).join('');

                totalEl.textContent = money(result.currency, total);

                noteEl.textContent = result.rows.some(r => r.adjusted)
                    ? 'One or more co-authors are billed at the registrant\'s rate: a paper is invoiced in a single currency.'
                    : (result.rows.length > 1 ? 'Every listed author is charged a registration fee.' : '');
            }

            document.addEventListener('change', renderFeeSummary);
            document.addEventListener('DOMContentLoaded', renderFeeSummary);
        </script>
    @endpush
@endonce
