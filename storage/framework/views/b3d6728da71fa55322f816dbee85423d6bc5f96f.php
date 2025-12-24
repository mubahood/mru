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
        <h3 class="h4">
            <b><?php echo e($title, false); ?></b>
        </h3>
        <div>
            <a href="<?php echo e(url('/accounts'), false); ?>" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-2 py-md-3">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group">
                    <?php $__currentLoopData = array_slice($values, 0, 9); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item d-flex justify-content-between  align-items-center text-uppercase"
                            style="font-weight: 700;">
                            <?php echo e($item['text'], false); ?>

                            <span class="text-primary"><?php echo e($item['value'], false); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <div class="col-md-6">
                <canvas id="graph-budget" style="width: 100%;"></canvas>
            </div>
        </div>



    </div>
</div>

<script>
    $(function() {

        var chartData = {
            labels: JSON.parse('<?php echo json_encode($labels); ?>'),
            datasets: [{
                data: JSON.parse('<?php echo json_encode($data); ?>'),
                backgroundColor: JSON.parse('<?php echo json_encode(COLORS); ?>'),
            }],

        };

        var ctx = document.getElementById('graph-budget').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                }
            }
        });
    });
</script>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/dashboard/budget.blade.php ENDPATH**/ ?>