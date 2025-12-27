<?php if($error = session()->get('error')): ?>
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php if(is_string($error)): ?>
            <p><?php echo $error; ?></p>
        <?php else: ?>
            <h4><i class="icon fa fa-ban"></i><?php echo e(\Illuminate\Support\Arr::get($error->get('title'), 0), false); ?></h4>
            <p><?php echo \Illuminate\Support\Arr::get($error->get('message'), 0); ?></p>
        <?php endif; ?>
    </div>
<?php elseif($errors = session()->get('errors')): ?>
    <?php if($errors->hasBag('error')): ?>
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php $__currentLoopData = $errors->getBag("error")->toArray(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <p><?php echo \Illuminate\Support\Arr::get($message, 0); ?></p>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php endif; ?>
<?php endif; ?>

<?php if($success = session()->get('success')): ?>
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php if(is_string($success)): ?>
            <p><?php echo $success; ?></p>
        <?php else: ?>
            <h4><i class="icon fa fa-check"></i><?php echo e(\Illuminate\Support\Arr::get($success->get('title'), 0), false); ?></h4>
            <p><?php echo \Illuminate\Support\Arr::get($success->get('message'), 0); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($info = session()->get('info')): ?>
    <div class="alert alert-info alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php if(is_string($info)): ?>
            <p><?php echo $info; ?></p>
        <?php else: ?>
            <h4><i class="icon fa fa-info"></i><?php echo e(\Illuminate\Support\Arr::get($info->get('title'), 0), false); ?></h4>
            <p><?php echo \Illuminate\Support\Arr::get($info->get('message'), 0); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($warning = session()->get('warning')): ?>
    <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php if(is_string($warning)): ?>
            <p><?php echo $warning; ?></p>
        <?php else: ?>
            <h4><i class="icon fa fa-warning"></i><?php echo e(\Illuminate\Support\Arr::get($warning->get('title'), 0), false); ?></h4>
            <p><?php echo \Illuminate\Support\Arr::get($warning->get('message'), 0); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH /Applications/MAMP/htdocs/mru/vendor/encore/laravel-admin/src/../resources/views/partials/alerts.blade.php ENDPATH**/ ?>