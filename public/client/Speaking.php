<?php
require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

$title = 'Speaking Practice | ' . $Database->site("TenWeb");

// Check login
if (!isset($_SESSION["account"])) {
    redirect(BASE_URL("Auth/DangNhap"));
}

$user = $Database->get_row("SELECT * FROM nguoidung WHERE taikhoan = '" . $_SESSION["account"] . "'");
if (!$user) {
    redirect(BASE_URL("Auth/DangNhap"));
}

// Get lesson parameters
$maKhoaHoc = isset($_GET['maKhoaHoc']) ? (int)$_GET['maKhoaHoc'] : null;
$maBaiHoc = isset($_GET['maBaiHoc']) ? (int)$_GET['maBaiHoc'] : null;

// Get course and lesson info
$khoaHoc = null;
$baiHoc = null;
if ($maKhoaHoc && $maBaiHoc) {
    $khoaHoc = $Database->get_row("SELECT * FROM khoahoc WHERE MaKhoaHoc = '$maKhoaHoc'");
    $baiHoc = $Database->get_row("SELECT * FROM baihoc WHERE MaKhoaHoc = '$maKhoaHoc' AND MaBaiHoc = '$maBaiHoc'");
}

// Get speaking topics with their lessons
try {
    $whereClause = "st.TrangThai = 1";
    
    // If we have lesson parameters, filter by them
    if ($maKhoaHoc && $maBaiHoc) {
        $whereClause .= " AND (st.MaKhoaHoc = $maKhoaHoc AND st.MaBaiHoc = $maBaiHoc)";
    }
    
    $topics = $Database->get_list("
        SELECT DISTINCT st.*, 
               (SELECT COUNT(*) FROM speaking_lessons sl WHERE sl.MaChuDe = st.MaChuDe AND sl.TrangThai = 1) as SoLuongBai,
               (SELECT COUNT(*) FROM speaking_results sr 
                JOIN speaking_lessons sl ON sr.MaBaiSpeaking = sl.MaBaiSpeaking 
                WHERE sl.MaChuDe = st.MaChuDe AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DaLam,
               (SELECT AVG(TongDiem) FROM speaking_results sr 
                JOIN speaking_lessons sl ON sr.MaBaiSpeaking = sl.MaBaiSpeaking 
                WHERE sl.MaChuDe = st.MaChuDe AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DiemTrungBinh
        FROM speaking_topics st 
        WHERE $whereClause
        GROUP BY st.MaChuDe
        ORDER BY st.ThuTu ASC, st.MaChuDe ASC
    ");
    
    // Remove duplicate topics based on MaChuDe
    $uniqueTopics = [];
    $seenTopics = [];
    
    foreach ($topics as $topic) {
        if (!in_array($topic['MaChuDe'], $seenTopics)) {
            $uniqueTopics[] = $topic;
            $seenTopics[] = $topic['MaChuDe'];
        }
    }
    $topics = $uniqueTopics;
    
    // Get lessons for each topic
    foreach ($topics as &$topic) {
        $lessons = $Database->get_list("
            SELECT DISTINCT sl.*, 
                   (SELECT COUNT(*) FROM speaking_results sr WHERE sr.MaBaiSpeaking = sl.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DaLam,
                   (SELECT MAX(TongDiem) FROM speaking_results sr WHERE sr.MaBaiSpeaking = sl.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DiemCaoNhat,
                   (SELECT AVG(TongDiem) FROM speaking_results sr WHERE sr.MaBaiSpeaking = sl.MaBaiSpeaking AND sr.TaiKhoan = '" . $user['TaiKhoan'] . "') as DiemTrungBinh
            FROM speaking_lessons sl 
            WHERE sl.MaChuDe = " . $topic['MaChuDe'] . " AND sl.TrangThai = 1 
            GROUP BY sl.MaBaiSpeaking
            ORDER BY sl.ThuTu ASC, sl.MaBaiSpeaking ASC
        ");
        
        // Remove duplicate lessons based on MaBaiSpeaking 
        $uniqueLessons = [];
        $seenLessons = [];
        
        foreach ($lessons as $lesson) {
            if (!in_array($lesson['MaBaiSpeaking'], $seenLessons)) {
                $uniqueLessons[] = $lesson;
                $seenLessons[] = $lesson['MaBaiSpeaking'];
            }
        }
        
        $topic['lessons'] = $uniqueLessons;
    }
} catch (Exception $e) {
    $topics = [];
}

require_once(__DIR__ . "/header.php");
?>

<style>
/* Global Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    overflow-x: hidden;
}

.speaking-container {
    width: 100%;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 50%, #6c5ce7 100%);
    min-height: 100vh;
    position: relative;
}

/* Animated Background */
.speaking-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    animation: backgroundMove 20s ease-in-out infinite;
    pointer-events: none;
}

@keyframes backgroundMove {
    0%, 100% { transform: translateX(0%) translateY(0%); }
    25% { transform: translateX(5%) translateY(-10%); }
    50% { transform: translateX(-5%) translateY(5%); }
    75% { transform: translateX(10%) translateY(-5%); }
}

/* Floating Elements */
.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.floating-element {
    position: absolute;
    color: rgba(255, 255, 255, 0.1);
    animation: float 15s infinite linear;
}

.floating-element:nth-child(1) {
    left: 10%;
    animation-delay: 0s;
    font-size: 20px;
}

.floating-element:nth-child(2) {
    left: 20%;
    animation-delay: 3s;
    font-size: 16px;
}

.floating-element:nth-child(3) {
    left: 80%;
    animation-delay: 6s;
    font-size: 24px;
}

.floating-element:nth-child(4) {
    left: 70%;
    animation-delay: 9s;
    font-size: 18px;
}

.floating-element:nth-child(5) {
    left: 50%;
    animation-delay: 12s;
    font-size: 22px;
}

@keyframes float {
    0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100px) rotate(360deg);
        opacity: 0;
    }
}

/* Content Wrapper */
.content-wrapper {
    position: relative;
    z-index: 10;
    padding: 40px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

        .speaking-header {
            text-align: center;
            margin-bottom: 80px;
            color: white;
            animation: fadeInUp 1s ease-out;
            position: relative;
        }

        .speaking-header::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #FFD700, #FFA500);
            border-radius: 2px;
            animation: expandLine 1.5s ease-out 0.5s both;
        }

        @keyframes expandLine {
            from { width: 0; }
            to { width: 100px; }
        }

        .speaking-title {
            font-size: 6.5rem;
            font-weight: 900;
            margin-bottom: 25px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
            background: linear-gradient(45deg, #FFD700, #FFA500, #FF6B6B, #4ECDC4);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 3s ease-in-out infinite;
            position: relative;
            letter-spacing: -2px;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .speaking-subtitle {
            font-size: 1.5rem;
            opacity: 0.95;
            margin-bottom: 35px;
            font-weight: 300;
            letter-spacing: 0.5px;
            line-height: 1.6;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }.speaking-breadcrumb {
    background: rgba(255,255,255,0.15);
    padding: 15px 30px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 15px;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.speaking-breadcrumb:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.speaking-breadcrumb a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.speaking-breadcrumb a:hover {
    color: #FFD700;
    text-decoration: none;
    transform: scale(1.05);
}

        /* Statistics Section */
        .speaking-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 35px;
            margin-bottom: 80px;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .stat-card {
            background: rgba(255,255,255,0.12);
            padding: 45px 30px;
            border-radius: 30px;
            text-align: center;
            color: white;
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #667eea, #764ba2, #FC466B, #3F5EFB);
            border-radius: 32px;
            z-index: -1;
            background-size: 300% 300%;
            animation: gradientMove 4s ease infinite;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::after {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-15px) scale(1.05);
            box-shadow: 0 25px 70px rgba(0,0,0,0.2);
            background: rgba(255,255,255,0.2);
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 10px 30px rgba(255,255,255,0.2);
        }

        .stat-icon i {
            font-size: 2rem;
            color: rgba(255,255,255,0.9);
        }

        .stat-number {
            font-size: 4rem;
            font-weight: 900;
            display: block;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }/* Topics Grid */
.topics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 35px;
    margin-bottom: 60px;
    animation: fadeInUp 1s ease-out 0.6s both;
}

.topic-card {
    background: rgba(255,255,255,0.95);
    border-radius: 30px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(20px);
}

.topic-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2, #FC466B, #3F5EFB);
    background-size: 300% 300%;
    animation: gradientMove 3s ease infinite;
}

@keyframes gradientMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.topic-card:hover {
    transform: translateY(-15px) scale(1.03);
    box-shadow: 0 30px 80px rgba(0,0,0,0.15);
}

.topic-level {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.topic-level.beginner {
    background: linear-gradient(45deg, #4CAF50, #45a049);
    color: white;
}

.topic-level.intermediate {
    background: linear-gradient(45deg, #FF9800, #F57C00);
    color: white;
}

.topic-level.advanced {
    background: linear-gradient(45deg, #F44336, #D32F2F);
    color: white;
}

.topic-header {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
}

.topic-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.topic-icon i {
    font-size: 2rem;
    color: white;
}

.topic-card:hover .topic-icon {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
}

.topic-title h3 {
    font-size: 1.6rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
}

.topic-eng {
    color: #7f8c8d;
    font-style: italic;
    font-weight: 500;
}

.topic-description {
    color: #5a6c7d;
    margin-bottom: 25px;
    line-height: 1.6;
    font-size: 1rem;
}

.topic-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 20px;
    border: 1px solid rgba(0,0,0,0.05);
}

.topic-stat {
    text-align: center;
    flex: 1;
}

.topic-stat-number {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 5px;
}

.topic-stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.progress-info {
    margin-bottom: 25px;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-weight: 600;
    color: #495057;
}

.progress-bar {
    height: 12px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 1s ease;
    border-radius: 10px;
    position: relative;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: progressShine 2s infinite;
}

@keyframes progressShine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.best-score {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    margin-top: 15px;
    box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
}

.topic-actions {
    display: flex;
    gap: 15px;
}

.btn-start-speaking {
    flex: 1;
    padding: 18px 25px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 700;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
}

.btn-start-speaking::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn-start-speaking:hover::before {
    left: 100%;
}

.btn-start-speaking:hover {
    background: linear-gradient(135deg, #5a6fd8, #6a4190);
    transform: translateY(-3px);
    text-decoration: none;
    color: white;
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
}

.btn-start-speaking.disabled {
    background: linear-gradient(135deg, #95a5a6, #7f8c8d);
    cursor: not-allowed;
    pointer-events: none;
}

        .no-topics {
            text-align: center;
            color: white;
            padding: 120px 40px;
            animation: fadeInUp 1s ease-out;
            position: relative;
            background: rgba(255,255,255,0.08);
            border-radius: 40px;
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255,255,255,0.15);
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 30px 80px rgba(0,0,0,0.1);
        }

        .no-topics::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #667eea, #764ba2, #FC466B, #3F5EFB);
            border-radius: 42px;
            z-index: -1;
            background-size: 300% 300%;
            animation: gradientMove 4s ease infinite;
        }

        .no-topics-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 40px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            animation: floatIcon 3s ease-in-out infinite;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(5deg); }
            66% { transform: translateY(5px) rotate(-3deg); }
        }

        .no-topics i {
            font-size: 3.5rem;
            color: rgba(255,255,255,0.9);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.1); opacity: 1; }
        }

        .no-topics h3 {
            font-size: 2.8rem;
            margin-bottom: 25px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFD700, #FFA500, #FF6B6B);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: none;
            letter-spacing: -1px;
        }

        .no-topics p {
            font-size: 1.4rem;
            opacity: 0.85;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.7;
            font-weight: 400;
        }

        .no-topics-action {
            margin-top: 40px;
        }

        .btn-refresh {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 32px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(20px);
        }

        .btn-refresh:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
            transform: translateY(-3px) scale(1.05);
            text-decoration: none;
            color: white;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            border-color: rgba(255,255,255,0.4);
        }/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .content-wrapper {
        padding: 20px 15px;
    }
    
    .speaking-title {
        font-size: 2.5rem;
    }
    
    .topics-grid {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .topic-card {
        padding: 30px 25px;
    }
    
    .topic-header {
        flex-direction: column;
        text-align: center;
    }
    
    .topic-icon {
        margin-right: 0;
        margin-bottom: 20px;
    }
    
    .topic-level {
        position: static;
        align-self: center;
        margin-bottom: 20px;
    }
    
    .topic-stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .speaking-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .stat-card {
        padding: 25px 20px;
    }
    
    .stat-number {
        font-size: 2.5rem;
    }
}

@media (max-width: 480px) {
    .speaking-title {
        font-size: 2rem;
    }
    
    .speaking-stats {
        grid-template-columns: 1fr;
    }
    
    .topics-grid {
        grid-template-columns: 1fr;
    }
    
    .topic-card {
        padding: 25px 20px;
    }
}
    padding: 30px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.topic-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.15);
}

