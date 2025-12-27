@extends('admin::index')

@section('content')
<!-- Spacer to prevent top cutoff -->
<div style="height: 1px; margin-top: 50px;"></div>

<style>
    /* Force top visibility - aggressive approach */
    .content-header {
        display: none !important;
    }
    
    html, body {
        scroll-padding-top: 0 !important;
    }
    
    .content-wrapper {
        padding: 0 15px 15px 15px !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .content-wrapper > section.content {
        margin: 0 !important;
        padding: 0 !important;
        padding-top: 0 !important;
    }
    
    body .dashboard-container {
        margin: 0 !important;
        padding: 0 !important;
        position: relative;
        top: 0;
    }
    
    body .dashboard-header {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColor }}dd 100%);
        padding: 25px 20px !important;
        margin: 0 -15px 15px -15px !important;
        color: white;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        width: calc(100% + 30px) !important;
    }
    
    .dashboard-header h1 {
        color: white;
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        display: inline-block;
    }
    
    .dashboard-header .year-selector {
        display: inline-block;
        float: right;
        margin-top: 2px;
    }
    
    .dashboard-header select {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        color: white;
        padding: 4px 10px;
        border-radius: 0;
        font-size: 13px;
        min-width: 150px;
        cursor: pointer;
        height: 28px;
    }
    
    .dashboard-header select option {
        color: #333;
        background: white;
    }
    
    .stat-card {
        background: white;
        border-radius: 0;
        padding: 0;
        margin: 0 10px 10px 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        overflow: hidden;
        border-left: 3px solid {{ $primaryColor }};
        transition: all 0.2s ease;
    }
    
    .stat-card:hover {
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .stat-card a {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    
    .stat-card a:hover {
        text-decoration: none;
        color: inherit;
    }
    
    .stat-card.success {
        border-left-color: {{ $secondaryColor }};
    }
    
    .stat-card.danger {
        border-left-color: #dd4b39;
    }
    
    .stat-card-body {
        padding: 12px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .stat-card-icon {
        width: 45px;
        height: 45px;
        border-radius: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        background: {{ $primaryColor }};
        flex-shrink: 0;
    }
    
    .stat-card.success .stat-card-icon {
        background: {{ $secondaryColor }};
    }
    
    .stat-card.danger .stat-card-icon {
        background: #dd4b39;
    }
    
    .stat-card-content {
        flex: 1;
        text-align: right;
        margin-left: 12px;
    }
    
    .stat-card-title {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    
    .stat-card-number {
        font-size: 24px;
        font-weight: 700;
        color: {{ $primaryColor }};
        line-height: 1;
    }
    
    .stat-card.success .stat-card-number {
        color: {{ $secondaryColor }};
    }
    
    .stat-card.danger .stat-card-number {
        color: #dd4b39;
    }
    
    .info-box {
        background: white;
        border-radius: 0;
        padding: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        margin: 0 10px 10px 10px;
    }
    
    .info-box h3 {
        color: {{ $primaryColor }};
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 12px 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-box h3 i {
        margin-right: 8px;
    }
    
    .performance-table {
        width: 100%;
        margin-bottom: 0;
    }
    
    .performance-table tr {
        border-bottom: 1px solid #f5f5f5;
    }
    
    .performance-table tr:last-child {
        border-bottom: none;
    }
    
    .performance-table th {
        padding: 8px 0;
        color: #555;
        font-weight: 600;
        font-size: 13px;
        width: 60%;
    }
    
    .performance-table td {
        padding: 8px 0;
        text-align: right;
        font-size: 16px;
        font-weight: 700;
    }
    
    .performance-table .success-rate {
        color: {{ $secondaryColor }};
    }
    
    .performance-table .gpa-score {
        color: {{ $primaryColor }};
    }
    
    .performance-table .total-count {
        color: #555;
    }
    
    .quick-link-btn {
        display: block;
        width: 100%;
        padding: 10px;
        margin-bottom: 8px;
        background: white;
        border: 1px solid {{ $primaryColor }};
        color: {{ $primaryColor }};
        text-align: center;
        border-radius: 0;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .quick-link-btn:hover {
        background: {{ $primaryColor }};
        color: white;
        text-decoration: none;
    }
    
    .quick-link-btn i {
        margin-right: 6px;
        font-size: 14px;
    }
    
    .students-year-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 12px;
    }
    
    .students-year-table thead th {
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
        padding: 10px 8px;
        border: 1px solid {{ $primaryColor }}30;
        font-weight: 700;
        font-size: 12px;
    }
    
    .students-year-table tbody td {
        padding: 8px;
        border: 1px solid #f0f0f0;
    }
    
    .students-year-table tbody tr:hover {
        background: #fafafa;
    }
    
    @media (max-width: 768px) {
        .stat-card-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }
        
        .stat-card-number {
            font-size: 20px;
        }
        
        .stat-card-title {
            font-size: 10px;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <h1><i class="fa fa-dashboard"></i> MRU Dashboard</h1>
        <div class="year-selector">
            <form method="GET" action="{{ admin_url('mru-dashboard') }}" id="dashboardFilterForm" style="display: flex; gap: 10px;">
                <select name="academic_year" class="form-control" onchange="document.getElementById('dashboardFilterForm').submit()">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->acadyear }}" {{ $selectedYear == $year->acadyear ? 'selected' : '' }}>
                            {{ $year->acadyear }}
                        </option>
                    @endforeach
                </select>
                <select name="semester" class="form-control" onchange="document.getElementById('dashboardFilterForm').submit()">
                    @foreach($semesters as $semValue => $semLabel)
                        <option value="{{ $semValue }}" {{ $selectedSemester == $semValue ? 'selected' : '' }}>
                            {{ $semLabel }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div style="clear:both;"></div>
    </div>

    <!-- Statistics Cards -->
    <div class="row" style="margin: 0 -5px;">
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="{{ admin_url('mru-course-registrations') }}?acad_year={{ $selectedYear }}{{ $selectedSemester ? '&semester='.$selectedSemester : '' }}">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Students Registered</div>
                            <div class="stat-card-number">{{ number_format($stats['students_registered']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="{{ admin_url('mru-courses') }}">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Courses</div>
                            <div class="stat-card-number">{{ number_format($stats['total_courses']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="{{ admin_url('mru-programmes') }}">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Programmes</div>
                            <div class="stat-card-number">{{ number_format($stats['total_programmes']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="{{ admin_url('mru-faculties') }}">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-building"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Faculties</div>
                            <div class="stat-card-number">{{ number_format($stats['total_faculties']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="row" style="margin: 0 -5px;">
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="{{ admin_url('mru-results') }}?acad={{ $selectedYear }}{{ $selectedSemester ? '&semester='.$selectedSemester : '' }}">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-file-text"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Results</div>
                            <div class="stat-card-number">{{ number_format($stats['total_results']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="{{ admin_url('mru-course-registrations') }}?acad_year={{ $selectedYear }}{{ $selectedSemester ? '&semester='.$selectedSemester : '' }}">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-registered"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Course Registrations</div>
                            <div class="stat-card-number">{{ number_format($stats['course_registrations']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card success">
                <a href="{{ admin_url('mru-results') }}?acad={{ $selectedYear }}{{ $selectedSemester ? '&semester='.$selectedSemester : '' }}&grade_ne=F">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Passing Results</div>
                            <div class="stat-card-number">{{ number_format($stats['passing_results']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card danger">
                <a href="{{ admin_url('mru-results') }}?acad={{ $selectedYear }}{{ $selectedSemester ? '&semester='.$selectedSemester : '' }}&grade=F">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-times-circle"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Failing Results</div>
                            <div class="stat-card-number">{{ number_format($stats['failing_results']) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Performance & Quick Links -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <h3><i class="fa fa-line-chart"></i> Academic Performance</h3>
                <table class="performance-table">
                    <tr>
                        <th>Pass Rate</th>
                        <td class="success-rate">{{ $stats['pass_rate'] }}%</td>
                    </tr>
                    <tr>
                        <th>Average GPA</th>
                        <td class="gpa-score">{{ $stats['average_gpa'] }}</td>
                    </tr>
                    <tr>
                        <th>Total Results</th>
                        <td class="total-count">{{ number_format($stats['total_results']) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <h3><i class="fa fa-graduation-cap"></i> Programme Enrollments</h3>
                <div style="max-height: 280px; overflow-y: auto;">
                    <table class="performance-table">
                        @foreach($programmeEnrollments as $programme)
                        <tr>
                            <th style="width: 70%; font-size: 11px; font-weight: 500;">
                                {{ $programme->progname ?? $programme->prog_id }}
                            </th>
                            <td style="text-align: right; font-size: 14px; font-weight: 700; color: {{ $primaryColor }};">
                                {{ number_format($programme->student_count) }}
                            </td>
                        </tr>
                        @endforeach
                        @if($programmeEnrollments->isEmpty())
                        <tr>
                            <td colspan="2" style="text-align: center; color: #999; padding: 20px;">
                                No programme enrollments found
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <h3><i class="fa fa-link"></i> Quick Links</h3>
                <a href="{{ admin_url('mru-results') }}" class="quick-link-btn">
                    <i class="fa fa-list"></i> View All Results
                </a>
                <a href="{{ admin_url('mru-academic-result-exports') }}" class="quick-link-btn">
                    <i class="fa fa-download"></i> Export Results
                </a>
                <a href="{{ admin_url('mru-students') }}" class="quick-link-btn">
                    <i class="fa fa-users"></i> Manage Students
                </a>
                <a href="{{ admin_url('mru-courses') }}" class="quick-link-btn">
                    <i class="fa fa-book"></i> Manage Courses
                </a>
            </div>
        </div>
    </div>

    <!-- Students by Programme and Year of Study -->
    <div class="row">
        <div class="col-md-12">
            <div class="info-box">
                <h3><i class="fa fa-table"></i> Students by Programme & Year of Study</h3>
                <div style="overflow-x: auto;">
                    <table class="students-year-table">
                        <thead>
                            <tr>
                                <th style="width: 40%; text-align: left;">Programme</th>
                                @for($year = 1; $year <= $studentsByProgrammeYear['max_year']; $year++)
                                    <th style="width: {{ 60 / $studentsByProgrammeYear['max_year'] }}%; text-align: center;">Year {{ $year }}</th>
                                @endfor
                                <th style="width: 10%; text-align: center; background: {{ $primaryColor }}; color: white;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @foreach($studentsByProgrammeYear['programmes'] as $programme)
                                @php 
                                    $progTotal = array_sum($programme['years']);
                                    $grandTotal += $progTotal;
                                @endphp
                                <tr>
                                    <td style="font-weight: 600; font-size: 11px;">
                                        {{ $programme['abbrev'] ?? $programme['progid'] }}
                                    </td>
                                    @for($year = 1; $year <= $studentsByProgrammeYear['max_year']; $year++)
                                        <td style="text-align: center; font-size: 13px; font-weight: 600; color: {{ $programme['years'][$year] > 0 ? $primaryColor : '#ccc' }};">
                                            {{ $programme['years'][$year] > 0 ? number_format($programme['years'][$year]) : '-' }}
                                        </td>
                                    @endfor
                                    <td style="text-align: center; font-weight: 700; font-size: 14px; background: #f5f5f5; color: {{ $primaryColor }};">
                                        {{ number_format($progTotal) }}
                                    </td>
                                </tr>
                            @endforeach
                            @if(empty($studentsByProgrammeYear['programmes']))
                                <tr>
                                    <td colspan="{{ $studentsByProgrammeYear['max_year'] + 2 }}" style="text-align: center; color: #999; padding: 20px;">
                                        No student data found for the selected academic year
                                    </td>
                                </tr>
                            @else
                                <tr style="background: {{ $primaryColor }}10; border-top: 2px solid {{ $primaryColor }};">
                                    <td style="font-weight: 700; font-size: 12px; color: {{ $primaryColor }};">GRAND TOTAL</td>
                                    @for($year = 1; $year <= $studentsByProgrammeYear['max_year']; $year++)
                                        @php
                                            $yearTotal = 0;
                                            foreach($studentsByProgrammeYear['programmes'] as $prog) {
                                                $yearTotal += $prog['years'][$year] ?? 0;
                                            }
                                        @endphp
                                        <td style="text-align: center; font-weight: 700; font-size: 14px; color: {{ $primaryColor }};">
                                            {{ number_format($yearTotal) }}
                                        </td>
                                    @endfor
                                    <td style="text-align: center; font-weight: 700; font-size: 15px; background: {{ $primaryColor }}; color: white;">
                                        {{ number_format($grandTotal) }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
