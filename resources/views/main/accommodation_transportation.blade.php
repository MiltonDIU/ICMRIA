@extends('layouts.main')

@section('content')
<main id="main" class="main-page">
    <!-- Hero Banner -->
    <section class="travel-hero text-white py-5 position-relative" style="background: linear-gradient(135deg, #001f3f 0%, #003366 55%, #004d80 100%); padding: 85px 0 65px;">
        <div class="container text-center">
            <div class="badge-pill-header mb-3">
                <span class="badge px-3 py-2 text-uppercase" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #E0F2FE; font-size: 12px; letter-spacing: 1.5px; border-radius: 30px; font-weight: 600;">
                    <i class="fa fa-map-marker mr-1"></i> Daffodil Smart City, Savar, Dhaka
                </span>
            </div>
            <h1 class="display-4 font-weight-bold text-white mb-3" style="font-size: 2.6rem; letter-spacing: -0.5px;">Accommodation & Transportation</h1>
            <p class="lead mx-auto text-light" style="max-width: 820px; font-size: 1.15rem; color: #d6e3f3 !important; line-height: 1.6;">
                Plan your visit to the International Conference on Multidisciplinary Research, Innovation, and Applications 2027. Discover transit routes, airport connectivity, university shuttles, and comfortable stay options.
            </p>

            <div class="d-flex flex-wrap justify-content-center mt-4" style="gap: 15px;">
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <i class="fa fa-plane mr-1" style="color: #7DD3FC;"></i> 25 km from Dhaka Airport (DAC)
                </div>
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <i class="fa fa-bus mr-1" style="color: #7DD3FC;"></i> Dedicated Conference Shuttle Service
                </div>
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <i class="fa fa-bed mr-1" style="color: #7DD3FC;"></i> On-Campus & Partner Hotel Lodging
                </div>
            </div>
        </div>
    </section>

    <!-- Content Sections -->
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container">

            <!-- Section 1: Venue Overview & Map -->
            <div class="venue-card bg-white p-4 p-md-5 rounded-lg shadow-sm mb-5" style="border: 1px solid #E2E8F0; border-radius: 12px;">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <span class="text-uppercase font-weight-bold" style="color: #0055A0; letter-spacing: 1.2px; font-size: 13px;">Conference Venue</span>
                        <h2 class="font-weight-bold mb-3" style="color: #003366;">Daffodil Smart City</h2>
                        <p class="text-muted mb-3" style="line-height: 1.8;">
                            Daffodil Smart City is an expansive, 150-acre green and digitized university campus located in Birulia, Savar, Dhaka. Designed as a modern educational township, it features international-standard auditoriums, smart air-conditioned halls, campus-wide Wi-Fi, and lakeside recreational facilities.
                        </p>
                        <div class="venue-details-list p-3 rounded mb-4" style="background: #F1F5F9; font-size: 13px; border: 1px solid #E2E8F0;">
                            <div class="mb-2"><i class="fa fa-map-marker text-primary mr-2"></i> <strong>Address:</strong> Daffodil Smart City, Khagan, Birulia, Savar, Dhaka-1216, Bangladesh</div>
                            <div class="mb-2"><i class="fa fa-university text-primary mr-2"></i> <strong>Host:</strong> Daffodil International University (DIU)</div>
                            <div><i class="fa fa-envelope text-primary mr-2"></i> <strong>Email:</strong> fahadhossain.swe@diu.edu.bd</div>
                        </div>
                        <a href="https://maps.google.com/maps?q=Daffodil+International+University,+Birulia,+Savar" target="_blank" class="btn btn-primary rounded-pill px-4 font-weight-bold" style="background: #0055A0; border-color: #0055A0;">
                            <i class="fa fa-external-link mr-1"></i> Open in Google Maps
                        </a>
                    </div>
                    <div class="col-lg-6">
                        <div class="rounded-lg overflow-hidden shadow-sm" style="height: 380px; border: 1px solid #CBD5E1; border-radius: 12px;">
                            <iframe src="https://maps.google.com/maps?q=23.8767,90.3204&hl=en&z=14&output=embed" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Transportation & How to Reach -->
            <div class="transportation-section mb-5">
                <div class="text-center mb-5">
                    <span class="text-uppercase font-weight-bold" style="color: #0055A0; letter-spacing: 1.2px; font-size: 13px;">Transit Guide</span>
                    <h2 class="font-weight-bold mt-1" style="color: #003366;">How to Reach the Venue</h2>
                    <p class="text-muted mx-auto" style="max-width: 700px; line-height: 1.6;">
                        Convenient transit options are available for international, regional, and national delegates travelling to Daffodil Smart City.
                    </p>
                </div>

                <div class="row">
                    <!-- Option 1: Airport -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden" style="border-radius: 12px; border: 1px solid #E2E8F0;">
                            <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #003366 0%, #004d80 100%);">
                                <i class="fa fa-plane fa-2x mb-2" style="color: #7DD3FC;"></i>
                                <h5 class="font-weight-bold text-white mb-0">From Dhaka Airport (DAC)</h5>
                                <small style="color: #BAE6FD;">Hazrat Shahjalal International</small>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                    <li><strong>Distance:</strong> Approximately 25 km (15.5 miles).</li>
                                    <li><strong>Travel Time:</strong> 45 to 60 minutes depending on traffic.</li>
                                    <li><strong>Ride-Sharing:</strong> Uber, Pathao, and airport pre-paid taxis are readily available 24/7.</li>
                                    <li><strong>Cost Estimate:</strong> BDT 800 – 1,200 (approx. USD 8–12).</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Shuttle Bus -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden" style="border-radius: 12px; border: 1px solid #E2E8F0;">
                            <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #003366 0%, #004d80 100%);">
                                <i class="fa fa-bus fa-2x mb-2" style="color: #7DD3FC;"></i>
                                <h5 class="font-weight-bold text-white mb-0">University Shuttle Buses</h5>
                                <small style="color: #BAE6FD;">Scheduled Conference Transit</small>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                    <li><strong>Pickup Hubs:</strong> Uttara (Sector 3 & 7), Mirpur-10, and Dhanmondi (Sobhanbag Campus).</li>
                                    <li><strong>Frequency:</strong> Regular morning departures (7:30 AM – 9:00 AM) and evening return trips.</li>
                                    <li><strong>Access:</strong> Free for all registered ICMRIA 2027 delegates with conference badge.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Option 3: Local Transit -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden" style="border-radius: 12px; border: 1px solid #E2E8F0;">
                            <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #003366 0%, #004d80 100%);">
                                <i class="fa fa-car fa-2x mb-2" style="color: #7DD3FC;"></i>
                                <h5 class="font-weight-bold text-white mb-0">Metro & Public Transit</h5>
                                <small style="color: #BAE6FD;">MRT Line 6 & Bus Routes</small>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                    <li><strong>Metro Rail (MRT 6):</strong> Take the Metro to Uttara North station, followed by a 20-minute taxi or shuttle.</li>
                                    <li><strong>Bus Services:</strong> Direct Savar/Ashulia buses available from Gabtoli and Mirpur-1.</li>
                                    <li><strong>Alight At:</strong> Daffodil Smart City Gate 1 or Gate 2.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Accommodation Options -->
            <div class="accommodation-section mb-5">
                <div class="text-center mb-5">
                    <span class="text-uppercase font-weight-bold" style="color: #0055A0; letter-spacing: 1.2px; font-size: 13px;">Where to Stay</span>
                    <h2 class="font-weight-bold mt-1" style="color: #003366;">Accommodation Options</h2>
                    <p class="text-muted mx-auto" style="max-width: 700px; line-height: 1.6;">
                        Lodging options ranging from on-campus university executive guest suites to international star-rated partner hotels.
                    </p>
                </div>

                <div class="row">
                    <!-- On-Campus Stay -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg p-4 bg-white" style="border-radius: 12px; border: 1px solid #E2E8F0; border-left: 4px solid #003366 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge px-3 py-1 font-weight-bold text-uppercase mb-2" style="background: #EEF4FA; color: #0055A0; font-size: 11px; border-radius: 4px;">On-Campus</span>
                                    <h4 class="font-weight-bold mb-1" style="color: #003366;">DIU Executive Guest House</h4>
                                    <small class="text-muted"><i class="fa fa-map-marker text-primary mr-1"></i> Inside Daffodil Smart City Campus</small>
                                </div>
                                <span class="badge px-2 py-1 font-weight-bold" style="background: #EEF4FA; color: #003366; border: 1px solid #CBD5E1;"><i class="fa fa-star text-primary"></i> Recommended</span>
                            </div>
                            <p class="text-muted small mb-3" style="line-height: 1.8;">
                                Fully furnished air-conditioned guest suites reserved for keynote speakers, session chairs, and visiting international delegates. Features high-speed Wi-Fi, executive lounge, and 24/7 security.
                            </p>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge badge-light p-2 text-muted mr-2" style="background: #F1F5F9;"><i class="fa fa-wifi text-primary mr-1"></i> Fast WiFi</span>
                                <span class="badge badge-light p-2 text-muted mr-2" style="background: #F1F5F9;"><i class="fa fa-coffee text-primary mr-1"></i> Breakfast</span>
                                <span class="badge badge-light p-2 text-muted mr-2" style="background: #F1F5F9;"><i class="fa fa-lock text-primary mr-1"></i> 24/7 Security</span>
                                <span class="badge badge-light p-2 text-muted" style="background: #F1F5F9;"><i class="fa fa-snowflake-o text-primary mr-1"></i> AC Suites</span>
                            </div>
                            <small class="text-muted d-block border-top pt-2"><em>*Subject to advance reservation and confirmation through the organizing committee.</em></small>
                        </div>
                    </div>

                    <!-- Partner Hotels in Uttara / Savar -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg p-4 bg-white" style="border-radius: 12px; border: 1px solid #E2E8F0; border-left: 4px solid #0055A0 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge px-3 py-1 font-weight-bold text-uppercase mb-2" style="background: #EEF4FA; color: #0055A0; font-size: 11px; border-radius: 4px;">Nearby Hotels</span>
                                    <h4 class="font-weight-bold mb-1" style="color: #003366;">Partner Hotels & Resorts</h4>
                                    <small class="text-muted"><i class="fa fa-map-marker text-primary mr-1"></i> Uttara & Savar Hubs (15–20 mins away)</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-3" style="line-height: 1.8;">
                                ICMRIA 2027 has partnered with hotels in Uttara and Savar to offer special corporate discount rates for registered conference participants. Shuttle pickup is arranged from designated partner hotel lobbies.
                            </p>
                            <ul class="text-muted small pl-3 mb-3" style="line-height: 1.8;">
                                <li><strong>Hotels in Uttara:</strong> Le Méridien Dhaka, Radisson Blu Water Garden, Platinum Grand, Best Western PLUS Maple Leaf.</li>
                                <li><strong>Resorts in Savar/Ashulia:</strong> Fantasy Kingdom Resort, BCDM Savar, Savar Golf Club Guest Houses.</li>
                            </ul>
                            <small class="text-muted d-block border-top pt-2"><em>*Mention "ICMRIA 2027 Conference Delegate" during booking to avail special corporate discounts.</em></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Travel Assistance & Contacts -->
            <div class="support-banner bg-white p-4 p-md-5 rounded-lg shadow-sm text-center border" style="border-color: #E2E8F0; border-radius: 12px;">
                <h3 class="font-weight-bold mb-2" style="color: #003366;">Need Assistance With Your Travel?</h3>
                <p class="text-muted mx-auto mb-4" style="max-width: 650px; line-height: 1.6;">
                    Our hospitality committee is ready to assist you with airport pickup coordination, visa invitation letters, and hotel bookings.
                </p>
                <div class="d-flex flex-wrap justify-content-center" style="gap: 15px;">
                    <a href="mailto:fahadhossain.swe@diu.edu.bd" class="btn btn-primary rounded-pill px-4 py-2 font-weight-bold" style="background: #0055A0; border-color: #0055A0;">
                        <i class="fa fa-envelope mr-1"></i> Contact Hospitality Committee
                    </a>
                    <a href="tel:01946704373" class="btn btn-outline-primary rounded-pill px-4 py-2 font-weight-bold" style="border-color: #0055A0; color: #0055A0;">
                        <i class="fa fa-phone mr-1"></i> Hotline: +8801946704373
                    </a>
                </div>
            </div>

        </div>
    </section>
</main>
@endsection
