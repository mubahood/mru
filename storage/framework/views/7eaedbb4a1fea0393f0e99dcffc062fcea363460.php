<?php
use App\Models\Utils;
?><style>
    .ext-icon {
        color: rgba(0, 0, 0, 0.5);
        margin-left: 10px;
    }

    .installed {
        color: #00a65a;
        margin-right: 10px;
    }

    .card {
        border-radius: 5px;
    }

    .case-item:hover {
        background-color: rgb(254, 254, 254);
    }
</style>
<div class="card mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3 class="h4 pt-3 text-uppercase">
            <b><?php echo e($title, false); ?></b>
        </h3>
        <div>
            <a href="<?php echo e(url('/fees'), false); ?>" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-2 py-md-3">
        <div class="row">
            <div class="col-md-12">
                <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="py-1" title="<?php echo e($item->description, false); ?>">
                        <div class="d-flex justify-content-between  align-items-center text-uppercase p-0 m-0"
                            style="font-weight: 600; line-height: 12px; font-size: 12px; 
                            ">
                            <?php
                            if ($item->account == null) {
                                $item->delete();
                            }
                            ?>
                            <?php echo e(Str::substr($item->account->name, 0, 15), false); ?>...
                            <span class="text-danger"> <?php echo e(number_format($item->amount), false); ?></span>
                        </div>
                        <div class="">
                            <small class="p-0 m-0"><?php echo e($item->description, false); ?> -
                                <?php echo e($item->created_at, false); ?></small>
                        </div>
                    </div>
                    <hr class="p-0 m-0 mb-1">
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/dashboard/recent_fees_bills.blade.php ENDPATH**/ ?>