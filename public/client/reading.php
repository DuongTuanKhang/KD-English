<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Reading Practice - WebNgoaiNgu';

// Check if user is logged in
if (!isset($_SESSION['account'])) {
    header("Location: " . BASE_URL("Page/Login"));
    exit();
}

// Get lesson info from URL parameters
$maKhoaHoc = isset($_GET['maKhoaHoc']) ? (int) $_GET['maKhoaHoc'] : 1;
$maBaiHoc = isset($_GET['maBaiHoc']) ? (int) $_GET['maBaiHoc'] : null; // Don't default to 1

// Get lesson details - only if we have a specific lesson
$khoaHoc = null;
if ($maBaiHoc) {
    $khoaHoc = $Database->get_row("
        SELECT bh.*, kh.TenKhoaHoc 
        FROM baihoc bh 
        JOIN khoahoc kh ON bh.MaKhoaHoc = kh.MaKhoaHoc 
        WHERE bh.MaKhoaHoc = '$maKhoaHoc' AND bh.MaBaiHoc = '$maBaiHoc'
    ");
}

if (!$khoaHoc) {
    header("Location: " . BASE_URL("Page/Home"));
    exit();
}

// Map lesson IDs to topics - MUST match exactly with admin interface
$topicMapping = [
    1 => 'Traffic',
    2 => 'Food',
    3 => 'Education',
    4 => 'Family',
    5 => 'Work',
    6 => 'Hobbie',
    7 => 'Technology',
    8 => 'Activities'
];

$currentTopic = ($maBaiHoc && isset($topicMapping[$maBaiHoc])) ? $topicMapping[$maBaiHoc] : null;

// Get Reading lessons - if we have specific topic, filter by it; otherwise get all
$readingLessons = [];

try {
    if ($maBaiHoc && $currentTopic) {
        // Specific topic - get readings for this topic only
        error_log("DEBUG: Searching for readings in lesson $maBaiHoc, course $maKhoaHoc, topic $currentTopic");

        $readingLessons = $Database->get_list("
            SELECT 
                MaBaiDoc,
                TieuDe,
                NoiDungBaiDoc,
                MucDo,
                ChuDe,
                TrangThai,
                ThoiGianTao,
                ThoiGianLam,
                MaBaiHoc,
                MaKhoaHoc
            FROM reading_lessons 
            WHERE ChuDe = '$currentTopic' AND (TrangThai = 1 OR TrangThai = 'active')
            ORDER BY ThuTu ASC, ThoiGianTao ASC
        ");
    } else {
        // No specific topic - get all topics for selection
        error_log("DEBUG: Getting all topics for selection, course $maKhoaHoc");
        $readingLessons = []; // Will show topic selection instead
    }

    // Debug: Check what we got
    if ($currentTopic) {
        error_log("Topic: $currentTopic, Found " . count($readingLessons) . " reading lessons");
        foreach ($readingLessons as $lesson) {
            error_log("Lesson ID: " . $lesson['MaBaiDoc'] . ", Title: " . $lesson['TieuDe'] . ", Status: " . $lesson['TrangThai']);
        }
    }

    if ($currentTopic) {
        error_log("DEBUG: Found " . count($readingLessons) . " lessons for topic $currentTopic");

        // Get questions for each lesson
        for ($i = 0; $i < count($readingLessons); $i++) {
            $questions = $Database->get_list("
                SELECT * FROM reading_questions 
                WHERE MaBaiDoc = " . $readingLessons[$i]['MaBaiDoc'] . "
                ORDER BY ThuTu ASC
            ");
            $readingLessons[$i]['questions'] = $questions;
            $readingLessons[$i]['SoCauHoi'] = count($questions);
        }
    }

} catch (Exception $e) {
    // Log error but don't show fallback data
    error_log("Reading error: " . $e->getMessage());
    $readingLessons = [];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= BASE_URL('assets/css/reading.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>

<body>

    <style>
        /* =====================================
   READING PRACTICE - GIAO DIỆN ĐƠN GIẢN ĐẸP
   ===================================== */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f7fa;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            width: 100vw;
            overflow-x: hidden;
        }

        /* Đảm bảo scroll luôn hoạt động */
        body.reading-active {
            overflow-y: auto;
        }

        /* Ẩn sidebar và navigation */
        .nav-container,
        .navigation,
        .sidebar,
        .menu-right,
        .navigation-mobile,
        .grid .row .nav-container,
        .main-page .nav-container,
        .left-sidebar,
        *[class*="sidebar"],
        *[class*="navigation"],
        *[class*="nav-"] {
            display: none !important;
            visibility: hidden !important;
        }

        .grid,
        .row,
        .main-page {
            display: block !important;
            width: 100% !important;
        }

        /* Container chính - Full màn hình */
        .reading-container-fullwidth {
            min-height: 100vh;
            padding: 0;
            width: 100vw !important;
            background: #f5f7fa;
            position: relative;
            margin: 0;
        }

        .reading-container {
            max-width: 100%;
            margin: 0;
            padding: 20px;
        }

        /* Khi reading practice active, container full width */
        .reading-practice.active {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
        }

        /* Full width khi làm bài */
        .reading-practice .reading-container {
            max-width: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Header đơn giản */
        .reading-header {
            background: white;
            border-radius: 16px;
            padding: 40px;
            margin: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8ecef;
            position: relative;
            overflow: hidden;
        }

        .reading-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745);
        }

        /* Breadcrumb được tạo kiểu đẹp */
        .breadcrumb {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 15px;
            font-weight: 500;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            background: linear-gradient(135deg, #f8f9ff, #e6f3ff);
            border-radius: 12px;
            border: 2px solid #e3f2fd;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .breadcrumb a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .breadcrumb a:hover {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
            border-color: #007bff;
        }

        .breadcrumb a:hover::before {
            left: 100%;
        }

        .breadcrumb span {
            color: #2c3e50;
            font-weight: 700;
            font-size: 16px;
            background: linear-gradient(135deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            padding: 12px 20px;
            background-color: #e8f5e9;
            border-radius: 12px;
            border: 2px solid #c8e6c9;
        }

        /* Title section */
        .reading-title {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .reading-title h1 {
            color: #2c3e50;
            font-size: 2.8em;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #007bff, #6610f2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .reading-title p {
            color: #6c757d;
            font-size: 1.2em;
            margin-bottom: 30px;
            line-height: 1.5;
            font-weight: 500;
        }

        /* Topic info badges - Layout ngang đẹp hơn */
        .topic-info {
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .topic-badge {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 14px 28px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1em;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .topic-badge::before {
            content: '📚';
            margin-right: 8px;
            font-size: 1.2em;
        }

        .topic-badge:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
        }

        .lesson-count {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 14px 28px;
            border-radius: 30px;
            font-size: 1em;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .lesson-count::before {
            content: '🎯';
            margin-right: 8px;
            font-size: 1.2em;
        }

        .lesson-count:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        /* Content sections */
        .reading-content>div {
            display: none;
        }

        .reading-content>div.active {
            display: block;
        }

        /* Reading practice ẩn mặc định */
        .reading-practice {
            display: none;
        }

        .reading-practice.active {
            display: grid !important;
        }

        /* Section title */
        .reading-list h2 {
            color: #2c3e50;
            font-size: 2em;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }

        .reading-list h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #007bff, #28a745);
            border-radius: 2px;
        }

        /* Grid layout cho reading items */
        .reading-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            padding: 0;
        }

        @media (max-width: 768px) {
            .reading-items {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        /* Reading item cards - Design đẹp và hiện đại */
        .reading-item {
            background: white;
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 30px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            background-image: linear-gradient(white, white), linear-gradient(135deg, #007bff, #28a745);
            background-origin: border-box;
            background-clip: padding-box, border-box;
        }

        .reading-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .reading-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-image: linear-gradient(135deg, #007bff, #28a745) 1;
        }

        .reading-item:hover::before {
            left: 100%;
        }

        /* Item number badge */
        .reading-item-number {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1em;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            border: 3px solid white;
            z-index: 2;
        }

        /* Item header */
        .reading-item-header {
            margin-bottom: 15px;
            padding-right: 40px;
        }

        .reading-item-level {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75em;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .level-beginner {
            background: #d4edda;
            color: #155724;
        }

        .level-intermediate {
            background: #fff3cd;
            color: #856404;
        }

        .level-advanced {
            background: #f8d7da;
            color: #721c24;
        }

        .level-hobbie {
            background: #e2e3f1;
            color: #6610f2;
        }

        .level-traffic {
            background: #d1ecf1;
            color: #0c5460;
        }

        .level-food {
            background: #ffeaa7;
            color: #8c6500;
        }

        .reading-item-header h3 {
            color: #333;
            font-size: 1.3em;
            font-weight: 600;
            line-height: 1.3;
            margin: 0;
        }

        /* Preview text */
        .reading-preview {
            color: #666;
            font-size: 0.95em;
            line-height: 1.5;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid #007bff;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        /* Meta information */
        .reading-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .reading-meta span {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.85em;
            font-weight: 500;
            gap: 5px;
        }

        .reading-meta i {
            color: #007bff;
        }

        /* Start button */
        .btn-start {
            width: 100%;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-start::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-start:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
        }

        .btn-start:hover::before {
            left: 100%;
        }

        /* No reading message */
        .no-reading {
            text-align: center;
            padding: 50px 30px;
            background: white;
            border-radius: 12px;
            margin: 30px auto;
            max-width: 500px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #e1e5e9;
        }

        .no-reading i {
            font-size: 3em;
            color: #6c757d;
            margin-bottom: 20px;
            display: block;
        }

        .no-reading h3 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .no-reading p {
            color: #666;
            font-size: 1em;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .no-reading .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .no-reading .btn:hover {
            background: #0056b3;
        }

        /* Reading Practice Layout - Full màn hình 100% */
        .reading-practice {
            display: none;
        }

        .reading-practice.active {
            display: grid !important;
            grid-template-columns: 50% 50%;
            gap: 0;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            background: white;
            border: none;
            outline: none;
        }

        @media (max-width: 768px) {
            .reading-practice.active {
                grid-template-columns: 1fr;
                grid-template-rows: 50% 50%;
                width: 100vw;
                height: 100vh;
            }

            .reading-passage {
                height: 50vh;
                max-height: 50vh;
                border-right: none;
                border-bottom: 2px solid #ddd;
            }

            .reading-questions {
                height: 50vh;
                max-height: 50vh;
            }
        }

        .reading-passage {
            background: white;
            border-radius: 0;
            padding: 30px;
            box-shadow: none;
            border: none;
            border-right: 2px solid #ddd;
            height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            position: relative;
        }

        .passage-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }

        .btn-back {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            margin-right: 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(108, 117, 125, 0.3);
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #5a6268, #495057);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }

        .passage-info h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.6em;
            font-weight: 700;
            line-height: 1.3;
        }

        .current-topic {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 6px 15px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 600;
            margin-top: 10px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .passage-content {
            line-height: 1.8;
            font-size: 1.1em;
            color: #2c3e50;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 30px;
            border-radius: 15px;
            border-left: 5px solid #007bff;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .reading-questions {
            background: #f5f5f5;
            border-radius: 0;
            padding: 30px;
            box-shadow: none;
            border: none;
            height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            position: relative;
        }

        .reading-questions h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.5em;
            font-weight: 700;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .reading-questions h3 .title-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reading-questions h3 i {
            color: #007bff;
            font-size: 1.2em;
        }

        /* Timer hiển thị thời gian làm bài */
        .reading-timer {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 10px rgba(255, 107, 107, 0.3);
            animation: pulse 2s infinite;
        }

        .reading-timer.warning {
            background: linear-gradient(135deg, #ff9f43, #f39c12);
            animation: fastPulse 1s infinite;
        }

        .reading-timer.danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            animation: fastPulse 0.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes fastPulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 3px 10px rgba(255, 107, 107, 0.3);
            }

            50% {
                transform: scale(1.08);
                box-shadow: 0 5px 20px rgba(255, 107, 107, 0.5);
            }
        }

        .question-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 5px solid #007bff;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .question-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .question-text {
            font-weight: 700;
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.1em;
            line-height: 1.4;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .question-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 18px;
            border-radius: 12px;
            background: white;
            border: 2px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .option-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .option-item:hover {
            border-color: #007bff;
            background: linear-gradient(135deg, #f8f9ff, #e6f3ff);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
        }

        .option-item:hover::before {
            left: 100%;
        }

        .option-item input[type="radio"] {
            margin: 0;
            width: 20px;
            height: 20px;
            accent-color: #007bff;
        }

        .option-item label {
            margin: 0;
            cursor: pointer;
            flex: 1;
            font-weight: 600;
            font-size: 1em;
            color: #2c3e50;
            line-height: 1.4;
        }

        .reading-actions {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 2px solid #f8f9fa;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1em;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268, #495057);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .reading-container-fullwidth {
                padding: 15px;
            }

            .reading-header {
                padding: 25px 20px;
                margin-bottom: 25px;
            }

            .reading-title h1 {
                font-size: 2.2em;
                margin-bottom: 15px;
            }

            .reading-title p {
                font-size: 1em;
                margin-bottom: 20px;
            }

            .topic-info {
                flex-direction: column;
                gap: 15px;
                margin-top: 15px;
            }

            .topic-badge,
            .lesson-count {
                padding: 12px 24px;
                font-size: 0.95em;
            }

            .reading-item {
                padding: 25px 20px;
            }

            .reading-item-number {
                width: 35px;
                height: 35px;
                font-size: 1em;
                top: 15px;
                right: 15px;
            }
        }
    </style>

    <div class="reading-container-fullwidth">
        <div class="reading-container">
            <div class="reading-header">
                <nav class="breadcrumb">
                    <a href="<?= BASE_URL("Page/BaiHoc/$maKhoaHoc/$maBaiHoc") ?>">← Quay lại bài học</a>
                    <span>Reading Practice - <?= $currentTopic ?></span>
                </nav>

                <div class="reading-title">
                    <h1>Reading Practice</h1>
                    <p>Chủ đề: <strong><?= $currentTopic ?></strong> | Bài học: <?= $khoaHoc['TenBaiHoc'] ?></p>
                    <div class="topic-info">
                        <span class="topic-badge"><?= $currentTopic ?></span>
                        <span class="lesson-count"><?= count($readingLessons) ?> bài Reading</span>
                    </div>
                </div>
            </div>

            <div class="reading-content">
                <!-- Reading List -->
                <div id="reading-list" class="reading-list active">
                    <?php if ($maBaiHoc && $currentTopic): ?>
                        <!-- Specific topic selected -->
                        <h2>Chọn bài Reading - Chủ đề: <?= $currentTopic ?></h2>

                        <?php if (count($readingLessons) > 0): ?>
                            <div class="reading-items">
                                <?php foreach ($readingLessons as $index => $lesson): ?>
                                    <div class="reading-item" onclick="startReading(<?= $lesson['MaBaiDoc'] ?>)">
                                        <div class="reading-item-number"><?= $index + 1 ?></div>
                                        <div class="reading-item-header">
                                            <span
                                                class="reading-item-level level-<?= strtolower($lesson['MucDo']) ?>"><?= $lesson['MucDo'] ?></span>
                                            <h3><?= htmlspecialchars($lesson['TieuDe']) ?></h3>
                                        </div>

                                        <div class="reading-preview">
                                            <?= htmlspecialchars(mb_substr(strip_tags($lesson['NoiDungBaiDoc']), 0, 150)) ?>...
                                        </div>

                                        <div class="reading-meta">
                                            <span><i class="fas fa-question-circle"></i> <?= $lesson['SoCauHoi'] ?> câu hỏi</span>
                                            <span><i class="fas fa-tag"></i> <?= $lesson['MucDo'] ?></span>
                                            <span><i class="fas fa-clock"></i>
                                                <?= isset($lesson['ThoiGianLam']) ? $lesson['ThoiGianLam'] . ' phút' : '15 phút' ?></span>
                                        </div>

                                        <button class="btn-start">
                                            <i class="fas fa-play"></i> Bắt đầu bài <?= $index + 1 ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-reading">
                                <i class="fas fa-book-open"></i>
                                <h3>Chưa có bài Reading</h3>
                                <p>Hiện tại chưa có bài Reading nào cho chủ đề <strong><?= $currentTopic ?></strong></p>
                                <p>Vui lòng liên hệ admin để thêm nội dung Reading cho chủ đề này.</p>
                                <a href="<?= BASE_URL("Page/BaiHoc/$maKhoaHoc/$maBaiHoc") ?>" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Quay lại bài học
                                </a>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- No specific topic selected - show all topics -->
                        <h2>Chọn chủ đề Reading Practice</h2>
                        <div class="topics-grid">
                            <?php foreach ($topicMapping as $lessonId => $topicName): ?>
                                <div class="topic-card"
                                    onclick="location.href='<?= BASE_URL("public/client/reading.php?maKhoaHoc=$maKhoaHoc&maBaiHoc=$lessonId") ?>'">
                                    <div class="topic-icon">
                                        <i class="fas fa-book-reader"></i>
                                    </div>
                                    <h3><?= $topicName ?></h3>
                                    <p>Lesson <?= $lessonId ?></p>
                                    <button class="btn-start">
                                        <i class="fas fa-arrow-right"></i> Vào chủ đề
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Reading Practice -->
                <div id="reading-practice" class="reading-practice">
                    <div class="reading-passage">
                        <div class="passage-header">
                            <button onclick="backToList()" class="btn-back">
                                <i class="fas fa-arrow-left"></i> Quay lại danh sách
                            </button>
                            <div class="passage-info">
                                <h2 id="reading-title"></h2>
                                <span class="current-topic"><?= $currentTopic ?></span>
                            </div>
                        </div>

                        <div class="passage-content" id="passage-content">
                            <!-- Reading content will be loaded here -->
                        </div>
                    </div>

                    <div class="reading-questions">
                        <h3>
                            <div class="title-left">
                                <i class="fas fa-question-circle"></i> Câu hỏi
                            </div>
                            <div class="reading-timer" id="reading-timer">
                                <i class="fas fa-clock"></i>
                                <span id="timer-display">15:00</span>
                            </div>
                        </h3>
                        <div id="questions-container">
                            <!-- Questions will be loaded here -->
                        </div>

                        <div class="reading-actions">
                            <button onclick="backToList()" class="btn btn-secondary">
                                <i class="fas fa-list"></i> Danh sách bài
                            </button>
                            <button onclick="submitReading()" class="btn btn-primary">
                                <i class="fas fa-check"></i> Nộp bài
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reading Result -->
                <div id="reading-result" class="reading-result">
                    <!-- Results will be shown here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentReadingLesson = null;
        let currentQuestions = [];
        let readingLessonsData = <?= json_encode($readingLessons) ?>;
        let timerInterval = null;
        let timeRemaining = 0;

        console.log('Reading system initialized');
        console.log('Available lessons:', readingLessonsData);

        function startReading(lessonId) {
            console.log('Starting reading lesson:', lessonId);

            // Find lesson data
            const lesson = readingLessonsData.find(l => l.MaBaiDoc == lessonId);

            if (!lesson) {
                alert('Không tìm thấy bài học!');
                return;
            }

            currentReadingLesson = lesson;
            currentQuestions = lesson.questions || [];

            showReadingPractice();
        }

        function showReadingPractice() {
            document.getElementById('reading-list').classList.remove('active');
            document.getElementById('reading-practice').classList.add('active');

            const header = document.querySelector('.reading-header');
            if (header) {
                header.style.display = 'none';
            }

            document.body.classList.add('reading-active');

            document.getElementById('reading-title').textContent = currentReadingLesson.TieuDe;
            document.getElementById('passage-content').innerHTML = currentReadingLesson.NoiDungBaiDoc.replace(/\n/g, '<br><br>');

            renderQuestions();
            startTimer();
        }

        function startTimer() {
            const timeLimit = currentReadingLesson.ThoiGianLam || 15;
            timeRemaining = timeLimit * 60;

            if (timerInterval) {
                clearInterval(timerInterval);
            }

            updateTimerDisplay();

            timerInterval = setInterval(() => {
                timeRemaining--;
                updateTimerDisplay();

                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    autoSubmitReading();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            const timerDisplay = document.getElementById('timer-display');
            if (timerDisplay) {
                timerDisplay.textContent = display;
            }
        }

        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        function autoSubmitReading() {
            alert('Hết thời gian! Bài thi sẽ được nộp tự động.');
            submitReading();
        }

        function renderQuestions() {
            const container = document.getElementById('questions-container');
            let html = '';

            if (currentQuestions.length === 0) {
                html = '<div style="text-align: center; padding: 20px; color: #666;">Chưa có câu hỏi cho bài này</div>';
            } else {
                currentQuestions.forEach((question, index) => {
                    html += `
                <div class="question-item">
                    <div class="question-text">
                        <strong>Câu ${index + 1}:</strong> ${question.CauHoi}
                    </div>
                    <div class="question-options">
                        <div class="option-item">
                            <input type="radio" id="q${index}_a" name="question_${index}" value="A">
                            <label for="q${index}_a">A. ${question.DapAnA}</label>
                        </div>
                        <div class="option-item">
                            <input type="radio" id="q${index}_b" name="question_${index}" value="B">
                            <label for="q${index}_b">B. ${question.DapAnB}</label>
                        </div>
                        <div class="option-item">
                            <input type="radio" id="q${index}_c" name="question_${index}" value="C">
                            <label for="q${index}_c">C. ${question.DapAnC}</label>
                        </div>
                        <div class="option-item">
                            <input type="radio" id="q${index}_d" name="question_${index}" value="D">
                            <label for="q${index}_d">D. ${question.DapAnD}</label>
                        </div>
                    </div>
                </div>
            `;
                });
            }

            container.innerHTML = html;
        }

        function backToList() {
            document.getElementById('reading-practice').classList.remove('active');
            document.getElementById('reading-result').classList.remove('active');
            document.getElementById('reading-list').classList.add('active');

            const header = document.querySelector('.reading-header');
            if (header) {
                header.style.display = 'block';
            }

            document.body.classList.remove('reading-active');
            stopTimer();
        }

        function submitReading() {
            stopTimer();

            if (currentQuestions.length === 0) {
                alert('Bài này chưa có câu hỏi để làm');
                return;
            }

            const answers = {};
            currentQuestions.forEach((question, index) => {
                const selectedOption = document.querySelector(`input[name="question_${index}"]:checked`);
                if (selectedOption) {
                    answers[index] = selectedOption.value;
                }
            });

            let correct = 0;
            let resultDetails = [];

            currentQuestions.forEach((question, index) => {
                const userAnswer = answers[index];
                const isCorrect = userAnswer === question.DapAnDung;
                if (isCorrect) correct++;

                resultDetails.push({
                    questionIndex: index + 1,
                    question: question.CauHoi,
                    userAnswer: userAnswer,
                    correctAnswer: question.DapAnDung,
                    isCorrect: isCorrect,
                    explanation: question.GiaiThich || 'Không có giải thích'
                });
            });

            const score = Math.round((correct / currentQuestions.length) * 100);
            showResult(score, correct, currentQuestions.length, resultDetails);
        }

        function showResult(score, correct, total, details) {
            localStorage.setItem('readingResult', JSON.stringify({
                score: score,
                correct: correct,
                total: total,
                questionDetails: details
            }));

            const currentParams = new URLSearchParams(window.location.search);
            const course = currentParams.get('maKhoaHoc') || 1;
            const topic = getCurrentTopicName();

            window.location.href = `reading_result.php?course=${course}&topic=${encodeURIComponent(topic)}&score=${score}&correct=${correct}&total=${total}`;
        }

        function getCurrentTopicName() {
            if (currentReadingLesson && currentReadingLesson.ChuDe) {
                return currentReadingLesson.ChuDe;
            }
            return '<?= $currentTopic ?>';
        }

        // Auto-start first reading lesson if we have specific lesson ID AND autostart parameter
        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($maBaiHoc && count($readingLessons) > 0 && isset($_GET['autostart']) && $_GET['autostart'] == '1'): ?>
                // If we have autostart=1 parameter, auto-start the first reading lesson
                console.log('Auto-starting first reading lesson for topic: <?= $currentTopic ?>');
                if (readingLessonsData && readingLessonsData.length > 0) {
                    startReading(readingLessonsData[0].MaBaiDoc);
                }
            <?php endif; ?>
        });
    </script>

</body>

</html>