@extends('layouts.admin')
@section('content')
<div class="card">
<div class="card-header">
Dashboard
</div>

<div class="card-body">
<div class="content">
@include('partials.bidding-banner')
@can('admin_report')
<div class="row mb-4">
    <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3 mb-xl-0">
        <div class="info-box shadow-none border bg-white" style="border-radius: 12px; border-left: 5px solid #6f42c1 !important; height: 100%;">
            <span class="info-box-icon text-purple"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Total Registration</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $total }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3 mb-xl-0">
        <div class="info-box shadow-none border bg-white" style="border-radius: 12px; border-left: 5px solid #17a2b8 !important; height: 100%;">
            <span class="info-box-icon text-info"><i class="fas fa-user-edit"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Total Submitters</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $totalSubmitters }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3 mb-xl-0">
        <div class="info-box shadow-none border bg-white" style="border-radius: 12px; border-left: 5px solid #007bff !important; height: 100%;">
            <span class="info-box-icon text-primary"><i class="fas fa-feather-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Total Authors</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $totalActualAuthors }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-6 col-md-6 col-sm-6 mb-3 mb-xl-0">
        <div class="info-box shadow-none border bg-white" style="border-radius: 12px; border-left: 5px solid #6c757d !important; height: 100%;">
            <span class="info-box-icon text-secondary"><i class="fas fa-walking"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Participants Only</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $totalParticipants }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-6 col-md-12 col-sm-12 mb-3 mb-xl-0">
        <div class="info-box shadow-none border bg-white" style="border-radius: 12px; border-left: 5px solid #28a745 !important; height: 100%;">
            <span class="info-box-icon text-success"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Participant Paid</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $paidParticipants }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="info-box shadow-none border bg-light" style="border-radius: 12px;">
            <span class="info-box-icon text-primary"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Total Abstracts</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $totalPapers }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-none border bg-light" style="border-radius: 12px;">
            <span class="info-box-icon text-warning"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Pending Review</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $pendingPapers }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-none border bg-light" style="border-radius: 12px;">
            <span class="info-box-icon text-success"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold text-muted small uppercase">Paid Papers</span>
                <span class="info-box-number h4 mb-0 font-weight-bold">{{ $paidPapers }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title font-weight-bold mb-0 text-dark">Abstract Status Overview</h5>
            </div>
            <div class="card-body">
                <div id="chart_paper_status" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title font-weight-bold mb-0 text-dark">Approved Abstract Payments</h5>
            </div>
            <div class="card-body">
                <div id="chart_paper_payment" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">

<div class="col-lg-6">
<div class="card">
<div class="card-header">
Total Registered Users
</div>

<div class="card-body">
<div id="chartdiv"></div>
</div>
</div>
</div>
<div class="col-lg-6">
    <div class="card shadow-sm border-0" style="border-radius: 12px; height: 100%;">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title font-weight-bold mb-0 text-dark"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i> Financial Overview by Registration Type</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="min-width: 600px;">
                    <thead class="bg-light small uppercase font-weight-bold text-muted">
                        <tr>
                            <th class="border-0 px-4 py-3">Currency</th>
                            <th class="border-0 py-3 text-center">Authors<br><small class="text-xs">(Paid / Unpaid)</small></th>
                            <th class="border-0 py-3 text-center">Participants<br><small class="text-xs">(Paid / Unpaid)</small></th>
                            <th class="border-0 px-4 py-3 text-right">Total<br><small class="text-xs">(Paid / Unpaid)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencyStats as $stat)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-weight-bold h6 mb-0 text-dark-blue">{{ $stat->currency }}</div>
                                    <small class="text-muted"><i class="fas fa-users mr-1"></i> {{ $stat->total_users }} Users</small>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="text-success font-weight-bold small"><i class="fas fa-check-circle mr-1 opacity-7"></i> {{ number_format($stat->author_paid_amt, 0) }}</div>
                                    <div class="text-danger font-weight-bold small"><i class="fas fa-clock mr-1 opacity-7"></i> {{ number_format($stat->author_unpaid_amt, 0) }}</div>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="text-success font-weight-bold small"><i class="fas fa-check-circle mr-1 opacity-7"></i> {{ number_format($stat->participant_paid_amt, 0) }}</div>
                                    <div class="text-danger font-weight-bold small"><i class="fas fa-clock mr-1 opacity-7"></i> {{ number_format($stat->participant_unpaid_amt, 0) }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="text-success font-weight-bold h6 mb-0"><strong>{{ number_format($stat->paid_amount, 0) }}</strong></div>
                                    <div class="text-danger font-weight-bold small"><strong>{{ number_format($stat->unpaid_amount, 0) }}</strong></div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> No currency-specific data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="col-lg-12">
    <div class="card shadow-sm border-0 mt-4" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-chart-bar mr-2 text-primary"></i> Top Submission Tracks
            </h5>
        </div>
        <div class="card-body py-4">
            <div class="row">
                @forelse($topTracks as $track)
                    @php
                        $percentage = $totalPapers > 0 ? ($track->submission_count / $totalPapers) * 100 : 0;
                        $barColor = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'][$loop->index % 10];
                    @endphp
                    <div class="col-md-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold text-dark text-truncate" style="max-width: 70%;" title="{{ $track->name }}">
                                {{ $track->name }}
                            </span>
                            <span class="badge badge-light border text-primary px-2">{{ $track->submission_count }} Total</span>
                        </div>
                        <div class="progress mb-2" style="height: 10px; border-radius: 10px; background-color: #f8f9fc;">
                            <div class="progress-bar" role="progressbar"
                                style="width: {{ $percentage }}%; background-color: {{ $barColor }}; border-radius: 10px;"
                                aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Status Breakdown Mini-Bars -->
                        <div class="row no-gutters mt-2">
                            <div class="col-4 pr-1">
                                <div class="d-flex justify-content-between x-small mb-1">
                                    <span class="text-muted">Approved</span>
                                    <span class="font-weight-bold text-success">{{ $track->approved_count }}</span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $track->submission_count > 0 ? ($track->approved_count / $track->submission_count) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="col-4 px-1">
                                <div class="d-flex justify-content-between x-small mb-1">
                                    <span class="text-muted">Pending</span>
                                    <span class="font-weight-bold text-warning">{{ $track->pending_count }}</span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $track->submission_count > 0 ? ($track->pending_count / $track->submission_count) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="col-4 pl-1">
                                <div class="d-flex justify-content-between x-small mb-1">
                                    <span class="text-muted">Rejected</span>
                                    <span class="font-weight-bold text-danger">{{ $track->rejected_count }}</span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $track->submission_count > 0 ? ($track->rejected_count / $track->submission_count) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="fas fa-folder-open mb-2 h2 d-block opacity-2"></i>
                        No submissions recorded yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div>

<div class="row">
    <!-- Daily Trend Chart -->
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mt-4" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="fas fa-chart-line mr-2 text-primary"></i> Daily Activity Trend (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <div id="chart_daily_trends"></div>
            </div>
        </div>
    </div>

    <!-- Country-wise Stats Table -->
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mt-4 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title font-weight-bold mb-0 text-dark">
                    <i class="fas fa-globe-americas mr-2 text-success"></i> Country-wise Registration & Submission Analytics
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="min-width: 800px;">
                        <thead class="bg-light small uppercase font-weight-bold text-muted">
                            <tr>
                                <th class="border-0 px-4 py-3">Country</th>
                                <th class="border-0 py-3 text-center">Total Registrations</th>
                                <th class="border-0 py-3 text-center">Authors<br><small class="text-xs">(Paid / Total)</small></th>
                                <th class="border-0 py-3 text-center">Participants<br><small class="text-xs">(Paid / Total)</small></th>
                                <th class="border-0 py-3 text-center">Submitted Papers</th>
                                <th class="border-0 px-4 py-3 text-right">Payment Completion (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($countryStats as $cStat)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-weight-bold text-dark">{{ $cStat->country_name }}</div>
                                    </td>
                                    <td class="py-3 text-center font-weight-bold text-dark-blue">{{ $cStat->total_registrations }}</td>
                                    <td class="py-3 text-center">
                                        <span class="text-success font-weight-bold">{{ $cStat->paid_authors }}</span>
                                        <span class="text-muted">/</span>
                                        <span class="text-secondary font-weight-bold">{{ $cStat->total_authors }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="text-success font-weight-bold">{{ $cStat->paid_participants }}</span>
                                        <span class="text-muted">/</span>
                                        <span class="text-secondary font-weight-bold">{{ $cStat->total_participants }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge badge-light border text-primary font-weight-bold px-3 py-2 rounded-pill">{{ $cStat->total_papers }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <div class="progress mr-2" style="width: 100px; height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $cStat->payment_percentage }}%; border-radius: 4px;"></div>
                                            </div>
                                            <span class="font-weight-bold text-success" style="font-size: 0.95rem;">{{ $cStat->payment_percentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> No country analytics data recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endcan

{{-- The delegates-only block that used to live here has moved to
     resources/views/admin/author_dashboard.blade.php. DashboardController returns that
     view for role 3 before this one is reached, so nothing here ever ran for them. --}}

</div>
</div>
</div>
@endsection

@push('style')
<style>
{{-- Dropped with the delegates-only block: #chartdiv3 (no such element anywhere),
     .link-muted (styled the commented-out blogs list) and .student-status-toggle
     (the student-status form, which now lives only on the Abstracts page). --}}
#chartdiv {
width: 100%;
height: 500px;
}
#chart_paper_status, #chart_paper_payment {
    width: 100%;
    height: 300px;
}
#chart_daily_trends {
    width: 100%;
    height: 400px;
}
</style>
@endpush

@push('script')

<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<!-- Chart code -->

<!-- Chart code -->
<script>
var total = {!! json_encode($total) !!};
var profiles = {!! json_encode($profiles) !!};
am4core.ready(function() {
// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end
var chart = am4core.create("chartdiv", am4charts.PieChart3D);
chart.hiddenState.properties.opacity = 0; // this creates initial fade-in
chart.data = profiles;
if (total === 0) {
// Create a new chart with a default value of "No data available"
var chart = am4core.create("totalOverview", am4charts.PieChart3D);
chart.innerRadius = 100;
var label = chart.seriesContainer.createChild(am4core.Label);
label.text = "No data available";
label.horizontalCenter = "middle";
label.verticalCenter = "middle";
label.fontSize = 30;
label.y = -20;
label.multiline = true;
label.textAlign = "center";
}else{
chart.innerRadius = 100;
var label = chart.seriesContainer.createChild(am4core.Label);
label.text = "Total\n{{ $total }}"; // use \n instead of <br>
label.horizontalCenter = "middle";
label.verticalCenter = "middle";
label.fontSize = 30;
label.y = -25; // move label 20 pixels upwards from the center
label.multiline = true; // enable multiline text
label.textAlign = "center"; // center-align the text within the label
}


chart.innerRadius = am4core.percent(40);
chart.depth = 30;
chart.legend = new am4charts.Legend();

var series = chart.series.push(new am4charts.PieSeries3D());
series.dataFields.value = "litres";
series.dataFields.depthValue = "litres";
series.dataFields.category = "country";
series.slices.template.cornerRadius = 3;
series.colors.step = 3;
}); // end am4core.ready()
</script>




{{-- Two scripts were removed here, both of which threw on every load of this page:

     - a star-rating handler reading `.rating label`. No such element has ever existed in
       this view, so `labels[0].style.color` hit undefined immediately.
     - a dropdown tidier reading `document.getElementById("schedule_id")`. No such element
       either, so `select.getElementsByTagName` was called on null.

     Both belonged to a session-feedback form that is not on this page. --}}

<script>
    am4core.ready(function() {
        am4core.useTheme(am4themes_animated);

        // Paper Status Chart
        var statusChart = am4core.create("chart_paper_status", am4charts.PieChart3D);
        statusChart.hiddenState.properties.opacity = 0;
        statusChart.data = {!! json_encode($paperStats) !!};
        statusChart.innerRadius = am4core.percent(40);
        statusChart.depth = 20;
        statusChart.legend = new am4charts.Legend();

        var statusSeries = statusChart.series.push(new am4charts.PieSeries3D());
        statusSeries.dataFields.value = "litres";
        statusSeries.dataFields.category = "category";
        statusSeries.slices.template.cornerRadius = 5;
        statusSeries.colors.step = 3;

        // Paper Payment Chart
        var paymentChart = am4core.create("chart_paper_payment", am4charts.PieChart3D);
        paymentChart.hiddenState.properties.opacity = 0;
        paymentChart.data = {!! json_encode($paperPaymentStats) !!};
        paymentChart.innerRadius = am4core.percent(40);
        paymentChart.depth = 20;
        paymentChart.legend = new am4charts.Legend();

        var paymentSeries = paymentChart.series.push(new am4charts.PieSeries3D());
        paymentSeries.dataFields.value = "litres";
        paymentSeries.dataFields.category = "category";
        paymentSeries.slices.template.cornerRadius = 5;
        paymentSeries.colors.list = [
            am4core.color("#28a745"),
            am4core.color("#ffc107")
        ];

        // Daily Activity Trends Chart
        var trendChart = am4core.create("chart_daily_trends", am4charts.XYChart);
        trendChart.data = {!! json_encode($dailyTrends) !!};

        // Create axes
        var categoryAxis = trendChart.xAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "date";
        categoryAxis.renderer.grid.template.location = 0;
        categoryAxis.renderer.minGridDistance = 40;
        categoryAxis.renderer.labels.template.rotation = -45;
        categoryAxis.renderer.labels.template.horizontalCenter = "right";
        categoryAxis.renderer.labels.template.verticalCenter = "middle";

        var valueAxis = trendChart.yAxes.push(new am4charts.ValueAxis());
        valueAxis.title.text = "Count";
        valueAxis.min = 0;

        // Create series for Authors
        var seriesAuthors = trendChart.series.push(new am4charts.ColumnSeries());
        seriesAuthors.dataFields.valueY = "authors";
        seriesAuthors.dataFields.categoryX = "date";
        seriesAuthors.name = "Registered Authors";
        seriesAuthors.tooltipText = "{name}: [bold]{valueY}[/]";
        seriesAuthors.columns.template.fill = am4core.color("#007bff");
        seriesAuthors.columns.template.stroke = am4core.color("#007bff");
        seriesAuthors.columns.template.width = am4core.percent(40);

        // Create series for Participants
        var seriesParticipants = trendChart.series.push(new am4charts.ColumnSeries());
        seriesParticipants.dataFields.valueY = "participants";
        seriesParticipants.dataFields.categoryX = "date";
        seriesParticipants.name = "Registered Participants";
        seriesParticipants.tooltipText = "{name}: [bold]{valueY}[/]";
        seriesParticipants.columns.template.fill = am4core.color("#28a745");
        seriesParticipants.columns.template.stroke = am4core.color("#28a745");
        seriesParticipants.columns.template.width = am4core.percent(40);

        // Create series for Papers
        var seriesPapers = trendChart.series.push(new am4charts.LineSeries());
        seriesPapers.dataFields.valueY = "papers";
        seriesPapers.dataFields.categoryX = "date";
        seriesPapers.name = "Submitted Papers";
        seriesPapers.tooltipText = "{name}: [bold]{valueY}[/]";
        seriesPapers.strokeWidth = 3;
        seriesPapers.stroke = am4core.color("#ffc107");
        seriesPapers.fill = am4core.color("#ffc107");
        
        // Add bullets to LineSeries
        var bullet = seriesPapers.bullets.push(new am4charts.CircleBullet());
        bullet.circle.radius = 4;
        bullet.circle.fill = am4core.color("#fff");
        bullet.circle.strokeWidth = 2;

        // Add legend
        trendChart.legend = new am4charts.Legend();

        // Add cursor
        trendChart.cursor = new am4charts.XYCursor();
    });
</script>
@endpush


{{-- The star-rating styles that were here belonged to a "Session Rating" card that has
     been commented out for as long as the file has existed; no .rating markup is rendered
     anywhere on this page. --}}
