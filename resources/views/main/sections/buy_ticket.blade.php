@php
    // One row per category now carries both stages, so the two tabs render the same
    // rows and differ only in which column they read.
    $earlyBirdPrices = $prices;
    $regularPrices   = $prices;

    // Category Metadata for visual identity and audience descriptions
    $categoryMeta = [
        'Student Presenter / Participant' => [
            'icon'      => 'fa-graduation-cap',
            'badge'     => 'Student Delegate',
            'target'    => 'Full-time Undergraduate, Graduate & PhD Students',
            'color'     => '#0055A0',
            'bg_tint'   => 'rgba(0, 85, 160, 0.08)',
            'short'     => 'Student',
        ],
        'Academic Presenter / Participant' => [
            'icon'      => 'fa-university',
            'badge'     => 'Academic Scholar',
            'target'    => 'Faculty Members, Researchers & Postdoctoral Fellows',
            'color'     => '#003366',
            'bg_tint'   => 'rgba(0, 51, 102, 0.08)',
            'short'     => 'Academic',
        ],
        'Industry / R&D Presenter / Participant' => [
            'icon'      => 'fa-briefcase',
            'badge'     => 'Corporate & Industry',
            'target'    => 'Corporate Practitioners, R&D Engineers & Tech Executives',
            'color'     => '#004080',
            'bg_tint'   => 'rgba(0, 64, 128, 0.08)',
            'short'     => 'Industry / R&D',
        ],
        'SAARC Presenter / Participant' => [
            'icon'      => 'fa-globe',
            'badge'     => 'SAARC Nations',
            'target'    => 'Delegates & Scholars from SAARC Member Countries',
            'color'     => '#0055A0',
            'bg_tint'   => 'rgba(0, 85, 160, 0.08)',
            'short'     => 'SAARC',
        ],
        'International Presenter / Participant' => [
            'icon'      => 'fa-plane',
            'badge'     => 'International Delegate',
            'target'    => 'Authors, Delegates & Speakers Worldwide',
            'color'     => '#003366',
            'bg_tint'   => 'rgba(0, 51, 102, 0.08)',
            'short'     => 'International',
        ],
    ];

    // Core Conference Entitlements included in ALL registration tiers
    $privileges = [
        [
            'icon'        => 'fa-microphone',
            'color'       => '#003366',
            'bg'          => 'rgba(0, 51, 102, 0.08)',
            'title'       => 'Keynote & Technical Sessions',
            'description' => 'Full access to all keynote addresses, invited speeches, and 8 parallel technical tracks.',
            'tag'         => 'Full Access',
            'highlight'   => false,
        ],
        [
            'icon'        => 'fa-certificate',
            'color'       => '#0055A0',
            'bg'          => 'rgba(0, 85, 160, 0.08)',
            'title'       => 'Official Presentation Certificate',
            'description' => 'Formal, verifiable Certificate of Paper Presentation or Delegate Participation.',
            'tag'         => 'Official',
            'highlight'   => false,
        ],
        [
            'icon'        => 'fa-book',
            'color'       => '#003366',
            'bg'          => 'rgba(0, 51, 102, 0.08)',
            'title'       => 'Scopus Q2 Journal Consideration',
            'description' => 'Eligible accepted and presented papers will be considered for Scopus-indexed Q2 publication.',
            'tag'         => 'Scopus Q2',
            'highlight'   => true,
        ],
    ];

    // Build unified comparison dataset for table and cards
    $comparisonRows = [];
    $allCatNames = $prices->pluck('name')->unique();
    foreach ($allCatNames as $catName) {
        $eb = $earlyBirdPrices->firstWhere('name', $catName);
        $reg = $regularPrices->firstWhere('name', $catName);
        $meta = $categoryMeta[$catName] ?? [
            'icon'    => 'fa-user',
            'badge'   => 'Delegate',
            'target'  => 'Conference Participant',
            'color'   => '#0055A0',
            'bg_tint' => 'rgba(0, 85, 160, 0.08)',
            'short'   => $catName,
        ];

        $currency = ($eb && strtoupper((string)($eb->currency ?? '')) === 'USD') || ($reg && strtoupper((string)($reg->currency ?? '')) === 'USD') ? 'USD' : 'BDT';
        $currSymbol = $currency === 'USD' ? 'US$' : '৳';

        $ebPrice = $eb ? (float)$eb->early_bird_price : null;
        $regPrice = $reg ? (float)$reg->regular_price : null;
        $savings = ($ebPrice && $regPrice && $regPrice > $ebPrice) ? ($regPrice - $ebPrice) : null;

        $comparisonRows[] = [
            'name'       => $catName,
            'meta'       => $meta,
            'currency'   => $currency,
            'symbol'     => $currSymbol,
            'eb_price'   => $ebPrice,
            'reg_price'  => $regPrice,
            'savings'    => $savings,
            'eb_obj'     => $eb,
            'reg_obj'    => $reg,
        ];
    }
