<header id="header" @if(Route::current()->getName() != 'home') class="header-fixed" @endif>
    <div class="container-fluid">

        <div id="logo" class="pull-left">
            <h1>
                <a href="{{ route('home') }}#intro">
                    <img width="230" src="{{ asset('img/icmria27-logo-white.png') }}" alt="ICMRIA 2027">
                </a>
            </h1>
        </div>

        <nav id="nav-menu-container">
            <ul class="nav-menu">
                <li class="menu-active"><a
                        href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#intro">Home</a></li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#about">About</a></li>
                {{-- <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#CallforPapers">Call for Papers</a></li> --}}
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#committee">Committee</a>
                </li>
                @php
                    $hasPublishedMessages = isset($conferenceMessages) ? $conferenceMessages->isNotEmpty() : \App\Models\ConferenceMessage::where('is_published', 1)->exists();
                @endphp
                @if($hasPublishedMessages)
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#messages">Messages</a>
                </li>
                @endif
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#speakers">Speakers</a>
                </li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#schedule">Schedule</a>
                </li>
                <li class="menu-has-children">
                    <a href="javascript:void(0)">Authors</a>
                    <ul>
                        <li><a href="{{ route('author-guidelines') }}">Paper Submission & Guidelines</a></li>
                        <li><a href="{{ route('callForPepper') }}">Conference Flyer / Call for Papers (CFP)</a></li>
                        <li><a href="{{ route('tracks') }}">Tracks</a></li>
                        <li><a href="{{ route('tracks') }}#sub-tracks">Sub-Tracks</a></li>
                        <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#important-dates">Important Dates</a></li>
                        <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#schedule">Program Schedule</a></li>
                        <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#buy-tickets">Registration Info & Costs</a></li>
                        <li><a href="{{ route('camera-ready-guidelines') }}">Camera Ready Guidelines & Presentation Guidelines</a></li>
                        <li><a href="{{ route('author-guidelines') }}#templates">Conference Template</a></li>
                        <li><a href="{{ route('accommodation-transportation') }}">Accommodation & Transportation Info</a></li>
                    </ul>
                </li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#buy-tickets">Registration Fees</a>
                </li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#contact">Contact</a></li>
                @if(!Auth::check())
                    <li class="buy-tickets"><a href="{{ route('book-ticket') }}">Abstract Submission</a></li>
                    <li class="buy-tickets"><a href="{{ route("login") }}">Sign In</a></li>
                @else
                    <li class="buy-tickets"><a href="{{ route("admin.home") }}">Dashboard</a></li>
                    <li class="buy-tickets"><a href="{{ route('logout') }}" onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">Logout</a> </li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endif

            </ul>
        </nav>
    </div>
</header>

<style>
    /* Desktop Dropdown Styling */
    .nav-menu ul {
        min-width: 290px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-radius: 6px;
        border-top: 3px solid var(--brand-blue, #0055A0);
        padding: 8px 0;
    }

    .nav-menu ul li a {
        padding: 8px 20px;
        font-size: 13px;
        color: #2d3748;
        font-weight: 500;
        transition: all 0.2s ease;
        text-align: left;
        white-space: normal;
        line-height: 1.4;
    }

    .nav-menu ul li a:hover {
        background: rgba(0, 85, 160, 0.08);
        color: var(--brand-blue, #0055A0);
        padding-left: 24px;
    }

    /* Single Dropdown Arrow Animation on hover */
    .sf-arrows .sf-with-ul {
        padding-right: 22px !important;
    }

    .sf-arrows .sf-with-ul:after {
        right: 8px;
        transition: transform 0.3s ease;
    }

    .nav-menu li:hover > .sf-with-ul:after,
    .nav-menu li.sfHover > .sf-with-ul:after {
        transform: rotate(180deg);
    }

    @media (max-width: 1367px) {
        #nav-menu-container {
            display: none !important;
        }

        #mobile-nav-toggle {
            display: inline !important;
        }

        #header {
            background: var(--brand-navy);
            height: 70px;
            padding: 15px 0;
            transition: all 0.5s;
        }

        .nav-menu>li {
            margin-left: 4px;
        }
    }

    /* Mobile Nav Dropdown Styling */
    #mobile-nav ul .menu-has-children ul {
        display: none;
        padding-left: 15px;
        background: rgba(0, 0, 0, 0.25);
    }
    #mobile-nav ul .menu-has-children ul li a {
        font-size: 14px;
        padding: 8px 20px;
        color: #ddd;
    }
    #mobile-nav ul .menu-has-children ul li a:hover {
        color: #fff;
    }
</style>
