@extends('layouts.main')

@section('content')
<main id="main" class="main-page">
    <!-- Hero Banner -->
    <section class="cfp-hero text-white py-5 position-relative" style="background: linear-gradient(135deg, #001f3f 0%, #003366 55%, #004d80 100%); padding: 90px 0 70px;">
        <div class="container text-center">
            <div class="badge-pill-header mb-3">
                <span class="badge px-3 py-2 text-uppercase font-weight-bold" style="background: rgba(125, 211, 252, 0.15); border: 1px solid rgba(125, 211, 252, 0.4); color: #BAE6FD; font-size: 12.5px; letter-spacing: 1px; border-radius: 30px;">
                    <i class="fa fa-bullhorn mr-1"></i> Call for Papers (CFP)
                </span>
            </div>
            <h1 class="display-4 font-weight-bold text-white mb-3" style="font-size: 2.75rem; letter-spacing: -0.5px;">Conference Flyer & Call for Papers</h1>
            <p class="lead mx-auto text-light" style="max-width: 820px; font-size: 1.15rem; color: #d6e3f3 !important; line-height: 1.6;">
                “Connecting Knowledge, Innovation and Society for a Sustainable and Intelligent Future”<br>
                <span style="font-size: 0.95rem; opacity: 0.9;">9–10 January 2027 | Daffodil Smart City, Dhaka, Bangladesh</span>
            </p>

            <div class="d-flex flex-wrap justify-content-center mt-4" style="gap: 15px;">
                <a href="#" class="btn btn-primary btn-lg px-4 py-3 shadow font-weight-bold" style="background: #0055A0; border-color: #0055A0; border-radius: 50px;">
                    <i class="fa fa-file-pdf-o mr-2"></i> Download Full CFP (PDF)
                </a>
                <a href="#" class="btn btn-outline-light btn-lg px-4 py-3 shadow font-weight-bold" style="border-radius: 50px;">
                    <i class="fa fa-file-word-o mr-2"></i> Download CFP (.DOCX)
                </a>
            </div>
        </div>
    </section>

    <!-- Key Highlights Ribbon -->
    <section class="py-4 bg-white border-bottom shadow-sm">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 py-2 border-right">
                    <i class="fa fa-star text-warning fa-lg mr-2"></i>
                    <strong class="text-dark">Scopus Q2 Journals</strong>
                    <p class="text-muted small mb-0">Selected high-impact papers considered for publication</p>
                </div>
                <div class="col-md-4 py-2 border-right">
                    <i class="fa fa-globe text-primary fa-lg mr-2"></i>
                    <strong class="text-dark">Hybrid Participation</strong>
                    <p class="text-muted small mb-0">Onsite at Daffodil Smart City + Interactive Virtual Sessions</p>
                </div>
                <div class="col-md-4 py-2">
                    <i class="fa fa-calendar-check-o text-success fa-lg mr-2"></i>
                    <strong class="text-dark">30 October 2026</strong>
                    <p class="text-muted small mb-0">Abstract Submission Deadline</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PDF Viewer & Details Section -->
    <section class="py-5" style="background: #f8fafd;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-5" style="border-radius: 12px; border: 1px solid rgba(0, 85, 160, 0.1);">
                        <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center flex-wrap" style="border-bottom: 1px solid #eef2f6;">
                            <div>
                                <h4 class="font-weight-bold mb-1" style="color: #003366;"><i class="fa fa-file-pdf-o text-primary mr-2"></i> Conference Brochure Preview</h4>
                                <p class="text-muted small mb-0">Browse the official brochure document below or download for offline review.</p>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <a href="#" class="btn btn-sm btn-outline-primary font-weight-bold px-3 py-2 rounded-pill">
                                    <i class="fa fa-external-link mr-1"></i> Open Fullscreen
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0" style="background: #525659;">
                            <iframe
                                src="{{ asset('documents/Call_for_Papers_with_References_long_form.pdf') }}"
                                width="100%"
                                height="850px"
                                style="border: none; display: block;">
                            </iframe>
                        </div>
                    </div>

                    <!-- Call to Action -->
                    <div class="p-4 p-md-5 text-center text-white rounded-lg shadow" style="background: linear-gradient(135deg, #003366 0%, #0055A0 100%); border-radius: 12px;">
                        <h3 class="font-weight-bold text-white mb-2">Submit Your Paper Today</h3>
                        <p class="text-light mb-4 mx-auto" style="max-width: 600px; color: #d6e3f3 !important;">
                            Be part of the multidisciplinary research movement bridging knowledge and innovation for a sustainable tomorrow.
                        </p>
                        <a href="{{ route('book-ticket') }}" class="btn btn-primary btn-lg px-5 py-3 font-weight-bold shadow" style="background: #0055A0; border-color: #0055A0; border-radius: 50px;">
                            <i class="fa fa-paper-plane mr-2"></i> Submit Abstract Online
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
