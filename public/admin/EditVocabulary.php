<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");
$title = 'Quản lý từ vựng';
require_once(__DIR__ . "/Header.php");
require_once(__DIR__ . "/Sidebar.php");

if (isset($_GET['maBaiHoc']) && isset($_GET['maKhoaHoc']) && isset($_GET['maTuVung'])) {
    $row = $Database->get_row("SELECT * FROM tuvung WHERE MaKhoaHoc = '" . check_string($_GET['maKhoaHoc']) . "' AND MaBaiHoc = '" . check_string($_GET['maBaiHoc']) . "' AND MaTuVung = '" . check_string($_GET['maTuVung']) . "'");
    if (!$row) {
        admin_msg_error("Từ vựng này không tồn tại", BASE_URL(''), 500);
    }
} else {
    admin_msg_error("Liên kết không tồn tại", BASE_URL(''), 0);
}

if (isset($_POST['btnSave']) && $row) {
    if (empty($_POST['noiDungTuVung']) || empty($_POST['dichNghia']) || empty($_POST['diem']) || empty($_POST['hinhAnh']) || empty($_POST['amThanh']) || empty($_POST['trangThaiTuVung'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    $noiDungTuVung = check_string($_POST['noiDungTuVung']);
    $dichNghia = check_string($_POST['dichNghia']);
    $loaiTu = isset($_POST['loaiTu']) ? check_string($_POST['loaiTu']) : '';
    $cachPhatAm = isset($_POST['cachPhatAm']) ? check_string($_POST['cachPhatAm']) : '';
    $diem = check_string($_POST['diem']);
    $hinhAnh = check_string($_POST['hinhAnh']);
    $amThanh = check_string($_POST['amThanh']);
    $trangThaiTuVung = check_string($_POST['trangThaiTuVung']);

    $Database->update("tuvung", array(
        'NoiDungTuVung' => $noiDungTuVung,
        'DichNghia' => $dichNghia,
        'LoaiTu' => $loaiTu,
        'CachPhatAm' => $cachPhatAm,
        'Diem' => $diem,
        'HinhAnh' => $hinhAnh,
        'AmThanh' => $amThanh,
        'TrangThaiTuVung' => $trangThaiTuVung,
    ), "`MaKhoaHoc` = '" . $row['MaKhoaHoc'] . "' and `MaBaiHoc` = '" . $row['MaBaiHoc'] . "' and `MaTuVung` = '" . $row['MaTuVung'] . "'");
    admin_msg_success("Thay đổi thành công", "", 1000);
}

if (isset($_POST['btnThemViDu']) && $row) {
    if (empty($_POST['maViDu']) || empty($_POST['cauViDu']) || empty($_POST['dichNghiaViDu'])) {
        admin_msg_error("Vui lòng nhập đầy đủ thông tin", "", 500);
    }
    $maViDu = check_string($_POST['maViDu']);
    $cauViDu = check_string($_POST['cauViDu']);
    $dichNghia = check_string($_POST['dichNghiaViDu']);

    $Database->insert("vidu", array(
        'MaKhoaHoc' => $row["MaKhoaHoc"],
        'MaBaiHoc' => $row["MaBaiHoc"],
        'MaTuVung' => $row["MaTuVung"],
        'MaViDu' => $maViDu,
        'CauViDu' => $cauViDu,
        'DichNghia' => $dichNghia,
    ));
    admin_msg_success("Thêm thành công", "", 1000);
}
?><div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chỉnh sửa từ vựng</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">CHỈNH SỬA TỪ VỰNG</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Mã khóa học</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" value="<?= $row['MaKhoaHoc']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Mã bài học</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" value="<?= $row['MaBaiHoc']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Mã từ vựng</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" value="<?= $row['MaTuVung']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Nội dung từ vựng</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="noiDungTuVung" value="<?= $row['NoiDungTuVung']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Dịch nghĩa</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="dichNghia" value="<?= $row['DichNghia']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Loại từ</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <select class="custom-select" name="loaiTu">
                                            <option value="<?= isset($row['LoaiTu']) ? $row['LoaiTu'] : ''; ?>" selected><?= isset($row['LoaiTu']) && $row['LoaiTu'] ? $row['LoaiTu'] : 'Chọn loại từ'; ?></option>
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
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Cách phát âm</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="cachPhatAm" value="<?= isset($row['CachPhatAm']) ? $row['CachPhatAm'] : ''; ?>" placeholder="Ví dụ: /həˈloʊ/">
                                        <small class="form-text text-muted">Nhập cách phát âm theo chuẩn IPA (International Phonetic Alphabet)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Điểm</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="diem" value="<?= $row['Diem']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Hình ảnh</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="hinhAnh" name="hinhAnh" value="<?= $row['HinhAnh']; ?>">
                                        <div class="btn btn-primary btn-block waves-effect" id="uploadHinhAnh">Upload</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Âm thanh</label>
                                <div class="col-sm-10">
                                    <div class="form-line">
                                        <input type="text" class="form-control" id="amThanh" name="amThanh" value="<?= $row['AmThanh']; ?>">
                                        <div class="btn btn-primary btn-block waves-effect" id="uploadAmThanh">Upload</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputPassword3" class="col-sm-2 col-form-label">Trạng thái</label>
                                <div class="col-sm-10">
                                    <select class="custom-select" name="trangThaiTuVung">
                                        <option value="<?= $row['TrangThaiTuVung']; ?>">
                                            <?php
                                            if ($row['TrangThaiTuVung'] == "1") {
                                                echo 'Hoạt động';
                                            }
                                            if ($row['TrangThaiTuVung'] == "0") {
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
                                        <input type="text" class="form-control" value="<?= $row['ThoiGianTaoTuVung']; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="btnSave" class="btn btn-primary btn-block waves-effect">
                                <span>LƯU</span>
                            </button>
                            <a type="button" href="<?= BASE_URL('admin/courses/' . $row["MaKhoaHoc"] . '/lesson/edit/' . $row["MaBaiHoc"] . ''); ?>" class="btn btn-danger btn-block waves-effect">
                                <span>TRỞ LẠI</span>
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">THÊM VÍ DỤ</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <?php
                            $maViDuMoi = 1;
                            $getMaViDuMoi = $Database->get_row("SELECT * FROM vidu WHERE MaKhoaHoc = '" . $row["MaKhoaHoc"] . "' AND MaBaiHoc = '" . $row["MaBaiHoc"] . "' AND MaTuVung = '" . $row["MaTuVung"] . "' ORDER BY MaViDu DESC LIMIT 1");
                            if ($getMaViDuMoi) {
                                $maViDuMoi = $getMaViDuMoi["MaViDu"] + 1;
                            }
                            ?>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Mã ví dụ</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="maViDu" value="<?= $maViDuMoi ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Câu ví dụ</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="cauViDu" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Dịch nghĩa</label>
                                <div class="col-sm-8">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="dichNghiaViDu" value="">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="btnThemViDu" class="btn btn-primary btn-block waves-effect">
                                <span>XÁC NHẬN</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách ví dụ</h3>
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
                                        <th>Mã ví dụ</th>
                                        <th>Câu ví dụ</th>
                                        <th>Dịch nghĩa</th>
                                        <th>Trạng thái</th>
                                        <th>Thời gian tạo</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    foreach ($Database->get_list("SELECT * FROM vidu WHERE MaKhoaHoc = '" . $row['MaKhoaHoc'] . "' AND MaBaiHoc = '" . $row['MaBaiHoc'] . "' AND MaTuVung = '" . $row['MaTuVung'] . "' ORDER BY MaViDu ASC") as $rowViDu) {
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= $rowViDu['MaViDu']; ?></td>
                                            <td><?= $rowViDu['CauViDu']; ?></td>
                                            <td><?= $rowViDu['DichNghia']; ?></td>
                                            <td><?= displayStatusAccount($rowViDu['TrangThaiViDu']); ?></td>
                                            <td><span class="badge badge-dark px-3"><?= $rowViDu['ThoiGianTaoViDu']; ?></span></td>
                                            <td>
                                                <a type="button" href="<?= BASE_URL('admin/lesson/edit/'); ?><?= $rowViDu['MaBaiHoc']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i>
                                                    <span>EDIT</span></a>
                                            </td>
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
        });

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
        });

        document.getElementById("uploadHinhAnh").addEventListener("click", function() {
            myWidgetHinhAnh.open();
        }, false);
        
        document.getElementById("uploadAmThanh").addEventListener("click", function() {
            myWidgetAmThanh.open();
        }, false);
    });
</script>

<?php
require_once(__DIR__ . "/Footer.php");
?>