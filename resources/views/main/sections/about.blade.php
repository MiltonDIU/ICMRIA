<section id="about">
  <div class="container">
    <div class="row">
      <!-- Left Column: About Event -->
      <div class="col-lg-6 mb-4 mb-lg-0">
        <h2>About The Event</h2>
        <p style="text-align: justify; line-height: 1.85; font-size: 15px; color: rgba(255, 255, 255, 0.92);">
          {{ $settings['about_description'] ?? '' }}
        </p>
      </div>

      <!-- Right Column: Where & When + Important Dates -->
      <div class="col-lg-6">
        <h2>Where &amp; When</h2>
        
        <div class="mb-4" style="color: rgba(255, 255, 255, 0.92); font-size: 15px; line-height: 1.8;">
          <p class="mb-2">
            <i class="fa fa-map-marker mr-2" style="color: #ffffff; opacity: 0.85;"></i>
            <strong>Venue:</strong> {!! $settings['about_where'] ?? 'Daffodil Smart City, Birulia, Savar, Dhaka, Bangladesh' !!}
          </p>
          <p class="mb-0">
            <i class="fa fa-calendar mr-2" style="color: #ffffff; opacity: 0.85;"></i>
            <strong>Dates:</strong> {!! $settings['about_when'] ?? '9–10 January 2027' !!}
          </p>
        </div>

        <!-- Important Dates Milestone Box (Clean, Elegant Academic Style) -->
        <div class="important-dates-box p-4 rounded position-relative" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.16); border-radius: 8px;">
          <span id="important-dates" style="position: absolute; top: -100px;"></span>
          <h3 style="font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; margin-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.15); padding-bottom: 10px;">
            <i class="fa fa-calendar-check-o mr-2"></i> Important Dates
          </h3>

          <div class="date-milestones-list" style="font-size: 14px;">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
              <span style="color: rgba(255, 255, 255, 0.85);">Abstract Submission Deadline:</span>
              <strong class="text-white">30 October 2026</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
              <span style="color: rgba(255, 255, 255, 0.85);">Full Manuscript Submission Window:</span>
              <strong class="text-white">15–30 November 2026</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
              <span style="color: rgba(255, 255, 255, 0.85);">Registration Deadline:</span>
              <strong class="text-white">26 December 2026</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2">
              <span style="color: rgba(255, 255, 255, 0.85);">Conference Dates:</span>
              <strong class="text-white">9–10 January 2027</strong>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>
