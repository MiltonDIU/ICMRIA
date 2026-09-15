{{--
    The registration fee for a paper: paid, awaiting verification, or to be paid. A
    transfer made outside the online gateway is reported here with its proof.
--}}
@php
    $paid = \App\Services\ProceedingsRules::isPaid($paper);
    $needsPayment = \App\Services\ProceedingsRules::needsPayment($paper);
@endphp

@if($paid || $needsPayment || $paper->paymentProofs->isNotEmpty())
    @php
        $isOwner = auth()->id() === $paper->user_id;
        $pending = $paper->paymentProofs->firstWhere('status', 'submitted');
        $canReport = $isOwner && $needsPayment && !$pending && \App\Services\ProceedingsRules::paymentWindowIsOpen();
        $proofStyles = ['submitted' => 'warning', 'verified' => 'success', 'rejected' => 'danger'];

        $fee = null;
        if ($needsPayment && $paper->user && $paper->user->profile) {
            try {
                $fee = \App\Services\PricingService::calculatePaperCost($paper->user->profile, $paper);
            } catch (\Throwable $e) {
                $fee = null;
            }
        }
    @endphp

    <div class="card shadow-sm border-0 mb-4 rounded-lg">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="font-weight-bold"><i class="fas fa-receipt mr-2 text-primary"></i> Registration Fee</span>
            @if($paid)
                <span class="badge badge-success px-3 py-2">Paid</span>
            @elseif($pending)
                <span class="badge badge-warning px-3 py-2">Awaiting verification</span>
            @else
                <span class="badge badge-secondary px-3 py-2">Unpaid</span>
            @endif
        </div>
        <div class="card-body">
            @if($paid)
                <p class="mb-3">
                    The registration fee for this paper has been received
                    @if($paper->pay_amount)({{ $paper->currency }} {{ number_format((float) $paper->pay_amount, 2) }})@endif.
                </p>
            @elseif($fee)
                <p class="mb-2">
                    Amount due for this paper: <strong>{{ $fee['currency'] }} {{ number_format((float) $fee['final_price'], 2) }}</strong>
                </p>
                <p class="small text-muted mb-3">
                    Pay online with the <strong>Pay</strong> button in your Papers list, or, if you paid by bank or mobile
                    transfer, report it below with the receipt so the conference team can verify it.
                </p>
            @endif

            @if($paper->paymentProofs->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm mb-3">
                        <thead>
                            <tr><th>Reported</th><th>Method</th><th>Transaction</th><th>Amount</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($paper->paymentProofs as $proof)
                                <tr>
                                    <td><small>{{ $proof->created_at->format('j M Y') }}</small></td>
                                    <td><small>{{ $proof->methodLabel() }}</small></td>
                                    <td><small>{{ $proof->transaction_id }}</small></td>
                                    <td><small>{{ $proof->currency }} {{ number_format((float) $proof->amount, 2) }}</small></td>
                                    <td>
                                        <span class="badge badge-{{ $proofStyles[$proof->status] ?? 'light' }}">{{ \App\Models\PaperPaymentProof::STATUSES[$proof->status] ?? $proof->status }}</span>
                                        @if($proof->status === 'rejected' && $proof->review_note)
                                            <br><small class="text-danger">{{ $proof->review_note }}</small>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('papers.payment-proof.download', $proof->id) }}" class="btn btn-sm btn-outline-secondary" title="Download proof">
                                            <i class="fas fa-paperclip"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($canReport)
                <details>
                    <summary class="btn btn-sm btn-outline-primary">I paid by bank or mobile transfer</summary>
                    <form action="{{ route('papers.payment-proof.store', $paper->id) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="method">Payment method *</label>
                                <select id="method" name="method" class="form-control" required>
                                    @foreach(\App\Models\PaperPaymentProof::METHODS as $value => $label)
                                        <option value="{{ $value }}" {{ old('method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="transaction_id">Transaction ID / reference *</label>
                                <input type="text" id="transaction_id" name="transaction_id" class="form-control" maxlength="100" value="{{ old('transaction_id') }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="amount">Amount paid *</label>
                                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01"
                                       value="{{ old('amount', $fee['final_price'] ?? '') }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="currency">Currency *</label>
                                <select id="currency" name="currency" class="form-control" required>
                                    @foreach(\App\Models\PaperPaymentProof::CURRENCIES as $currency)
                                        <option value="{{ $currency }}" {{ old('currency', $fee['currency'] ?? 'BDT') === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="paid_on">Date paid *</label>
                                <input type="date" id="paid_on" name="paid_on" class="form-control" max="{{ now()->toDateString() }}" value="{{ old('paid_on') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="proof">Receipt or screenshot *</label>
                            <input type="file" id="proof" name="proof" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="form-text text-muted">PDF or image, up to 5 MB.</small>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send for verification</button>
                    </form>
                </details>
            @elseif($isOwner && $pending)
                <p class="small text-muted mb-0">Your reported payment is awaiting verification by the conference team.</p>
            @endif
        </div>
    </div>
@endif
