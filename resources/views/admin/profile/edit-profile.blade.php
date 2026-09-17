@extends('layouts.admin')
@section('content')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">
                {{ ($canEditAdminFields ?? true) ? 'Edit profile — ' . $user->name : 'Edit your details' }}
            </span>
            <a href="{{ ($canEditAdminFields ?? true) ? route('show-profile') : route('my-profile') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route("update-profile") }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $profile->id }}">

                <!-- Section 1: Personal Information -->
                <h5 class="text-primary mb-3 border-bottom pb-2"><i class="fas fa-user-circle mr-2"></i> Personal Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $profile->first_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $profile->last_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email (Read-only)</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="whatsapp_number">WhatsApp Number *</label>
                            <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $profile->whatsapp_number) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Professional Information -->
                <h5 class="text-primary mt-4 mb-3 border-bottom pb-2"><i class="fas fa-briefcase mr-2"></i> Professional Information</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="designation">Designation *</label>
                            <input type="text" id="designation" name="designation" class="form-control" value="{{ old('designation', $profile->designation) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department" class="form-control" value="{{ old('department', $profile->department) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="institution">Institution *</label>
                            <input type="text" id="institution" name="institution" class="form-control" value="{{ old('institution', $profile->institution) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="orcid_id">ORCID iD</label>
                            <input type="text" id="orcid_id" name="orcid_id" class="form-control @error('orcid_id') is-invalid @enderror"
                                   value="{{ old('orcid_id', $profile->orcid_id) }}" placeholder="0000-0000-0000-0000" maxlength="19">
                            <small class="form-text text-muted">Optional. Sixteen digits in four groups; the last character may be an X.</small>
                            @error('orcid_id') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="country_id">Country *</label>
                            <select name="country_id" id="country_id" class="form-control select2" required>
                                <option value="">--- Select Country ---</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ $profile->country_id == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Registration & Payment -->
                <h5 class="text-primary mt-4 mb-3 border-bottom pb-2"><i class="fas fa-file-invoice-dollar mr-2"></i> Registration & Payment</h5>
                <div class="row">
                    @if($canEditAdminFields ?? true)
                        {{-- The registration form asks for this and the fee is derived from it, so
                             correcting somebody's country without it would leave them on the wrong
                             tier. The options follow the country above, exactly as on registration. --}}
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="price_id">Delegate Category *</label>
                                <select name="price_id" id="price_id" class="form-control @error('price_id') is-invalid @enderror" required>
                                    <option value="">--- Select Category ---</option>
                                    @foreach($prices as $priceOption)
                                        <option value="{{ $priceOption->id }}" data-category="{{ $priceOption->category }}"
                                                {{ old('price_id', $profile->price_id) == $priceOption->id ? 'selected' : '' }}>
                                            {{ $priceOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Drives the registration fee. Only the categories the chosen country qualifies for are offered.
                                </small>
                                @error('price_id') <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span> @enderror
                            </div>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="participation_mode">Participation Mode *</label>
                            <select name="participation_mode" id="participation_mode" class="form-control" required>
                                <option value="onsite" {{ $profile->participation_mode == 'onsite' ? 'selected' : '' }}>Onsite</option>
                                <option value="online" {{ $profile->participation_mode == 'online' ? 'selected' : '' }}>Online</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- The fee, the payment status and the registration ID are the organising
                     committee's to set. A delegate is shown them, so they can check the figures
                     and query anything wrong, but the inputs are not rendered at all: the
                     controller refuses these fields from a delegate either way. --}}
                @unless($canEditAdminFields ?? true)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="p-3 mb-3 rounded border" style="background: #F4F7FA;">
                                <div class="row">
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <small class="text-muted d-block">Registration ID</small>
                                        <strong>{{ $profile->registration_id ?: 'Issued once payment clears' }}</strong>
                                    </div>
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <small class="text-muted d-block">Registration type</small>
                                        <strong>{{ $profile->is_author ? 'Author' : 'Participant' }}</strong>
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <small class="text-muted d-block">Delegate category</small>
                                        <strong>{{ $profile->price->name ?? '—' }}</strong>
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <small class="text-muted d-block">Amount</small>
                                        <strong>{{ $profile->currency ?? 'BDT' }} {{ number_format($profile->pay_amount ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted d-block">Payment</small>
                                        <strong>{{ $profile->payment_status == '1' ? 'Paid' : 'Unpaid' }}</strong>
                                    </div>
                                </div>
                                <small class="form-text text-muted mt-2 mb-0">
                                    <i class="fas fa-lock mr-1"></i>
                                    Set by the organising committee. Write to us if any of it looks wrong.
                                </small>
                            </div>
                        </div>
                    </div>
                @else
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group py-4">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_author" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_author" name="is_author" value="1" {{ $profile->is_author ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="is_author">Registered as Author?</label>
                            </div>
                            <small class="form-text text-muted">An author is billed per abstract; a participant pays one registration fee.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group py-4">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="author_list_confirmed" value="0">
                                <input type="checkbox" class="custom-control-input" id="author_list_confirmed" name="author_list_confirmed" value="1" {{ $profile->author_list_confirmed ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="author_list_confirmed">Author List Confirmed?</label>
                            </div>
                            <small class="form-text text-muted">Clearing this lets the author set student status again.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_status">Payment Status *</label>
                            <select name="payment_status" id="payment_status" class="form-control" required>
                                <option value="0" {{ $profile->payment_status == '0' ? 'selected' : '' }}>Not Complete</option>
                                <option value="1" {{ $profile->payment_status == '1' ? 'selected' : '' }}>Complete</option>
                            </select>
                            <small class="form-text text-muted">
                                Setting this to Complete issues the Registration ID and freezes the amount.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Derived, not typed. The registration ID comes from IdGeneratorService the
                     moment a payment clears, and the amount and currency from PricingService,
                     which reads the delegate category, the country and the papers on file.
                     A hand-typed figure here would survive only until the next recalculation
                     anywhere else in the portal, so the form shows the result instead of
                     inviting one. Saving re-derives both. --}}
                <div class="p-3 mb-3 rounded border" style="background: #F4F7FA;">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <small class="text-muted d-block">Registration ID</small>
                            <strong>{{ $profile->registration_id ?: 'Issued when the payment clears' }}</strong>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <small class="text-muted d-block">Amount due</small>
                            <strong>{{ $profile->currency ?? 'BDT' }} {{ number_format($profile->pay_amount ?? 0, 2) }}</strong>
                            <small class="text-muted d-block">
                                {{ ucwords(str_replace('_', ' ', \App\Services\PricingService::currentStage())) }} rate
                                @if($profile->payment_status == '1')
                                    &middot; frozen, this profile is paid
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4 text-md-right">
                            @if($profile->payment_status != '1')
                                {{-- Outside the main form: nested forms are not allowed. --}}
                                <button type="submit" form="recalculate-fee-form" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sync-alt mr-1"></i> Recalculate now
                                </button>
                            @endif
                        </div>
                    </div>
                    <small class="form-text text-muted mt-2 mb-0">
                        <i class="fas fa-calculator mr-1"></i>
                        Both are worked out from the delegate category, country and abstracts &mdash; change those above and save,
                        and the amount follows. To correct a figure that still looks wrong, fix what it is derived from.
                    </small>
                </div>
                @endunless

                <!-- Section 4: Workshops -->
                <h5 class="text-primary mt-4 mb-3 border-bottom pb-2"><i class="fas fa-laptop-code mr-2"></i> Workshops / Selected Sessions</h5>
                <div class="row">
                    @foreach ($schedules as $dayKey => $daySchedules)
                        <div class="col-md-4 mb-4">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="font-weight-bold mb-3"><i class="calendar-day mr-2"></i> {{ "Day - " . $dayKey }}</h6>
                                @foreach ($daySchedules as $schedule)
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="schedule_{{ $schedule->id }}" name="schedule_ids[]" value="{{ $schedule->id }}" {{ in_array($schedule->id, $workshops) ? 'checked' : '' }}>
                                        <label class="custom-control-label small" for="schedule_{{ $schedule->id }}">
                                            <strong>{{ $schedule->title }}</strong>
                                            <br>
                                            <span class="text-muted"><i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button class="btn btn-primary px-5 shadow-sm" type="submit">
                        <i class="fas fa-save mr-1"></i> {{ trans('global.save') }}
                    </button>
                    <a href="{{ ($canEditAdminFields ?? true) ? route('show-profile') : route('my-profile') }}" class="btn btn-light border ml-2">Cancel</a>
                </div>
            </form>

            @if(($canEditAdminFields ?? true) && $profile->payment_status != '1')
                {{-- Sibling of the edit form, not a child: the Recalculate button above
                     reaches it through its form attribute. --}}
                <form action="{{ route('profile.recalculate-fee') }}" method="POST" id="recalculate-fee-form" class="d-none">
                    @csrf
                    <input type="hidden" name="profile_id" value="{{ $profile->id }}">
                </form>
            @endif
        </div>
    </div>
@endsection

@if($canEditAdminFields ?? true)
@push('script')
<script>
    // Delegate category depends on country: Bangladesh gets the BDT tiers, the other
    // SAARC states get the SAARC rate, everyone else is international. Countries absent
    // from this map are international. The server enforces the same rule via
    // App\Rules\DelegateCategoryMatchesCountry, so this only saves the admin from
    // submitting a combination that would come back as a validation error.
    $(function () {
        const allowedCategoriesByCountry = @json($countryCategories);
        const defaultAllowedCategories = ['international'];

        const $country = $('#country_id');
        const $category = $('#price_id');
        if (!$country.length || !$category.length) {
            return;
        }

        function syncCategoryOptions() {
            const allowed = allowedCategoriesByCountry[$country.val()] || defaultAllowedCategories;
            let selectedStillAllowed = false;

            $category.find('option').each(function () {
                if (!this.value) return;
                const permitted = allowed.indexOf(this.dataset.category) !== -1;
                this.hidden = !permitted;
                this.disabled = !permitted;
                if (permitted && this.value === $category.val()) {
                    selectedStillAllowed = true;
                }
            });

            // Changing the country can strand the current category. Blanking it makes the
            // admin choose again rather than saving a tier the country cannot use.
            if (!selectedStillAllowed) {
                $category.val('');
            }
        }

        $country.on('change', syncCategoryOptions);
        syncCategoryOptions();
    });
</script>
@endpush
@endif
{{-- Removed: a second jQuery, a coupon-validation handler for a field that no longer
     exists (profiles has no coupon_code column, and PricingService never reads one), and
     a toggleTextField() for #radioYes / #textFieldContainer, neither of which is on this
     page. All of it sat outside @section, so Blade emitted it ahead of the layout and it
     never ran. --}}
<style>
    .bg-color {
        background: #fbf8f8;
        padding: 20px;
        height: 100%;
    }
</style>
