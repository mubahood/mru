<div class="container-fluid" style="padding: 20px;">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">
                <i class="fa fa-check-circle"></i> Curriculum Generation Report
            </h3>
        </div>
        <div class="card-body">
            <!-- Specialization Info -->
            <div class="alert alert-info">
                <h4><i class="fa fa-graduation-cap"></i> Specialization Details</h4>
                <p><strong>ID:</strong> <?php echo e($specialization->spec_id, false); ?></p>
                <p><strong>Name:</strong> <?php echo e($specialization->spec, false); ?></p>
                <p><strong>Programme:</strong> <?php echo e($specialization->prog_id, false); ?> 
                    <?php if($specialization->programme): ?>
                        - <?php echo e($specialization->programme->progname, false); ?>

                    <?php endif; ?>
                </p>
            </div>

            <!-- Summary Statistics -->
            <div class="row" style="margin-bottom: 30px;">
                <div class="col-md-3">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3><?php echo e($total_processed, false); ?></h3>
                            <p>Total Courses Processed</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-book"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3><?php echo e($total_created, false); ?></h3>
                            <p>New Records Created</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3><?php echo e($total_existing, false); ?></h3>
                            <p>Already Existing</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3><?php echo e($total_skipped + $total_errors, false); ?></h3>
                            <p>Skipped/Errors</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Created Records -->
            <?php if(count($created) > 0): ?>
                <div class="card card-success">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-plus-circle"></i> Newly Created Records (<?php echo e($total_created, false); ?>)
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-success">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th style="text-align: center;">Credits</th>
                                    <th style="text-align: center;">Type</th>
                                    <th style="text-align: center;">Status</th>
                                    <th style="text-align: center;">Approval</th>
                                    <th style="text-align: center;">Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $created; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($record['course_code'], false); ?></strong></td>
                                        <td><?php echo e($record['course_name'], false); ?></td>
                                        <td style="text-align: center;">Year <?php echo e($record['year'], false); ?></td>
                                        <td style="text-align: center;">Semester <?php echo e($record['semester'], false); ?></td>
                                        <td style="text-align: center;"><?php echo e($record['credits'], false); ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-info"><?php echo e(ucfirst($record['type']), false); ?></span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-success"><?php echo e(ucfirst($record['status']), false); ?></span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-warning"><?php echo e(ucfirst($record['approval_status']), false); ?></span>
                                        </td>
                                        <td style="text-align: center;"><?php echo e($record['student_count'] ?? 0, false); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Existing Records -->
            <?php if(count($existing) > 0): ?>
                <div class="card card-warning">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-check"></i> Already Existing Records (<?php echo e($total_existing, false); ?>)
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-warning">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th style="text-align: center;">Status</th>
                                    <th style="text-align: center;">Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $existing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($record['course_code'], false); ?></strong></td>
                                        <td><?php echo e($record['course_name'], false); ?></td>
                                        <td style="text-align: center;">Year <?php echo e($record['year'], false); ?></td>
                                        <td style="text-align: center;">Semester <?php echo e($record['semester'], false); ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-<?php echo e($record['status'] === 'active' ? 'success' : 'secondary', false); ?>">
                                                <?php echo e(ucfirst($record['status']), false); ?>

                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-<?php echo e($record['approval_status'] === 'approved' ? 'success' : ($record['approval_status'] === 'pending' ? 'warning' : 'danger'), false); ?>">
                                                <?php echo e(ucfirst($record['approval_status']), false); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Skipped Records -->
            <?php if(count($skipped) > 0): ?>
                <div class="card card-default">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-times-circle"></i> Skipped Records (<?php echo e($total_skipped, false); ?>)
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-gray">
                                <tr>
                                    <th>Course Code</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $skipped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($record['course_code'], false); ?></strong></td>
                                        <td style="text-align: center;">Year <?php echo e($record['year'], false); ?></td>
                                        <td style="text-align: center;">Semester <?php echo e($record['semester'], false); ?></td>
                                        <td><?php echo e($record['reason'], false); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Errors -->
            <?php if(count($errors) > 0): ?>
                <div class="card card-danger">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-exclamation-circle"></i> Errors (<?php echo e($total_errors, false); ?>)
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-danger">
                                <tr>
                                    <th>Course Code</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th>Error Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($record['course_code'], false); ?></strong></td>
                                        <td style="text-align: center;"><?php echo e($record['year'], false); ?></td>
                                        <td style="text-align: center;"><?php echo e($record['semester'], false); ?></td>
                                        <td><code><?php echo e($record['error'], false); ?></code></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="text-center" style="margin-top: 30px;">
                <a href="<?php echo e(admin_url('mru-specialisations'), false); ?>" class="btn btn-primary btn-lg">
                    <i class="fa fa-arrow-left"></i> Back to Specializations
                </a>
                <a href="<?php echo e(admin_url('mru-specialization-courses'), false); ?>" class="btn btn-success btn-lg" style="margin-left: 10px;">
                    <i class="fa fa-list"></i> View Specialization Courses
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .small-box {
        border-radius: 5px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box .inner h3 {
        font-size: 38px;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box .inner p {
        font-size: 15px;
        margin: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
    }
    .bg-aqua {
        background-color: #00c0ef !important;
        color: #fff;
    }
    .bg-green {
        background-color: #00a65a !important;
        color: #fff;
    }
    .bg-yellow {
        background-color: #f39c12 !important;
        color: #fff;
    }
    .bg-red {
        background-color: #dd4b39 !important;
        color: #fff;
    }
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        margin-bottom: 30px;
    }
    .card-header {
        padding: 15px;
        border-bottom: 1px solid #f4f4f4;
    }
    .table {
        font-size: 14px;
    }
    .table thead th {
        color: #fff;
        font-weight: bold;
    }
</style>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/admin/mru/specializations/generate-curriculum-report.blade.php ENDPATH**/ ?>