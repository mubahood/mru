
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
    
    
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="<?php echo e(asset('storage/photos/default-avatar.png'), false); ?>" 
                                 class="img-thumbnail" 
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 alt="Student Photo">
                        </div>
                        <div class="col-md-8">
                            <h3 class="mb-2"><?php echo e($student->full_name ?? 'N/A', false); ?></h3>
                            <p class="text-muted mb-2">
                                <strong>Reg No:</strong> <?php echo e($student->regno ?? 'N/A', false); ?> | 
                                <strong>Entry No:</strong> <?php echo e($student->entryno ?? 'N/A', false); ?>

                            </p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary"><?php echo e($student->progid ?? 'N/A', false); ?></span>
                                <span class="badge bg-info"><?php echo e($student->studsesion ?? 'N/A', false); ?></span>
                                <span class="badge bg-secondary"><?php echo e($student->entryyear ?? 'N/A', false); ?></span>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="<?php echo e(admin_url('mru-students'), false); ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-user"></i> Personal & Contact Information</h5>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">First Name</div>
                    <div class="info-value"><?php echo e($student->firstname ?? '-', false); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Other Names</div>
                    <div class="info-value"><?php echo e($student->othername ?? '-', false); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Gender</div>
                    <div class="info-value"><?php echo e($student->gender ?? '-', false); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value"><?php echo e($student->dob ? date('d M Y', strtotime($student->dob)) : '-', false); ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">Nationality</div>
                    <div class="info-value"><?php echo e($student->nationality ?? '-', false); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Religion</div>
                    <div class="info-value"><?php echo e($student->religion ?? '-', false); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Home District</div>
                    <div class="info-value"><?php echo e($student->home_dist ?? '-', false); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Campus</div>
                    <div class="info-value"><?php echo e($student->campus ?? '-', false); ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?php echo e($student->email ?? '-', false); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?php echo e($student->studPhone ?? '-', false); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-graduation-cap"></i> Academic Information</h5>
        </div>
        <div class="section-body">
            
            
            <div style="margin-bottom: 20px;">
                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #dee2e6; padding-bottom: 8px;">
                    <i class="fa fa-certificate"></i> Programme & Faculty Details
                </h6>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Programme Code</div>
                        <div class="info-value"><strong><?php echo e($student->progid ?? '-', false); ?></strong></div>
                    </div>
                    <div class="col-md-5 col-sm-6">
                        <div class="info-label">Programme Name</div>
                        <div class="info-value">
                            <?php if($student->programme): ?>
                                <strong><?php echo e($student->programme->progname, false); ?></strong>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Level</div>
                        <div class="info-value">
                            <?php if($student->programme && $student->programme->levelCode): ?>
                                <?php
                                    $levelLabels = [1 => 'Certificate', 2 => 'Diploma', 3 => 'Degree', 4 => 'Masters', 5 => 'PhD'];
                                ?>
                                <span class="badge bg-primary"><?php echo e($levelLabels[$student->programme->levelCode] ?? 'Level ' . $student->programme->levelCode, false); ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Study System</div>
                        <div class="info-value"><?php echo e($student->programme->study_system ?? '-', false); ?></div>
                    </div>
                </div>
                
                
                <?php if($student->specialisationDetails && $student->specialisationDetails->spec): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info" style="border-radius: 0; border-left: 4px solid #0dcaf0; background-color: #e7f6ff; padding: 12px 15px; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <strong style="font-size: 14px; text-transform: uppercase; color: #0d6efd;">
                                        <i class="fa fa-star"></i> Teaching Subject(s) / Area of Specialization:
                                    </strong>
                                    <div style="font-size: 18px; font-weight: bold; color: #333; margin-top: 5px;">
                                        <?php echo e($student->specialisationDetails->spec, false); ?>

                                    </div>
                                </div>
                                <div>
                                    <?php if($student->specialisationDetails->abbrev): ?>
                                        <span class="badge bg-info" style="font-size: 13px; padding: 8px 12px;"><?php echo e($student->specialisationDetails->abbrev, false); ?></span>
                                    <?php endif; ?>
                                    <?php if(stripos($student->progid, 'BED') !== false || stripos($student->progid, 'ED') !== false || stripos($student->programme->progname ?? '', 'Education') !== false): ?>
                                        <span class="badge bg-success" style="font-size: 13px; padding: 8px 12px; margin-left: 5px;">Education Programme</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Duration</div>
                        <div class="info-value"><?php echo e($student->programme->couselength ?? $student->duration ?? '-', false); ?> Year(s)</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Max Duration</div>
                        <div class="info-value"><?php echo e($student->programme->maxduration ?? '-', false); ?> Year(s)</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Min Credits Required</div>
                        <div class="info-value"><?php echo e($student->programme->mincredit ?? '-', false); ?> CUs</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Abbreviation</div>
                        <div class="info-value"><?php echo e($student->programme->abbrev ?? '-', false); ?></div>
                    </div>
                </div>
                <hr style="margin: 15px 0; border-top: 1px dashed #dee2e6;">
                <div class="row">
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Faculty Code</div>
                        <div class="info-value"><?php echo e($student->programme->faculty_code ?? '-', false); ?></div>
                    </div>
                    <div class="col-md-5 col-sm-6">
                        <div class="info-label">Faculty Name</div>
                        <div class="info-value">
                            <?php if($student->programme && $student->programme->faculty): ?>
                                <strong><?php echo e($student->programme->faculty->faculty_name, false); ?></strong>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Faculty Abbrev.</div>
                        <div class="info-value"><?php echo e($student->programme->faculty->abbrev ?? '-', false); ?></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Faculty Dean</div>
                        <div class="info-value"><?php echo e($student->programme->faculty->faculty_dean ?? '-', false); ?></div>
                    </div>
                </div>
            </div>

            
            <div style="margin-bottom: 20px;">
                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #dee2e6; padding-bottom: 8px;">
                    <i class="fa fa-calendar"></i> Enrollment & Study Details
                </h6>
                <div class="row">
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Entry Year</div>
                        <div class="info-value"><strong><?php echo e($student->entryyear ?? '-', false); ?></strong></div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Current Year</div>
                        <div class="info-value"><span class="badge bg-info">Year <?php echo e($student->current_year_of_study, false); ?></span></div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Expected Graduation</div>
                        <div class="info-value"><strong><?php echo e($student->expected_graduation_year ?? '-', false); ?></strong></div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Years Since Entry</div>
                        <div class="info-value"><?php echo e($student->entryyear ? (date('Y') - $student->entryyear) : '-', false); ?> Year(s)</div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="info-label">Intake</div>
                        <div class="info-value"><?php echo e($student->intake ?? '-', false); ?></div>
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
                            <?php if($student->studsesion): ?>
                                <?php
                                    $sessionColors = ['DAY' => 'success', 'WEEKEND' => 'info', 'EVENING' => 'warning', 'INSERVICE' => 'secondary', 'Full Time' => 'primary'];
                                    $color = $sessionColors[$student->studsesion] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo e($color, false); ?>"><?php echo e($student->studsesion, false); ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Entry Method</div>
                        <div class="info-value"><?php echo e($student->entrymethod ?? '-', false); ?></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Hall of Residence</div>
                        <div class="info-value"><?php echo e($student->StudentHall ?? '-', false); ?></div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-label">Campus Location</div>
                        <div class="info-value">
                            <?php
                                $campuses = [1 => 'Main Campus', 2 => 'Mbale Campus', 3 => 'Arua Campus', 4 => 'Kabale Campus', 5 => 'Fort Portal Campus'];
                            ?>
                            <?php echo e($campuses[$student->studCampus] ?? ($student->studCampus ?? '-'), false); ?>

                        </div>
                    </div>
                </div>
            </div>

            
            <div>
                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #dee2e6; padding-bottom: 8px;">
                    <i class="fa fa-cog"></i> Academic Settings
                </h6>
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="info-label">Grading System ID</div>
                        <div class="info-value"><?php echo e($student->gradSystemID ?? '-', false); ?></div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="info-label">Campus</div>
                        <div class="info-value">
                            <?php
                                $campuses = [1 => 'Main Campus', 2 => 'Mbale Campus', 3 => 'Arua Campus', 4 => 'Kabale Campus', 5 => 'Fort Portal Campus'];
                            ?>
                            <?php echo e($campuses[$student->studCampus] ?? ($student->studCampus ?? '-'), false); ?>

                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="info-label">Billing ID</div>
                        <div class="info-value"><?php echo e($student->billingID ?? '-', false); ?></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-graduation-cap"></i> Academic Records by Semester</h5>
        </div>
        <div class="section-body">
            <?php
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
            ?>

            <?php if($sortedKeys->count() > 0): ?>
                <?php $__currentLoopData = $sortedKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
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
                    ?>
                    
                    <div class="card mb-3" style="border: 1px solid #dee2e6;">
                        <div class="card-header" style="background: #e9ecef; padding: 12px 20px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <strong><?php echo e($year, false); ?></strong> - Semester <?php echo e($semester, false); ?>

                                </h6>
                                <div>
                                    <?php if($semesterGPA): ?>
                                        <span class="badge bg-success">GPA: <?php echo e(number_format($semesterGPA, 2), false); ?></span>
                                    <?php endif; ?>
                                    <?php if($semesterCredits): ?>
                                        <span class="badge bg-info"><?php echo e($semesterCredits, false); ?> Credits</span>
                                    <?php endif; ?>
                                    <span class="badge bg-secondary"><?php echo e($registrations->count(), false); ?> Courses</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 15px;">
                            
                            
                            <?php if($registrations->count() > 0): ?>
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
                                        <?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong><?php echo e($registration->courseID ?? '-', false); ?></strong></td>
                                            <td><?php echo e($registration->course->courseName ?? '-', false); ?></td>
                                            <td>
                                                <?php if($registration->course_status == 'REGULAR'): ?>
                                                    <span class="badge bg-success"><?php echo e($registration->course_status, false); ?></span>
                                                <?php elseif($registration->course_status == 'RETAKE'): ?>
                                                    <span class="badge bg-warning"><?php echo e($registration->course_status, false); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-info"><?php echo e($registration->course_status ?? 'N/A', false); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($registration->stud_session ?? '-', false); ?></td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            
                            <?php if($courseworks->count() > 0): ?>
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
                                        <?php $__currentLoopData = $courseworks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong><?php echo e($mark->settings->courseID ?? '-', false); ?></strong></td>
                                            <td><?php echo e($mark->settings->course->courseName ?? '-', false); ?></td>
                                            <td><?php echo e($mark->total_assignments ?? 0, false); ?></td>
                                            <td><?php echo e($mark->total_tests ?? 0, false); ?></td>
                                            <td><strong><?php echo e($mark->final_score ?? 0, false); ?></strong></td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            
                            <?php if($results->count() > 0): ?>
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
                                        <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong><?php echo e($result->courseid ?? '-', false); ?></strong></td>
                                            <td><?php echo e($result->course->courseName ?? '-', false); ?></td>
                                            <td><?php echo e($result->CreditUnits ?? '-', false); ?></td>
                                            <td><?php echo e($result->score ?? '-', false); ?></td>
                                            <td>
                                                <strong 
                                                    <?php if(in_array($result->grade, ['A', 'B+', 'B'])): ?>
                                                        class="text-success"
                                                    <?php elseif(in_array($result->grade, ['C+', 'C'])): ?>
                                                        class="text-warning"
                                                    <?php elseif(in_array($result->grade, ['D+', 'D', 'F'])): ?>
                                                        class="text-danger"
                                                    <?php endif; ?>
                                                >
                                                    <?php echo e($result->grade ?? '-', false); ?>

                                                </strong>
                                            </td>
                                            <td><?php echo e($result->gradept ?? '-', false); ?></td>
                                            <td><strong><?php echo e(number_format($result->gpa ?? 0, 2), false); ?></strong></td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            <?php if($registrations->count() == 0 && $courseworks->count() == 0 && $results->count() == 0): ?>
                                <p class="text-muted text-center">No records available for this semester</p>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="alert alert-info">No academic records available</div>
            <?php endif; ?>
        </div>
    </div>

    
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
                        <?php $__empty_1 = true; $__currentLoopData = $student->practicalExamMarks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $practical): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($practical->settings->courseID ?? '-', false); ?></td>
                            <td><?php echo e($practical->settings->course->courseName ?? '-', false); ?></td>
                            <td><?php echo e($practical->settings->acadyear ?? '-', false); ?></td>
                            <td><?php echo e($practical->settings->semester ?? '-', false); ?></td>
                            <td><?php echo e($practical->final_score ?? 0, false); ?></td>
                            <td><?php echo e($practical->settings->total_mark ?? 100, false); ?></td>
                            <td><?php echo e($practical->final_score > 0 ? number_format(($practical->final_score / ($practical->settings->total_mark ?? 100)) * 100, 1) : 0, false); ?>%</td>
                            <td>
                                <?php if($practical->final_score >= ($practical->settings->total_mark ?? 100) * 0.5): ?>
                                    <span class="badge bg-success">Pass</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Fail</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No practical exam marks available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-line-chart"></i> Academic Progress Summary</h5>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">Cumulative GPA</div>
                    <div class="info-value"><span class="badge bg-success" style="font-size: 16px;"><?php echo e(number_format($student->cumulative_gpa, 2), false); ?></span></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Total Credits Earned</div>
                    <div class="info-value"><strong><?php echo e($student->total_credits_earned, false); ?> / <?php echo e($student->duration * 30, false); ?></strong> CUs</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Current Year of Study</div>
                    <div class="info-value">Year <?php echo e($student->current_year_of_study, false); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Expected Graduation</div>
                    <div class="info-value"><?php echo e($student->expected_graduation_year ?? '-', false); ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="info-label">Academic Standing</div>
                    <div class="info-value">
                        <?php
                            $standing = $student->academic_standing;
                            $badgeClass = $standing == 'Dean\'s List' ? 'success' : ($standing == 'Good Standing' ? 'info' : ($standing == 'Probation' ? 'warning' : 'secondary'));
                        ?>
                        <span class="badge bg-<?php echo e($badgeClass, false); ?>"><?php echo e($standing, false); ?></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Completion Progress</div>
                    <div class="info-value">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e($student->completion_percentage, false); ?>%;"><?php echo e($student->completion_percentage, false); ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Courses Completed</div>
                    <div class="info-value"><strong><?php echo e($student->results->where('grade', '!=', 'F')->count(), false); ?> / <?php echo e($student->results->count(), false); ?></strong></div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Remaining Credits</div>
                    <div class="info-value"><strong><?php echo e(max(0, ($student->duration * 30) - $student->total_credits_earned), false); ?></strong> CUs</div>
                </div>
            </div>
        </div>
    </div>

    
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
                        <?php $__empty_1 = true; $__currentLoopData = $semesterGpaSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($semester['acad'] ?? '-', false); ?></td>
                            <td>Semester <?php echo e($semester['semester'] ?? '-', false); ?></td>
                            <td><?php echo e($semester['courses_taken'] ?? 0, false); ?></td>
                            <td><?php echo e($semester['credits_earned'] ?? 0, false); ?></td>
                            <td><strong><?php echo e(number_format($semester['semester_gpa'] ?? 0, 2), false); ?></strong></td>
                            <td><strong><?php echo e(number_format($student->cumulative_gpa, 2), false); ?></strong></td>
                            <td>
                                <?php if($semester['semester_gpa'] >= 3.0): ?>
                                    <span class="badge bg-success">Pass</span>
                                <?php elseif($semester['semester_gpa'] >= 2.0): ?>
                                    <span class="badge bg-warning">Probation</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Fail</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No semester GPA data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
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
                        <?php $__empty_1 = true; $__currentLoopData = $retakes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $retake): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($retake->courseid ?? '-', false); ?></td>
                            <td><?php echo e($retake->course->courseName ?? '-', false); ?></td>
                            <td><?php echo e($retake->acad ?? '-', false); ?> Sem <?php echo e($retake->semester ?? '-', false); ?></td>
                            <td><?php echo e($retake->score ?? 0, false); ?></td>
                            <td>1</td>
                            <td><?php echo e($retake->score ?? 0, false); ?></td>
                            <td>
                                <?php if($retake->grade != 'F'): ?>
                                    <span class="badge bg-success">PASS</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">FAIL</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No retake records available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
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

    
    <div class="card section-card">
        <div class="section-header">
            <h5><i class="fa fa-folder"></i> Documents</h5>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="mb-2">Student Photo</h6>
                    <div class="text-center">
                        <img src="<?php echo e(asset('storage/photos/default-avatar.png'), false); ?>" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 250px; border: 1px solid #ddd;"
                             alt="Student Photo">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="mb-2">Signature</h6>
                    <div class="text-center">
                        <img src="<?php echo e(asset('storage/signatures/default-signature.png'), false); ?>" 
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/admin/mru/students/show.blade.php ENDPATH**/ ?>