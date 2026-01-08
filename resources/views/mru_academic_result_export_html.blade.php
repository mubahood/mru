<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $export->export_name }} - Academic Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .export-container {
            background: white;
            padding: 15px;
            margin: 10px auto;
            max-width: 100%;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        .header-section {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-logo {
            max-height: 60px;
            max-width: 120px;
        }
        
        .header-info {
            flex: 1;
            text-align: center;
            margin: 0 15px;
        }
        
        .header-info h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 3px 0;
            color: #333;
        }
        
        .header-info h2 {
            font-size: 13px;
            margin: 3px 0;
            color: #666;
            text-decoration: underline;
        }
        
        .header-info p {
            font-size: 9px;
            margin: 2px 0;
            color: #666;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 8px 10px;
            margin-bottom: 12px;
            border: 1px solid #dee2e6;
        }
        
        .info-section p {
            margin: 3px 0;
            font-size: 10px;
        }
        
        .specialization-section {
            margin-bottom: 20px;
        }
        
        .spec-header {
            background: #0d6efd;
            color: white;
            padding: 5px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #0d6efd;
            font-weight: 600;
        }
        
        .spec-header h3 {
            margin: 0;
            font-size: 11px;
            font-weight: 600;
        }
        
        .spec-header .badge {
            font-size: 9px;
            background: rgba(255,255,255,0.3);
            padding: 2px 6px;
            border: 1px solid rgba(255,255,255,0.5);
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 9px;
        }
        
        .results-table thead {
            background: #0d6efd;
            color: white;
        }
        
        .results-table th {
            padding: 5px 4px;
            text-align: center;
            font-weight: 600;
            font-size: 9px;
            border: 1px solid #000;
        }
        
        .results-table td {
            padding: 4px 3px;
            border: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
        }
        
        .results-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .regno-col {
            width: 80px;
            font-weight: 600;
            font-size: 9px;
        }
        
        .name-col {
            width: 150px;
            text-align: left !important;
            font-size: 9px;
        }
        
        .name-col a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }
        
        .name-col a:hover {
            text-decoration: underline;
        }
        
        .status-col {
            width: 60px;
            font-weight: bold;
            font-size: 9px;
        }
        
        /* Status Colors (same as PDF/Excel) */
        .status-pass {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-fail {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-incomplete {
            background-color: #fff3cd;
            color: #856404;
        }
        
        /* Grade Colors */
        .grade-pass {
            color: #198754;
            font-weight: 600;
        }
        
        .grade-fail {
            color: #dc3545;
            font-weight: 600;
        }
        
        .grade-empty {
            color: #6c757d;
        }
        
        .course-header {
            writing-mode: horizontal-tb;
            max-width: 60px;
            word-wrap: break-word;
        }
        
        .course-name-row th {
            font-size: 8px;
            font-weight: normal;
            background: #0d6efd;
            padding: 3px 2px;
            opacity: 0.9;
        }
        
        .action-buttons {
            margin-bottom: 10px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .action-buttons .btn {
            font-size: 11px;
            padding: 4px 10px;
        }
        
        .status-legend {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 6px;
            font-size: 9px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .legend-box {
            width: 15px;
            height: 15px;
        }
        
        @media print {
            .action-buttons, .no-print {
                display: none !important;
            }
            
            body {
                background: white;
            }
            
            .export-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            
            .results-table {
                font-size: 8px;
            }
            
            .results-table th,
            .results-table td {
                padding: 2px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ admin_url('mru-academic-result-exports') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Exports
            </a>
        </div>

        <div class="export-container">
            <!-- Header -->
            <div class="header-section">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" class="header-logo">
                @endif
                
                <div class="header-info">
                    @if($enterprise)
                        <h1>{{ $enterprise->name ?? 'Enterprise Name' }}</h1>
                        <p style="margin: 5px 0;">{{ $enterprise->address ?? '' }}</p>
                        <p style="margin: 5px 0;">
                            @if($enterprise->phone) Tel: {{ $enterprise->phone }} @endif
                            @if($enterprise->email) | Email: {{ $enterprise->email }} @endif
                        </p>
                    @endif
                    <h2>{{ $export->export_name }}</h2>
                </div>
                
                @if($logoPath)
                    <div style="width: 150px;"></div> <!-- Spacer for symmetry -->
                @endif
            </div>

            <!-- Info Section -->
            <div class="info-section">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Programme:</strong> {{ $export->programme->programme ?? 'N/A' }}</p>
                        <p><strong>Academic Year:</strong> {{ $export->academic_year }}</p>
                        <p><strong>Semester:</strong> {{ $export->semester }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Year of Study:</strong> Year {{ $export->study_year }}</p>
                        @if(($export->minimum_passes_required ?? 0) > 0)
                            <p><strong>Minimum Passes Required:</strong> {{ $export->minimum_passes_required }} subjects</p>
                        @endif
                        <p><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                
                <!-- Status Legend -->
                <div class="status-legend">
                    <div class="legend-item">
                        <div class="legend-box status-pass"></div>
                        <span>PASS</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box status-fail"></div>
                        <span>FAIL</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box status-incomplete"></div>
                        <span>INCOMPLETE</span>
                    </div>
                </div>
            </div>

            <!-- Results by Specialization -->
            @foreach($specializationData as $specData)
                <div class="specialization-section">
                    <div class="spec-header">
                        <h3>{{ $specData['spec_name'] }}</h3>
                        <span class="badge bg-light text-dark">
                            {{ $specData['student_count'] }} {{ Str::plural('Student', $specData['student_count']) }}
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="results-table">
                            <thead>
                                <!-- Row 1: Course IDs and Status -->
                                <tr>
                                    <th rowspan="2" class="regno-col">REG NO</th>
                                    <th rowspan="2" class="name-col">STUDENT NAME</th>
                                    <th rowspan="2" class="status-col">STATUS</th>
                                    <th rowspan="2" class="status-col"># COURSES</th>
                                    @foreach($specData['courses'] as $course)
                                        <th class="course-header">{{ $course->courseID }}</th>
                                    @endforeach
                                </tr>
                                <!-- Row 2: Course Names -->
                                <tr class="course-name-row">
                                    @foreach($specData['courses'] as $course)
                                        <th title="{{ $course->courseName }}">
                                            {{ Str::limit($course->courseName, 30) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($specData['students'] as $student)
                                    <tr>
                                        <!-- Registration Number -->
                                        <td class="regno-col">{{ $student->regno }}</td>
                                        
                                        <!-- Student Name (Clickable) -->
                                        <td class="name-col">
                                            <a href="{{ admin_url('mru-students/' . $student->ID) }}" 
                                               target="_blank"
                                               title="View {{ $student->firstname }} {{ $student->othername }}'s profile"
                                               data-bs-toggle="tooltip">
                                                {{ $student->firstname }} {{ $student->othername }}
                                            </a>
                                            <br>
                                            <a href="{{ admin_url('mru-students/' . $student->ID . '/transcript') }}" 
                                               target="_blank"
                                               class="btn btn-xs btn-danger no-print"
                                               style="font-size: 7px; padding: 2px 5px; margin-top: 2px; background: #dc3545; border: none;"
                                               title="Download Academic Transcript PDF">
                                                <i class="bi bi-file-earmark-pdf"></i> TRANSCRIPT
                                            </a>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td class="status-col {{ $student->statusClass }}"
                                            title="Passed: {{ $student->coursesPassed }}/{{ $student->totalCourses }}"
                                            data-bs-toggle="tooltip">
                                            {{ $student->status }}
                                        </td>
                                        
                                        <!-- Course Count -->
                                        <td class="status-col" style="font-weight: 600;">
                                            {{ $student->coursesWithResults }}
                                        </td>
                                        
                                        <!-- Course Results -->
                                        @foreach($specData['courses'] as $course)
                                            @php
                                                $result = App\Services\MruAcademicResultHtmlService::getStudentCourseResult(
                                                    $specData['results'], 
                                                    $student->regno, 
                                                    $course->courseID
                                                );
                                            @endphp
                                            <td class="{{ $result['class'] }}"
                                                title="{{ $course->courseName }}: {{ $result['grade'] }}"
                                                data-bs-toggle="tooltip">
                                                {{ $result['grade'] }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @if(count($specializationData) === 0)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No results found for the specified criteria.
                </div>
            @endif

            <!-- Students with Incomplete Marks Summary -->
            @if(!empty($incompleteStudents) && count($incompleteStudents) > 0)
                <div class="card shadow-sm mb-3" style="margin-top: 30px;">
                    <div class="card-header" style="background-color: #d32f2f; color: white; padding: 12px;">
                        <h5 class="mb-0" style="font-size: 14px;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            STUDENTS WITH INCOMPLETE MARKS
                        </h5>
                        <small style="font-size: 10px;">
                            Students who have submitted some results but are missing marks for certain courses
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="alert alert-info m-3 mb-2" style="font-size: 10px;">
                            <i class="bi bi-info-circle"></i>
                            <strong>Total students with incomplete marks: {{ count($incompleteStudents) }}</strong>
                            <br>These students need to submit marks for the missing courses to complete their academic record.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 9px;">
                                <thead style="background-color: #1a5490; color: white; position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="width: 40px; text-align: center;">No.</th>
                                        <th style="width: 100px;">Reg No</th>
                                        <th style="width: 180px;">Student Name</th>
                                        <th style="width: 150px;">Specialization</th>
                                        <th style="width: 80px; text-align: center;">Total Courses</th>
                                        <th style="width: 80px; text-align: center;">Marks Obtained</th>
                                        <th style="width: 80px; text-align: center;">Marks Missing</th>
                                        <th>Missing Courses</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incompleteStudents as $index => $student)
                                        <tr style="{{ $index % 2 == 0 ? 'background-color: #ffffff;' : 'background-color: #f8f9fa;' }}">
                                            <td style="text-align: center;">{{ $index + 1 }}</td>
                                            <td><strong>{{ $student['regno'] }}</strong></td>
                                            <td>{{ $student['name'] }}</td>
                                            <td><small>{{ $student['specialization'] }}</small></td>
                                            <td style="text-align: center; font-weight: bold;">{{ $student['total_courses'] }}</td>
                                            <td style="text-align: center; font-weight: bold; color: #2e7d32;">
                                                {{ $student['marks_obtained'] }}
                                            </td>
                                            <td style="text-align: center; font-weight: bold; color: #d32f2f;">
                                                {{ $student['marks_missing_count'] }}
                                            </td>
                                            <td style="font-size: 8px;">
                                                <span class="badge bg-danger" style="font-size: 7px;">
                                                    {{ $student['missing_courses'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-warning m-3 mt-2" style="font-size: 9px;">
                            <i class="bi bi-lightbulb"></i>
                            <strong>Action Required:</strong> Contact the listed students to submit their missing course results.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>