@endphp

<section id="buy-tickets" class="section-with-bg wow fadeInUp">
  <span id="important-dates"></span>
  <div class="container">

    <div class="section-header text-center">
      <h2>Registration & Participation Fees</h2>
      <p>Transparent fee structure with comprehensive, all-inclusive privileges for all authors and delegates</p>
    </div>

    <!-- ========================================================= -->
{{-- [TEMPORARILY COMMENTED OUT - To be finalized with conference committee]
    <!-- 1. UNIVERSAL ALL-INCLUSIVE PRIVILEGES SHOWCASE            -->
    <!-- ========================================================= -->
    <div class="all-inclusive-showcase mb-5">
      <div class="showcase-banner">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <div class="showcase-tag-wrap mb-2">
              <span class="privilege-pill-badge"><i class="fa fa-star mr-1"></i> All-Inclusive Package</span>
              <span class="privilege-sub-badge"><i class="fa fa-check mr-1"></i> Identical Benefits for All Categories</span>
            </div>
            <h3 class="showcase-title">What Every Registration Package Includes</h3>
            <p class="showcase-subtitle mb-0">
              Every registered author, presenter, and participant receives <strong>100% full access</strong> to the core conference entitlements below. Only the registration fee varies based on delegate category.
            </p>
          </div>
          <div class="col-lg-4 text-lg-right text-center mt-3 mt-lg-0">
            <a href="{{ route('book-ticket') }}" class="btn btn-showcase-cta">
              <i class="fa fa-pencil-square-o mr-1"></i> Register Now
            </a>
          </div>
        </div>
      </div>

      <!-- 7 Privileges Grid -->
      <div class="privileges-grid mt-4">
        @foreach($privileges as $idx => $priv)
          <div class="privilege-card @if($priv['highlight']) highlight-privilege @endif">
            <div class="privilege-icon-wrap" style="background: {{ $priv['bg'] }}; color: {{ $priv['color'] }};">
              <i class="fa {{ $priv['icon'] }}"></i>
            </div>
            <div class="privilege-content">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <h5 class="privilege-card-title mb-0">{{ $priv['title'] }}</h5>
                <span class="badge privilege-badge-tag">{{ $priv['tag'] }}</span>
              </div>
              <p class="privilege-card-desc mb-0">{{ $priv['description'] }}</p>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Universal Guarantee Strip -->
      <div class="privilege-guarantee-bar mt-3">
        <div class="d-flex align-items-center justify-content-center flex-wrap">
          <span class="guarantee-item mr-3 mb-1"><i class="fa fa-check-circle mr-1 text-primary"></i> Universal Access to All Tracks</span>
          <span class="guarantee-item mr-3 mb-1"><i class="fa fa-check-circle mr-1 text-primary"></i> Flexible Presentation (Onsite / Online)</span>
          <span class="guarantee-item mr-3 mb-1"><i class="fa fa-check-circle mr-1 text-primary"></i> Official Verifiable Certificate</span>
          <span class="guarantee-item mb-1"><i class="fa fa-check-circle mr-1 text-primary"></i> Scopus-Indexed Q2 Journal Eligibility</span>
        </div>
      </div>
    </div>
    --}}

    <!-- ========================================================= -->
    <!-- 2. INTERACTIVE PRICING VIEW SELECTOR                      -->
    <!-- ========================================================= -->
    <div class="pricing-tabs-wrapper mb-4">
      <ul class="nav nav-pills ticket-nav-tabs justify-content-center" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="tab-early-bird" data-toggle="pill" href="#early-bird-tickets" role="tab" aria-controls="early-bird-tickets" aria-selected="true">
            <i class="fa fa-clock-o mr-1"></i> Early Bird Rates
            <span class="badge badge-pill ml-1" style="background: rgba(255,255,255,0.25); color: #ffffff;">Special Rate</span>
          </a>
        </li>
        @if($regularPrices->isNotEmpty())
          <li class="nav-item">
            <a class="nav-link" id="tab-regular" data-toggle="pill" href="#regular-tickets" role="tab" aria-controls="regular-tickets" aria-selected="false">
              <i class="fa fa-calendar-check-o mr-1"></i> Regular / Late Rates
            </a>
          </li>
        @endif
        <li class="nav-item">
          <a class="nav-link" id="tab-matrix" data-toggle="pill" href="#matrix-tickets" role="tab" aria-controls="matrix-tickets" aria-selected="false">
            <i class="fa fa-table mr-1"></i> Complete Comparison Matrix
          </a>
        </li>
      </ul>
    </div>

    <!-- ========================================================= -->
    <!-- 3. TAB CONTENTS: CARDS & COMPARISON TABLE                 -->
    <!-- ========================================================= -->
    <div class="tab-content pricing-tab-content">
      
      <!-- Tab 1: Early Bird Cards -->
      <div role="tabpanel" class="tab-pane fade show active" id="early-bird-tickets" aria-labelledby="tab-early-bird">
        <div class="row justify-content-center">
          @foreach($earlyBirdPrices as $price)
            @php
                $meta = $categoryMeta[$price->name] ?? [
                    'icon'    => 'fa-user',
                    'badge'   => 'Delegate',
                    'target'  => 'Conference Participant',
                    'color'   => '#0055A0',
                    'bg_tint' => 'rgba(0, 85, 160, 0.08)',
                    'short'   => $price->name,
                ];
                $currSymbol = strtoupper((string)($price->currency ?? '')) === 'USD' ? 'US$' : '৳';
                
                $ebPrice = (float)$price->early_bird_price;
                $regPrice = (float)$price->regular_price;
                $savings = $regPrice > $ebPrice ? ($regPrice - $ebPrice) : null;
            @endphp
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card ticket-card h-100 shadow-sm">
                <div class="card-body d-flex flex-column p-4">
                  
                  <!-- Card Header: Category & Icon -->
                  <div class="d-flex align-items-center mb-3">
                    <div class="cat-avatar-wrap mr-3" style="background: {{ $meta['bg_tint'] }}; color: {{ $meta['color'] }};">
                      <i class="fa {{ $meta['icon'] }}"></i>
                    </div>
                    <div>
                      <span class="badge badge-category" style="background: {{ $meta['bg_tint'] }}; color: {{ $meta['color'] }}; border: 1px solid {{ $meta['color'] }}40;">
                        {{ $meta['badge'] }}
                      </span>
                      <h5 class="ticket-card-title mb-0 mt-1">{{ $price->name }}</h5>
                    </div>
                  </div>

                  <p class="target-audience-text text-muted mb-3">
                    <i class="fa fa-users mr-1 text-primary"></i> {{ $meta['target'] }}
                  </p>

                  <!-- Price Section -->
                  <div class="ticket-price-box text-center py-3 mb-3">
                    <span class="price-rate-label d-block text-uppercase">Early Bird Special</span>
                    <div class="ticket-price-val">
                      <span class="currency-symbol">{{ $currSymbol }}</span>
                      <span class="price-num">{{ number_format($ebPrice) }}</span>
                    </div>
                    @if($savings)
                      <div class="savings-tag mt-1">
                        <span class="badge" style="background: rgba(0, 85, 160, 0.12); color: #0055A0; font-weight: 700;"><i class="fa fa-arrow-down mr-1"></i>Save {{ $currSymbol }} {{ number_format($savings) }}</span>
                        <small class="text-muted ml-1">(Regular: {{ $currSymbol }}{{ number_format($regPrice) }})</small>
                      </div>
                    @else
                      <div class="savings-tag mt-1">
                        <span class="badge badge-info">Direct Delegate Rate</span>
                      </div>
                    @endif
                  </div>

                  <!-- Inclusions Summary (Highlighting All-Inclusive without duplicating 7 lines) -->
                  <div class="card-inclusions-summary mb-4">
                    <div class="inclusion-pill">
                      <i class="fa fa-check-circle text-primary mr-2"></i>
                      <span><strong>Full Privileges:</strong> Keynotes, technical tracks & certificate</span>
                    </div>
                    <div class="inclusion-pill">
                      <i class="fa fa-check-circle text-primary mr-2"></i>
                      <span><strong>Presentation Mode:</strong> Onsite (DIU) or Virtual (Online)</span>
                    </div>
                    <div class="inclusion-pill">
                      <i class="fa fa-check-circle text-primary mr-2"></i>
                      <span><strong>Certificates & Kit:</strong> Official certificate & bag</span>
                    </div>
                  </div>

                  <!-- Action CTA -->
                  <div class="mt-auto text-center">
                    @if(isset($profile))
                      <span class="btn btn-outline-success btn-block disabled"><i class="fa fa-check mr-1"></i> Already Registered</span>
                    @else
                      <a href="{{ route('book-ticket') }}" class="btn btn-ticket-register btn-block">
                        <i class="fa fa-ticket mr-1"></i> Register as {{ $meta['short'] }}
                      </a>
                    @endif
                  </div>

                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      @if($regularPrices->isNotEmpty())
        <!-- Tab 2: Regular / Late Cards -->
        <div role="tabpanel" class="tab-pane fade" id="regular-tickets" aria-labelledby="tab-regular">
          <div class="row justify-content-center">
            @foreach($regularPrices as $price)
              @php
                  $meta = $categoryMeta[$price->name] ?? [
                      'icon'    => 'fa-user',
                      'badge'   => 'Delegate',
                      'target'  => 'Conference Participant',
                      'color'   => '#0055A0',
                      'bg_tint' => 'rgba(0, 85, 160, 0.08)',
                      'short'   => $price->name,
                  ];
                  $currSymbol = strtoupper((string)($price->currency ?? '')) === 'USD' ? 'US$' : '৳';
              @endphp
              <div class="col-lg-4 col-md-6 mb-4">
                <div class="card ticket-card h-100 shadow-sm">
                  <div class="card-body d-flex flex-column p-4">
                    
                    <!-- Card Header -->
                    <div class="d-flex align-items-center mb-3">
                      <div class="cat-avatar-wrap mr-3" style="background: {{ $meta['bg_tint'] }}; color: {{ $meta['color'] }};">
                        <i class="fa {{ $meta['icon'] }}"></i>
                      </div>
                      <div>
                        <span class="badge badge-category" style="background: {{ $meta['bg_tint'] }}; color: {{ $meta['color'] }}; border: 1px solid {{ $meta['color'] }}40;">
                          {{ $meta['badge'] }}
                        </span>
                        <h5 class="ticket-card-title mb-0 mt-1">{{ $price->name }}</h5>
                      </div>
                    </div>

                    <p class="target-audience-text text-muted mb-3">
                      <i class="fa fa-users mr-1 text-primary"></i> {{ $meta['target'] }}
                    </p>

                    <!-- Price Section -->
                    <div class="ticket-price-box text-center py-3 mb-3">
                      <span class="price-rate-label d-block text-uppercase text-secondary">Regular / Standard Rate</span>
                      <div class="ticket-price-val">
                        <span class="currency-symbol">{{ $currSymbol }}</span>
                        <span class="price-num">{{ number_format($price->regular_price) }}</span>
                      </div>
                      <div class="savings-tag mt-1">
                        <span class="badge badge-light border text-muted">Standard Registration</span>
                      </div>
                    </div>

                    <!-- Inclusions Summary -->
                    <div class="card-inclusions-summary mb-4">
                      <div class="inclusion-pill">
                        <i class="fa fa-check-circle text-primary mr-2"></i>
                        <span><strong>Full Privileges:</strong> Keynotes, technical tracks & certificate</span>
                      </div>
                      <div class="inclusion-pill">
                        <i class="fa fa-check-circle text-primary mr-2"></i>
                        <span><strong>Presentation Mode:</strong> Onsite (DIU) or Virtual (Online)</span>
                      </div>
                      <div class="inclusion-pill">
                        <i class="fa fa-check-circle text-primary mr-2"></i>
                        <span><strong>Certificates & Kit:</strong> Official certificate & bag</span>
                      </div>
                    </div>

                    <!-- Action CTA -->
                    <div class="mt-auto text-center">
                      @if(isset($profile))
                        <span class="btn btn-outline-success btn-block disabled"><i class="fa fa-check mr-1"></i> Already Registered</span>
                      @else
                        <a href="{{ route('book-ticket') }}" class="btn btn-ticket-register btn-block">
                          <i class="fa fa-ticket mr-1"></i> Register as {{ $meta['short'] }}
                        </a>
                      @endif
                    </div>

                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <!-- Tab 3: Complete Comparison Matrix (Executive Table) -->
      <div role="tabpanel" class="tab-pane fade" id="matrix-tickets" aria-labelledby="tab-matrix">
        <div class="comparison-matrix-card card shadow-sm mb-4">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <div>
              <h5 class="mb-1 font-weight-bold text-dark"><i class="fa fa-columns text-primary mr-2"></i>All-in-One Registration Fee Comparison</h5>
              <p class="text-muted small mb-0">Side-by-side rate matrix showing Early Bird vs Regular fees across all 5 delegate categories.</p>
            </div>
            <span class="badge px-3 py-2 mt-2 mt-sm-0" style="background: #EEF4FA; color: #0055A0; font-weight: 600;"><i class="fa fa-check mr-1"></i> Full Privileges Included in Every Category</span>
          </div>

          <div class="table-responsive">
            <table class="table table-hover matrix-table mb-0 align-middle">
              <thead class="thead-light">
                <tr>
                  <th scope="col" class="pl-4">Category & Target Demographic</th>
                  <th scope="col" class="text-center">Currency</th>
                  <th scope="col" class="text-center matrix-th-highlight">Early Bird Rate</th>
                  <th scope="col" class="text-center">Regular Rate</th>
                  <th scope="col" class="text-center">Early Savings</th>
                  <th scope="col" class="text-center pr-4">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($comparisonRows as $row)
                  <tr>
                    <td class="pl-4">
                      <div class="d-flex align-items-center">
                        <div class="cat-mini-icon mr-3" style="background: {{ $row['meta']['bg_tint'] }}; color: {{ $row['meta']['color'] }};">
                          <i class="fa {{ $row['meta']['icon'] }}"></i>
                        </div>
                        <div>
                          <strong class="text-dark d-block">{{ $row['name'] }}</strong>
                          <small class="text-muted">{{ $row['meta']['target'] }}</small>
                        </div>
                      </div>
                    </td>
                    <td class="text-center font-weight-bold text-secondary">
                      {{ $row['currency'] }}
                    </td>
                    <td class="text-center matrix-td-highlight">
                      @if($row['eb_price'] !== null)
                        <span class="matrix-price-val">{{ $row['symbol'] }} {{ number_format($row['eb_price']) }}</span>
                        <span class="badge badge-pill d-block mx-auto mt-1" style="max-width: 90px; font-size: 10px; background: #EEF4FA; color: #0055A0; font-weight: 600;">Early Bird</span>
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($row['reg_price'] !== null)
                        <span class="matrix-price-regular">{{ $row['symbol'] }} {{ number_format($row['reg_price']) }}</span>
                        <span class="badge badge-pill badge-secondary d-block mx-auto mt-1" style="max-width: 80px; font-size: 10px;">Regular</span>
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($row['savings'])
                        <span class="badge badge-pill px-2 py-1" style="background: rgba(0, 85, 160, 0.1); color: #0055A0; font-weight: 700;">
                          <i class="fa fa-arrow-down mr-1"></i>Save {{ $row['symbol'] }} {{ number_format($row['savings']) }}
                        </span>
                      @else
                        <span class="text-muted small">Standard</span>
                      @endif
                    </td>
                    <td class="text-center pr-4">
                      <a href="{{ route('book-ticket') }}" class="btn btn-sm btn-matrix-register">
                        Register <i class="fa fa-chevron-right ml-1"></i>
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- ========================================================= -->
    <!-- 4. REGISTRATION GUIDELINES & IMPORTANT INFORMATION       -->
    <!-- ========================================================= -->
    <div class="registration-guidelines mt-4">
      <div class="row">
        <div class="col-md-4 mb-3">
          <div class="guideline-card h-100 p-3 shadow-sm">
            <div class="d-flex align-items-center mb-2">
              <div class="guideline-icon mr-2 text-primary"><i class="fa fa-credit-card fa-lg"></i></div>
              <h6 class="mb-0 font-weight-bold text-dark">Flexible Payment Gateways</h6>
            </div>
            <p class="small text-muted mb-0">
              Local payments via <strong>bKash, Nagad, Rocket, DBBL, Visa, Mastercard & Bank Transfer</strong> (BDT). International payments via <strong>Credit Card & SWIFT/Wire Transfer</strong> (USD).
            </p>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="guideline-card h-100 p-3 shadow-sm">
            <div class="d-flex align-items-center mb-2">
              <div class="guideline-icon mr-2 text-primary"><i class="fa fa-globe fa-lg"></i></div>
              <h6 class="mb-0 font-weight-bold text-dark">Hybrid Presentation Modes</h6>
            </div>
            <p class="small text-muted mb-0">
              Presenters may attend <strong>In-Person at Daffodil Smart City</strong>, Dhaka or present <strong>Live Online via Zoom</strong>. All presentation modes receive the official certificate.
            </p>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="guideline-card h-100 p-3 shadow-sm">
            <div class="d-flex align-items-center mb-2">
              <div class="guideline-icon mr-2 text-primary"><i class="fa fa-file-text-o fa-lg"></i></div>
              <h6 class="mb-0 font-weight-bold text-dark">Paper Coverage & Publication</h6>
            </div>
            <p class="small text-muted mb-0">
              Each accepted paper must have at least one author registered. Registered papers will be considered for publication in <strong>Scopus-indexed Q2 journals</strong>.
            </p>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Modal Order Form (Retained for quick action / compatibility) -->
  <div id="buy-ticket-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="buyTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold" id="buyTicketModalLabel"><i class="fa fa-ticket mr-2"></i>Conference Registration</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <form method="POST" action="#">
            <div class="form-group">
              <label class="font-weight-bold small text-muted">Full Name</label>
              <input type="text" class="form-control" name="your-name" placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold small text-muted">Email Address</label>
              <input type="email" class="form-control" name="your-email" placeholder="name@institution.edu" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold small text-muted">Select Registration Package</label>
              <select id="ticket-type" name="ticket-type" class="form-control" required>
                <option value="">-- Choose Your Category & Rate --</option>
                @if($earlyBirdPrices->isNotEmpty())
                  <optgroup label="Early Bird Registration (Special Rate)">
                    @foreach($earlyBirdPrices as $price)
                      <option value="{{ Str::slug($price->name . '-early-bird') }}">
                        {{ $price->name }} (Early Bird — {{ $price->currency_symbol }}{{ number_format($price->early_bird_price) }})
                      </option>
                    @endforeach
                  </optgroup>
                @endif
                @if($regularPrices->isNotEmpty())
                  <optgroup label="Regular / Late Registration">
                    @foreach($regularPrices as $price)
                      <option value="{{ Str::slug($price->name . '-regular') }}">
                        {{ $price->name }} (Regular — {{ $price->currency_symbol }}{{ number_format($price->regular_price) }})
                      </option>
                    @endforeach
                  </optgroup>
                @endif
              </select>
            </div>
            <div class="text-center mt-4">
              <a href="{{ route('book-ticket') }}" class="btn btn-primary btn-block py-2 font-weight-bold">
                Continue to Online Registration Form <i class="fa fa-arrow-right ml-1"></i>
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</section>

