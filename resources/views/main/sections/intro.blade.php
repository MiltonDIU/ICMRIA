<section id="intro">
    <div class="intro-container wow fadeIn">

        <p class="intro-pre-title">International Conference on</p>

        <div class="intro-logo-wrap">
            <img src="{{ asset('img/icmria27-logo-white.png') }}" alt="ICMRIA 2027" class="intro-logo">
        </div>

        <h1 class="main-title">Multidisciplinary Research, Innovation and Applications 2027</h1>
        <p class="sub-title">Connecting Knowledge, Innovation and Society for a Sustainable and Intelligent Future</p>

        <p class="intro-meta mb-4 pb-0">
            <span><i class="fa fa-calendar"></i> {!! $settings['about_when'] ?? '9–10 January 2027' !!}</span>
            <span class="meta-separator">&nbsp;&bull;&nbsp;</span>
            <span><i class="fa fa-map-marker"></i> Daffodil Smart City, Dhaka, Bangladesh</span>
        </p>

        @if(!empty($settings['youtube_link']))
            <a href="{{ $settings['youtube_link'] }}" class="venobox play-btn mb-4" data-vbtype="video" data-autoplay="true"></a>
        @endif

        <div class="intro-action mb-3">
            @if(!Auth::check())
                <a href="{{ route('book-ticket') }}" class="about-btn scrollto">Abstract Submission</a>
            @else
                <a href="{{ route('admin.home') }}" class="about-btn scrollto">Dashboard</a>
            @endif
        </div>

        <p class="organize">Organized by</p>
        <div class="organize-details">
            <h4 class="organize-uni">Daffodil International University (DIU)</h4>
            <p class="organize-loc">Daffodil Smart City, Birulia, Savar, Dhaka, Bangladesh</p>
        </div>

    </div>
</section>

<style>
    #intro {
        width: 100%;
        min-height: 100vh;
        height: auto !important;
        position: relative;
        background: url("{{ asset('img/BackgroundImage.png') }}") center center / cover no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 120px 20px 60px;
        overflow: hidden;
    }

    #intro:before {
        content: "";
        background: linear-gradient(to bottom, rgba(0, 15, 35, 0.84), rgba(0, 25, 55, 0.90));
        position: absolute;
        bottom: 0;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1;
    }

    #intro .intro-container {
        position: relative !important;
        z-index: 2;
        width: 100%;
        max-width: 1100px;
        margin: 0 auto !important;
        top: 0 !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0 15px !important;
        background: transparent !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .intro-pre-title {
        color: #ffffff;
        font-size: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 6px;
        font-family: 'Raleway', 'Open Sans', sans-serif;
    }

    .intro-logo-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 10px 0 16px;
    }

    .intro-logo {
        max-width: 440px;
        width: 85%;
        height: auto;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
    }

    .main-title {
        display: block;
        font-size: 38px;
        color: #ffffff !important;
        font-weight: 700;
        line-height: 1.25;
        font-family: 'Raleway', 'Open Sans', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
    }

    .sub-title {
        display: block;
        font-family: 'Open Sans', sans-serif;
        font-size: 19px;
        color: rgba(255, 255, 255, 0.92);
        line-height: 1.5;
        font-weight: 400;
        max-width: 850px;
        margin: 0 auto 20px;
    }

    .intro-meta {
        color: #ebebeb;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 25px;
    }

    .intro-meta i {
        color: var(--brand-green, #10BB43);
        margin-right: 6px;
    }

    .meta-separator {
        color: rgba(255, 255, 255, 0.6);
    }

    #intro .about-btn {
        font-family: "Open Sans", sans-serif;
        font-weight: 600;
        font-size: 18px;
        letter-spacing: 1px;
        display: inline-block;
        padding: 12px 36px;
        border-radius: 50px;
        transition: all 0.3s ease;
        line-height: 1;
        color: #fff;
        background: var(--brand-green, #10BB43);
        border: 2px solid var(--brand-green, #10BB43);
        margin: 5px 0 20px;
        box-shadow: 0 4px 15px rgba(16, 187, 67, 0.35);
    }

    #intro .about-btn:hover {
        background: transparent;
        border: 2px solid #ffffff;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 255, 255, 0.2);
    }

    .organize {
        color: rgba(255, 255, 255, 0.85);
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin: 25px 0 8px;
    }

    .organize-details {
        margin: 0 auto;
    }

    .organize-uni {
        color: #ffffff;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 4px;
        font-family: 'Raleway', 'Open Sans', sans-serif;
    }

    .organize-loc {
        color: rgba(255, 255, 255, 0.85);
        font-size: 15px;
        margin: 0;
    }

    @media (max-width: 991px) {
        #intro {
            padding: 100px 15px 45px;
        }

        .main-title {
            font-size: 28px;
        }

        .sub-title {
            font-size: 16px;
        }

        .intro-logo {
            max-width: 340px;
        }

        .organize-uni {
            font-size: 20px;
        }
    }

    @media (max-width: 768px) {
        #intro {
            padding: 90px 15px 35px;
        }

        .intro-pre-title {
            font-size: 14px;
            letter-spacing: 1px;
        }

        .intro-logo {
            max-width: 250px;
        }

        .main-title {
            font-size: 22px;
            line-height: 1.3;
        }

        .sub-title {
            font-size: 14px;
            margin-bottom: 15px;
        }

        .intro-meta {
            font-size: 14px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .meta-separator {
            display: none;
        }

        #intro .about-btn {
            font-size: 15px;
            padding: 10px 26px;
        }

        .organize {
            font-size: 14px;
            margin-top: 15px;
        }

        .organize-uni {
            font-size: 17px;
        }

        .organize-loc {
            font-size: 13px;
        }
    }
</style>