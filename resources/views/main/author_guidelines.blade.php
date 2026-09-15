@extends('layouts.main')

@section('content')
<main id="main" class="main-page">
    <!-- Hero Banner -->
    <section class="guidelines-hero text-white py-5 position-relative" style="background: linear-gradient(135deg, #001f3f 0%, #003366 55%, #004d80 100%); margin-top: -2px; padding: 90px 0 70px;">
        <div class="container text-center">
            <div class="badge-pill-header mb-3">
                <span class="badge px-3 py-2 text-uppercase font-weight-bold" style="background: rgba(125, 211, 252, 0.15); border: 1px solid rgba(125, 211, 252, 0.4); color: #BAE6FD; font-size: 12.5px; letter-spacing: 1px; border-radius: 30px;">
                    <i class="fa fa-graduation-cap mr-1"></i> ICMRI 2027 Author Portal
                </span>
            </div>
            <h1 class="display-4 font-weight-bold text-white mb-3" style="font-size: 2.75rem; letter-spacing: -0.5px;">Paper Submission & Guidelines</h1>
            <p class="lead mx-auto text-light" style="max-width: 800px; font-size: 1.15rem; color: #d6e3f3 !important; line-height: 1.6;">
                Detailed instructions for submitting research abstracts, full manuscripts, peer-review processes, formatting templates, and conference presentation policies.
            </p>

            <!-- Key Feature Badges -->
            <div class="d-flex flex-wrap justify-content-center mt-4" style="gap: 12px;">
                <div class="feature-tag px-3 py-2 rounded" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); font-size: 14px;">
                    <i class="fa fa-book text-warning mr-1"></i> Scopus Q2 Journal Publication
                </div>
                <div class="feature-tag px-3 py-2 rounded" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); font-size: 14px;">
                    <i class="fa fa-shield text-info mr-1"></i> Rigorous Peer Review
                </div>
                <div class="feature-tag px-3 py-2 rounded" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); font-size: 14px;">
                    <i class="fa fa-file-text-o text-success mr-1"></i> IEEE Conference Format
                </div>
                <div class="feature-tag px-3 py-2 rounded" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); font-size: 14px;">
                    <i class="fa fa-globe text-primary mr-1"></i> Hybrid (Onsite & Online)
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 pt-2">
                <a href="{{ route('book-ticket') }}" class="btn btn-primary btn-lg px-4 py-3 mr-2 shadow-sm font-weight-bold" style="background: #0055A0; border-color: #0055A0; border-radius: 50px;">
                    <i class="fa fa-paper-plane mr-2"></i> Submit Abstract / Paper
                </a>
                <a href="#templates" class="btn btn-outline-light btn-lg px-4 py-3 shadow-sm font-weight-bold" style="border-radius: 50px;">
                    <i class="fa fa-download mr-2"></i> Download Template
                </a>
            </div>
        </div>
    </section>

    <!-- Important Dates Ribbon -->
    <section id="important-dates" class="py-4 bg-white shadow-sm border-bottom">
        <div class="container">
            <div class="row text-center align-items-center">
                <div class="col-6 col-md-3 py-2 border-right">
                    <span class="d-block text-uppercase font-weight-bold text-muted" style="font-size: 11px; letter-spacing: 1px;">Abstract Deadline</span>
                    <strong class="d-block text-danger" style="font-size: 1.15rem;">30 Oct 2026</strong>
                </div>
                <div class="col-6 col-md-3 py-2 border-right">
                    <span class="d-block text-uppercase font-weight-bold text-muted" style="font-size: 11px; letter-spacing: 1px;">Full Manuscript Window</span>
                    <strong class="d-block text-primary" style="font-size: 1.15rem;">15–30 Nov 2026</strong>
                </div>
                <div class="col-6 col-md-3 py-2 border-right">
                    <span class="d-block text-uppercase font-weight-bold text-muted" style="font-size: 11px; letter-spacing: 1px;">Registration Deadline</span>
                    <strong class="d-block text-dark" style="font-size: 1.15rem;">26 Dec 2026</strong>
                </div>
                <div class="col-6 col-md-3 py-2">
                    <span class="d-block text-uppercase font-weight-bold text-muted" style="font-size: 11px; letter-spacing: 1px;">Conference Dates</span>
                    <strong class="d-block text-success" style="font-size: 1.15rem;">9–10 Jan 2027</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Sections -->
    <section class="py-5" style="background: #f8fafd;">
        <div class="container">

            <!-- Section 1: Submission & Peer Review Workflow Visualization -->
            <div class="workflow-container bg-white p-4 p-md-5 rounded-lg shadow-sm mb-5" style="border: 1px solid rgba(0, 85, 160, 0.1); border-radius: 12px;">
                <div class="text-center mb-5">
                    <span class="text-uppercase text-primary font-weight-bold" style="letter-spacing: 1px; font-size: 13px;">End-to-End Conference Lifecycle</span>
                    <h2 class="font-weight-bold" style="color: #003366;">Paper Submission & Review Workflow</h2>
                    <p class="text-muted mx-auto" style="max-width: 700px;">
                        Following international peer-review standards, all submissions to ICMRI 2027 proceed through a standardized 6-phase review and evaluation pipeline.
                    </p>
                </div>

                <!-- 6 Phases Pipeline Grid -->
                <div class="row">
                    <!-- Phase 1 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="phase-card h-100 p-4 rounded" style="background: #fefefe; border: 1px solid #e2e8f0; border-top: 3px solid #0055A0; position: relative;">
                            <div class="phase-num d-inline-flex align-items-center justify-content-center text-white font-weight-bold mb-3" style="width: 36px; height: 36px; background: #003366; border-radius: 50%; font-size: 14px;">1</div>
                            <h5 class="font-weight-bold mb-2 text-dark">Pre-Submission & Profile</h5>
                            <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                <li>Account creation with institutional email & affiliation.</li>
                                <li>ORCID ID integration & researcher profile setup.</li>
                                <li>Role assignment (Author, Reviewer, Track Chair).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Phase 2 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="phase-card h-100 p-4 rounded" style="background: #fefefe; border: 1px solid #e2e8f0; border-top: 3px solid #0055A0; position: relative;">
                            <div class="phase-num d-inline-flex align-items-center justify-content-center text-white font-weight-bold mb-3" style="width: 36px; height: 36px; background: #003366; border-radius: 50%; font-size: 14px;">2</div>
                            <h5 class="font-weight-bold mb-2 text-dark">Manuscript Submission</h5>
                            <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                <li>Select target Conference Track & Sub-Track.</li>
                                <li>Enter Title, 200–250 words Abstract, and 4–6 Keywords.</li>
                                <li>Upload anonymized manuscript file (IEEE standard format).</li>
                                <li>Conflict of Interest (CoI) declaration.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Phase 3 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="phase-card h-100 p-4 rounded" style="background: #fefefe; border: 1px solid #e2e8f0; border-top: 3px solid #0055A0; position: relative;">
                            <div class="phase-num d-inline-flex align-items-center justify-content-center text-white font-weight-bold mb-3" style="width: 36px; height: 36px; background: #003366; border-radius: 50%; font-size: 14px;">3</div>
                            <h5 class="font-weight-bold mb-2 text-dark">Review Assignment & Bidding</h5>
                            <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                <li>Automated keyword & expertise matching.</li>
                                <li>Reviewer bidding (Want to Review / Neutral / Conflict).</li>
                                <li>Track Chairs assign 2–3 independent peer reviewers.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Phase 4 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="phase-card h-100 p-4 rounded" style="background: #fefefe; border: 1px solid #e2e8f0; border-top: 3px solid #0055A0; position: relative;">
                            <div class="phase-num d-inline-flex align-items-center justify-content-center text-white font-weight-bold mb-3" style="width: 36px; height: 36px; background: #003366; border-radius: 50%; font-size: 14px;">4</div>
                            <h5 class="font-weight-bold mb-2 text-dark">Reviewing & Evaluation</h5>
                            <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                <li>Evaluation criteria: Originality, Technical Depth, Methodology & Relevance (1–5 scale).</li>
                                <li>Constructive author feedback & confidential chair notes.</li>
                                <li>Standard recommendation (Accept, Revisions, Reject).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Phase 5 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="phase-card h-100 p-4 rounded" style="background: #fefefe; border: 1px solid #e2e8f0; border-top: 3px solid #0055A0; position: relative;">
                            <div class="phase-num d-inline-flex align-items-center justify-content-center text-white font-weight-bold mb-3" style="width: 36px; height: 36px; background: #003366; border-radius: 50%; font-size: 14px;">5</div>
                            <h5 class="font-weight-bold mb-2 text-dark">Decision & Notification</h5>
                            <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                <li>Track Chair review consolidation & TPC Chair approval.</li>
                                <li>Automated decision email sent to corresponding author.</li>
                                <li>Full reviewer comments provided for revisions.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Phase 6 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="phase-card h-100 p-4 rounded" style="background: #fefefe; border: 1px solid #e2e8f0; border-top: 3px solid #0055A0; position: relative;">
                            <div class="phase-num d-inline-flex align-items-center justify-content-center text-white font-weight-bold mb-3" style="width: 36px; height: 36px; background: #003366; border-radius: 50%; font-size: 14px;">6</div>
                            <h5 class="font-weight-bold mb-2 text-dark">Camera-Ready & Registration</h5>
                            <ul class="text-muted small pl-3 mb-0" style="line-height: 1.8;">
                                <li>Final camera-ready manuscript upload with all authors.</li>
                                <li>Signed Copyright Transfer Form submission.</li>
                                <li>Author registration & payment verification.</li>
                                <li>Proceedings & Book of Abstracts scheduling.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Detailed Guidelines Cards -->
            <div class="row mb-5">
                <!-- Abstract Guidelines -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm rounded-lg" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom p-4">
                            <h4 class="font-weight-bold mb-1 text-primary"><i class="fa fa-list-alt mr-2"></i> Abstract Submission Guidelines</h4>
                            <p class="text-muted small mb-0">Criteria for submitting research abstracts for consideration.</p>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex mb-3">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>Word Limit & Structure:</strong>
                                        <p class="text-muted small mb-0">Abstracts must be between <strong>200–250 words</strong>, clearly stating research objectives, methodology, key findings, and contributions to multidisciplinary innovation.</p>
                                    </div>
                                </li>
                                <li class="d-flex mb-3">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>Metadata Requirements:</strong>
                                        <p class="text-muted small mb-0">Must include paper title, full author names, institutional affiliations, country, corresponding author email, selected track, and <strong>4–6 descriptive keywords</strong>.</p>
                                    </div>
                                </li>
                                <li class="d-flex mb-3">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>File Formats:</strong>
                                        <p class="text-muted small mb-0">Accepted in Microsoft Word (.DOCX) or PDF format through the official submission portal.</p>
                                    </div>
                                </li>
                                <li class="d-flex">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>Originality & Ethical Conduct:</strong>
                                        <p class="text-muted small mb-0">Submissions must be original, unpublished, and not under concurrent review by any other journal or conference.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Manuscript & Formatting Guidelines -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm rounded-lg" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom p-4">
                            <h4 class="font-weight-bold mb-1 text-primary"><i class="fa fa-file-code-o mr-2"></i> Manuscript & Formatting Standards</h4>
                            <p class="text-muted small mb-0">Technical requirements for full paper preparation.</p>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex mb-3">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>IEEE Standard Conference Format:</strong>
                                        <p class="text-muted small mb-0">Papers must strictly adhere to standard IEEE two-column conference paper guidelines (A4 size, standard margins, Times New Roman typeface).</p>
                                    </div>
                                </li>
                                <li class="d-flex mb-3">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>Page Limits:</strong>
                                        <p class="text-muted small mb-0">Regular papers are recommended to be between <strong>6 to 8 pages</strong>, including all figures, tables, and references.</p>
                                    </div>
                                </li>
                                <li class="d-flex mb-3">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>Blind Review Anonymity:</strong>
                                        <p class="text-muted small mb-0">Initial manuscript drafts submitted for peer review must NOT include author names, affiliations, or self-identifying citations.</p>
                                    </div>
                                </li>
                                <li class="d-flex">
                                    <i class="fa fa-check-circle text-success mt-1 mr-3"></i>
                                    <div>
                                        <strong>Presentation Mandate:</strong>
                                        <p class="text-muted small mb-0">At least one registered author must present each accepted paper (onsite at Daffodil Smart City or virtually) to be included in conference proceedings.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Downloads & Resource Center -->
            <div id="templates" class="templates-section bg-white p-4 p-md-5 rounded-lg shadow-sm mb-5" style="border: 1px solid #E2E8F0; border-radius: 12px;">
                <div class="text-center mb-4">
                    <span class="badge px-3 py-1 font-weight-bold text-uppercase mb-2" style="background: #EEF4FA; color: #0055A0; font-size: 11.5px; border-radius: 20px;">
                        Templates & Author Forms
                    </span>
                    <h3 class="font-weight-bold" style="color: #003366;">Conference Templates & Documents</h3>
                    <p class="text-muted mx-auto" style="max-width: 650px;">
                        Official IEEE conference manuscript templates, presentation guides, and copyright forms will be released shortly.
                    </p>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="download-card p-4 text-center rounded border h-100 d-flex flex-column justify-content-between" style="background: #F8FAFC; border-color: #E2E8F0 !important; transition: all 0.3s;">
                            <div>
                                <i class="fa fa-file-word-o fa-3x mb-3" style="color: #0055A0;"></i>
                                <h5 class="font-weight-bold" style="color: #003366;">Word Document Template</h5>
                                <p class="text-muted small">Standard conference manuscript template for Microsoft Word (.DOCX).</p>
                            </div>
                            <div>
                                <span class="badge badge-light px-2 py-1 text-muted mb-2" style="font-size: 11px;">Coming Soon</span>
                                <a href="#" class="btn btn-outline-primary btn-block" style="border-color: #0055A0; color: #0055A0;">
                                    <i class="fa fa-download mr-1"></i> Download .DOCX
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="download-card p-4 text-center rounded border h-100 d-flex flex-column justify-content-between" style="background: #F8FAFC; border-color: #E2E8F0 !important; transition: all 0.3s;">
                            <div>
                                <i class="fa fa-file-pdf-o fa-3x mb-3" style="color: #0055A0;"></i>
                                <h5 class="font-weight-bold" style="color: #003366;">Call for Papers (Full PDF)</h5>
                                <p class="text-muted small">Comprehensive CFP brochure detailing all tracks, sub-tracks, and advisory boards.</p>
                            </div>
                            <div>
                                <span class="badge badge-light px-2 py-1 text-muted mb-2" style="font-size: 11px;">Coming Soon</span>
                                <a href="#" class="btn btn-outline-primary btn-block" style="border-color: #0055A0; color: #0055A0;">
                                    <i class="fa fa-file-pdf-o mr-1"></i> View Full PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="download-card p-4 text-center rounded border h-100 d-flex flex-column justify-content-between" style="background: #F8FAFC; border-color: #E2E8F0 !important; transition: all 0.3s;">
                            <div>
                                <i class="fa fa-shield fa-3x mb-3" style="color: #0055A0;"></i>
                                <h5 class="font-weight-bold" style="color: #003366;">Copyright Transfer Form</h5>
                                <p class="text-muted small">Mandatory copyright agreement form required upon final camera-ready submission.</p>
                            </div>
                            <div>
                                <span class="badge badge-light px-2 py-1 text-muted mb-2" style="font-size: 11px;">Coming Soon</span>
                                <a href="#" class="btn btn-outline-primary btn-block" style="border-color: #0055A0; color: #0055A0;">
                                    <i class="fa fa-download mr-1"></i> Download Form
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action Banner -->
            <div class="p-4 p-md-5 text-center text-white rounded-lg shadow" style="background: linear-gradient(135deg, #003366 0%, #0055A0 100%); border-radius: 12px;">
                <h3 class="font-weight-bold text-white mb-2">Ready to Submit Your Research?</h3>
                <p class="text-light mb-4 mx-auto" style="max-width: 600px; color: #d6e3f3 !important;">
                    Join scholars, researchers, and innovators from across the world at ICMRI 2027. Selected high-impact papers will be submitted to Scopus-indexed Q2 journals.
                </p>
                <a href="{{ route('book-ticket') }}" class="btn btn-primary btn-lg px-5 py-3 font-weight-bold shadow" style="background: #0055A0; border-color: #0055A0; border-radius: 50px;">
                    <i class="fa fa-paper-plane mr-2"></i> Submit Abstract Online
                </a>
            </div>

        </div>
    </section>
</main>
@endsection
