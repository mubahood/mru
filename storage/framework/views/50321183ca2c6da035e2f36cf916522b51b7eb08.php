<thead>
<tr class="quick-create">
    <td colspan="<?php echo e($columnCount, false); ?>" style="height: 47px;padding-left: 57px;background-color: #f9f9f9; vertical-align: middle;">

        <span class="create" style="color: #bdbdbd;cursor: pointer;display: block;">
             <i class="fa fa-plus"></i>&nbsp;<?php echo e(__('admin.quick_create'), false); ?>

        </span>

        <form class="form-inline create-form" style="display: none;" method="post">
            <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                &nbsp;<?php echo $field->render(); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                &nbsp;
            <button class="btn btn-primary btn-sm"><?php echo e(__('admin.submit'), false); ?></button>&nbsp;
            <a href="javascript:void(0);" class="cancel"><?php echo e(__('admin.cancel'), false); ?></a>
            <?php echo e(csrf_field(), false); ?>

        </form>
    </td>
</tr>
</thead><?php /**PATH /Applications/MAMP/htdocs/mru/vendor/encore/laravel-admin/src/../resources/views/grid/quick-create/form.blade.php ENDPATH**/ ?>