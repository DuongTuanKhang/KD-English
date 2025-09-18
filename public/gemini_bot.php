<?php
header('Content-Type: application/json; charset=utf-8');

// Nếu chưa login thì chặn (tùy bạn muốn giữ checkLogin hay bỏ)
session_start();
if (!isset($_SESSION["account"])) {
    echo json_encode(["ok" => false, "msg" => "Bạn chưa đăng nhập"]);
    exit;
}

// === Nhận dữ liệu từ AJAX ===
$prompt = $_POST["prompt"] ?? "";
if (trim($prompt) === "") {
    echo json_encode(["ok" => false, "msg" => "Không có nội dung"]);
    exit;
}

// === Gọi Gemini API ===
// Lấy API key từ config (hoặc khai báo tay ở đây)
$apiKey = "AIzaSyBfkI0hnNo-FqyuqfAxGwg11tm30kbaZ4Q";

$url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . $apiKey;


// Tạo body request
$body = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
]);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    echo json_encode(["ok" => false, "msg" => curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

$data = json_decode($response, true);

// Gemini trả về text trong candidates[0].content.parts[0].text
$text = $data["candidates"][0]["content"]["parts"][0]["text"] ?? null;

if ($httpcode == 200 && $text) {
    echo json_encode(["ok" => true, "text" => $text], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["ok" => false, "msg" => "Gemini trả về lỗi", "debug" => $data]);
}
