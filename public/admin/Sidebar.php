<!-- CSS Fix for VS Code warnings - Font Awesome classes -->
<style>
/* Font Awesome classes to prevent VS Code warnings */
.fa-bars, .fa-comments, .fa-bell, .fa-expand-arrows-alt, .fa-th-large,
.fa-book, .fa-tachometer-alt, .fa-users, .fa-graduation-cap, .fa-book-reader,
.fa-robot, .fa-cog, .fa-pen-alt {
    /* These classes are defined by Font Awesome library */
}
</style>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= BASE_URL('admin/home'); ?>" class="nav-link">Home</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= BASE_URL('admin/home'); ?>" class="brand-link">
        <i class="brand-image fas fa-book" style="color: #007bff; font-size: 2rem; margin-left: 10px;"></i>
        <span class="brand-text font-weight-light">Admin Panel</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="nav-item menu-open">
                    <a href="<?= BASE_URL('admin/home'); ?>" class="nav-link active">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-header">QUẢN LÝ</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL('admin/users'); ?>" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Thành viên
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL('admin/courses'); ?>" class="nav-link">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>
                            Khóa học
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL('admin/reading-management'); ?>" class="nav-link">
                        <i class="nav-icon fas fa-book-reader"></i>
                        <p>
                            Reading
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL('admin/writing-management'); ?>" class="nav-link">
                        <i class="nav-icon fas fa-pen-alt"></i>
                        <p>
                            Writing
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL('admin/grammar-management'); ?>" class="nav-link">
                        <i class="nav-icon fas fa-spell-check"></i>
                        <p>
                            Grammar Quiz
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL('admin/chatgpt'); ?>" class="nav-link">
                        <i class="nav-icon fas fa-robot"></i>
                        <p>
                            Chat GPT
                        </p>
                    </a>
                </li>
                <li class="nav-header">CÀI ĐẶT</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL('admin/system'); ?>" class="nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Hệ thống
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
