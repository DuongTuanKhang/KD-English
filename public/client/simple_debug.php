<?php
require_once '../../configs/config.php';

// Simple session for demo
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'user001';
    $_SESSION['user_name'] = 'Demo User';
}

echo "<h1>Simple Debug - Writing Topics</h1>";

// Test 1: Check all topics
echo "<h2>1. All Writing Topics:</h2>";
$sql1 = "SELECT MaChuDe, TenChuDe, MoTa, TrangThai FROM writing_topics ORDER BY ThoiGianTao DESC";
$result1 = $conn->query($sql1);
echo "<table border='1'>";
echo "<tr><th>MaChuDe</th><th>TenChuDe</th><th>MoTa</th><th>TrangThai</th></tr>";
while ($row = $result1->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['MaChuDe'] . "</td>";
    echo "<td>" . $row['TenChuDe'] . "</td>";
    echo "<td>" . $row['MoTa'] . "</td>";
    echo "<td>" . $row['TrangThai'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test 2: Check all prompts
echo "<h2>2. All Writing Prompts:</h2>";
$sql2 = "SELECT MaDeBai, MaChuDe, TieuDe, TrangThai FROM writing_prompts ORDER BY MaChuDe, ThoiGianTao";
$result2 = $conn->query($sql2);
echo "<table border='1'>";
echo "<tr><th>MaDeBai</th><th>MaChuDe</th><th>TieuDe</th><th>TrangThai</th></tr>";
while ($row = $result2->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['MaDeBai'] . "</td>";
    echo "<td>" . $row['MaChuDe'] . "</td>";
    echo "<td>" . $row['TieuDe'] . "</td>";
    echo "<td>" . $row['TrangThai'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test 3: Test the fixed query
echo "<h2>3. FIXED Query Result:</h2>";
$sql3 = "SELECT 
    wt.MaChuDe,
    wt.TenChuDe,
    wt.MoTa,
    COUNT(wp.MaDeBai) as total_exercises,
    (SELECT wp3.MaDeBai FROM writing_prompts wp3 
     WHERE wp3.MaChuDe = wt.MaChuDe AND wp3.TrangThai = 1 
     ORDER BY wp3.ThoiGianTao ASC LIMIT 1) as first_exercise_id
FROM writing_topics wt
LEFT JOIN writing_prompts wp ON wt.MaChuDe = wp.MaChuDe AND wp.TrangThai = 1
WHERE wt.TrangThai = 1
GROUP BY wt.MaChuDe
ORDER BY wt.ThoiGianTao DESC";

$result3 = $conn->query($sql3);
echo "<table border='1'>";
echo "<tr><th>MaChuDe</th><th>TenChuDe</th><th>MoTa</th><th>total_exercises</th><th>first_exercise_id</th></tr>";
while ($row = $result3->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['MaChuDe'] . "</td>";
    echo "<td>" . $row['TenChuDe'] . "</td>";
    echo "<td>" . $row['MoTa'] . "</td>";
    echo "<td>" . $row['total_exercises'] . "</td>";
    echo "<td>" . ($row['first_exercise_id'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
