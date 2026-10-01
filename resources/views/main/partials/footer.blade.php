<footer id="footer">
  <div class="footer-top">
    <div class="container">
      <div class="row">

        <div class="col-lg-3 col-md-6 footer-info">
          <img src="{{ asset('img/icmria27-logo-white.png') }}" alt="ICMRIA 2027" style="max-width: 220px; margin-bottom: 15px;">
          <p>{!! $settings['footer_description'] ?? '' !!}</p>
        </div>

        <div class="col-lg-3 col-md-6 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><i class="fa fa-angle-right"></i> <a href="#">Home</a></li>
            <li><i class="fa fa-angle-right"></i> <a href="#about">About us</a></li>
            <!--<li><i class="fa fa-angle-right"></i> <a href="#">Services</a></li>-->
            <!--<li><i class="fa fa-angle-right"></i> <a href="#">Terms of service</a></li>-->
            <li><i class="fa fa-angle-right"></i> <a href="{{ route('privacy-policy') }}">Privacy policy</a></li>
            @guest
              <li><i class="fa fa-angle-right"></i> <a href="{{ route('login') }}">Login</a></li>
            @endguest
            @auth
              <li><i class="fa fa-angle-right"></i> <a href="{{ route('admin.home') }}">Admin Panel</a></li>
            @endauth
          </ul>
        </div>

        <div class="col-lg-3 col-md-6 footer-links">
          <h4>QR Code</h4>
          <img width="65%" src="{{ asset('/') }}img/qr-code.png">
          <!--<ul>-->
          <!--  <li><i class="fa fa-angle-right"></i> <a href="#">Home</a></li>-->
          <!--  <li><i class="fa fa-angle-right"></i> <a href="#">About us</a></li>-->
            <!--<li><i class="fa fa-angle-right"></i> <a href="#">Services</a></li>-->
            <!--<li><i class="fa fa-angle-right"></i> <a href="#">Terms of service</a></li>-->
          <!--  <li><i class="fa fa-angle-right"></i> <a href="#">Privacy policy</a></li>-->
          <!--  @guest-->
          <!--    <li><i class="fa fa-angle-right"></i> <a href="{{ route('login') }}">Login</a></li>-->
          <!--  @endguest-->
          <!--  @auth-->
          <!--    <li><i class="fa fa-angle-right"></i> <a href="{{ route('admin.home') }}">Admin Panel</a></li>-->
          <!--  @endauth-->
          <!--</ul>-->
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
