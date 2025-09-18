<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Quản lý loại từ';
require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");
?>

<style>
/* CSS Fix for VS Code warnings - Font Awesome classes */
.fa-minus {
    /* This class is defined by Font Awesome library */
}
</style>

<?php

// Xử lý thêm loại từ mới
if (isset($_POST['btnThemLoaiTu'])) {
    if (empty($_POST['tenLoaiTu']) || empty($_POST['moTa'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    
    $tenLoaiTu = check_string($_POST['tenLoaiTu']);
    $moTa = check_string($_POST['moTa']);
    
    // Kiểm tra loại từ đã tồn tại chưa
    $check = $Database->get_row("SELECT * FROM loaitu WHERE TenLoaiTu = '$tenLoaiTu'");
    if ($check) {
        admin_msg_error("Loại từ này đã tồn tại", "", 500);
    }
    
    $Database->insert("loaitu", array(
        'TenLoaiTu' => $tenLoaiTu,
        'MoTa' => $moTa,
        'TrangThai' => 1
    ));
    
    admin_msg_success("Thêm loại từ thành công", "", 1000);
}

// Xử lý cập nhật loại từ
if (isset($_POST['btnCapNhatLoaiTu'])) {
    if (empty($_POST['maLoaiTu']) || empty($_POST['tenLoaiTu']) || empty($_POST['moTa'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    
    $maLoaiTu = check_string($_POST['maLoaiTu']);
    $tenLoaiTu = check_string($_POST['tenLoaiTu']);
    $moTa = check_string($_POST['moTa']);
    $trangThai = check_string($_POST['trangThai']);
    
    $Database->update("loaitu", array(
        'TenLoaiTu' => $tenLoaiTu,
        'MoTa' => $moTa,
        'TrangThai' => $trangThai
    ), "MaLoaiTu = '$maLoaiTu'");
    
    admin_msg_success("Cập nhật loại từ thành công", "", 1000);
}

// Xử lý xóa loại từ
if (isset($_POST['btnXoaLoaiTu'])) {
    $maLoaiTu = check_string($_POST['maLoaiTu']);
    
    // Kiểm tra xem có từ vựng nào đang sử dụng loại từ này không
    $checkUsage = $Database->get_row("SELECT COUNT(*) as count FROM tuvung WHERE LoaiTu = (SELECT TenLoaiTu FROM loaitu WHERE MaLoaiTu = '$maLoaiTu')");
    if ($checkUsage['count'] > 0) {
        admin_msg_error("Không thể xóa loại từ này vì đang có từ vựng sử dụng", "", 500);
    }
    
    $Database->remove("loaitu", "MaLoaiTu = '$maLoaiTu'");
    admin_msg_success("Xóa loại từ thành công", "", 1000);
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý loại từ</h1>
                </div>
            </div>
        </div>
    </section>
    
    <section class="content">
        <div class="row">
            <!-- Form thêm loại từ mới -->
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">THÊM LOẠI TỪ MỚI</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Tên loại từ</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="tenLoaiTu" placeholder="Ví dụ: Noun, Verb, Adjective...">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Mô tả</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="moTa" rows="3" placeholder="Mô tả về loại từ này..."></textarea>
                                </div>
                            </div>
                            <button type="submit" name="btnThemLoaiTu" class="btn btn-primary">Thêm loại từ</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Danh sách loại từ -->
            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">DANH SÁCH LOẠI TỪ</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên loại từ</th>
                                        <th>Mô tả</th>
                                        <th>Trạng thái</th>
                                        <th>Thời gian tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    foreach ($Database->get_list("SELECT * FROM loaitu ORDER BY MaLoaiTu ASC") as $row) {
                                    ?>
                                        <tr>
                                            <td><?= ++$i; ?></td>
                                            <td><?= $row['TenLoaiTu']; ?></td>
                                            <td><?= $row['MoTa']; ?></td>
                                            <td>
                                                <?php if ($row['TrangThai'] == 1) { ?>
                                                    <span class="badge badge-success">Hoạt động</span>
                                                <?php } else { ?>
                                                    <span class="badge badge-danger">Ẩn</span>
                                                <?php } ?>
                                            </td>
                                            <td><?= $row['ThoiGianTao']; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?= $row['MaLoaiTu']; ?>">
                                                    Chỉnh sửa
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?= $row['MaLoaiTu']; ?>">
                                                    Xóa
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal chỉnh sửa -->
                                        <div class="modal fade" id="editModal<?= $row['MaLoaiTu']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Chỉnh sửa loại từ</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="maLoaiTu" value="<?= $row['MaLoaiTu']; ?>">
                                                            <div class="form-group">
                                                                <label>Tên loại từ</label>
                                                                <input type="text" class="form-control" name="tenLoaiTu" value="<?= $row['TenLoaiTu']; ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Mô tả</label>
                                                                <textarea class="form-control" name="moTa" rows="3"><?= $row['MoTa']; ?></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Trạng thái</label>
                                                                <select class="form-control" name="trangThai">
                                                                    <option value="1" <?= $row['TrangThai'] == 1 ? 'selected' : ''; ?>>Hoạt động</option>
                                                                    <option value="0" <?= $row['TrangThai'] == 0 ? 'selected' : ''; ?>>Ẩn</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                            <button type="submit" name="btnCapNhatLoaiTu" class="btn btn-primary">Cập nhật</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Modal xóa -->
                                        <div class="modal fade" id="deleteModal<?= $row['MaLoaiTu']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Xác nhận xóa</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Bạn có chắc chắn muốn xóa loại từ "<strong><?= $row['TenLoaiTu']; ?></strong>" không?
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="maLoaiTu" value="<?= $row['MaLoaiTu']; ?>">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                            <button type="submit" name="btnXoaLoaiTu" class="btn btn-danger">Xóa</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once(__DIR__ . "/Footer.php"); ?>
