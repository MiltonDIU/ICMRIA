@extends('layouts.main')

@section('content')
<main id="main" class="main-page">
    <!-- Hero Banner -->
    <section class="camera-hero text-white py-5 position-relative" style="background: linear-gradient(135deg, #001f3f 0%, #003366 55%, #004d80 100%); padding: 90px 0 70px;">
        <div class="container text-center">
            <div class="badge-pill-header mb-3">
                <span class="badge px-3 py-2 text-uppercase" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #E0F2FE; font-size: 12px; letter-spacing: 1.5px; border-radius: 30px; font-weight: 600;">
                    <i class="fa fa-check-square-o mr-1"></i> For Accepted Papers & Presenters
                </span>
            </div>
            <h1 class="display-4 font-weight-bold text-white mb-3" style="font-size: 2.75rem; letter-spacing: -0.5px;">Camera-Ready & Presentation Guidelines</h1>
            <p class="lead mx-auto text-light" style="max-width: 820px; font-size: 1.15rem; color: #d6e3f3 !important; line-height: 1.6;">
                Congratulations on your paper acceptance! Please follow these academic guidelines to finalize your manuscript, submit the copyright agreement, and prepare for presentation at ICMRIA 2027.
            </p>

            <div class="d-flex flex-wrap justify-content-center mt-4" style="gap: 15px;">
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <i class="fa fa-calendar mr-1" style="color: #7DD3FC;"></i> Camera-Ready Deadline: <strong>26 Dec 2026</strong>
                </div>
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <i class="fa fa-clock-o mr-1" style="color: #7DD3FC;"></i> Oral Presentation: <strong>15 Min + 5 Min Q&A</strong>
                </div>
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <i class="fa fa-file-image-o mr-1" style="color: #7DD3FC;"></i> Poster Format: <strong>A0 Portrait</strong>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <a href="{{ route('book-ticket') }}" class="btn btn-primary btn-lg px-4 py-3 shadow font-weight-bold" style="background: #0055A0; border-color: #0055A0; border-radius: 50px;">
                    <i class="fa fa-upload mr-2"></i> Upload Camera-Ready Paper
                </a>
                <a href="#presentation-specs" class="btn btn-outline-light btn-lg px-4 py-3 shadow font-weight-bold" style="border-radius: 50px; border-width: 1.5px;">
                    <i class="fa fa-desktop mr-2"></i> Presentation Specifications
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5" style="background: #F8FAFC;">
        <div class="container">

            <!-- Section 1: 6-Step Camera-Ready Checklist -->
            <div class="checklist-container bg-white p-4 p-md-5 rounded-lg shadow-sm mb-5" style="border: 1px solid #E2E8F0; border-radius: 12px;">
                <div class="text-center mb-5">
                    <span class="text-uppercase font-weight-bold" style="color: #0055A0; letter-spacing: 1.2px; font-size: 13px;">Final Manuscript Submission</span>
                    <h2 class="font-weight-bold mt-1" style="color: #003366;">Camera-Ready Checklist for Authors</h2>
                    <p class="text-muted mx-auto" style="max-width: 700px; line-height: 1.6;">
                        Before uploading your final camera-ready file to the conference submission portal, verify that each requirement below has been fulfilled.
                    </p>
                </div>

                <div class="row">
                    <!-- Step 1 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="check-box p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-top: 3px solid #003366;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="step-num mr-2">1</span>
                                <h5 class="font-weight-bold mb-0" style="color: #003366;">Reviewer Comments</h5>
                            </div>
                            <p class="text-muted small mb-0" style="line-height: 1.7;">
                                Carefully revise your paper addressing all feedback raised in the peer review. Ensure all methodological clarifications and citations have been updated.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="check-box p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-top: 3px solid #003366;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="step-num mr-2">2</span>
                                <h5 class="font-weight-bold mb-0" style="color: #003366;">Author Affiliations</h5>
                            </div>
                            <p class="text-muted small mb-0" style="line-height: 1.7;">
                                Un-blind the author block: include full names, institutional affiliations, departments, city, country, corresponding email, and valid ORCID IDs.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="check-box p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-top: 3px solid #003366;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="step-num mr-2">3</span>
                                <h5 class="font-weight-bold mb-0" style="color: #003366;">IEEE Format Verification</h5>
                            </div>
                            <p class="text-muted small mb-0" style="line-height: 1.7;">
                                Preserve standard IEEE 2-column formatting. Verify margins, embedded fonts, high-resolution figures, and adhere strictly to the 6–8 page conference limit.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="check-box p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-top: 3px solid #003366;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="step-num mr-2">4</span>
                                <h5 class="font-weight-bold mb-0" style="color: #003366;">Copyright Agreement</h5>
                            </div>
                            <p class="text-muted small mb-0" style="line-height: 1.7;">
                                Complete and sign the official Copyright Transfer Form. Upload the signed PDF document alongside your camera-ready manuscript through the portal.
                            </p>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="check-box p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-top: 3px solid #003366;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="step-num mr-2">5</span>
                                <h5 class="font-weight-bold mb-0" style="color: #003366;">Registration Confirmation</h5>
                            </div>
                            <p class="text-muted small mb-0" style="line-height: 1.7;">
                                At least one author must complete full registration by <strong>26 Dec 2026</strong> to ensure inclusion in the conference program and proceedings.
                            </p>
                        </div>
                    </div>

                    <!-- Step 6 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="check-box p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-top: 3px solid #003366;">
                            <div class="d-flex align-items-center mb-3">
                                <span class="step-num mr-2">6</span>
                                <h5 class="font-weight-bold mb-0" style="color: #003366;">File Naming Standard</h5>
                            </div>
                            <p class="text-muted small mb-0" style="line-height: 1.7;">
                                Submit the file in PDF format titled: <code>ICMRI2027_[PaperID]_CameraReady.pdf</code> (for example: <code>ICMRI2027_104_CameraReady.pdf</code>).
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Presentation Formats -->
            <div id="presentation-specs" class="presentation-section mb-5">
                <div class="text-center mb-5">
                    <span class="text-uppercase font-weight-bold" style="color: #0055A0; letter-spacing: 1.2px; font-size: 13px;">Conference Delivery Modes</span>
                    <h2 class="font-weight-bold mt-1" style="color: #003366;">Presentation Guidelines & Specifications</h2>
                    <p class="text-muted mx-auto" style="max-width: 700px; line-height: 1.6;">
                        ICMRIA 2027 welcomes both Onsite sessions at Daffodil Smart City and Virtual interactive sessions for international delegates.
                    </p>
                </div>

                <div class="row">
                    <!-- Oral Presentation -->
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden" style="border-radius: 12px; border: 1px solid #E2E8F0;">
                            <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #003366 0%, #004d80 100%);">
                                <i class="fa fa-microphone fa-2x mb-2" style="color: #7DD3FC;"></i>
                                <h4 class="font-weight-bold text-white mb-0">Oral Presentation</h4>
                                <small style="color: #BAE6FD;">Technical Session Presentation</small>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <ul class="list-unstyled mb-0" style="font-size: 13px; line-height: 1.8; color: #334155;">
                                    <li class="mb-2"><strong>Duration:</strong> 15 minutes presentation + 5 minutes Q&A.</li>
                                    <li class="mb-2"><strong>Aspect Ratio:</strong> 16:9 widescreen recommended (PowerPoint .PPTX or PDF).</li>
                                    <li class="mb-2"><strong>Equipment:</strong> Digital podium, wireless presenter clicker, and lapel mic provided.</li>
                                    <li><strong>Backup:</strong> Please bring your presentation on a standard USB flash drive.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Poster Presentation -->
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden" style="border-radius: 12px; border: 1px solid #E2E8F0;">
                            <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #003366 0%, #004d80 100%);">
                                <i class="fa fa-picture-o fa-2x mb-2" style="color: #7DD3FC;"></i>
                                <h4 class="font-weight-bold text-white mb-0">Poster Presentation</h4>
                                <small style="color: #BAE6FD;">Interactive Poster Gallery Session</small>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <ul class="list-unstyled mb-0" style="font-size: 13px; line-height: 1.8; color: #334155;">
                                    <li class="mb-2"><strong>Dimensions:</strong> Standard <strong>A0 Portrait</strong> (841 mm wide &times; 1189 mm high).</li>
                                    <li class="mb-2"><strong>Typography:</strong> Title &ge; 48pt; Headings &ge; 32pt; Body text &ge; 24pt for 1–2m readability.</li>
                                    <li class="mb-2"><strong>Mounting:</strong> Display boards and mounting materials will be provided on-site.</li>
                                    <li><strong>Presence:</strong> At least one author must attend the designated poster evaluation hour.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Virtual Presentation -->
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden" style="border-radius: 12px; border: 1px solid #E2E8F0;">
                            <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #003366 0%, #004d80 100%);">
                                <i class="fa fa-video-camera fa-2x mb-2" style="color: #7DD3FC;"></i>
                                <h4 class="font-weight-bold text-white mb-0">Virtual Presentation</h4>
                                <small style="color: #BAE6FD;">International & Remote Delegates</small>
                            </div>
                            <div class="card-body p-4 bg-white">
                                <ul class="list-unstyled mb-0" style="font-size: 13px; line-height: 1.8; color: #334155;">
                                    <li class="mb-2"><strong>Platform:</strong> Zoom Video Conferencing with technical session hosts.</li>
                                    <li class="mb-2"><strong>Connectivity:</strong> HD webcam, noise-cancelling microphone, stable broadband.</li>
                                    <li class="mb-2"><strong>Pre-Recorded Video:</strong> Submit a 15-minute MP4 backup video 5 days before the event.</li>
                                    <li><strong>Live Discussion:</strong> Live attendance during the scheduled Q&A session is required.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Presentation Best Practices -->
            <div class="dos-donts bg-white p-4 p-md-5 rounded-lg shadow-sm mb-5" style="border: 1px solid #E2E8F0; border-radius: 12px;">
                <div class="text-center mb-4">
                    <span class="text-uppercase font-weight-bold" style="color: #0055A0; letter-spacing: 1.2px; font-size: 13px;">Academic Presentation Standards</span>
                    <h3 class="font-weight-bold mt-1" style="color: #003366;">Presentation Best Practices</h3>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #CBD5E1; border-left: 4px solid #0055A0; border-radius: 8px;">
                            <h5 class="font-weight-bold mb-3" style="color: #003366;">
                                <i class="fa fa-check-circle mr-2" style="color: #0055A0;"></i> Recommended Practices
                            </h5>
                            <ul class="pl-3 small mb-0" style="line-height: 1.9; color: #334155;">
                                <li>Highlight research motivation, problem statement, methodology, and empirical findings clearly.</li>
                                <li>Use clean, high-contrast slides (deep navy/dark slate on crisp light backgrounds).</li>
                                <li>Incorporate clear architectural diagrams, comparative graphs, and benchmark tables.</li>
                                <li>Rehearse delivery in advance to conclude comfortably within the 15-minute presentation limit.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-4 rounded h-100" style="background: #F8FAFC; border: 1px solid #CBD5E1; border-left: 4px solid #64748B; border-radius: 8px;">
                            <h5 class="font-weight-bold mb-3" style="color: #334155;">
                                <i class="fa fa-minus-circle mr-2" style="color: #64748B;"></i> Practices to Avoid
                            </h5>
                            <ul class="pl-3 small mb-0" style="line-height: 1.9; color: #475569;">
                                <li>Avoid reading verbatim from dense text slides or overcrowded bullet points.</li>
                                <li>Avoid low-resolution figures, blurry screenshots, or unreadable axis labels.</li>
                                <li>Avoid exceeding allocated presentation time to maintain conference program flow.</li>
                                <li>Avoid distracting audio effects or excessive animations that dilute technical content.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<style>
.step-num {
    background: #003366;
    color: #FFFFFF;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 13px;
    font-weight: 700;
}
.camera-hero .btn-primary:hover {
    background: #003d73 !important;
    border-color: #003d73 !important;
}
.camera-hero .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #FFFFFF !important;
}
</style>
@endsection
