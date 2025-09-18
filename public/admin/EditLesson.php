<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Quản lý bài học';
require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");
?>
<?php
if (isset($_GET['maBaiHoc']) && isset($_GET['maKhoaHoc'])) {
    $row = $Database->get_row(" SELECT * FROM baihoc A inner join khoahoc B on A.MaKhoaHoc = B.MaKhoaHoc WHERE A.MaKhoaHoc = '" . check_string($_GET['maKhoaHoc']) . "'  and A.MaBaiHoc = '" . check_string($_GET['maBaiHoc']) . "'  ");
    if (!$row) {
        admin_msg_error("Bài học này không tồn tại", BASE_URL(''), 500);
    }
} else {
    admin_msg_error("Liên kết không tồn tại", BASE_URL(''), 0);
}
if (isset($_POST['btnSave']) && $row) {
    if (empty($_POST['tenBaiHoc']) || empty($_POST['trangThai'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    $tenBaiHoc = ($_POST['tenBaiHoc']);
    $trangThai = check_string($_POST['trangThai']);

    $Database->update("baihoc", array(
        'TenBaiHoc' => $tenBaiHoc,
        'TrangThaiBaiHoc'         => $trangThai

    ), " `MaKhoaHoc` = '" . $row['MaKhoaHoc'] . "' and `MaBaiHoc` = '" . $row['MaBaiHoc'] . "'  ");
    admin_msg_success("Thay đổi thành công", "", 1000);
}
if (isset($_POST['btnThemTuVung']) && $row) {

    if (empty($_POST['maTuVung']) || empty($_POST['noiDungTuVung']) || empty($_POST['dichNghia']) || empty($_POST['diem']) || empty($_POST['hinhAnh']) || empty($_POST['amThanh'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    $maKhoaHoc = $_GET['maKhoaHoc'];
    $maBaiHoc = $_GET['maBaiHoc'];
    $maTuVung = ($_POST['maTuVung']);
    $noiDungTuVung = ($_POST['noiDungTuVung']);
    $dichNghia = ($_POST['dichNghia']);
    $loaiTu = isset($_POST['loaiTu']) ? check_string($_POST['loaiTu']) : '';
    $cachPhatAm = isset($_POST['cachPhatAm']) ? check_string($_POST['cachPhatAm']) : '';
    $diem = ($_POST['diem']);
    $hinhAnh = ($_POST['hinhAnh']);
    $amThanh = ($_POST['amThanh']);

    $Database->insert("tuvung", array(
        'MaKhoaHoc' => $maKhoaHoc,
        'MaBaiHoc' => $maBaiHoc,
        'MaTuVung' => $maTuVung,
        'NoiDungTuVung' => $noiDungTuVung,
        'DichNghia'         => $dichNghia,
        'LoaiTu'            => $loaiTu,
        'CachPhatAm'        => $cachPhatAm,
        'Diem'         => $diem,
        'HinhAnh'         => $hinhAnh,
        'AmThanh'         => $amThanh,
    ));
    admin_msg_success("Thêm thành công", "", 1000);
}

// Xử lý xóa từ vựng riêng lẻ
if (isset($_POST['btnXoaTuVung'])) {
    $maKhoaHoc = check_string($_POST['maKhoaHoc']);
    $maBaiHoc = check_string($_POST['maBaiHoc']);
    $maTuVung = check_string($_POST['maTuVung']);
    
    // Xóa dữ liệu liên quan trong các bảng khác trước
    $Database->remove("boquatuvung", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
    $Database->remove("hoctuvung", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
    $Database->remove("ontaploai1", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
    $Database->remove("vidu", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
    
    // Sau đó xóa từ vựng
    $Database->remove("tuvung", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
    admin_msg_success("Xóa từ vựng thành công", "", 1000);
}

// Xử lý xóa nhiều từ vựng
if (isset($_POST['btnXoaNhieuTuVung'])) {
    if (empty($_POST['selected_vocabulary']) || !is_array($_POST['selected_vocabulary'])) {
        admin_msg_error("Vui lòng chọn ít nhất một từ vựng để xóa", "", 1000);
    } else {
        $selectedIds = $_POST['selected_vocabulary'];
        $maKhoaHoc = check_string($_POST['maKhoaHoc']);
        $maBaiHoc = check_string($_POST['maBaiHoc']);
        
        $deletedCount = 0;
        foreach ($selectedIds as $maTuVung) {
            $maTuVung = check_string($maTuVung);
            
            // Xóa dữ liệu liên quan trong các bảng khác trước
            $Database->remove("boquatuvung", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
            $Database->remove("hoctuvung", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
            $Database->remove("ontaploai1", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
            $Database->remove("vidu", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
            
            // Sau đó xóa từ vựng
            $result = $Database->remove("tuvung", "MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc' AND MaTuVung = '$maTuVung'");
            if ($result) {
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            admin_msg_success("Đã xóa thành công $deletedCount từ vựng", "", 1000);
        } else {
            admin_msg_error("Không thể xóa từ vựng", "", 1000);
        }
    }
}
?>



<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chỉnh sửa bài học</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">CHỈNH SỬA BÀI HỌC</h3>
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
                                        <input type="text" class="form-control" name="tenKhoaHoc" value="<?= $row['TenKhoaHoc']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Tên bài học</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="tenBaiHoc" value="<?= $row['TenBaiHoc']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputPassword3" class="col-sm-2 col-form-label">Trạng thái</label>
                                <div class="col-sm-10">
                                    <select class="custom-select" name="trangThai">
                                        <option value="<?= $row['TrangThaiBaiHoc']; ?>">
                                            <?php
                                            if ($row['TrangThaiBaiHoc'] == "1") {
                                                echo 'Hoạt động';
                                            }
                                            if ($row['TrangThaiBaiHoc'] == "0") {
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
                                        <input type="text" class="form-control" id="inputEmail3" value="<?= $row['ThoiGianTaoBaiHoc']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="btnSave" class="btn btn-primary btn-block waves-effect">
                                <span>LƯU</span>
                            </button>
                            <a type="button" href="<?= BASE_URL('admin/courses/edit/' . $row["MaKhoaHoc"] . ''); ?>" class="btn btn-danger btn-block waves-effect">
                                <span>TRỞ LẠI</span>
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">THÊM TỪ VỰNG</h3>
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
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Mã bài học</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <select class="custom-select" name="maBaiHoc" disabled>
                                            <option value="<?= $row['MaBaiHoc']; ?>" selected="selected">
                                                <?= $row["MaBaiHoc"] ?>

                                            </option>
                                            <?php
                                            foreach ($Database->get_list(" select * from baihoc where MaKhoaHoc = '" . $row['MaKhoaHoc'] . "'  order by MaBaiHoc asc") as $optionBaiHoc) {

                                            ?>
                                                <option value="<?= $optionBaiHoc["MaBaiHoc"] ?>"><?= $optionBaiHoc["MaBaiHoc"] ?></option>
                                            <?php
                                            }
                                            ?>

                                        </select>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $maTuVungMoi = 1;
                            $getMaTuVungMoi = $Database->get_row("select * from tuvung where MaKhoaHoc = '" . $row["MaKhoaHoc"] . "' and MaBaiHoc = '" . $row["MaBaiHoc"] . "' order by MaTuVung desc limit 1");
                            if ($getMaTuVungMoi) {
                                $maTuVungMoi = $getMaTuVungMoi["MaTuVung"] + 1;
                            }
                            ?>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Mã từ vựng</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="inputEmail3" name="maTuVung" value="<?= $maTuVungMoi ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Nội dung từ vựng</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="inputEmail3" name="noiDungTuVung" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Dịch nghĩa</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="inputEmail3" name="dichNghia" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Loại từ</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <select class="custom-select" name="loaiTu">
                                            <option value="" selected>Chọn loại từ</option>
                                            <option value="Noun">Noun - Danh từ</option>
                                            <option value="Verb">Verb - Động từ</option>
                                            <option value="Adjective">Adjective - Tính từ</option>
                                            <option value="Adverb">Adverb - Trạng từ</option>
                                            <option value="Pronoun">Pronoun - Đại từ</option>
                                            <option value="Preposition">Preposition - Giới từ</option>
                                            <option value="Conjunction">Conjunction - Liên từ</option>
                                            <option value="Interjection">Interjection - Thán từ</option>
                                            <option value="Article">Article - Mạo từ</option>
                                            <option value="Number">Number - Số từ</option>
                                            <option value="Other">Other - Khác</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Cách phát âm</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="cachPhatAm" value="" placeholder="Ví dụ: /həˈloʊ/">
                                        <small class="form-text text-muted">Nhập cách phát âm theo chuẩn IPA (International Phonetic Alphabet)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Điểm</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="inputEmail3" name="diem" value="10">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Hình ảnh</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="hinhAnh" name="hinhAnh" value="">
                                        <div class="btn btn-primary btn-block waves-effect" id="uploadHinhAnh">Upload</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Âm thanh</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="amThanh" name="amThanh" value="">
                                        <div class="btn btn-primary btn-block waves-effect" id="uploadAmThanh">Upload</div>

                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="btnThemTuVung" class="btn btn-primary btn-block waves-effect">
                                <span>XÁC NHẬN</span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách các từ vựng</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-danger btn-sm" id="btnXoaNhieu" disabled onclick="xoaNhieuTuVung()">
                                <i class="fas fa-trash"></i> Xóa đã chọn
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="bulkDeleteForm" method="POST">
                            <input type="hidden" name="maKhoaHoc" value="<?= $row['MaKhoaHoc']; ?>">
                            <input type="hidden" name="maBaiHoc" value="<?= $row['MaBaiHoc']; ?>">
                            
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                            </th>
                                            <th>STT</th>
                                            <th>Mã bài học</th>
                                            <th>Mã khóa học</th>
                                            <th>Mã từ vựng</th>
                                            <th>Nội dung từ vựng</th>
                                            <th>Dịch nghĩa</th>
                                            <th>Loại từ</th>
                                            <th>Cách phát âm</th>
                                            <th>Điểm</th>
                                            <th>Trạng thái</th>
                                            <th>Thời gian tạo</th>
                                            <th>ACTION</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    foreach ($Database->get_list(" SELECT * FROM tuvung A inner join baihoc B on A.MaBaiHoc = B.MaBaiHoc and A.MaKhoaHoc = B.MaKhoaHoc WHERE A.MaKhoaHoc = '" . $row['MaKhoaHoc'] . "' and A.MaBaiHoc = '" . $row['MaBaiHoc'] . "' ORDER BY A.MaTuVung ASC ") as $rowTuVung) {
                                    ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_vocabulary[]" value="<?= $rowTuVung['MaTuVung']; ?>" class="vocabulary-checkbox" onchange="checkSelectedItems()">
                                            </td>
                                            <td><?= $i++; ?></td>
                                            <td><?= $rowTuVung['MaBaiHoc']; ?></td>
                                            <td><?= $rowTuVung['MaKhoaHoc']; ?></td>
                                            <td><?= $rowTuVung['MaTuVung']; ?></td>
                                            <td><?= $rowTuVung['NoiDungTuVung']; ?></td>
                                            <td><?= $rowTuVung['DichNghia']; ?></td>
                                            <td><span class="badge badge-info"><?= isset($rowTuVung['LoaiTu']) && $rowTuVung['LoaiTu'] ? $rowTuVung['LoaiTu'] : 'Chưa cập nhật'; ?></span></td>
                                            <td><span class="text-primary"><?= isset($rowTuVung['CachPhatAm']) && $rowTuVung['CachPhatAm'] ? $rowTuVung['CachPhatAm'] : 'Chưa cập nhật'; ?></span></td>
                                            <td><?= $rowTuVung['Diem']; ?></td>
                                            <td><?= displayStatusAccount($rowTuVung['TrangThaiTuVung']); ?></td>
                                            <td><span class="badge badge-success px-3"><?= date('d/m/Y H:i', strtotime($rowTuVung['ThoiGianTaoTuVung'])); ?></span></td>
                                            <td>
                                                <a type="button" href="<?= BASE_URL('admin/courses/' . $rowTuVung['MaKhoaHoc'] . '/lesson/' . $rowTuVung['MaBaiHoc'] . '/vocabulary/edit/' . $rowTuVung['MaTuVung'] . ''); ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i>
                                                    <span>EDIT</span></a>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?= $rowTuVung['MaTuVung']; ?>">
                                                    <i class="fas fa-trash"></i> XÓA
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal xóa từ vựng riêng lẻ -->
                                        <div class="modal fade" id="deleteModal<?= $rowTuVung['MaTuVung']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Xác nhận xóa từ vựng</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Bạn có chắc chắn muốn xóa từ vựng "<strong><?= $rowTuVung['NoiDungTuVung']; ?></strong>" không?</p>
                                                        <p class="text-muted">Hành động này không thể hoàn tác.</p>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="maKhoaHoc" value="<?= $rowTuVung['MaKhoaHoc']; ?>">
                                                            <input type="hidden" name="maBaiHoc" value="<?= $rowTuVung['MaBaiHoc']; ?>">
                                                            <input type="hidden" name="maTuVung" value="<?= $rowTuVung['MaTuVung']; ?>">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                            <button type="submit" name="btnXoaTuVung" class="btn btn-danger">Xóa từ vựng</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </tbody>

                            </table>
                        </div>
                        </form>
                        
                        <!-- Modal xóa nhiều từ vựng -->
                        <div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Xác nhận xóa nhiều từ vựng</h5>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Bạn có chắc chắn muốn xóa <span id="selectedCount">0</span> từ vựng đã chọn không?</p>
                                        <p class="text-danger"><strong>Cảnh báo:</strong> Hành động này không thể hoàn tác.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                        <button type="button" class="btn btn-danger" onclick="confirmBulkDelete()">Xóa tất cả</button>
                                    </div>
                                </div>
                            </div>
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
            "columnDefs": [{
                "orderable": false,
                "targets": [0, -1] // Không cho phép sắp xếp cột checkbox và action
            }]
        });
    });

    // Chức năng chọn tất cả checkbox
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.vocabulary-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        
        checkSelectedItems();
    }

    // Kiểm tra số lượng checkbox được chọn
    function checkSelectedItems() {
        const checkboxes = document.querySelectorAll('.vocabulary-checkbox:checked');
        const btnXoaNhieu = document.getElementById('btnXoaNhieu');
        const selectAllCheckbox = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.vocabulary-checkbox');
        
        // Cập nhật trạng thái nút xóa nhiều
        if (checkboxes.length > 0) {
            btnXoaNhieu.disabled = false;
            btnXoaNhieu.innerHTML = `<i class="fas fa-trash"></i> Xóa đã chọn (${checkboxes.length})`;
        } else {
            btnXoaNhieu.disabled = true;
            btnXoaNhieu.innerHTML = '<i class="fas fa-trash"></i> Xóa đã chọn';
        }
        
        // Cập nhật trạng thái checkbox "Chọn tất cả"
        if (checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkboxes.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    // Chức năng xóa nhiều từ vựng
    function xoaNhieuTuVung() {
        const checkboxes = document.querySelectorAll('.vocabulary-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Vui lòng chọn ít nhất một từ vựng để xóa');
            return;
        }
        
        document.getElementById('selectedCount').textContent = checkboxes.length;
        $('#bulkDeleteModal').modal('show');
    }

    // Xác nhận xóa nhiều từ vựng
    function confirmBulkDelete() {
        const form = document.getElementById('bulkDeleteForm');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'btnXoaNhieuTuVung';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    }

    // Khởi tạo trạng thái ban đầu
    $(document).ready(function() {
        checkSelectedItems();
        
        // Thêm sự kiện cho tất cả checkbox
        $('.vocabulary-checkbox').on('change', checkSelectedItems);
    });


    $(function() {

        let myWidgetHinhAnh = cloudinary.createUploadWidget({
            cloudName: 'dydrox9id',
            apiKey: '471261558796725',
            uploadPreset: 'ml_default',
            sources: ['local', 'url', 'camera'],
            multiple: false,
            maxFileSize: 10000000,
            maxImageFileSize: 10000000,
            folder: 'vocabulary_images'
        }, (error, result) => {
            if (!error && result && result.event === "success") {
                console.log('Done! Here is the image info: ', result.info);
                $("#hinhAnh").val(result.info.url);
            }
            if (error) {
                console.error('Upload error:', error);
                alert('Lỗi upload: ' + error.message);
            }
        })
        let myWidgetAmThanh = cloudinary.createUploadWidget({
            cloudName: 'dydrox9id',
            apiKey: '471261558796725',
            uploadPreset: 'ml_default',
            sources: ['local', 'url'],
            multiple: false,
            maxFileSize: 40000000,
            resourceType: 'auto',
            folder: 'vocabulary_audio'
        }, (error, result) => {
            if (!error && result && result.event === "success") {
                console.log('Done! Here is the audio info: ', result.info);
                $("#amThanh").val(result.info.url);
            }
            if (error) {
                console.error('Upload error:', error);
                alert('Lỗi upload: ' + error.message);
            }
        })
        document.getElementById("uploadHinhAnh").addEventListener("click", function() {
            console.log(myWidgetHinhAnh.open());
        }, false);
        document.getElementById("uploadAmThanh").addEventListener("click", function() {
            console.log(myWidgetAmThanh.open());
        }, false);
    })

    function processImage(id) {
        var options = {
            client_hints: true,
        };
        return '<img src="' + $.cloudinary.url(id, options) + '" style="width: 100%; height: auto"/>';
    }
</script>






<?php
require_once(__DIR__ . "/Footer.php"); ?>?>