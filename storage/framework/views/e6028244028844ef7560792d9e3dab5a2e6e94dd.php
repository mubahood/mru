<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($export->export_name, false); ?> - Academic Results</title>
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
        
        @media  print {
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
            <a href="<?php echo e(admin_url('mru-academic-result-exports'), false); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Exports
            </a>
        </div>

        <div class="export-container">
            <!-- Header -->
            <div class="header-section">
                <?php if($logoPath): ?>
                    <img src="<?php echo e($logoPath, false); ?>" alt="Logo" class="header-logo">
                <?php endif; ?>
                
                <div class="header-info">
                    <?php if($enterprise): ?>
                        <h1><?php echo e($enterprise->name ?? 'Enterprise Name', false); ?></h1>
                        <p style="margin: 5px 0;"><?php echo e($enterprise->address ?? '', false); ?></p>
                        <p style="margin: 5px 0;">
                            <?php if($enterprise->phone): ?> Tel: <?php echo e($enterprise->phone, false); ?> <?php endif; ?>
                            <?php if($enterprise->email): ?> | Email: <?php echo e($enterprise->email, false); ?> <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <h2><?php echo e($export->export_name, false); ?></h2>
                </div>
                
                <?php if($logoPath): ?>
                    <div style="width: 150px;"></div> <!-- Spacer for symmetry -->
                <?php endif; ?>
            </div>

            <!-- Info Section -->
            <div class="info-section">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Programme:</strong> <?php echo e($export->programme->programme ?? 'N/A', false); ?></p>
                        <p><strong>Academic Year:</strong> <?php echo e($export->academic_year, false); ?></p>
                        <p><strong>Semester:</strong> <?php echo e($export->semester, false); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Year of Study:</strong> Year <?php echo e($export->study_year, false); ?></p>
                        <?php if(($export->minimum_passes_required ?? 0) > 0): ?>
                            <p><strong>Minimum Passes Required:</strong> <?php echo e($export->minimum_passes_required, false); ?> subjects</p>
                        <?php endif; ?>
                        <p><strong>Generated:</strong> <?php echo e(now()->format('d M Y, H:i'), false); ?></p>
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
            <?php $__currentLoopData = $specializationData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="specialization-section">
                    <div class="spec-header">
                        <h3><?php echo e($specData['spec_name'], false); ?></h3>
                        <span class="badge bg-light text-dark">
                            <?php echo e($specData['student_count'], false); ?> <?php echo e(Str::plural('Student', $specData['student_count']), false); ?>

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
                                    <?php $__currentLoopData = $specData['courses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="course-header"><?php echo e($course->courseID, false); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <!-- Row 2: Course Names -->
                                <tr class="course-name-row">
                                    <?php $__currentLoopData = $specData['courses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th title="<?php echo e($course->courseName, false); ?>">
                                            <?php echo e(Str::limit($course->courseName, 30), false); ?>

                                        </th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $specData['students']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <!-- Registration Number -->
                                        <td class="regno-col"><?php echo e($student->regno, false); ?></td>
                                        
                                        <!-- Student Name (Clickable) -->
                                        <td class="name-col">
                                            <a href="<?php echo e(admin_url('mru-students/' . $student->ID), false); ?>" 
                                               target="_blank"
                                               title="View <?php echo e($student->firstname, false); ?> <?php echo e($student->othername, false); ?>'s profile"
                                               data-bs-toggle="tooltip">
                                                <?php echo e($student->firstname, false); ?> <?php echo e($student->othername, false); ?>

                                            </a>
                                            <br>
                                            <a href="<?php echo e(admin_url('mru-students/' . $student->ID . '/transcript'), false); ?>" 
                                               target="_blank"
                                               class="btn btn-xs btn-outline-success no-print"
                                               style="font-size: 8px; padding: 1px 4px; margin-top: 2px;"
                                               title="Print Academic Transcript">
                                                <i class="bi bi-printer"></i> Transcript
                                            </a>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td class="status-col <?php echo e($student->statusClass, false); ?>"
                                            title="Passed: <?php echo e($student->coursesPassed, false); ?>/<?php echo e($student->totalCourses, false); ?>"
                                            data-bs-toggle="tooltip">
                                            <?php echo e($student->status, false); ?>

                                        </td>
                                        
                                        <!-- Course Count -->
                                        <td class="status-col" style="font-weight: 600;">
                                            <?php echo e($student->coursesWithResults, false); ?>

                                        </td>
                                        
                                        <!-- Course Results -->
                                        <?php $__currentLoopData = $specData['courses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $result = App\Services\MruAcademicResultHtmlService::getStudentCourseResult(
                                                    $specData['results'], 
                                                    $student->regno, 
                                                    $course->courseID
                                                );
                                            ?>
                                            <td class="<?php echo e($result['class'], false); ?>"
                                                title="<?php echo e($course->courseName, false); ?>: <?php echo e($result['grade'], false); ?>"
                                                data-bs-toggle="tooltip">
                                                <?php echo e($result['grade'], false); ?>

                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if(count($specializationData) === 0): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No results found for the specified criteria.
                </div>
            <?php endif; ?>
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
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/mru_academic_result_export_html.blade.php ENDPATH**/ ?>