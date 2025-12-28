<?php $__env->startSection('content'); ?>
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
        background: linear-gradient(135deg, <?php echo e($primaryColor, false); ?> 0%, <?php echo e($primaryColor, false); ?>dd 100%);
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
        border-left: 3px solid <?php echo e($primaryColor, false); ?>;
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
        border-left-color: <?php echo e($secondaryColor, false); ?>;
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
        background: <?php echo e($primaryColor, false); ?>;
        flex-shrink: 0;
    }
    
    .stat-card.success .stat-card-icon {
        background: <?php echo e($secondaryColor, false); ?>;
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
        color: <?php echo e($primaryColor, false); ?>;
        line-height: 1;
    }
    
    .stat-card.success .stat-card-number {
        color: <?php echo e($secondaryColor, false); ?>;
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
        color: <?php echo e($primaryColor, false); ?>;
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
        color: <?php echo e($secondaryColor, false); ?>;
    }
    
    .performance-table .gpa-score {
        color: <?php echo e($primaryColor, false); ?>;
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
        border: 1px solid <?php echo e($primaryColor, false); ?>;
        color: <?php echo e($primaryColor, false); ?>;
        text-align: center;
        border-radius: 0;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .quick-link-btn:hover {
        background: <?php echo e($primaryColor, false); ?>;
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
        background: <?php echo e($primaryColor, false); ?>15;
        color: <?php echo e($primaryColor, false); ?>;
        padding: 10px 8px;
        border: 1px solid <?php echo e($primaryColor, false); ?>30;
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
            <form method="GET" action="<?php echo e(admin_url('mru-dashboard'), false); ?>" id="dashboardFilterForm" style="display: flex; gap: 10px;">
                <select name="academic_year" class="form-control" onchange="document.getElementById('dashboardFilterForm').submit()">
                    <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($year->acadyear, false); ?>" <?php echo e($selectedYear == $year->acadyear ? 'selected' : '', false); ?>>
                            <?php echo e($year->acadyear, false); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="semester" class="form-control" onchange="document.getElementById('dashboardFilterForm').submit()">
                    <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semValue => $semLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($semValue, false); ?>" <?php echo e($selectedSemester == $semValue ? 'selected' : '', false); ?>>
                            <?php echo e($semLabel, false); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
        </div>
        <div style="clear:both;"></div>
    </div>

    <!-- Statistics Cards -->
    <div class="row" style="margin: 0 -5px;">
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="<?php echo e(admin_url('mru-course-registrations'), false); ?>?acad_year=<?php echo e($selectedYear, false); ?><?php echo e($selectedSemester ? '&semester='.$selectedSemester : '', false); ?>">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Students Registered</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['students_registered']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="<?php echo e(admin_url('mru-courses'), false); ?>">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Courses</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['total_courses']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="<?php echo e(admin_url('mru-programmes'), false); ?>">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Programmes</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['total_programmes']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="<?php echo e(admin_url('mru-faculties'), false); ?>">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-building"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Faculties</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['total_faculties']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="row" style="margin: 0 -5px;">
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="<?php echo e(admin_url('mru-results'), false); ?>?acad=<?php echo e($selectedYear, false); ?><?php echo e($selectedSemester ? '&semester='.$selectedSemester : '', false); ?>">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-file-text"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Results</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['total_results']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card">
                <a href="<?php echo e(admin_url('mru-course-registrations'), false); ?>?acad_year=<?php echo e($selectedYear, false); ?><?php echo e($selectedSemester ? '&semester='.$selectedSemester : '', false); ?>">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-registered"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Course Registrations</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['course_registrations']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card success">
                <a href="<?php echo e(admin_url('mru-results'), false); ?>?acad=<?php echo e($selectedYear, false); ?><?php echo e($selectedSemester ? '&semester='.$selectedSemester : '', false); ?>&grade_ne=F">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Passing Results</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['passing_results']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding: 0 5px;">
            <div class="stat-card danger">
                <a href="<?php echo e(admin_url('mru-results'), false); ?>?acad=<?php echo e($selectedYear, false); ?><?php echo e($selectedSemester ? '&semester='.$selectedSemester : '', false); ?>&grade=F">
                    <div class="stat-card-body">
                        <div class="stat-card-icon">
                            <i class="fa fa-times-circle"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Failing Results</div>
                            <div class="stat-card-number"><?php echo e(number_format($stats['failing_results']), false); ?></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Students by Programme and Year of Study & Quick Links -->
    <div class="row">
        <div class="col-md-8">
            <div class="info-box">
                <h3><i class="fa fa-table"></i> Students by Programme & Year of Study</h3>
                <div style="overflow-x: auto;">
                    <table class="students-year-table">
                        <thead>
                            <tr>
                                <th style="width: 40%; text-align: left;">Programme</th>
                                <?php for($year = 1; $year <= $studentsByProgrammeYear['max_year']; $year++): ?>
                                    <th style="width: <?php echo e(50 / $studentsByProgrammeYear['max_year'], false); ?>%; text-align: center;">Year <?php echo e($year, false); ?></th>
                                <?php endfor; ?>
                                <th style="width: 10%; text-align: center; background: <?php echo e($primaryColor, false); ?>; color: white;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $grandTotal = 0; ?>
                            <?php $__currentLoopData = $studentsByProgrammeYear['programmes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $programme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php 
                                    $progTotal = array_sum($programme['years']);
                                    $grandTotal += $progTotal;
                                ?>
                                <tr>
                                    <td style="font-weight: 600; font-size: 12px;">
                                        <?php echo e($programme['progname'] ?? $programme['abbrev'] ?? $programme['progid'], false); ?>

                                    </td>
                                    <?php for($year = 1; $year <= $studentsByProgrammeYear['max_year']; $year++): ?>
                                        <td style="text-align: center; font-size: 13px; font-weight: 600; color: <?php echo e($programme['years'][$year] > 0 ? $primaryColor : '#ccc', false); ?>;">
                                            <?php echo e($programme['years'][$year] > 0 ? number_format($programme['years'][$year]) : '-', false); ?>

                                        </td>
                                    <?php endfor; ?>
                                    <td style="text-align: center; font-weight: 700; font-size: 14px; background: #f5f5f5; color: <?php echo e($primaryColor, false); ?>;">
                                        <?php echo e(number_format($progTotal), false); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if(empty($studentsByProgrammeYear['programmes'])): ?>
                                <tr>
                                    <td colspan="<?php echo e($studentsByProgrammeYear['max_year'] + 2, false); ?>" style="text-align: center; color: #999; padding: 20px;">
                                        No student data found for the selected academic year
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr style="background: <?php echo e($primaryColor, false); ?>10; border-top: 2px solid <?php echo e($primaryColor, false); ?>;">
                                    <td style="font-weight: 700; font-size: 12px; color: <?php echo e($primaryColor, false); ?>;">GRAND TOTAL</td>
                                    <?php for($year = 1; $year <= $studentsByProgrammeYear['max_year']; $year++): ?>
                                        <?php
                                            $yearTotal = 0;
                                            foreach($studentsByProgrammeYear['programmes'] as $prog) {
                                                $yearTotal += $prog['years'][$year] ?? 0;
                                            }
                                        ?>
                                        <td style="text-align: center; font-weight: 700; font-size: 14px; color: <?php echo e($primaryColor, false); ?>;">
                                            <?php echo e(number_format($yearTotal), false); ?>

                                        </td>
                                    <?php endfor; ?>
                                    <td style="text-align: center; font-weight: 700; font-size: 15px; background: <?php echo e($primaryColor, false); ?>; color: white;">
                                        <?php echo e(number_format($grandTotal), false); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <h3><i class="fa fa-link"></i> Quick Links</h3>
                <a href="<?php echo e(admin_url('mru-results'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-list"></i> View All Results
                </a>
                <a href="<?php echo e(admin_url('mru-academic-result-exports'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-download"></i> Export Results
                </a>
                <a href="<?php echo e(admin_url('mru-students'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-users"></i> Manage Students
                </a>
                <a href="<?php echo e(admin_url('mru-courses'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-book"></i> Manage Courses
                </a>
                <a href="<?php echo e(admin_url('mru-programmes'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-graduation-cap"></i> Manage Programmes
                </a>
                <a href="<?php echo e(admin_url('mru-faculties'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-building"></i> Manage Faculties
                </a>
                <a href="<?php echo e(admin_url('mru-course-registrations'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-registered"></i> Course Registrations
                </a>
                <a href="<?php echo e(admin_url('mru-academic-years'), false); ?>" class="quick-link-btn">
                    <i class="fa fa-calendar"></i> Academic Years
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin::index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/admin/mru-dashboard.blade.php ENDPATH**/ ?>