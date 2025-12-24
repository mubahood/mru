<?php
use App\Models\Utils;
$ent = Utils::ent();
?><style>
    .sidebar {
        background-color: #FFFFFF;
    }

    .content-header {
        background-color: #F9F9F9;
    }

    .sidebar-menu .active {
        border-left: solid 5px <?php echo e($ent->color, false); ?> !important;
        color: <?php echo e($ent->color, false); ?> !important;
    }


    .navbar,
    .logo,
    .sidebar-toggle,
    .user-header,
    .btn-dropbox,
    .btn-twitter,
    .btn-instagram,
    .btn-primary,
    .navbar-static-top {
        background-color: <?php echo e($ent->color, false); ?> !important;
    }

    .dropdown-menu {
        border: none !important;
    }

    .box-success {
        border-top: <?php echo e($ent->color, false); ?> .5rem solid !important;
    }

    :root {
        --primary: <?php echo e($ent->color, false); ?>;
    }
</style>
<?php /**PATH /Applications/MAMP/htdocs/mru/resources/views/widgets/css.blade.php ENDPATH**/ ?>