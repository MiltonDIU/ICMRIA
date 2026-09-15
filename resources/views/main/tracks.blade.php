@extends('layouts.main')

@section('content')
<main id="main" class="main-page">
    <!-- Hero Banner -->
    <section class="tracks-hero text-white py-5 position-relative" style="background: linear-gradient(135deg, #001f3f 0%, #003366 55%, #004d80 100%); padding: 85px 0 65px;">
        <div class="container text-center">
            <div class="badge-pill-header mb-3">
                <span class="badge px-3 py-2 text-uppercase" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: #E0F2FE; font-size: 12px; letter-spacing: 1.5px; border-radius: 30px; font-weight: 600;">
                    <i class="fa fa-th-list mr-1"></i> Call for Papers & Research Domains
                </span>
            </div>
            <h1 class="display-4 font-weight-bold text-white mb-3" style="font-size: 2.6rem; letter-spacing: -0.5px;">Conference Tracks & Sub-Tracks</h1>
            <p class="lead mx-auto text-light" style="max-width: 820px; font-size: 1.15rem; color: #d6e3f3 !important; line-height: 1.6;">
                ICMRIA 2027 invites original, unpublished research papers, case studies, and reviews across 8 multidisciplinary tracks and 33 specialized sub-tracks.
            </p>

            <div class="d-flex flex-wrap justify-content-center mt-4" style="gap: 15px;">
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <strong class="text-white" style="font-size: 1.1rem;">8</strong> <span style="color: #cbd5e1;">Core Tracks</span>
                </div>
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <strong class="text-white" style="font-size: 1.1rem;">33</strong> <span style="color: #cbd5e1;">Specialized Sub-Tracks</span>
                </div>
                <div class="stat-badge px-4 py-2 rounded-pill" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); color: #F0F4F8;">
                    <strong class="text-white" style="font-size: 1.1rem;">Scopus Q2</strong> <span style="color: #cbd5e1;">Journal Publication Option</span>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <a href="{{ route('book-ticket') }}" class="btn btn-primary btn-lg px-4 py-3 shadow font-weight-bold" style="background: #0055A0; border-color: #0055A0; border-radius: 50px;">
                    <i class="fa fa-paper-plane mr-2"></i> Submit Paper to a Track
                </a>
                <a href="#tracks-list-section" class="btn btn-outline-light btn-lg px-4 py-3 shadow font-weight-bold" style="border-radius: 50px; border-width: 1.5px;">
                    <i class="fa fa-list mr-2"></i> View All Tracks
                </a>
            </div>
        </div>
    </section>

    <!-- Interactive Track Jump & Filter Bar -->
    <section class="py-3 bg-white border-bottom sticky-top" style="top: 70px; z-index: 90; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 15px;">
                <!-- Search filter -->
                <div class="d-flex align-items-center flex-grow-1" style="max-width: 420px;">
                    <i class="fa fa-search text-muted mr-2"></i>
                    <input type="text" id="trackSearchInput" class="form-control rounded-pill" placeholder="Search track or keyword (e.g. AI, Climate, Banking, EEE, Health)..." style="font-size: 13.5px; padding: 9px 18px; border: 1px solid #CBD5E1;">
                </div>

                <!-- Quick Track Jump Pills -->
                <div class="d-none d-lg-flex flex-wrap align-items-center" style="gap: 6px;">
                    <span class="text-muted small font-weight-bold mr-1">Quick Jump:</span>
                    @for($i = 1; $i <= 8; $i++)
                        <a href="#track-{{ $i }}" class="btn btn-sm btn-light rounded-pill px-2 py-1 font-weight-bold track-jump-pill" style="font-size: 11.5px; border: 1px solid #E2E8F0; color: #003366;">
                            T{{ $i }}
                        </a>
                    @endfor
                </div>

                <div class="text-muted small">
                    <span id="trackCountDisplay" class="font-weight-600" style="color: #475569;">Showing all 8 tracks</span>
                </div>
            </div>
        </div>
    </section>

    <span id="sub-tracks"></span>

    <!-- Main Tracks List Container -->
    <section id="tracks-list-section" class="py-5" style="background: #F8FAFC;">
        <div class="container">

            @php
            $trackList = [
                1 => [
                    'num' => '01',
                    'title' => 'Artificial Intelligence, Data Science & Emerging Technologies',
                    'icon' => 'fa-laptop',
                    'faculty' => 'Faculty of Science & Information Technology (FSIT)',
                    'chair' => 'Prof. Dr. Md. Fokhray Hossain',
                    'chair_title' => 'Dean, Faculty of Science & Information Technology (FSIT), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Machine Learning, Deep Learning & Generative AI',
                            'chair' => 'Prof. Dr. Sheak Rashed Haider Noori',
                            'chair_title' => 'Head, Department of Computer Science and Engineering (CSE), DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Big Data Analytics, Data Mining & Information Retrieval',
                            'chair' => 'Dr. S. M. Aminul Haque',
                            'chair_title' => 'Professor & Associate Head, Dept. of CSE, DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Computer Vision, Natural Language Processing & Pattern Recognition',
                            'chair' => 'Dr. Imran Mahmud',
                            'chair_title' => 'Professor & Head, Department of Software Engineering (SWE), DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Cloud, Edge Computing, Internet of Things (IoT) & Cybersecurity',
                            'chair' => 'Mr. Md. Sarwar Hossain Mollah',
                            'chair_title' => 'Associate Professor & Head, Department of Computing & Information System (CIS), DIU',
                        ],
                    ]
                ],
                2 => [
                    'num' => '02',
                    'title' => 'Sustainable Development, Environment & Climate Action',
                    'icon' => 'fa-leaf',
                    'faculty' => 'Faculty of Science & Information Technology (FSIT)',
                    'chair' => 'Prof. Dr. Bimal Chandra Das',
                    'chair_title' => 'Associate Dean, Faculty of Science & Information Technology (FSIT), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Climate Change Mitigation, Adaptation & Environmental Dynamics',
                            'chair' => 'Dr. A. B. M. Kamal Pasha',
                            'chair_title' => 'Associate Professor & Head, Dept. of Environmental Science & Disaster Management (ESDM), DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Renewable Energy Systems, Green Tech & Energy Policy',
                            'chair' => 'Prof. Dr. Md. Saidur Rahman',
                            'chair_title' => 'Professor, Department of ESDM, DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Environmental Risk Assessment, Water Resources & Waste Management',
                            'chair' => 'Dr. Kazi A. S. M. Nurul Huda',
                            'chair_title' => 'Associate Professor & Head, Department of Civil Engineering (CE), DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Sustainable Urbanization, Smart Cities & Biodiversity Conservation',
                            'chair' => 'Mr. Sheikh Muhammad Rezwan',
                            'chair_title' => 'Assistant Professor & Head, Department of Architecture, DIU',
                        ],
                    ]
                ],
                3 => [
                    'num' => '03',
                    'title' => 'Business, Economics, Management & Innovation',
                    'icon' => 'fa-line-chart',
                    'faculty' => 'Faculty of Business & Entrepreneurship (FBE)',
                    'chair' => 'Prof. Dr. Mohammad Rokibul Kabir',
                    'chair_title' => 'Dean, Faculty of Business & Entrepreneurship (FBE), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Digital Transformation, E-Commerce & Entrepreneurship',
                            'chair' => 'Dr. Md. Azizur Rahman',
                            'chair_title' => 'Associate Professor & Head, Department of Business Administration, DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Sustainable Finance, Banking, Accounting & Fintech Innovations',
                            'chair' => 'Professor Dr. Md. Abdur Rouf',
                            'chair_title' => 'Professor, Department of Accounting, DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Supply Chain, Logistics, Operations & Strategic Management',
                            'chair' => 'Mr. Siddiqur Rahman',
                            'chair_title' => 'Assistant Professor, Department of Business Administration, DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Marketing Analytics, Consumer Behavior & Real Estate Management',
                            'chair' => 'Dr. Dewan Golam Yazdani Showrav',
                            'chair_title' => 'Associate Professor & Head, Department of Marketing, DIU',
                        ],
                    ]
                ],
                4 => [
                    'num' => '04',
                    'title' => 'Core Engineering, Infrastructure & Smart Systems',
                    'icon' => 'fa-cogs',
                    'faculty' => 'Faculty of Engineering (FE)',
                    'chair' => 'Prof. Dr. M. Shamsul Alam',
                    'chair_title' => 'Dean, Faculty of Engineering (FE), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Electrical & Electronic Engineering (EEE): Smart Grids, Power Systems, VLSI, Robotics & Automation',
                            'chair' => 'Dr. Dara Abdus Satter',
                            'chair_title' => 'Associate Professor & Head, Department of Electrical & Electronic Engineering (EEE), DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Textile Engineering: Advanced Textile Materials, Smart Fabrics, Apparel & Chemical Processing',
                            'chair' => 'Prof. Dr. Md. Mahbubul Haque',
                            'chair_title' => 'Head, Department of Textile Engineering, DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Civil Engineering: Structural, Geotechnical, Transportation, Construction & Resilience',
                            'chair' => 'Dr. Kazi A. S. M. Nurul Huda',
                            'chair_title' => 'Associate Professor & Head, Department of Civil Engineering (CE), DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Architecture & Urban Planning: Sustainable Building Design, Heritage & Spatial Dynamics',
                            'chair' => 'Mr. Sheikh Muhammad Rezwan',
                            'chair_title' => 'Assistant Professor & Head, Department of Architecture, DIU',
                        ],
                        [
                            'num' => '5',
                            'title' => 'Information & Communication Engineering (ICE): Wireless Tech, Telecom & Signal Processing',
                            'chair' => 'Dr. Nusrat Jahan',
                            'chair_title' => 'Associate Professor & Head, Dept. of Information Technology & Management (ITM), DIU',
                        ],
                    ]
                ],
                5 => [
                    'num' => '05',
                    'title' => 'Social Sciences, Humanities, Law & Public Policy',
                    'icon' => 'fa-balance-scale',
                    'faculty' => 'Faculty of Humanities & Social Sciences (FHSS)',
                    'chair' => 'Prof. Dr. Liza Sharmin',
                    'chair_title' => 'Dean, Faculty of Humanities & Social Sciences (FHSS), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Legal Frameworks, Human Rights, Ethics & Governance',
                            'chair' => 'Prof. Dr. Kudrat-E-Khuda Babu',
                            'chair_title' => 'Professor & Head, Department of Law, DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Digital Humanities, Culture, Identity & Societal Transformation',
                            'chair' => 'Dr. Ehatasham Ul Hoque Eiten',
                            'chair_title' => 'Assistant Professor & Head, Department of English, DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Public Policy, International Relations, Peace & Conflict Resolution',
                            'chair' => 'Dr. Md. Fouad Hossain Sarker',
                            'chair_title' => 'Associate Professor & Head, Department of Development Studies, DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Social Welfare, Community Development & Public Administration',
                            'chair' => 'Ms. Bilkis Khanam',
                            'chair_title' => 'Assistant Professor & Head, Department of General Educational Development (GED), DIU',
                        ],
                    ]
                ],
                6 => [
                    'num' => '06',
                    'title' => 'Health Sciences, Biotechnology & Public Health',
                    'icon' => 'fa-medkit',
                    'faculty' => 'Faculty of Health & Life Sciences (FHLS)',
                    'chair' => 'Prof. Dr. Bellal Hossain',
                    'chair_title' => 'Dean, Faculty of Health & Life Sciences (FHLS), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Pharmaceutical Sciences, Drug Discovery & Advanced Therapeutics',
                            'chair' => 'Prof. Dr. Muniruddin Ahmed',
                            'chair_title' => 'Head, Department of Pharmacy, DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Biotechnology, Genomics, Bioinformatics & Molecular Biology',
                            'chair' => 'Prof. Dr. Md. Bellal Hossain',
                            'chair_title' => 'Professor & Head, Dept. of Nutrition & Food Engineering (NFE), DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Public Health Interventions, Epidemiology & Healthcare Systems',
                            'chair' => 'Dr. A. B. M. Alauddin Chowdhury',
                            'chair_title' => 'Associate Professor & Head, Department of Public Health, DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Health Technology, Telemedicine, Medical Devices & Clinical Innovations',
                            'chair' => 'Dr. Md. Shahjahan',
                            'chair_title' => 'Professor, Faculty of Health & Life Sciences, DIU',
                        ],
                    ]
                ],
                7 => [
                    'num' => '07',
                    'title' => 'Education, Language, Literature & Communication Studies',
                    'icon' => 'fa-graduation-cap',
                    'faculty' => 'Faculty of Humanities & Social Sciences (FHSS)',
                    'chair' => 'Prof. Dr. Liza Sharmin',
                    'chair_title' => 'Dean, Faculty of Humanities & Social Sciences (FHSS), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Smart Pedagogy, EdTech, E-Learning & Outcome-Based Education (OBE)',
                            'chair' => 'Prof. Dr. Md. Mostafa Kamal',
                            'chair_title' => 'Dean (Academic Affairs) & Professor, DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Applied Linguistics, Language Teaching (ELT) & Translation Studies',
                            'chair' => 'Dr. Ehatasham Ul Hoque Eiten',
                            'chair_title' => 'Assistant Professor & Head, Department of English, DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Modern & Comparative Literature, Cultural Studies',
                            'chair' => 'Prof. A. M. M. Hamidur Rahman',
                            'chair_title' => 'Professor, Department of English, DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Mass Media, Digital Journalism, PR & Strategic Communication',
                            'chair' => 'Mr. Aftab Hossain',
                            'chair_title' => 'Assistant Professor & Head, Dept. of Journalism, Media & Communication (JMC), DIU',
                        ],
                    ]
                ],
                8 => [
                    'num' => '08',
                    'title' => 'Agriculture, Food Security & Rural Development',
                    'icon' => 'fa-tree',
                    'faculty' => 'Faculty of Health & Life Sciences (FHLS)',
                    'chair' => 'Prof. Dr. Bellal Hossain',
                    'chair_title' => 'Dean, Faculty of Health & Life Sciences (FHLS), DIU',
                    'subtracks' => [
                        [
                            'num' => '1',
                            'title' => 'Smart Agriculture, Precision Farming, Agribusiness & IoT in Farming',
                            'chair' => 'Prof. Dr. M. A. Rahim',
                            'chair_title' => 'Head, Department of Agricultural Science, DIU',
                        ],
                        [
                            'num' => '2',
                            'title' => 'Food Safety, Processing, Quality Assurance & Food Security',
                            'chair' => 'Prof. Dr. Md. Bellal Hossain',
                            'chair_title' => 'Head, Department of Nutrition & Food Engineering (NFE), DIU',
                        ],
                        [
                            'num' => '3',
                            'title' => 'Agro-Ecology, Crop Protection, Soil Health & Plant Genetics',
                            'chair' => 'Dr. Md. Ahad Ali',
                            'chair_title' => 'Associate Professor, Department of Agricultural Science, DIU',
                        ],
                        [
                            'num' => '4',
                            'title' => 'Rural Economics, Sustainable Food Supply Chains & Community Upliftment',
                            'chair' => 'Dr. Md. Rashedul Islam',
                            'chair_title' => 'Associate Professor, Dept. of Agricultural Science, DIU',
                        ],
                    ]
                ],
            ];
            @endphp

            <!-- Clean, Scannable Track List -->
            <div class="row">
                @foreach($trackList as $id => $trk)
                <div class="col-12 mb-4 track-card-item" id="track-{{ $id }}" data-search="{{ strtolower($trk['title'] . ' ' . $trk['chair'] . ' ' . implode(' ', array_map(fn($s) => $s['title'] . ' ' . $s['chair'], $trk['subtracks']))) }}">
                    <div class="bg-white rounded-lg shadow-sm border overflow-hidden" style="border-color: #E2E8F0 !important; border-radius: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                        
                        <!-- Track Header Bar -->
                        <div class="p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center" style="background: #F1F5F9; border-bottom: 1px solid #E2E8F0;">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <span class="badge px-3 py-2 mr-3 font-weight-bold" style="background: #003366; color: #FFFFFF; font-size: 13px; border-radius: 6px; letter-spacing: 0.5px;">
                                    TRACK {{ $trk['num'] }}
                                </span>
                                <div>
                                    <h3 class="font-weight-bold mb-0" style="color: #003366; font-size: 1.25rem;">{{ $trk['title'] }}</h3>
                                    <small class="text-muted"><i class="fa fa-university mr-1 text-primary"></i> {{ $trk['faculty'] }}</small>
                                </div>
                            </div>
                            <div class="chair-badge py-1 px-3 rounded text-md-right" style="background: #FFFFFF; border: 1px solid #CBD5E1; font-size: 12px;">
                                <span class="text-muted d-block small">Overall Track Chair</span>
                                <strong style="color: #003366;"><i class="fa fa-user-circle mr-1 text-primary"></i> {{ $trk['chair'] }}</strong>
                                <span class="d-block text-muted" style="font-size: 11px;">{{ $trk['chair_title'] }}</span>
                            </div>
                        </div>

                        <!-- Sub-Tracks Clean List -->
                        <div class="p-0">
                            <ul class="list-group list-group-flush mb-0">
                                @foreach($trk['subtracks'] as $sub)
                                <li class="list-group-item p-3 p-md-3 d-flex flex-wrap align-items-center justify-content-between subtrack-row" style="border-color: #F1F5F9;">
                                    <div class="d-flex align-items-start align-items-md-center flex-grow-1 mr-3 mb-2 mb-md-0">
                                        <span class="sub-num-badge mr-3 font-weight-bold" style="background: #EEF4FA; color: #0055A0; width: 28px; height: 28px; min-width: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                            {{ $sub['num'] }}
                                        </span>
                                        <div>
                                            <div class="font-weight-bold" style="color: #1E293B; font-size: 14.5px; line-height: 1.4;">
                                                {{ $sub['title'] }}
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 12px;">
                                                <i class="fa fa-user-o mr-1 text-primary"></i> <strong class="text-dark">Chair:</strong> {{ $sub['chair'] }} &mdash; <span class="text-muted">{{ $sub['chair_title'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-md-auto">
                                        <a href="{{ route('book-ticket') }}" class="btn btn-sm btn-outline-primary px-3 py-1 font-weight-bold rounded-pill" style="border-color: #0055A0; color: #0055A0; font-size: 12px;">
                                            Submit Paper <i class="fa fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>

            <!-- Empty Search State -->
            <div id="noResultsMessage" class="text-center py-5 d-none bg-white rounded border p-4">
                <i class="fa fa-search fa-3x text-muted mb-3"></i>
                <h4 class="font-weight-bold text-dark">No matching tracks found</h4>
                <p class="text-muted">Try searching with a different keyword like "AI", "Climate", "Finance", "Robotics", "Health", or "Agriculture".</p>
                <button class="btn btn-primary rounded-pill px-4" onclick="resetTrackSearch()">View All Tracks</button>
            </div>

            <!-- Bottom CTA -->
            <div class="mt-4 p-4 p-md-5 bg-white rounded-lg text-center border" style="border-color: #E2E8F0; border-radius: 12px;">
                <h4 class="font-weight-bold" style="color: #003366;">Ready to Submit Your Research?</h4>
                <p class="text-muted mx-auto mb-4" style="max-width: 650px;">
                    Abstract submission deadline is <strong>30 October 2026</strong>. Accepted papers will appear in the conference proceedings and be considered for Scopus-indexed Q2 journal publication.
                </p>
                <div class="d-flex flex-wrap justify-content-center" style="gap: 12px;">
                    <a href="{{ route('book-ticket') }}" class="btn btn-primary px-4 py-2 font-weight-bold rounded-pill" style="background: #0055A0; border-color: #0055A0;">
                        <i class="fa fa-paper-plane mr-1"></i> Submit Abstract Online
                    </a>
                    <a href="{{ route('author-guidelines') }}" class="btn btn-outline-primary px-4 py-2 font-weight-bold rounded-pill" style="border-color: #0055A0; color: #0055A0;">
                        <i class="fa fa-file-text-o mr-1"></i> Author Guidelines & Template
                    </a>
                </div>
            </div>

        </div>
    </section>
</main>

<style>
.track-card-item:hover {
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.08) !important;
}
.subtrack-row {
    transition: background 0.15s ease;
}
.subtrack-row:hover {
    background: #F8FAFC !important;
}
.track-jump-pill:hover {
    background: #003366 !important;
    color: #FFFFFF !important;
    border-color: #003366 !important;
}
.tracks-hero .btn-primary:hover {
    background: #003d73 !important;
    border-color: #003d73 !important;
}
.tracks-hero .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #FFFFFF !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('trackSearchInput');
    const trackItems = document.querySelectorAll('.track-card-item');
    const trackCountDisplay = document.getElementById('trackCountDisplay');
    const noResultsMessage = document.getElementById('noResultsMessage');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            let visibleCount = 0;

            trackItems.forEach(item => {
                const searchData = item.getAttribute('data-search') || '';
                if (!query || searchData.includes(query)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            if (trackCountDisplay) {
                trackCountDisplay.textContent = query ? `Showing ${visibleCount} of 8 tracks` : 'Showing all 8 tracks';
            }

            if (noResultsMessage) {
                if (visibleCount === 0) {
                    noResultsMessage.classList.remove('d-none');
                } else {
                    noResultsMessage.classList.add('d-none');
                }
            }
        });
    }

    window.resetTrackSearch = function() {
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    };
});
</script>
@endsection