@push('style')
<style>
  /* =========================================================
     BUY TICKETS & REGISTRATION STYLES (ICMRIA 2027)
     ========================================================= */

  #buy-tickets {
    padding: 70px 0;
    background: #f8fafc;
  }

  /* Universal Privileges Showcase Banner */
  .all-inclusive-showcase {
    background: #ffffff;
    border-radius: 16px;
    padding: 32px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0, 57, 107, 0.06);
  }

  .showcase-banner {
    padding-bottom: 24px;
    border-bottom: 1px solid #edf2f7;
  }

  .showcase-tag-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .privilege-pill-badge {
    background: linear-gradient(135deg, #0055A0, #003366);
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
  }

  .privilege-sub-badge {
    background: rgba(0, 85, 160, 0.08);
    color: #0055A0;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
  }

  .showcase-title {
    font-size: 24px;
    font-weight: 800;
    color: #003366;
    margin-top: 6px;
    margin-bottom: 8px;
    letter-spacing: -0.3px;
  }

  .showcase-subtitle {
    font-size: 14.5px;
    color: #4a5568;
    line-height: 1.6;
    max-width: 720px;
  }

  .btn-showcase-cta {
    background: #0055A0;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 14.5px;
    padding: 12px 28px;
    border-radius: 50px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0, 85, 160, 0.25);
  }

  .btn-showcase-cta:hover {
    background: #003366;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 51, 102, 0.35);
  }

  /* 7 Privileges Responsive Grid */
  .privileges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
  }

  .privilege-card {
    background: #f8fafc;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: all 0.25s ease;
  }

  .privilege-card:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.05);
  }

  .privilege-card.highlight-privilege {
    background: #F0F7FF;
    border-color: #BAE6FD;
  }

  .privilege-icon-wrap {
    width: 44px;
    height: 44px;
    min-width: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  .privilege-content {
    flex: 1;
    min-width: 0;
  }

  .privilege-card-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #003366;
    line-height: 1.3;
  }

  .privilege-badge-tag {
    font-size: 9.5px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 6px;
    background: #e2e8f0;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .highlight-privilege .privilege-badge-tag {
    background: #0055A0;
    color: #ffffff;
  }

  .privilege-card-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.45;
    margin-top: 4px;
  }

  /* Universal Guarantee Bar */
  .privilege-guarantee-bar {
    background: #f1f5f9;
    border-radius: 10px;
    padding: 12px 20px;
    font-size: 13px;
    color: #334155;
    font-weight: 600;
  }

  .guarantee-item {
    display: inline-flex;
    align-items: center;
  }

  /* Pricing View Tabs */
  .pricing-tabs-wrapper .ticket-nav-tabs {
    border-bottom: none;
    gap: 12px;
  }

  .pricing-tabs-wrapper .ticket-nav-tabs .nav-link {
    border-radius: 50px;
    font-weight: 700;
    font-size: 14.5px;
    padding: 12px 26px;
    color: #003366;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  }

  .pricing-tabs-wrapper .ticket-nav-tabs .nav-link:hover {
    border-color: #0055A0;
    color: #0055A0;
  }

  .pricing-tabs-wrapper .ticket-nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #0055A0, #003366);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 6px 18px rgba(0, 85, 160, 0.35);
  }

  .pricing-tabs-wrapper .ticket-nav-tabs .nav-link.active .badge-pill {
    background-color: rgba(255, 255, 255, 0.25);
    color: #ffffff;
  }

  /* Clean Pricing Cards */
  .ticket-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    transition: all 0.3s ease;
    background: #ffffff;
  }

  .ticket-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 57, 107, 0.1) !important;
    border-color: #cbd5e1;
  }

  .cat-avatar-wrap {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
  }

  .badge-category {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 6px;
  }

  .ticket-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #003366;
    line-height: 1.35;
  }

  .target-audience-text {
    font-size: 13px;
    line-height: 1.45;
    min-height: 38px;
  }

  .ticket-price-box {
    background: #f8fafc;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
  }

  .price-rate-label {
    font-size: 11px;
    font-weight: 700;
    color: #0055A0;
    letter-spacing: 0.8px;
  }

  .ticket-price-val {
    color: #003366;
    font-weight: 800;
    line-height: 1.1;
    margin: 4px 0;
  }

  .ticket-price-val .currency-symbol {
    font-size: 22px;
    font-weight: 700;
    vertical-align: top;
    margin-right: 2px;
  }

  .ticket-price-val .price-num {
    font-size: 36px;
    font-weight: 800;
    letter-spacing: -0.5px;
  }

  .savings-tag {
    font-size: 12px;
  }

  .savings-tag .badge {
    padding: 4px 8px;
  }

  .card-inclusions-summary {
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    padding-top: 14px;
  }

  .inclusion-pill {
    display: flex;
    align-items: flex-start;
    font-size: 12.5px;
    color: #475569;
    margin-bottom: 8px;
    line-height: 1.4;
  }

  .inclusion-pill:last-child {
    margin-bottom: 0;
  }

  .btn-ticket-register {
    background: linear-gradient(135deg, #0055A0, #003366);
    color: #ffffff !important;
    font-weight: 700;
    font-size: 14px;
    padding: 12px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 85, 160, 0.25);
  }

  .btn-ticket-register:hover {
    background: linear-gradient(135deg, #003366, #001f3f);
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 51, 102, 0.35);
  }

  /* Comparison Matrix Table */
  .comparison-matrix-card {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
  }

  .matrix-table thead th {
    font-size: 13px;
    font-weight: 700;
    color: #003366;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-top: none;
    padding: 14px 16px;
  }

  .matrix-th-highlight,
  .matrix-td-highlight {
    background-color: rgba(0, 85, 160, 0.04);
  }

  .matrix-table tbody td {
    padding: 18px 16px;
    vertical-align: middle;
    border-color: #f1f5f9;
  }

  .cat-mini-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }

  .matrix-price-val {
    font-size: 18px;
    font-weight: 800;
    color: #0055A0;
  }

  .matrix-price-regular {
    font-size: 16px;
    font-weight: 600;
    color: #475569;
  }

  .btn-matrix-register {
    background: #0055A0;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 12.5px;
    padding: 6px 14px;
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  .btn-matrix-register:hover {
    background: #0055A0;
    color: #ffffff !important;
  }

  /* Guideline Cards */
  .guideline-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
  }

  .guideline-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
  }

  /* Responsive Adjustments */
  @media (max-width: 767.98px) {
    #buy-tickets {
      padding: 50px 0;
    }
    .all-inclusive-showcase {
      padding: 20px;
    }
    .showcase-title {
      font-size: 20px;
    }
    .privileges-grid {
      grid-template-columns: 1fr;
    }
    .pricing-tabs-wrapper .ticket-nav-tabs .nav-link {
      font-size: 13px;
      padding: 10px 16px;
    }
  }
</style>
@endpush
