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
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#CallforPapers">Call for
                        Papers</a></li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#committee">Committee</a>
                </li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#messages">Messages</a>
                </li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#speakers">Speakers</a>
                </li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#schedule">Schedule</a>
                </li>
                <li><a href="{{ Route::current()->getName() != 'home' ? route('home') : '' }}#buy-tickets">Registration</a>
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
</style>
