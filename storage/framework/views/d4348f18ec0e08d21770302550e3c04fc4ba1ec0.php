<?php $__env->startSection('content'); ?>
<style>
    .semester-card {
        background: white;
        border-radius: 0;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-left: 4px solid #3c8dbc;
    }
    
    .semester-card h3 {
        margin-top: 0;
        color: #333;
        font-size: 20px;
        font-weight: 600;
    }
    
    .semester-description {
        color: #666;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .semester-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .stat-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 0;
        border-left: 3px solid #3c8dbc;
    }
    
    .stat-item.success {
        border-left-color: #00a65a;
    }
    
    .stat-item.danger {
        border-left-color: #dd4b39;
    }
    
    .stat-label {
        font-size: 12px;
        color: #777;
        margin-bottom: 5px;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .stat-value {
        font-size: 24px;
        color: #333;
        font-weight: bold;
    }
    
    .semester-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .semester-status.active {
        background: #00a65a;
        color: white;
    }
    
    .semester-status.inactive {
        background: #999;
        color: white;
    }
    
    .page-header {
        background: white;
        padding: 20px;
        margin: -15px -15px 20px -15px;
        border-bottom: 1px solid #ddd;
    }
    
    .page-header h1 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }
    
    .academic-year-badge {
        background: #3c8dbc;
        color: white;
        padding: 5px 15px;
        border-radius: 3px;
        font-size: 14px;
        margin-left: 10px;
    }
</style>

<div class="page-header">
    <h1>
        <i class="fa fa-clock-o"></i> MRU Semesters
        <span class="academic-year-badge"><?php echo e($currentYear, false); ?></span>
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="semester-card">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h3>
                        <i class="fa fa-calendar"></i> 
                        Semester <?php echo e($semester->semester_number, false); ?>: <?php echo e($semester->semester_name, false); ?>

                    </h3>
                    <p class="semester-description"><?php echo e($semester->description, false); ?></p>
                </div>
                <div>
                    <span class="semester-status <?php echo e($semester->is_active ? 'active' : 'inactive', false); ?>">
                        <?php echo e($semester->is_active ? 'ACTIVE' : 'INACTIVE', false); ?>

                    </span>
                </div>
            </div>
            
            <div class="semester-stats">
                <div class="stat-item">
                    <div class="stat-label">Total Registrations</div>
                    <div class="stat-value"><?php echo e(number_format($semester->statistics['total_registrations']), false); ?></div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-label">Unique Students</div>
                    <div class="stat-value"><?php echo e(number_format($semester->statistics['unique_students']), false); ?></div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-label">Unique Courses</div>
                    <div class="stat-value"><?php echo e(number_format($semester->statistics['unique_courses']), false); ?></div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-label">Total Results</div>
                    <div class="stat-value"><?php echo e(number_format($semester->statistics['total_results']), false); ?></div>
                </div>
                
                <div class="stat-item success">
                    <div class="stat-label">Passing Results</div>
                    <div class="stat-value"><?php echo e(number_format($semester->statistics['passing_results']), false); ?></div>
                </div>
                
                <div class="stat-item danger">
                    <div class="stat-label">Failing Results</div>
                    <div class="stat-value"><?php echo e(number_format($semester->statistics['failing_results']), false); ?></div>
                </div>
            </div>
            
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
                <a href="<?php echo e(admin_url('mru-course-registrations'), false); ?>?acad_year=<?php echo e($currentYear, false); ?>&semester=<?php echo e($semester->semester_number, false); ?>" 
                   class="btn btn-sm btn-primary">
                    <i class="fa fa-list"></i> View Registrations
                </a>
                <a href="<?php echo e(admin_url('mru-results'), false); ?>?acad=<?php echo e($currentYear, false); ?>&semester=<?php echo e($semester->semester_number, false); ?>" 
                   class="btn btn-sm btn-info">
                    <i class="fa fa-file-text"></i> View Results
                </a>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin::index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/admin/mru-semesters-grid.blade.php ENDPATH**/ ?>