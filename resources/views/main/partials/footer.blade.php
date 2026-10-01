<footer id="footer">
  <div class="footer-top">
    <div class="container">
      <div class="row">

        <div class="col-lg-3 col-md-6 footer-info">
          <img src="{{ asset('img/icmria27-logo-white.png') }}" alt="ICMRIA 2027" style="max-width: 220px; margin-bottom: 15px;">
          <p>{!! $settings['footer_description'] ?? 'ICMRIA 2027 &mdash; an international forum connecting knowledge, innovation and society across technology, environment, business, health, humanities and governance.' !!}</p>
        </div>

        <div class="col-lg-3 col-md-6 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><i class="fa fa-angle-right"></i> <a href="{{ url('/') }}">Home</a></li>
            <li><i class="fa fa-angle-right"></i> <a href="{{ url('/#about') }}">About Conference</a></li>
            <li><i class="fa fa-angle-right"></i> <a href="{{ route('author-guidelines') }}">Author Guidelines</a></li>
            <li><i class="fa fa-angle-right"></i> <a href="{{ route('tracks') }}">Conference Tracks</a></li>
            <li><i class="fa fa-angle-right"></i> <a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
            @guest
              <li><i class="fa fa-angle-right"></i> <a href="{{ route('login') }}">Login</a></li>
            @endguest
            @auth
              <li><i class="fa fa-angle-right"></i> <a href="{{ route('admin.home') }}">Admin Panel</a></li>
            @endauth
          </ul>
        </div>

        <div class="col-lg-3 col-md-6 footer-links">
          <h4>Conference Portal</h4>
          <div style="background: #ffffff; padding: 12px; border-radius: 10px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 170px; text-align: center;">
            <a href="https://icmria.daffodilvarsity.edu.bd" target="_blank" title="Scan or click to visit official conference website">
              <img src="{{ asset('img/qr-code.png') }}" alt="ICMRIA 2027 Official Website QR Code" style="width: 100%; max-width: 145px; height: auto; display: block; border-radius: 4px;">
            </a>
            <div style="margin-top: 8px; font-size: 11px; font-weight: 600; color: #003366; line-height: 1.3;">
              <i class="fa fa-qrcode mr-1"></i> Scan to Visit
            </div>
          </div>
          <div class="mt-2" style="font-size: 12px;">
            <a href="https://icmria.daffodilvarsity.edu.bd" target="_blank" style="color: #94a3b8; word-break: break-all;">
              icmria.daffodilvarsity.edu.bd
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 footer-contact">
          <h4>Contact Us</h4>
          <p>
            {!! $settings['footer_address'] ?? 'Daffodil International University<br>Daffodil Smart City (DSC),<br>Birulia, Savar, Dhaka-1216' !!}<br><br>
            <strong>Official Helpline:</strong><br>
            <i class="fa fa-phone mr-1"></i> <a href="tel:+8801711851121" style="color: #cbd5e1;">+880 1711-851121</a><br>
            <i class="fa fa-phone mr-1"></i> <a href="tel:+8801946704373" style="color: #cbd5e1;">+880 1946-704373</a><br><br>
            <strong>WhatsApp Support:</strong><br>
            <i class="fa fa-whatsapp mr-1 text-success"></i> <span style="color: #cbd5e1;">+880 1711-851121, +880 1946-704373</span><br><br>
            <strong>Email:</strong><br>
            <i class="fa fa-envelope-o mr-1"></i> <a href="mailto:icmria2027@diu.edu.bd" style="color: #cbd5e1;">icmria2027@diu.edu.bd</a><br>
          </p>

          <div class="social-links">
            <a href="{{ $settings['footer_twitter'] ?? '' }}" class="twitter"><i class="fa fa-twitter"></i></a>
            <a href="{{ $settings['footer_facebook'] ?? '' }}" class="facebook"><i class="fa fa-facebook"></i></a>
            <a href="{{ $settings['footer_instagram'] ?? '' }}" class="instagram"><i class="fa fa-instagram"></i></a>
            <a href="{{ $settings['footer_googleplus'] ?? '' }}" class="google-plus"><i class="fa fa-google-plus"></i></a>
            <a href="{{ $settings['footer_linkedin'] ?? '' }}" class="linkedin"><i class="fa fa-linkedin"></i></a>
          </div>

        </div>

      </div>
    </div>
  </div>

  <div class="container">
    <div class="copyright">
      &copy; Copyright <strong>{{ env('APP_NAME', 'Daffodil Family') }}</strong>. All Rights Reserved
    </div>
    <div class="credits">
      Developed by <a href="https://daffodilweb.com/" target="_blank">Daffodil Web & e-Commerce Ltd.</a>
    </div>
  </div>
</footer><!-- #footer -->
