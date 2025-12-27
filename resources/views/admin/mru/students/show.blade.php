{{-- Student Detail Page - Simple Sections Layout --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* Remove all rounded corners */
    .card, .btn, .badge, img, .img-thumbnail, .table {
        border-radius: 0 !important;
    }
    
    .info-label {
        font-weight: 600;
        color: #666;
        margin-bottom: 6px;
    }
    .info-value {
        color: #333;
        margin-bottom: 18px;
    }
    .section-card {
        margin-bottom: 20px;
        border: 1px solid #ddd;
    }
    .section-header {
        background: #f5f5f5;
        padding: 15px 20px;
        border-bottom: 2px solid #007bff;
        margin: 0;
    }
    .section-header h5 {
        margin: 0;
        font-size: 16px;
    }
    .section-body {
        padding: 20px;
    }
    .card {
        border: 1px solid #ddd;
        box-shadow: none !important;
    }
    .card-body {
        padding: 15px 20px;
    }
    .table {
        margin-bottom: 0;
    }
</style>

<div class="container-fluid">
    
    {{-- Header Section --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="{{ asset('storage/photos/default-avatar.png') }}" 
                                 class="img-thumbnail" 
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 alt="Student Photo">
                        </div>
                        <div class="col-md-8">
                            <h3 class="mb-2">{{ $student->full_name ?? 'N/A' }}</h3>
                            <p class="text-muted mb-2">
                                <strong>Reg No:</strong> {{ $student->regno ?? 'N/A' }} | 
                                <strong>Entry No:</strong> {{ $student->entryno ?? 'N/A' }}
                            </p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary">{{ $student->progid ?? 'N/A' }}</span>
                                <span class="badge bg-info">{{ $student->studsesion ?? 'N/A' }}</span>
                                <span class="badge bg-secondary">{{ $student->entryyear ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ $transcriptUrl ?? '#' }}" target="_blank" class="btn btn-sm btn-danger mb-2" style="width: 100%;">
                                <i class="fa fa-file-pdf-o"></i> Download Transcript
                            </a>
                            <a href="{{ admin_url('mru-students') }}" class="btn btn-sm btn-outline-secondary" style="width: 100%;">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Personal & Contact Information Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-user"></i> Personal & Contact Information</h5>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">First Name</div>
                    <div class="info-value">{{ $student->firstname ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Other Names</div>
                    <div class="info-value">{{ $student->othername ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Gender</div>
                    <div class="info-value">{{ $student->gender ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">{{ $student->dob ? date('d M Y', strtotime($student->dob)) : '-' }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">Nationality</div>
                    <div class="info-value">{{ $student->nationality ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Religion</div>
                    <div class="info-value">{{ $student->religion ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Home District</div>
                    <div class="info-value">{{ $student->home_dist ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Campus</div>
                    <div class="info-value">{{ $student->campus ?? '-' }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->email ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $student->studPhone ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Academic Information Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-graduation-cap"></i> Academic Information</h5>
        </div>
        <div class="section-body">
            
            {{-- Programme & Faculty Details --}}
            <div style="margin-bottom: 20px;">
                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #dee2e6; padding-bottom: 8px;">
                    <i class="fa fa-certificate"></i> Programme & Faculty Details
                </h6>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Programme Code</div>
                        <div class="info-value"><strong>{{ $student->progid ?? '-' }}</strong></div>
                    </div>
                    <div class="col-md-5 col-sm-6">
                        <div class="info-label">Programme Name</div>
                        <div class="info-value">
                            @if($student->programme)
                                <strong>{{ $student->programme->progname }}</strong>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Level</div>
                        <div class="info-value">
                            @if($student->programme && $student->programme->levelCode)
                                @php
                                    $levelLabels = [1 => 'Certificate', 2 => 'Diploma', 3 => 'Degree', 4 => 'Masters', 5 => 'PhD'];
                                @endphp
                                <span class="badge bg-primary">{{ $levelLabels[$student->programme->levelCode] ?? 'Level ' . $student->programme->levelCode }}</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Study System</div>
                        <div class="info-value">{{ $student->programme->study_system ?? '-' }}</div>
                    </div>
                </div>
                
                {{-- Show Specialisation prominently if it exists --}}
                @if($student->specialisationDetails && $student->specialisationDetails->spec)
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info" style="border-radius: 0; border-left: 4px solid #0dcaf0; background-color: #e7f6ff; padding: 12px 15px; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <strong style="font-size: 14px; text-transform: uppercase; color: #0d6efd;">
                                        <i class="fa fa-star"></i> Teaching Subject(s) / Area of Specialization:
                                    </strong>
                                    <div style="font-size: 18px; font-weight: bold; color: #333; margin-top: 5px;">
                                        {{ $student->specialisationDetails->spec }}
                                    </div>
                                </div>
                                <div>
                                    @if($student->specialisationDetails->abbrev)
                                        <span class="badge bg-info" style="font-size: 13px; padding: 8px 12px;">{{ $student->specialisationDetails->abbrev }}</span>
                                    @endif
                                    @if(stripos($student->progid, 'BED') !== false || stripos($student->progid, 'ED') !== false || stripos($student->programme->progname ?? '', 'Education') !== false)
                                        <span class="badge bg-success" style="font-size: 13px; padding: 8px 12px; margin-left: 5px;">Education Programme</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Duration</div>
                        <div class="info-value">{{ $student->programme->couselength ?? $student->duration ?? '-' }} Year(s)</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Max Duration</div>
                        <div class="info-value">{{ $student->programme->maxduration ?? '-' }} Year(s)</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Min Credits Required</div>
                        <div class="info-value">{{ $student->programme->mincredit ?? '-' }} CUs</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Abbreviation</div>
                        <div class="info-value">{{ $student->programme->abbrev ?? '-' }}</div>
                    </div>
                </div>
                <hr style="margin: 15px 0; border-top: 1px dashed #dee2e6;">
                <div class="row">
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Faculty Code</div>
                        <div class="info-value">{{ $student->programme->faculty_code ?? '-' }}</div>
                    </div>
                    <div class="col-md-5 col-sm-6">
                        <div class="info-label">Faculty Name</div>
                        <div class="info-value">
                            @if($student->programme && $student->programme->faculty)
                                <strong>{{ $student->programme->faculty->faculty_name }}</strong>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Faculty Abbrev.</div>
                        <div class="info-value">{{ $student->programme->faculty->abbrev ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Faculty Dean</div>
                        <div class="info-value">{{ $student->programme->faculty->faculty_dean ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Enrollment & Study Details --}}
            <div style="margin-bottom: 20px;">
                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #dee2e6; padding-bottom: 8px;">
                    <i class="fa fa-calendar"></i> Enrollment & Study Details
                </h6>
                <div class="row">
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Entry Year</div>
                        <div class="info-value"><strong>{{ $student->entryyear ?? '-' }}</strong></div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Current Year</div>
                        <div class="info-value"><span class="badge bg-info">Year {{ $student->current_year_of_study }}</span></div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Expected Graduation</div>
                        <div class="info-value"><strong>{{ $student->expected_graduation_year ?? '-' }}</strong></div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Years Since Entry</div>
                        <div class="info-value">{{ $student->entryyear ? (date('Y') - $student->entryyear) : '-' }} Year(s)</div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Intake</div>
                        <div class="info-value">{{ $student->intake ?? '-' }}</div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Status</div>
                        <div class="info-value"><span class="badge bg-success">Active</span></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Study Session</div>
                        <div class="info-value">
                            @if($student->studsesion)
                                @php
                                    $sessionColors = ['DAY' => 'success', 'WEEKEND' => 'info', 'EVENING' => 'warning', 'INSERVICE' => 'secondary', 'Full Time' => 'primary'];
                                    $color = $sessionColors[$student->studsesion] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $student->studsesion }}</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Entry Method</div>
                        <div class="info-value">{{ $student->entrymethod ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Hall of Residence</div>
                        <div class="info-value">{{ $student->StudentHall ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Campus Location</div>
                        <div class="info-value">
                            @php
                                $campuses = [1 => 'Main Campus', 2 => 'Mbale Campus', 3 => 'Arua Campus', 4 => 'Kabale Campus', 5 => 'Fort Portal Campus'];
                            @endphp
                            {{ $campuses[$student->studCampus] ?? ($student->studCampus ?? '-') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Settings --}}
            <div>
                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #dee2e6; padding-bottom: 8px;">
                    <i class="fa fa-cog"></i> Academic Settings
                </h6>
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="info-label">Grading System ID</div>
                        <div class="info-value">{{ $student->gradSystemID ?? '-' }}</div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="info-label">Campus</div>
                        <div class="info-value">
                            @php
                                $campuses = [1 => 'Main Campus', 2 => 'Mbale Campus', 3 => 'Arua Campus', 4 => 'Kabale Campus', 5 => 'Fort Portal Campus'];
                            @endphp
                            {{ $campuses[$student->studCampus] ?? ($student->studCampus ?? '-') }}
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="info-label">Billing ID</div>
                        <div class="info-value">{{ $student->billingID ?? '-' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Academic Records by Semester Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-graduation-cap"></i> Academic Records by Semester</h5>
        </div>
        <div class="section-body">
            @php
                // Group all data by academic year and semester
                $groupedData = collect();
                
                // Combine all academic records
                $allRecords = collect();
                
                // Add registrations
                foreach($student->courseRegistrations as $reg) {
                    $key = ($reg->acad_year ?? 'Unknown') . '-' . ($reg->semester ?? 0);
                    $allRecords->push([
                        'year' => $reg->acad_year ?? 'Unknown',
                        'semester' => $reg->semester ?? 0,
                        'key' => $key,
                        'type' => 'registration',
                        'data' => $reg
                    ]);
                }
                
                // Add coursework marks
                foreach($student->courseworkMarks as $mark) {
                    if($mark->settings) {
                        $key = ($mark->settings->acadyear ?? 'Unknown') . '-' . ($mark->settings->semester ?? 0);
                        $allRecords->push([
                            'year' => $mark->settings->acadyear ?? 'Unknown',
                            'semester' => $mark->settings->semester ?? 0,
                            'key' => $key,
                            'type' => 'coursework',
                            'data' => $mark
                        ]);
                    }
                }
                
                // Add results
                foreach($student->results as $result) {
                    $key = ($result->acad ?? 'Unknown') . '-' . ($result->semester ?? 0);
                    $allRecords->push([
                        'year' => $result->acad ?? 'Unknown',
                        'semester' => $result->semester ?? 0,
                        'key' => $key,
                        'type' => 'result',
                        'data' => $result
                    ]);
                }
                
                // Group by year-semester key
                $groupedData = $allRecords->groupBy('key');
                
                // Sort keys (year-semester) in descending order
                $sortedKeys = $groupedData->keys()->sortByDesc(function($key) {
                    $parts = explode('-', $key);
                    $year = $parts[0] ?? '0000';
                    $sem = (int)($parts[1] ?? 0);
                    // Create sortable string: year then semester
                    return $year . '-' . str_pad($sem, 2, '0', STR_PAD_LEFT);
                });
            @endphp

            @if($sortedKeys->count() > 0)
                @foreach($sortedKeys as $semesterKey)
                    @php
                        $records = $groupedData[$semesterKey];
                        $firstRecord = $records->first();
                        $year = $firstRecord['year'];
                        $semester = $firstRecord['semester'];
                        
                        // Get data by type
                        $registrations = $records->where('type', 'registration')->pluck('data');
                        $courseworks = $records->where('type', 'coursework')->pluck('data');
                        $results = $records->where('type', 'result')->pluck('data');
                        
                        // Calculate semester stats
                        $semesterGPA = $results->avg('gpa');
                        $semesterCredits = $results->sum('CreditUnits');
                    @endphp
                    
                    <div class="card mb-3" style="border: 1px solid #dee2e6;">
                        <div class="card-header" style="background: #e9ecef; padding: 12px 20px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <strong>{{ $year }}</strong> - Semester {{ $semester }}
                                </h6>
                                <div>
                                    @if($semesterGPA)
                                        <span class="badge bg-success">GPA: {{ number_format($semesterGPA, 2) }}</span>
                                    @endif
                                    @if($semesterCredits)
                                        <span class="badge bg-info">{{ $semesterCredits }} Credits</span>
                                    @endif
                                    <span class="badge bg-secondary">{{ $registrations->count() }} Courses</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 15px;">
                            
                            {{-- Course Registration for this semester --}}
                            @if($registrations->count() > 0)
                            <h6 class="mt-2 mb-2"><i class="fa fa-book"></i> Course Registration</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Status</th>
                                            <th>Session</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registrations as $registration)
                                        <tr>
                                            <td><strong>{{ $registration->courseID ?? '-' }}</strong></td>
                                            <td>{{ $registration->course->courseName ?? '-' }}</td>
                                            <td>
                                                @if($registration->course_status == 'REGULAR')
                                                    <span class="badge bg-success">{{ $registration->course_status }}</span>
                                                @elseif($registration->course_status == 'RETAKE')
                                                    <span class="badge bg-warning">{{ $registration->course_status }}</span>
                                                @else
                                                    <span class="badge bg-info">{{ $registration->course_status ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $registration->stud_session ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            {{-- Coursework Marks for this semester --}}
                            @if($courseworks->count() > 0)
                            <h6 class="mt-3 mb-2"><i class="fa fa-edit"></i> Coursework Marks</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Assignment</th>
                                            <th>Test</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($courseworks as $mark)
                                        <tr>
                                            <td><strong>{{ $mark->settings->courseID ?? '-' }}</strong></td>
                                            <td>{{ $mark->settings->course->courseName ?? '-' }}</td>
                                            <td>{{ $mark->total_assignments ?? 0 }}</td>
                                            <td>{{ $mark->total_tests ?? 0 }}</td>
                                            <td><strong>{{ $mark->final_score ?? 0 }}</strong></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            {{-- Academic Results for this semester --}}
                            @if($results->count() > 0)
                            <h6 class="mt-3 mb-2"><i class="fa fa-trophy"></i> Final Results</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Credits</th>
                                            <th>Score</th>
                                            <th>Grade</th>
                                            <th>Points</th>
                                            <th>GPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $result)
                                        <tr>
                                            <td><strong>{{ $result->courseid ?? '-' }}</strong></td>
                                            <td>{{ $result->course->courseName ?? '-' }}</td>
                                            <td>{{ $result->CreditUnits ?? '-' }}</td>
                                            <td>{{ $result->score ?? '-' }}</td>
                                            <td>
                                                <strong 
                                                    @if(in_array($result->grade, ['A', 'B+', 'B']))
                                                        class="text-success"
                                                    @elseif(in_array($result->grade, ['C+', 'C']))
                                                        class="text-warning"
                                                    @elseif(in_array($result->grade, ['D+', 'D', 'F']))
                                                        class="text-danger"
                                                    @endif
                                                >
                                                    {{ $result->grade ?? '-' }}
                                                </strong>
                                            </td>
                                            <td>{{ $result->gradept ?? '-' }}</td>
                                            <td><strong>{{ number_format($result->gpa ?? 0, 2) }}</strong></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            @if($registrations->count() == 0 && $courseworks->count() == 0 && $results->count() == 0)
                                <p class="text-muted text-center">No records available for this semester</p>
                            @endif

                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info">No academic records available</div>
            @endif
        </div>
    </div>

    {{-- Practical Exam Marks Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-flask"></i> Practical Exam Marks</h5>
        </div>
        <div class="section-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Practical Mark</th>
                            <th>Max Mark</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->practicalExamMarks as $practical)
                        <tr>
                            <td>{{ $practical->settings->courseID ?? '-' }}</td>
                            <td>{{ $practical->settings->course->courseName ?? '-' }}</td>
                            <td>{{ $practical->settings->acadyear ?? '-' }}</td>
                            <td>{{ $practical->settings->semester ?? '-' }}</td>
                            <td>{{ $practical->final_score ?? 0 }}</td>
                            <td>{{ $practical->settings->total_mark ?? 100 }}</td>
                            <td>{{ $practical->final_score > 0 ? number_format(($practical->final_score / ($practical->settings->total_mark ?? 100)) * 100, 1) : 0 }}%</td>
                            <td>
                                @if($practical->final_score >= ($practical->settings->total_mark ?? 100) * 0.5)
                                    <span class="badge bg-success">Pass</span>
                                @else
                                    <span class="badge bg-danger">Fail</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No practical exam marks available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Academic Progress Summary Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-line-chart"></i> Academic Progress Summary</h5>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">Cumulative GPA</div>
                    <div class="info-value"><span class="badge bg-success" style="font-size: 16px;">{{ number_format($student->cumulative_gpa, 2) }}</span></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Total Credits Earned</div>
                    <div class="info-value"><strong>{{ $student->total_credits_earned }} / {{ $student->duration * 30 }}</strong> CUs</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Current Year of Study</div>
                    <div class="info-value">Year {{ $student->current_year_of_study }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Expected Graduation</div>
                    <div class="info-value">{{ $student->expected_graduation_year ?? '-' }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">Academic Standing</div>
                    <div class="info-value">
                        @php
                            $standing = $student->academic_standing;
                            $badgeClass = $standing == 'Dean\'s List' ? 'success' : ($standing == 'Good Standing' ? 'info' : ($standing == 'Probation' ? 'warning' : 'secondary'));
                        @endphp
                        <span class="badge bg-{{ $badgeClass }}">{{ $standing }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Completion Progress</div>
                    <div class="info-value">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $student->completion_percentage }}%;">{{ $student->completion_percentage }}%</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Courses Completed</div>
                    <div class="info-value"><strong>{{ $student->results->where('grade', '!=', 'F')->count() }} / {{ $student->results->count() }}</strong></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Remaining Credits</div>
                    <div class="info-value"><strong>{{ max(0, ($student->duration * 30) - $student->total_credits_earned) }}</strong> CUs</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Semester-wise GPA Summary Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-bar-chart"></i> Semester-wise GPA Summary</h5>
        </div>
        <div class="section-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Courses Taken</th>
                            <th>Credits Earned</th>
                            <th>Semester GPA</th>
                            <th>Cumulative GPA</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($semesterGpaSummary as $index => $semester)
                        <tr>
                            <td>{{ $semester['acad'] ?? '-' }}</td>
                            <td>Semester {{ $semester['semester'] ?? '-' }}</td>
                            <td>{{ $semester['courses_taken'] ?? 0 }}</td>
                            <td>{{ $semester['credits_earned'] ?? 0 }}</td>
                            <td><strong>{{ number_format($semester['semester_gpa'] ?? 0, 2) }}</strong></td>
                            <td><strong>{{ number_format($student->cumulative_gpa, 2) }}</strong></td>
                            <td>
                                @if($semester['semester_gpa'] >= 3.0)
                                    <span class="badge bg-success">Pass</span>
                                @elseif($semester['semester_gpa'] >= 2.0)
                                    <span class="badge bg-warning">Probation</span>
                                @else
                                    <span class="badge bg-danger">Fail</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No semester GPA data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Retakes & Supplementary Exams Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-repeat"></i> Retakes & Supplementary Exams</h5>
        </div>
        <div class="section-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Original Attempt</th>
                            <th>Original Score</th>
                            <th>Retake Attempts</th>
                            <th>Best Score</th>
                            <th>Current Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($retakes as $retake)
                        <tr>
                            <td>{{ $retake->courseid ?? '-' }}</td>
                            <td>{{ $retake->course->courseName ?? '-' }}</td>
                            <td>{{ $retake->acad ?? '-' }} Sem {{ $retake->semester ?? '-' }}</td>
                            <td>{{ $retake->score ?? 0 }}</td>
                            <td>1</td>
                            <td>{{ $retake->score ?? 0 }}</td>
                            <td>
                                @if($retake->grade != 'F')
                                    <span class="badge bg-success">PASS</span>
                                @else
                                    <span class="badge bg-danger">FAIL</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No retake records available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Programme Requirements Progress Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-tasks"></i> Programme Requirements Progress</h5>
        </div>
        <div class="section-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="mb-2">Core Courses</h6>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%;">0 / 0 (0%)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="mb-2">Elective Courses</h6>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 0%;">0 / 0 (0%)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="mb-2">Overall Progress</h6>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;">0 / 0 CUs (0%)</div>
                    </div>
                </div>
            </div>
            
            <hr style="margin: 20px 0;">
            
            <h6 class="mb-2">Outstanding Courses</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No outstanding courses</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Exam Settings & Mark Distribution Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-cog"></i> Exam Settings & Mark Distribution</h5>
        </div>
        <div class="section-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Academic Year</th>
                            <th>Coursework %</th>
                            <th>Final Exam %</th>
                            <th>Practical %</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No exam settings available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Financial Summary Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-money"></i> Financial Summary</h5>
        </div>
        <div class="section-body">
            <div class="row text-center mb-3">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body" style="padding: 20px;">
                            <h6 class="mb-2">Total Fees</h6>
                            <h3 class="mb-0">UGX 0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body" style="padding: 20px;">
                            <h6 class="mb-2">Amount Paid</h6>
                            <h3 class="mb-0">UGX 0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body" style="padding: 20px;">
                            <h6 class="mb-2">Balance</h6>
                            <h3 class="mb-0">UGX 0</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <h6 class="mb-3">Payment History</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Receipt No</th>
                            <th>Description</th>
                            <th>Amount (UGX)</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No payment records available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Documents Section --}}
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-folder"></i> Documents</h5>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="mb-2">Student Photo</h6>
                    <div class="text-center">
                        <img src="{{ asset('storage/photos/default-avatar.png') }}" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 250px; border: 1px solid #ddd;"
                             alt="Student Photo">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="mb-2">Signature</h6>
                    <div class="text-center">
                        <img src="{{ asset('storage/signatures/default-signature.png') }}" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 100px; border: 1px solid #ddd;"
                             alt="Student Signature">
                    </div>
                </div>
            </div>
            
            <hr style="margin: 20px 0;">
            
            <h6 class="mb-2">Other Documents</h6>
            <div class="list-group">
                <div class="list-group-item text-center text-muted">
                    No additional documents available
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>