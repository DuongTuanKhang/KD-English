<?php
require_once '../../configs/config.php';

// Simple session for demo
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'user001';
    $_SESSION['user_name'] = 'Demo User';
}

echo "<h1>Database Investigation</h1>";

// Check what's in writing_topics
echo "<h2>1. Writing Topics Table:</h2>";
$sql1 = "SELECT * FROM writing_topics WHERE TrangThai = 1 ORDER BY ThoiGianTao DESC";
$result1 = $conn->query($sql1);
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr><th>MaChuDe</th><th>TenChuDe</th><th>MoTa</th><th>MaBaiHoc</th><th>MaKhoaHoc</th><th>TrangThai</th></tr>";
while ($row = $result1->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['MaChuDe'] . "</td>";
    echo "<td>" . $row['TenChuDe'] . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['MoTa'], 0, 50)) . "...</td>";
    echo "<td>" . $row['MaBaiHoc'] . "</td>";
    echo "<td>" . $row['MaKhoaHoc'] . "</td>";
    echo "<td>" . $row['TrangThai'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check what's in writing_prompts
echo "<h2>2. Writing Prompts Table:</h2>";
$sql2 = "SELECT * FROM writing_prompts WHERE TrangThai = 1 ORDER BY MaChuDe, ThoiGianTao";
$result2 = $conn->query($sql2);
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr><th>MaDeBai</th><th>MaChuDe</th><th>TieuDe</th><th>NoiDungDeBai</th><th>TrangThai</th></tr>";
while ($row = $result2->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['MaDeBai'] . "</td>";
    echo "<td>" . $row['MaChuDe'] . "</td>";
    echo "<td>" . $row['TieuDe'] . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['NoiDungDeBai'], 0, 100)) . "...</td>";
    echo "<td>" . $row['TrangThai'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Show which prompts belong to which topics
echo "<h2>3. Prompts per Topic:</h2>";
$sql3 = "SELECT wt.MaChuDe, wt.TenChuDe, COUNT(wp.MaDeBai) as PromptCount 
         FROM writing_topics wt 
         LEFT JOIN writing_prompts wp ON wt.MaChuDe = wp.MaChuDe AND wp.TrangThai = 1
         WHERE wt.TrangThai = 1
         GROUP BY wt.MaChuDe, wt.TenChuDe";
$result3 = $conn->query($sql3);
echo "<table border='1' style='border-collapse:collapse;'>";
echo "<tr><th>MaChuDe</th><th>TenChuDe</th><th>Number of Prompts</th></tr>";
while ($row = $result3->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['MaChuDe'] . "</td>";
    echo "<td>" . $row['TenChuDe'] . "</td>";
    echo "<td>" . $row['PromptCount'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
