<?php if(isset($u) && $u != null && $u->ent != null && $u->ent->active_academic_year() != null): ?>
    <?php
    $canSwitchYears = false;
    //$dpYear = $u->ent->dpYear();
    $dpTerm = $u->ent->dpTerm();
    if ($u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm') || $u->isRole('bursar')) {
        $canSwitchYears = true;
    }
    ?>
    <?php if($dpTerm != null): ?>


       
    <?php endif; ?>
<?php endif; ?>

<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell-o"></i>
        <span class="label label-danger"><?php echo e(count($items), false); ?></span>
    </a>
    <ul class="dropdown-menu">
        <li class="header">You have <?php echo e(count($items), false); ?> system warnings</li>
        <li>


            <ul class="menu">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e($item['link'], false); ?>">
                            <i class="fa fa-warning text-danger"></i> <?php echo e($item['message'], false); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

        </li>
        <li class="footer"><a href="#">View all</a></li>
    </ul>
</li>

</li>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/widgets/admin-links.blade.php ENDPATH**/ ?>