.topic-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #FF6B6B, #4ECDC4, #45B7D1, #96CEB4);
    background-size: 300% 300%;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.topic-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.topic-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    background: linear-gradient(45deg, #667eea, #764ba2);
}

.topic-title {
    flex: 1;
}

.topic-title h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin: 0 0 5px 0;
}

.topic-title .topic-eng {
    font-size: 1.1rem;
    color: #666;
    font-style: italic;
}

.topic-level {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}

.topic-level.beginner {
    background: linear-gradient(45deg, #4CAF50, #8BC34A);
}

.topic-level.intermediate {
    background: linear-gradient(45deg, #FF9800, #FFC107);
}

.topic-level.advanced {
    background: linear-gradient(45deg, #F44336, #E91E63);
}

.topic-description {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.topic-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.topic-stat {
    text-align: center;
}

.topic-stat-number {
    font-size: 1.3rem;
    font-weight: bold;
    color: #333;
    display: block;
}

.topic-stat-label {
    font-size: 0.8rem;
    color: #666;
}

.progress-info {
    margin-bottom: 20px;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.9rem;
    color: #666;
}

.progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.best-score {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 15px;
}

/* Lessons Section */
.lessons-section {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 2px solid #e9ecef;
}

.lessons-title {
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.lessons-title i {
    color: #667eea;
}

.lessons-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    margin-top: 25px;
}

@media (max-width: 1200px) {
    .lessons-list {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .lessons-list {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

@media (max-width: 480px) {
    .lessons-list {
        grid-template-columns: 1fr;
    }
}

.lesson-item {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 24px;
    border: 2px solid transparent;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
}

.lesson-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.lesson-item:hover {
    border-color: rgba(102, 126, 234, 0.3);
    box-shadow: 0 20px 50px rgba(102, 126, 234, 0.25);
    transform: translateY(-10px);
}

.lesson-item:hover::before {
    transform: scaleX(1);
}

.lesson-content {
    padding: 30px 25px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.lesson-title {
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 20px;
    font-size: 1.6rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 72px;
    letter-spacing: -0.5px;
}

.lesson-meta {
    margin-bottom: 22px;
    padding: 18px 20px;
    background: linear-gradient(135deg, #f8f9ff 0%, #e9ecff 100%);
    border-radius: 16px;
    border: 1px solid rgba(102, 126, 234, 0.15);
}

.lesson-meta small {
    font-size: 1.05rem;
    color: #4a5568;
    line-height: 1.7;
    display: block;
    font-weight: 500;
}

.lesson-stats-wrapper {
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.lesson-difficulty,
.lesson-score {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 1.05rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.lesson-difficulty {
    background: linear-gradient(135deg, #fff5eb 0%, #ffe9d5 100%);
    color: #d97706;
    border: 1px solid rgba(217, 119, 6, 0.2);
}

.lesson-difficulty:hover {
    background: linear-gradient(135deg, #ffe9d5 0%, #ffd9b3 100%);
}

.lesson-difficulty i {
    font-size: 1.2rem;
}

.lesson-score {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
    border: 1px solid rgba(5, 150, 105, 0.2);
}

.lesson-score:hover {
    background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
}

.lesson-score i {
    font-size: 1.2rem;
}

.lesson-footer {
    padding: 0 25px 30px 25px;
    margin-top: auto;
    display: flex;
    flex-direction: row;
    gap: 7px;
    align-items: center;
    justify-content: flex-start;
}

.lesson-status.completed {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    font-size: 1rem;
    font-weight: 700;
    padding: 10px 20px;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}

.lesson-status.completed i {
    font-size: 1.1rem;
}

.btn-lesson {
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    cursor: pointer;
    flex-shrink: 0;
    white-space: nowrap;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn-lesson::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-lesson:hover::before {
    width: 300px;
    height: 300px;
}

.btn-lesson:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-4px);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
    color: white;
    text-decoration: none;
}

.btn-lesson:active {
    transform: translateY(-2px);
}

.btn-lesson i {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.btn-lesson:hover i {
    transform: translateX(5px);
}

.no-lessons {
    margin-top: 20px;
    text-align: center;
}

.topic-actions {
    display: flex;
    gap: 10px;
}

.btn-start-speaking {
    flex: 1;
    padding: 12px 20px;
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-size: 1.0rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-start-speaking:hover {
    background: linear-gradient(45deg, #5a6fd8, #6a4190);
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

.btn-start-speaking.disabled {
    background: #ccc;
    cursor: not-allowed;
    pointer-events: none;
}

.no-topics {
    text-align: center;
    color: white;
    padding: 60px 20px;
}

.no-topics i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.7;
}

.no-topics h3 {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.no-topics p {
    font-size: 1.1rem;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .speaking-container {
        padding: 15px;
    }
    
    .speaking-title {
        font-size: 2rem;
    }
    
    .topics-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .topic-card {
        padding: 20px;
    }
    
    .topic-header {
        flex-direction: column;
        text-align: center;
    }
    
    .topic-level {
        position: static;
        align-self: center;
        margin-bottom: 15px;
    }
    
    .topic-stats {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<div class="speaking-container">
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-element"><i class="fas fa-microphone"></i></div>
        <div class="floating-element"><i class="fas fa-volume-up"></i></div>
        <div class="floating-element"><i class="fas fa-headphones"></i></div>
        <div class="floating-element"><i class="fas fa-music"></i></div>
        <div class="floating-element"><i class="fas fa-wave-square"></i></div>
    </div>

    <div class="content-wrapper">
        <!-- Header -->
        <div class="speaking-header">
            <h1 class="speaking-title">🎤 Speaking Practice</h1>
            <?php if ($khoaHoc && $baiHoc): ?>
            <p class="speaking-subtitle">
                Luyện phát âm tiếng Anh - <?= htmlspecialchars($khoaHoc['TenKhoaHoc']) ?> > <?= htmlspecialchars($baiHoc['TenBaiHoc']) ?>
            </p>
            <div class="speaking-breadcrumb">
                <a href="<?= BASE_URL("Page/Home") ?>"><i class="fas fa-home"></i></a>
                <span>/</span>
                <a href="<?= BASE_URL("Page/KhoaHoc/$maKhoaHoc") ?>"><?= htmlspecialchars($khoaHoc['TenKhoaHoc']) ?></a>
                <span>/</span>
                <a href="<?= BASE_URL("Page/BaiHoc/$maKhoaHoc/$maBaiHoc") ?>"><?= htmlspecialchars($baiHoc['TenBaiHoc']) ?></a>
                <span>/</span>
                <span>Speaking</span>
            </div>
            <?php else: ?>
            <p class="speaking-subtitle">Luyện phát âm tiếng Anh với AI thông minh</p>
            <?php endif; ?>
        </div>



        <!-- Topics Grid -->
        <?php if (count($topics) > 0): ?>
            <div class="topics-grid">
                <?php foreach ($topics as $topic): ?>
                <div class="topic-card">
                    <div class="topic-level <?= strtolower($topic['CapDo']) ?>">
                        <?= $topic['CapDo'] ?>
                    </div>
                    
                    <div class="topic-header">
                        <div class="topic-icon">
                            <i class="fas fa-microphone"></i>
                        </div>
                        <div class="topic-title">
                            <h3><?= htmlspecialchars($topic['TenChuDe']) ?></h3>
                            <div class="topic-eng"><?= htmlspecialchars($topic['TenChuDeEng']) ?></div>
                        </div>
                    </div>

                    <?php if ($topic['MoTa']): ?>
                    <div class="topic-description">
                        <?= htmlspecialchars($topic['MoTa']) ?>
                    </div>
                    <?php endif; ?>



                    <!-- Lessons List -->
                    <?php 
                    // Debug: Check lessons
                    // echo "<pre>Topic " . $topic['TenChuDe'] . " lessons: " . count($topic['lessons'] ?? []) . "</pre>";
                    ?>
                    <?php if (!empty($topic['lessons'])): ?>
                    <div class="lessons-section">
                        <h4 class="lessons-title">
                            <i class="fas fa-list"></i> 
                            Danh sách bài học (<?= count($topic['lessons']) ?>)
                        </h4>
                        <div class="lessons-list">
                            <?php foreach ($topic['lessons'] as $lesson): ?>
                            <div class="lesson-item">
                                <div class="lesson-content">
                                    <div class="lesson-title"><?= htmlspecialchars($lesson['TextMau'] ?? 'Lesson Content') ?></div>
                                    
                                    <div class="lesson-meta">
                                        <small>
                                            Cấp độ: <?= htmlspecialchars($lesson['CapDo'] ?? 'Beginner') ?> | 
                                            Điểm tối thiểu: <?= $lesson['DiemToiThieu'] ?? 50 ?>/100
                                        </small>
                                    </div>
                                    
                                    <div class="lesson-stats-wrapper">
                                        <div class="lesson-difficulty">
                                            <i class="fas fa-chart-line"></i>
                                            <?= isset($lesson['CapDo']) ? htmlspecialchars($lesson['CapDo']) : 'Beginner' ?>
                                        </div>
                                        <?php if ($lesson['DaLam'] > 0): ?>
                                        <div class="lesson-score">
                                            <i class="fas fa-trophy"></i>
                                            Best: <?= $lesson['DiemCaoNhat'] ? round($lesson['DiemCaoNhat'], 1) : '0' ?>/100
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="lesson-footer">
                                    <?php if ($lesson['DaLam'] > 0): ?>
                                    <span class="lesson-status completed">
                                        <i class="fas fa-check-circle"></i>
                                        Hoàn thành
                                    </span>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL("SpeakingLesson?lesson=" . $lesson['MaBaiSpeaking']) ?>" class="btn-lesson">
                                        <i class="fas fa-play-circle"></i>
                                        <?= $lesson['DaLam'] > 0 ? 'Luyện lại' : 'Bắt đầu' ?>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="no-lessons">
                        <div class="btn-start-speaking disabled">
                            <i class="fas fa-hourglass-half"></i> 
                            Chưa có bài học
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-topics">
                <div class="no-topics-icon">
                    <i class="fas fa-microphone-slash"></i>
                </div>
                <h3>Chưa có chủ đề Speaking</h3>
                <p>Hệ thống đang được cập nhật với nhiều chủ đề thú vị. Vui lòng quay lại sau để trải nghiệm tính năng mới!</p>
                <div class="no-topics-action">
                    <a href="javascript:location.reload()" class="btn-refresh">
                        <i class="fas fa-sync-alt"></i>
                        Tải lại trang
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Animate topic cards on load
    $('.topic-card').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(50px) scale(0.9)'
        }).delay(index * 150).animate({
            'opacity': '1'
        }, 800, function() {
            $(this).css('transform', 'translateY(0) scale(1)');
        });
    });
    
    // Animate stat cards
    $('.stat-card').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(30px)'
        }).delay(index * 100).animate({
            'opacity': '1'
        }, 600, function() {
            $(this).css('transform', 'translateY(0)');
        });
    });
    
    // Advanced hover effects for topic cards
    $('.topic-card').hover(
        function() {
            $(this).find('.topic-icon').css({
                'transform': 'scale(1.15) rotate(15deg)',
                'box-shadow': '0 20px 50px rgba(102, 126, 234, 0.5)'
            });
            
            // Animate progress bars
            $(this).find('.progress-fill').css('animation', 'progressPulse 1s ease-in-out');
        },
        function() {
            $(this).find('.topic-icon').css({
                'transform': 'scale(1) rotate(0deg)',
                'box-shadow': '0 10px 30px rgba(102, 126, 234, 0.3)'
            });
            
            $(this).find('.progress-fill').css('animation', 'none');
        }
    );
    
    // Button click effects
    $('.btn-start-speaking').on('click', function(e) {
        if (!$(this).hasClass('disabled')) {
            // Create ripple effect
            const button = $(this);
            const ripple = $('<span class="ripple"></span>');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.css({
                width: size + 'px',
                height: size + 'px',
                left: x + 'px',
                top: y + 'px',
                position: 'absolute',
                borderRadius: '50%',
                background: 'rgba(255, 255, 255, 0.3)',
                transform: 'scale(0)',
                animation: 'ripple 0.6s linear',
                pointerEvents: 'none'
            });
            
            button.css('position', 'relative').append(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        }
    });
    
    // Parallax effect for floating elements
    $(window).on('scroll', function() {
        const scrolled = $(this).scrollTop();
        $('.floating-element').each(function() {
            const rate = scrolled * -0.5;
            $(this).css('transform', 'translateY(' + rate + 'px)');
        });
    });
    
    // Number counter animation for stats
    $('.stat-number').each(function() {
        const $this = $(this);
        const finalNumber = parseInt($this.text()) || 0;
        
        if (finalNumber > 0) {
            $({ counter: 0 }).animate({ counter: finalNumber }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.ceil(this.counter));
                },
                complete: function() {
                    $this.text(finalNumber);
                }
            });
        }
    });
    
    // Smooth scroll for internal links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });
    
    // Progress bar animation
    $('.progress-fill').each(function() {
        const $this = $(this);
        const width = $this.css('width');
        $this.css('width', '0').animate({
            width: width
        }, 1500, 'easeOutCubic');
    });
});

// CSS Animations for ripple effect
const rippleStyle = `
<style>
@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

@keyframes progressPulse {
    0%, 100% { 
        box-shadow: 0 0 5px rgba(102, 126, 234, 0.5); 
    }
    50% { 
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.8); 
    }
}

.ripple {
    pointer-events: none;
}
</style>
`;

$('head').append(rippleStyle);
</script>

<?php
require_once(__DIR__ . "/footer.php");
?>