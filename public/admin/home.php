<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'DASHBOARD ADMIN';
require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");
?>

<!-- CSS Fix for VS Code warnings - Font Awesome classes -->
<style>
/* Font Awesome classes to prevent VS Code warnings */
.fa-users, .fa-graduation-cap, .fa-book, .fa-spell-check {
    /* These classes are defined by Font Awesome library */
}
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $Database->num_rows("SELECT * FROM `nguoidung` "); ?></h3>
                            <p>Tổng thành viên</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $Database->num_rows("SELECT * FROM `khoahoc` "); ?></h3>
                            <p>Tổng khóa học</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $Database->num_rows("SELECT * FROM `baihoc` "); ?></h3>
                            <p>Tổng bài học</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $Database->num_rows("SELECT * FROM `tuvung` "); ?></h3>
                            <p>Tổng từ vựng</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-spell-check"></i>
                        </div>
                    </div>
                </div>
                </div>
                <!-- ./col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
    $(function() {
        $("#datatable").DataTable({
            "responsive": false,
            "autoWidth": false,
        });
        $("#datatable1").DataTable({
            "responsive": false,
            "autoWidth": false,
        });
    });
</script>

<?php
require_once(__DIR__ . "/Footer.php");
?>
