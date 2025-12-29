<div class="container-fluid" style="padding: 15px;">
    <!-- Specialization Info - Compact -->
    <div class="alert alert-info" style="margin-bottom: 15px; padding: 10px;">
        <strong><?php echo e($specialization->spec, false); ?></strong> (<?php echo e($specialization->abbrev, false); ?>) | 
        Programme: <strong><?php echo e($specialization->prog_id, false); ?></strong> - <?php echo e($specialization->programme->progname ?? 'N/A', false); ?>

    </div>

    <!-- Summary Statistics - Compact Row -->
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-4">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?php echo e($summary['total_courses'], false); ?></h3>
                    <p>Total Courses Found</p>
                </div>
                <div class="icon"><i class="fa fa-book"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3><?php echo e($summary['already_exists'], false); ?></h3>
                    <p>Already in Curriculum</p>
                </div>
                <div class="icon"><i class="fa fa-check"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3><?php echo e($summary['invalid_courses'], false); ?></h3>
                    <p>Invalid Courses</p>
                </div>
                <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="box" style="margin-bottom: 20px;">
        <div class="box-body">
            <p style="margin-bottom: 15px;">
                <i class="fa fa-info-circle"></i> 
                <strong>Review the courses below and click "Import All Courses" to add them to the curriculum.</strong>
            </p>
            <form method="POST" action="<?php echo e(admin_url('mru-specialisations/' . $specialization->spec_id . '/process-generate-curriculum'), false); ?>">
                <?php echo e(csrf_field(), false); ?>

                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Are you sure you want to import all courses to the curriculum?');">
                    <i class="fa fa-download"></i> Import All Courses (<?php echo e($summary['will_create'], false); ?> new)
                </button>
                <a href="<?php echo e(admin_url('mru-specialisations'), false); ?>" class="btn btn-default btn-lg">
                    <i class="fa fa-arrow-left"></i> Back to Specializations
                </a>
            </form>
        </div>
    </div>

    <!-- Course List by Year and Semester -->
    <?php $__currentLoopData = $groupedAnalysis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearNum => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <!-- Year Header -->
        <div style="background: #3c8dbc; color: white; padding: 12px 20px; margin-bottom: 15px; margin-top: 25px; border-left: 5px solid #2c6a8f;">
            <h3 style="margin: 0; font-size: 20px; font-weight: bold;">
                <i class="fa fa-graduation-cap"></i> YEAR <?php echo e($yearNum, false); ?>

                <span class="pull-right" style="font-size: 14px; opacity: 0.9;">
                    <?php echo e($semesters->flatten(1)->count(), false); ?> courses
                </span>
            </h3>
        </div>
        
        <div class="row">
            <?php $__currentLoopData = $semesters->sortKeys(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semNum => $courses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6" style="margin-bottom: 15px;">
                    <div class="box" style="border-top: 3px solid <?php echo e($semNum == 1 ? '#00a65a' : '#f39c12', false); ?>;">
                        <div class="box-header with-border" style="background: <?php echo e($semNum == 1 ? '#e8f5e9' : '#fff3e0', false); ?>;">
                            <h4 class="box-title" style="margin: 0; color: <?php echo e($semNum == 1 ? '#00a65a' : '#f39c12', false); ?>;">
                                <i class="fa fa-book"></i> <strong>Semester <?php echo e($semNum, false); ?></strong>
                            </h4>
                            <span class="label label-<?php echo e($semNum == 1 ? 'success' : 'warning', false); ?> pull-right"><?php echo e(count($courses), false); ?> courses</span>
                        </div>
                        <div class="box-body" style="padding: 0;">
                            <table class="table table-condensed table-hover" style="margin: 0; font-size: 12px;">
                                <tbody>
                                    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="<?php echo e($course['status'] === 'invalid' ? 'danger' : ($course['status'] === 'exists' ? 'warning' : ''), false); ?>">
                                            <td style="width: 5%; text-align: center;">
                                                <i class="fa fa-<?php echo e($course['status'] === 'will_create' ? 'plus text-success' : ($course['status'] === 'exists' ? 'check text-warning' : 'times text-danger'), false); ?>"></i>
                                            </td>
                                            <td style="width: 20%;"><strong><?php echo e($course['course_code'], false); ?></strong></td>
                                            <td style="width: 40%;"><?php echo e(Str::limit($course['course_name'], 30), false); ?></td>
                                            <td style="width: 10%; text-align: center;">
                                                <span class="badge badge-info"><?php echo e($course['credits'], false); ?>CU</span>
                                            </td>
                                            <td style="width: 10%; text-align: center; font-size: 10px;">
                                                <?php echo e($course['student_count'], false); ?>

                                            </td>
                                            <td style="width: 15%; text-align: center;">
                                                <?php if($course['status'] === 'will_create'): ?>
                                                    <span class="label label-success"><i class="fa fa-plus"></i> New</span>
                                                <?php elseif($course['status'] === 'exists'): ?>
                                                    <span class="label label-warning"><i class="fa fa-check"></i> Exists</span>
                                                <?php else: ?>
                                                    <span class="label label-danger"><i class="fa fa-times"></i> Invalid</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($summary['will_create'] === 0): ?>
        <div class="alert alert-info" style="margin-top: 10px;">
            <i class="fa fa-info-circle"></i> No new courses to create. All courses are either already linked or invalid.
        </div>
    <?php endif; ?>
</div>

<style>
    .small-box {
        border-radius: 3px;
        position: relative;
        display: block;
        margin-bottom: 10px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box .inner h3 {
        font-size: 32px;
        font-weight: bold;
        margin: 0 0 5px 0;
        padding: 0;
    }
    .small-box .inner p {
        font-size: 13px;
        margin: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 5px;
        right: 10px;
        font-size: 60px;
        color: rgba(0,0,0,0.15);
    }
    .bg-aqua { background-color: #00c0ef !important; color: #fff; }
    .bg-green { background-color: #00a65a !important; color: #fff; }
    .bg-yellow { background-color: #f39c12 !important; color: #fff; }
    .bg-red { background-color: #dd4b39 !important; color: #fff; }
    .table-condensed > tbody > tr > td {
        padding: 5px !important;
    }
    .box {
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 3px;
        margin-bottom: 0;
    }
    .box-header {
        padding: 8px 10px;
        border-bottom: 1px solid #f4f4f4;
    }
    .box-title {
        font-size: 14px;
        font-weight: bold;
        margin: 0;
    }
    .box-primary {
        border-top: 3px solid #3c8dbc;
    }
</style>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/admin/mru/specializations/generate-curriculum.blade.php ENDPATH**/ ?>