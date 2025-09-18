<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Quản lý khóa học';
require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");
?>
<?php
if (isset($_GET['id'])) {
    $row = $Database->get_row(" SELECT * FROM `khoahoc` WHERE `MaKhoaHoc` = '" . check_string($_GET['id']) . "'  ");
    if (!$row) {
        admin_msg_error("Khóa học này không tồn tại", BASE_URL(''), 500);
    }
} else {
    admin_msg_error("Liên kết không tồn tại", BASE_URL(''), 0);
}
if (isset($_POST['btnSave'])) {
    if (empty($_POST['tenKhoaHoc']) || empty($_POST['linkAnh']) || empty($_POST['noiDung'])  || empty($_POST['trangThai'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    $tenKhoaHoc = ($_POST['tenKhoaHoc']);
    $linkAnh = ($_POST['linkAnh']);
    $noiDung = ($_POST['noiDung']);
    $trangThai = ($_POST['trangThai']);
    $Database->update("khoahoc", array(
        'TenKhoaHoc' => $tenKhoaHoc,
        'LinkAnh'           => $linkAnh,
        'NoiDung'  => $noiDung,
        'TrangThaiKhoaHoc'         => $trangThai

    ), " `MaKhoaHoc` = '" . $_GET['id'] . "' ");
    admin_msg_success("Thay đổi thành công", "", 1000);
}

if (isset($_POST['btnThemBaiHoc'])) {

    if (empty($_POST['maBaiHoc']) || empty($_POST['tenBaiHoc'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    $maKhoaHoc = ($_GET['id']);
    $maBaiHoc = check_string($_POST['maBaiHoc']);
    $tenBaiHoc = ($_POST['tenBaiHoc']);
    $Database->insert("baihoc", array(
        'MaKhoaHoc' => $maKhoaHoc,
        'MaBaiHoc' => $maBaiHoc,
        'TenBaiHoc' => $tenBaiHoc,

    ));
    admin_msg_success("Thêm thành công", "", 1000);
}

// Xử lý xóa bài học
if (isset($_POST['btnXoaBaiHoc'])) {
    $maKhoaHoc = check_string($_POST['maKhoaHoc']);
    $maBaiHoc = check_string($_POST['maBaiHoc']);
    
    // Kiểm tra xem có từ vựng nào trong bài học này không
    $checkTuVung = $Database->get_row("SELECT COUNT(*) as count FROM tuvung WHERE MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc'");
    
    if ($checkTuVung['count'] > 0) {
        // Tự động xóa tất cả từ vựng trong bài học trước
        $Database->remove("tuvung", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc'");
        admin_msg_success("Đã xóa " . $checkTuVung['count'] . " từ vựng trong bài học.", "", 2000);
    }
    
    // Xóa bài học
    $Database->remove("baihoc", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc'");
    admin_msg_success("Xóa bài học thành công", "", 1000);
}
?>



<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chỉnh sửa khóa học</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">CHỈNH SỬA KHÓA HỌC</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Tên khóa học</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="tenKhoaHoc" value="<?= $row['TenKhoaHoc']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Link Ảnh</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="linkAnh" value="<?= $row['LinkAnh']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Nội dung</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="noiDung" value="<?= $row['NoiDung']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Số học viên</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" value="<?= $Database->num_rows("SELECT * FROM dangkykhoahoc WHERE MaKhoaHoc = '" . $row["MaKhoaHoc"] . "'  "); ?>" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputPassword3" class="col-sm-2 col-form-label">Trạng thái</label>
                                <div class="col-sm-10">
                                    <select class="custom-select" name="trangThai">
                                        <option value="<?= $row['TrangThaiKhoaHoc']; ?>">
                                            <?php
                                            if ($row['TrangThaiKhoaHoc'] == "1") {
                                                echo 'Hoạt động';
                                            }
                                            if ($row['TrangThaiKhoaHoc'] == "0") {
                                                echo 'Banned';
                                            }
                                            ?>
                                        </option>
                                        <option value="1">Hoạt động</option>
                                        <option value="0">Banned</option>
                                    </select>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Ngày tạo</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="inputEmail3" value="<?= $row['ThoiGianTaoKhoaHoc']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="btnSave" class="btn btn-primary btn-block waves-effect">
                                <span>LƯU</span>
                            </button>
                            <a type="button" href="<?= BASE_URL('admin/courses'); ?>" class="btn btn-danger btn-block waves-effect">
                                <span>TRỞ LẠI</span>
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">THÊM BÀI HỌC</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Mã khóa học</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <select class="custom-select" name="maKhoaHoc" disabled>
                                            <option value="<?= $row['MaKhoaHoc']; ?>" selected="selected">
                                                <?= $row["MaKhoaHoc"] ?>

                                            </option>
                                            <?php
                                            foreach ($Database->get_list(" select * from khoahoc order by MaKhoaHoc asc") as $optionKhoaHoc) {

                                            ?>
                                                <option value="<?= $optionKhoaHoc["MaKhoaHoc"] ?>"><?= $optionKhoaHoc["MaKhoaHoc"] ?></option>
                                            <?php
                                            }
                                            ?>

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $maBaiHocMoi = 0;
                            $getMaBaiHocMoi = $Database->get_row("select * from baihoc where MaKhoaHoc = '" . $row["MaKhoaHoc"] . "'  order by MaBaiHoc desc limit 1");
                            if ($getMaBaiHocMoi) {
                                $maBaiHocMoi = $getMaBaiHocMoi["MaBaiHoc"] + 1;
                            }
                            ?>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Mã bài học</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="inputEmail3" name="maBaiHoc" value="<?= $maBaiHocMoi ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Tên bài học</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="inputEmail3" name="tenBaiHoc" value="">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="btnThemBaiHoc" class="btn btn-primary btn-block waves-effect">
                                <span>XÁC NHẬN</span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách bài học</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Mã bài học</th>
                                        <th>Tên bài học</th>
                                        <th>Trạng thái</th>
                                        <th>Thời gian tạo</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    foreach ($Database->get_list(" SELECT * FROM baihoc A inner join khoahoc B on A.MaKhoaHoc = B.MaKhoaHoc WHERE A.MaKhoaHoc = '" . $row['MaKhoaHoc'] . "' ORDER BY MaBaiHoc ASC ") as $row) {
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= $row['MaBaiHoc']; ?></td>
                                            <td><?= $row['TenBaiHoc']; ?></td>
                                            <td><?= displayStatusAccount($row['TrangThaiBaiHoc']); ?></td>
                                            <td><span class="badge badge-info px-3"><?= date('d/m/Y H:i', strtotime($row['ThoiGianTaoBaiHoc'])); ?></span></td>
                                            <td>
                                                <a type="button" href="<?= BASE_URL('admin/courses/' . $row['MaKhoaHoc'] . '/lesson/edit/'); ?><?= $row['MaBaiHoc']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i>
                                                    <span>EDIT</span></a>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?= $row['MaBaiHoc']; ?>">
                                                    <i class="fas fa-trash"></i> XÓA
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal xóa bài học -->
                                        <div class="modal fade" id="deleteModal<?= $row['MaBaiHoc']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Xác nhận xóa bài học</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Bạn có chắc chắn muốn xóa bài học "<strong><?= $row['TenBaiHoc']; ?></strong>" không?</p>
                                                        <?php 
                                                        $countTuVung = $Database->get_row("SELECT COUNT(*) as count FROM tuvung WHERE MaKhoaHoc = '" . $row['MaKhoaHoc'] . "' AND MaBaiHoc = '" . $row['MaBaiHoc'] . "'");
                                                        if($countTuVung['count'] > 0) {
                                                        ?>
                                                        <div class="alert alert-warning">
                                                            <strong>Chú ý:</strong> Bài học này có <strong><?= $countTuVung['count']; ?></strong> từ vựng. 
                                                            Khi xóa bài học, tất cả từ vựng bên trong cũng sẽ bị xóa cùng lúc.
                                                        </div>
                                                        <?php } else { ?>
                                                        <p class="text-info">Bài học này không có từ vựng nào.</p>
                                                        <?php } ?>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="maKhoaHoc" value="<?= $row['MaKhoaHoc']; ?>">
                                                            <input type="hidden" name="maBaiHoc" value="<?= $row['MaBaiHoc']; ?>">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                            <button type="submit" name="btnXoaBaiHoc" class="btn btn-danger">
                                                                <?php if($countTuVung['count'] > 0) { ?>
                                                                Xóa bài học và <?= $countTuVung['count']; ?> từ vựng
                                                                <?php } else { ?>
                                                                Xóa bài học
                                                                <?php } ?>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        </tr>
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



<script>
    $(function() {
        $("#datatable").DataTable({
            "responsive": true,
            "autoWidth": false,
        });
    });
</script>






<?php
require_once(__DIR__ . "/Footer.php"); ?>?>