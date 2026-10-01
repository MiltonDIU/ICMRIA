<section id="supporters" class="wow fadeInUp section-with-bg">
  <div class="container">
    <div class="section-header text-center">
      <h2>Sponsors &amp; Partners</h2>
      <p>Supporting Innovation, Academic Research &amp; Multidisciplinary Collaboration</p>
    </div>

    @if(isset($sponsors) && $sponsors->count() > 0)
      <div class="row justify-content-center supporters-wrap clearfix" style="margin:0; padding:0;">
        @foreach($sponsors as $sponsor)
          <div class="col-md-3 col-6 d-flex justify-content-center align-items-center mb-4" style="padding:0;">
            <div class="supporter-logo" style="text-align:center;">
              <img src="{{ $sponsor->logo!=null ? $sponsor->logo->getUrl() : '' }}"
                   alt="{{ $sponsor->name }}"
                   class="img-fluid"
                   style="object-fit:contain; border:none;">
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
          <div class="p-4 p-md-5 bg-white shadow-sm" style="border: 1px dashed #CBD5E1; border-radius: 12px;">
            <span class="badge px-3 py-2 text-uppercase font-weight-bold mb-3" style="background: rgba(0, 85, 160, 0.08); color: #0055A0; font-size: 12px; border-radius: 20px;">
              Partners &amp; Sponsors &bull; TBA
            </span>
            <h4 class="font-weight-bold mb-2" style="color: #003366;">Official Sponsors &amp; Partners to be Announced</h4>
            <p class="text-muted mx-auto mb-4" style="max-width: 580px; line-height: 1.7; font-size: 14.5px;">
              Confirmed sponsor, academic, and strategic partner organizations will be unveiled shortly.
            </p>
            <a href="mailto:{{ $settings['contact_email'] ?? 'icmria2027@diu.edu.bd' }}?subject=ICMRIA%202027%20Sponsorship%20Inquiry" class="btn btn-outline-primary btn-sm px-4 py-2 font-weight-bold rounded-pill" style="border-color: #0055A0; color: #0055A0;">
              <i class="fa fa-envelope-o mr-1"></i> Inquire for Sponsorship
            </a>
          </div>
        </div>
      </div>
    @endif
  </div>
</section>

<style>
#supporters {
  padding: 60px 0;
  background: #f8fafc;
}
#supporters .supporters-wrap {
  border-top: 0px solid #e0e5fa;
  border-left: 0px solid #e0e5fa;
}

#supporters .supporter-logo {
  border: 1px solid #e0e5fa;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: transform 0.3s ease;
  width: 100%;
}

#supporters .supporter-logo img {
  object-fit: contain;
  transition: transform 0.3s ease;
}

#supporters .supporter-logo:hover img {
  transform: scale(1.05);
}
</style>