<?php
require_once __DIR__ . '/../../configs/config.php';
require_once __DIR__ . '/../../configs/function.php';

// Get lesson ID from URL parameter 
$lessonId = isset($_GET['lesson']) ? (int)$_GET['lesson'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

$lesson = $Database->get_row("SELECT sl.*, st.TenChuDe, st.MaKhoaHoc, st.MaBaiHoc FROM speaking_lessons sl 
    LEFT JOIN speaking_topics st ON sl.MaChuDe = st.MaChuDe 
    WHERE sl.MaBaiSpeaking = '$lessonId' AND sl.TrangThai = 1");

if (!$lesson) {
    // Redirect to speaking page if lesson not found
    header("Location: " . BASE_URL('Speaking'));
    exit();
}

$title = 'AI Speech Master: ' . $lesson['TieuDe'] . ' | WebHocNgoaiNgu';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎤 AI Speech Master - <?= htmlspecialchars($lesson['TieuDe'] ?? 'Học phát âm') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
            --warning-gradient: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
            --text-dark: #2d3748;
            --text-light: #718096;
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 50%, #6c5ce7 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            color: var(--text-dark);
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            margin: 0;
            padding: 0;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
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
            z-index: -1;
        }

        @keyframes backgroundMove {
            0%, 100% { transform: translateX(0%) translateY(0%); }
            25% { transform: translateX(5%) translateY(-10%); }
            50% { transform: translateX(-5%) translateY(5%); }
            75% { transform: translateX(10%) translateY(-5%); }
        }

        .floating-orbs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite linear;
        }

        .orb:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .orb:nth-child(2) { width: 120px; height: 120px; right: 10%; animation-delay: 5s; }
        .orb:nth-child(3) { width: 60px; height: 60px; left: 50%; animation-delay: 10s; }
        .orb:nth-child(4) { width: 90px; height: 90px; right: 30%; animation-delay: 15s; }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }

        /* Header */
        .header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .lesson-header {
            text-align: center;
            color: white;
        }

        .lesson-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .lesson-topic {
            font-size: 16px;
            opacity: 0.8;
        }

        .ai-badge {
            background: var(--success-gradient);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
        }

        /* Main Content */
        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Target Section */
        .target-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .target-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--warning-gradient);
        }

        .target-word {
            font-size: 48px;
            font-weight: 900;
            color: white;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .target-phonetic {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.8);
            font-style: italic;
            margin-bottom: 25px;
        }

        .audio-controls {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 20px;
        }

        .speed-label {
            font-size: 16px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            margin-right: 10px;
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
        }

        .audio-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .audio-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .audio-btn:hover::before {
            transform: translateX(100%);
        }

        .audio-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-xl);
            background: rgba(255, 255, 255, 0.3);
        }

        .audio-btn.active {
            background: var(--success-gradient);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        .audio-btn.secondary {
            background: var(--primary-gradient);
        }

        .audio-btn.speed-slow {
            background: var(--danger-gradient);
        }

        .audio-btn.speed-medium-slow {
            background: var(--warning-gradient);
        }

        .audio-btn.speed-fast {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }

        .audio-btn.speed-very-fast {
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%);
        }

        /* Practice Grid */
        .practice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .practice-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .practice-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            color: white;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .card-icon {
            font-size: 24px;
        }

        /* Microphone Section */
        .mic-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .mic-button {
            width: 150px;
            height: 150px;
            border: none;
            border-radius: 50%;
            background: var(--danger-gradient);
            color: white;
            font-size: 60px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .mic-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mic-button:hover::before {
            opacity: 1;
        }

        .mic-button:hover {
            transform: scale(1.1);
        }

        .mic-button.recording {
            background: var(--success-gradient);
            animation: record-pulse 2s infinite;
        }

        @keyframes record-pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(17, 153, 142, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(17, 153, 142, 0);
            }
        }

        .mic-status {
            font-size: 18px;
            font-weight: 600;
            color: white;
            text-align: center;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .recording-indicator {
            display: none;
            align-items: center;
            gap: 8px;
            color: #11998e;
            font-weight: 700;
        }

        .recording-indicator.active {
            display: flex;
        }

        .record-dot {
            width: 10px;
            height: 10px;
            background: #11998e;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        /* Analysis Section */
        .analysis-placeholder {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
        }

        .analysis-icon {
            font-size: 40px;
            color: rgba(255, 255, 255, 0.3);
            margin-bottom: 15px;
        }

        /* Results Section */
        .results-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-xl);
            display: none;
            position: relative;
            overflow: hidden;
        }

        .results-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--success-gradient);
        }

        .results-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .results-title {
            font-size: 26px;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
        }

        .results-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .comparison-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid #11998e;
        }

        .comparison-label {
            font-size: 14px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .comparison-text {
            font-size: 16px;
            font-weight: 600;
            color: white;
            line-height: 1.5;
            word-wrap: break-word;
            margin-bottom: 15px;
        }

        .audio-playback {
            margin-top: 15px;
            text-align: center;
        }

        .playback-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .playback-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.2) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .playback-btn:hover::before {
            transform: translateX(100%);
        }

        .playback-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .playback-btn.recorded {
            background: var(--success-gradient);
        }

        .playback-btn:disabled {
            background: rgba(255, 255, 255, 0.3) !important;
            cursor: not-allowed;
            transform: none !important;
            opacity: 0.5;
        }

        .playback-btn:disabled:hover {
            transform: none !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
        }

        .playback-btn:disabled::before {
            display: none;
        }

        .playback-btn.playing {
            background: var(--danger-gradient);
            animation: pulse-play 1s infinite;
        }

        @keyframes pulse-play {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .score-display {
            background: var(--success-gradient);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .score-display::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .score-label {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .score-value {
            font-size: 48px;
            font-weight: 900;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .score-description {
            font-size: 16px;
            margin-top: 10px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: white;
        }

        .btn-primary {
            background: var(--primary-gradient);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--warning-gradient);
            box-shadow: var(--shadow-lg);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        /* Error Styling */
        .error-message {
            background: var(--danger-gradient);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            font-size: 16px;
            text-align: center;
            font-weight: 600;
        }

        /* Shortcuts Help */
        .shortcuts-help {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 15px;
            color: white;
            font-size: 12px;
            max-width: 300px;
            z-index: 1000;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .shortcuts-help:hover {
            opacity: 1;
        }

        .shortcuts-title {
            font-weight: 700;
            margin-bottom: 8px;
            text-align: center;
        }

        .shortcuts-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .shortcut-item {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        kbd {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
            color: white;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .practice-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .comparison-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .lesson-title {
                font-size: 24px;
            }
            
            .target-word {
                font-size: 36px;
            }
            
            .mic-button {
                width: 120px;
                height: 120px;
                font-size: 48px;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .target-section,
            .practice-card,
            .results-section {
                padding: 20px;
            }
            
            .audio-controls {
                gap: 8px;
                justify-content: center;
            }
            
            .audio-btn {
                padding: 8px 12px;
                font-size: 12px;
                gap: 4px;
            }
            
            .speed-label {
                font-size: 14px;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            .target-word {
                font-size: 28px;
            }
            
            .score-value {
                font-size: 36px;
            }
            
            .mic-button {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .audio-controls {
                gap: 6px;
                flex-direction: column;
                align-items: center;
            }
            
            .audio-btn {
                padding: 8px 15px;
                font-size: 11px;
                min-width: 80px;
                justify-content: center;
            }
            
            .speed-label {
                font-size: 13px;
                margin-bottom: 8px;
            }
            
            .playback-btn {
                padding: 6px 12px;
                font-size: 11px;
                gap: 4px;
            }
            
            .shortcuts-help {
                display: none;
            }
        }

        /* Focus states for accessibility */
        .mic-button:focus,
        .audio-btn:focus,
        .btn:focus,
        .back-button:focus {
            outline: 3px solid #fbbf24;
            outline-offset: 3px;
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --primary-gradient: linear-gradient(135deg, #0000ff 0%, #8b5cf6 100%);
                --success-gradient: linear-gradient(135deg, #00ff00 0%, #38ef7d 100%);
                --danger-gradient: linear-gradient(135deg, #ff0000 0%, #3f5efb 100%);
                --text-dark: #000000;
            }
        }
    </style>
</head>
<body>
    <div class="floating-orbs">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
    </div>

    <div class="app-container">
        <header class="header">
            <div class="header-content">
                <a href="<?= BASE_URL('Speaking?maKhoaHoc=' . ($lesson['MaKhoaHoc'] ?? 1) . '&maBaiHoc=' . ($lesson['MaBaiHoc'] ?? 1)) ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                
                <div class="lesson-header">
                    <h1 class="lesson-title"><?= htmlspecialchars($lesson['TieuDe'] ?? 'AI Speech Master') ?></h1>
                    <p class="lesson-topic">Chủ đề: <?= htmlspecialchars($lesson['TenChuDe'] ?? 'Activities') ?></p>
                </div>
                
                <div class="ai-badge">
                    <i class="fas fa-robot"></i>
                    AI Powered
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="target-section">
                <div class="target-word" id="targetWord"><?= htmlspecialchars($lesson['TextMau'] ?? 'Text', ENT_NOQUOTES, 'UTF-8') ?></div>
                <div class="audio-controls">
                    <div class="speed-label">🎵 Tốc độ phát âm:</div>
                    <button class="audio-btn speed-slow" onclick="playTargetAudio(0.3)" title="Phát âm rất chậm (Phím 1)">
                        <i class="fas fa-backward"></i>
                        Rất chậm
                    </button>
                    <button class="audio-btn speed-medium-slow" onclick="playTargetAudio(0.6)" title="Phát âm chậm (Phím 2)">
                        <i class="fas fa-play"></i>
                        Chậm
                    </button>
                    <button class="audio-btn secondary speed-normal active" onclick="playTargetAudio(1.0)" title="Phát âm bình thường (Phím 3 hoặc P)">
                        <i class="fas fa-volume-up"></i>
                        Bình thường
                    </button>
                    <button class="audio-btn speed-fast" onclick="playTargetAudio(1.4)" title="Phát âm nhanh (Phím 4)">
                        <i class="fas fa-forward"></i>
                        Nhanh
                    </button>
                    <button class="audio-btn speed-very-fast" onclick="playTargetAudio(1.8)" title="Phát âm rất nhanh (Phím 5)">
                        <i class="fas fa-fast-forward"></i>
                        Rất nhanh
                    </button>
                </div>
            </div>

            <div class="practice-grid">
                <div class="practice-card">
                    <h3 class="card-title">
                        <i class="fas fa-microphone card-icon"></i>
                        AI Recording
                    </h3>
                    <div class="mic-container">
                        <button class="mic-button" id="micButton" onclick="toggleRecording()">
                            <i class="fas fa-microphone" id="micIcon"></i>
                        </button>
                        <div class="mic-status" id="micStatus">� Nhấn các nút tốc độ để nghe phát âm, sau đó nhấn mic để luyện tập</div>
                        <div class="recording-indicator" id="recordingIndicator">
                            <div class="record-dot"></div>
                            Đang ghi âm...
                        </div>
                    </div>
                </div>

                <div class="practice-card">
                    <h3 class="card-title">
                        <i class="fas fa-brain card-icon"></i>
                        AI Analysis
                    </h3>
                    <div class="analysis-section" id="analysisSection">
                        <i class="fas fa-chart-line analysis-icon"></i>
                        <div class="analysis-placeholder">
                            <div>Chưa có dữ liệu phân tích</div>
                            <div style="margin-top: 8px; font-size: 14px; opacity: 0.8;">Hãy thử phát âm để nhận phản hồi từ AI</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="results-section" id="resultsSection">
                <div class="results-header">
                    <h3 class="results-title">🎯 Kết Quả Phân Tích AI</h3>
                    <p class="results-subtitle">Phản hồi chi tiết về phát âm của bạn</p>
                </div>

                <div class="comparison-grid">
                    <div class="comparison-item">
                        <div class="comparison-label">Văn bản gốc</div>
                        <div class="comparison-text" id="originalText"></div>
                        <div class="audio-playback">
                            <button class="playback-btn" onclick="playTargetAudio(1.0)" title="Nghe lại bản gốc">
                                <i class="fas fa-volume-up"></i>
                                Nghe bản gốc
                            </button>
                        </div>
                    </div>
                    <div class="comparison-item">
                        <div class="comparison-label">Bạn đã nói</div>
                        <div class="comparison-text" id="recognizedText"></div>
                        <div class="audio-playback">
                            <button class="playback-btn recorded" onclick="playRecordedAudio()" id="playRecordedBtn" title="Nghe lại giọng nói của bạn">
                                <i class="fas fa-play"></i>
                                Nghe giọng bạn
                            </button>
                        </div>
                    </div>
                </div>

                <div class="score-display">
                    <div class="score-label">🏆 Điểm Phát Âm AI</div>
                    <div class="score-value" id="scoreValue">0</div>
                    <div class="score-description" id="scoreDescription">Chưa có đánh giá</div>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="tryAgain()">
                        <i class="fas fa-redo"></i>
                        Thử lại
                    </button>
                    <a href="<?= BASE_URL('Speaking?maKhoaHoc=' . $lesson['MaKhoaHoc'] . '&maBaiHoc=3') ?>" class="btn btn-secondary">
                        <i class="fas fa-list"></i>
                        Bài khác
                    </a>
                </div>
            </div>
        </main>
        
        <!-- Keyboard Shortcuts Help -->
        <div class="shortcuts-help">
            <div class="shortcuts-title">⌨️ Phím tắt:</div>
            <div class="shortcuts-list">
                <span class="shortcut-item"><kbd>Space</kbd> Ghi âm</span>
                <span class="shortcut-item"><kbd>1</kbd> Rất chậm</span>
                <span class="shortcut-item"><kbd>2</kbd> Chậm</span>
                <span class="shortcut-item"><kbd>3</kbd> Bình thường</span>
                <span class="shortcut-item"><kbd>4</kbd> Nhanh</span>
                <span class="shortcut-item"><kbd>5</kbd> Rất nhanh</span>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let isRecording = false;
        let recognition = null;
        let mediaRecorder = null;
        let audioChunks = [];
        let recordedAudioBlob = null;
        let targetText = <?= json_encode($lesson['TextMau'] ?? 'Color') ?>;

        // Initialize Speech Recognition
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';

            recognition.onstart = function() {
                updateMicStatus('🎤 Đang lắng nghe... Hãy nói rõ ràng!', true);
            };

            recognition.onresult = function(event) {
                const spokenText = event.results[0][0].transcript.toLowerCase().trim();
                processResult(spokenText);
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                let errorMessage = 'Lỗi nhận diện giọng nói';
                switch(event.error) {
                    case 'no-speech':
                        errorMessage = '🔇 Không nghe thấy giọng nói. Vui lòng thử lại.';
                        break;
                    case 'network':
                        errorMessage = '🌐 Lỗi kết nối mạng. Vui lòng kiểm tra internet.';
                        break;
                    case 'not-allowed':
                        errorMessage = '🎤 Vui lòng cho phép truy cập microphone.';
                        break;
                }
                showError(errorMessage);
                resetMic();
            };

            recognition.onend = function() {
                stopMediaRecorder();
                resetMic();
            };
        } else {
            showError('❌ Trình duyệt không hỗ trợ nhận diện giọng nói. Vui lòng sử dụng Chrome hoặc Edge.');
        }

        // Initialize MediaRecorder
        async function initializeMediaRecorder() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    console.warn('MediaRecorder not supported');
                    return false;
                }
                
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        sampleRate: 44100
                    }
                });
                
                // Check for supported MIME types
                let mimeType = 'audio/webm;codecs=opus';
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'audio/webm';
                    if (!MediaRecorder.isTypeSupported(mimeType)) {
                        mimeType = 'audio/mp4';
                        if (!MediaRecorder.isTypeSupported(mimeType)) {
                            mimeType = 'audio/wav';
                        }
                    }
                }
                
                mediaRecorder = new MediaRecorder(stream, {
                    mimeType: mimeType
                });
                
                mediaRecorder.ondataavailable = function(event) {
                    console.log('Data available event, size:', event.data.size);
                    if (event.data.size > 0) {
                        audioChunks.push(event.data);
                        console.log('Audio chunk added, total chunks:', audioChunks.length);
                    }
                };
                
                mediaRecorder.onstop = function() {
                    console.log('MediaRecorder stopped, total chunks:', audioChunks.length);
                    if (audioChunks.length > 0) {
                        recordedAudioBlob = new Blob(audioChunks, { type: mimeType });
                        console.log('Audio recorded successfully, size:', recordedAudioBlob.size, 'format:', mimeType);
                        
                        // Enable the play button immediately
                        const playRecordedBtn = document.getElementById('playRecordedBtn');
                        if (playRecordedBtn) {
                            playRecordedBtn.disabled = false;
                            playRecordedBtn.style.opacity = '1';
                            console.log('Play button enabled');
                        }
                    } else {
                        console.warn('No audio chunks recorded');
                    }
                };
                
                console.log('MediaRecorder initialized successfully with format:', mimeType);
                return true;
            } catch (error) {
                console.warn('Could not initialize audio recording:', error);
                return false;
            }
        }

        function toggleRecording() {
            console.log('toggleRecording called, isRecording:', isRecording);
            if (!recognition) {
                showError('❌ Trình duyệt không hỗ trợ nhận diện giọng nói');
                return;
            }

            if (isRecording) {
                console.log('Stopping recording...');
                recognition.stop();
                stopMediaRecorder();
            } else {
                console.log('Starting recording...');
                try {
                    // Clear previous recording
                    recordedAudioBlob = null;
                    audioChunks = [];
                    console.log('Previous recording cleared');
                    
                    // Initialize MediaRecorder if not already done
                    if (!mediaRecorder) {
                        console.log('MediaRecorder not initialized, initializing...');
                        initializeMediaRecorder().then((success) => {
                            if (success) {
                                console.log('MediaRecorder initialized successfully, starting recording');
                                startRecording();
                            } else {
                                console.warn('MediaRecorder initialization failed, continuing with speech recognition only');
                                startRecording(); // Continue without audio recording
                            }
                        }).catch(error => {
                            console.error('Failed to initialize MediaRecorder:', error);
                            startRecording(); // Continue without audio recording
                        });
                    } else {
                        console.log('MediaRecorder already initialized, starting recording');
                        startRecording();
                    }
                } catch (error) {
                    console.error('Error in toggleRecording:', error);
                    showError('❌ Không thể bắt đầu ghi âm. Vui lòng thử lại.');
                }
            }
        }

        function startRecording() {
            try {
                recognition.start();
                startMediaRecorder();
                isRecording = true;
            } catch (error) {
                console.error('Recording error:', error);
                showError('❌ Không thể bắt đầu ghi âm. Vui lòng thử lại.');
            }
        }

        function startMediaRecorder() {
            console.log('startMediaRecorder called, mediaRecorder state:', mediaRecorder ? mediaRecorder.state : 'null');
            if (mediaRecorder && mediaRecorder.state === 'inactive') {
                try {
                    audioChunks = [];
                    mediaRecorder.start(100); // Record in smaller chunks for better reliability
                    console.log('MediaRecorder started successfully, chunks cleared');
                } catch (error) {
                    console.error('Error starting MediaRecorder:', error);
                }
            } else if (!mediaRecorder) {
                console.warn('MediaRecorder not initialized');
            } else {
                console.warn('MediaRecorder state is:', mediaRecorder.state);
            }
        }

        function stopMediaRecorder() {
            console.log('stopMediaRecorder called, mediaRecorder state:', mediaRecorder ? mediaRecorder.state : 'null');
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                try {
                    mediaRecorder.stop();
                    console.log('MediaRecorder stopped, chunks collected:', audioChunks.length);
                } catch (error) {
                    console.error('Error stopping MediaRecorder:', error);
                }
            } else if (mediaRecorder && mediaRecorder.state === 'paused') {
                try {
                    mediaRecorder.stop();
                    console.log('MediaRecorder stopped from paused state');
                } catch (error) {
                    console.error('Error stopping MediaRecorder from paused:', error);
                }
            }
        }

        function updateMicStatus(message, recording = false) {
            document.getElementById('micStatus').innerHTML = message;
            const micButton = document.getElementById('micButton');
            const micIcon = document.getElementById('micIcon');
            const recordingIndicator = document.getElementById('recordingIndicator');
            
            if (recording) {
                micButton.classList.add('recording');
                micIcon.className = 'fas fa-stop';
                recordingIndicator.classList.add('active');
            } else {
                micButton.classList.remove('recording');
                micIcon.className = 'fas fa-microphone';
                recordingIndicator.classList.remove('active');
            }
        }

        function resetMic() {
            isRecording = false;
            updateMicStatus('� Nhấn các nút tốc độ để nghe phát âm, sau đó nhấn mic để luyện tập', false);
        }

        function processResult(spokenText) {
            const originalText = targetText.toLowerCase().trim();
            
            // Display results
            document.getElementById('originalText').textContent = targetText;
            document.getElementById('recognizedText').textContent = spokenText;
            
            // Calculate advanced score
            const score = calculateAdvancedScore(originalText, spokenText);
            document.getElementById('scoreValue').textContent = score + '%';
            
            // Update score description
            const description = getScoreDescription(score);
            document.getElementById('scoreDescription').innerHTML = description;
            
            // Update recorded audio button state
            const playRecordedBtn = document.getElementById('playRecordedBtn');
            console.log('processResult: checking recorded audio blob:', recordedAudioBlob ? recordedAudioBlob.size : 'null');
            if (recordedAudioBlob && recordedAudioBlob.size > 0) {
                playRecordedBtn.disabled = false;
                playRecordedBtn.style.opacity = '1';
                playRecordedBtn.title = 'Nghe lại giọng nói của bạn';
                console.log('Play button enabled - audio available');
            } else {
                playRecordedBtn.disabled = true;
                playRecordedBtn.style.opacity = '0.5';
                playRecordedBtn.title = 'Không có âm thanh đã ghi';
                console.log('Play button disabled - no audio available');
            }
            
            // Show results section with animation
            const resultsSection = document.getElementById('resultsSection');
            resultsSection.style.display = 'block';
            
            // Smooth scroll to results
            setTimeout(() => {
                resultsSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300);
            
            // Save result
            saveResult(spokenText, score);
            
            // Update analysis card
            updateAnalysisCard(score, originalText, spokenText);
        }

        function calculateAdvancedScore(original, spoken) {
            if (original === spoken) return 100;
            
            // Advanced scoring algorithm
            const originalWords = original.split(' ');
            const spokenWords = spoken.split(' ');
            
            let exactMatches = 0;
            let partialMatches = 0;
            
            originalWords.forEach((originalWord, index) => {
                if (spokenWords[index]) {
                    if (originalWord === spokenWords[index]) {
                        exactMatches++;
                    } else if (calculateSimilarity(originalWord, spokenWords[index]) > 0.7) {
                        partialMatches++;
                    }
                }
            });
            
            const exactScore = (exactMatches / originalWords.length) * 80;
            const partialScore = (partialMatches / originalWords.length) * 40;
            const lengthPenalty = Math.abs(originalWords.length - spokenWords.length) * 5;
            
            const finalScore = Math.max(0, Math.min(100, exactScore + partialScore - lengthPenalty));
            return Math.round(finalScore);
        }

        function calculateSimilarity(str1, str2) {
            const longer = str1.length > str2.length ? str1 : str2;
            const shorter = str1.length > str2.length ? str2 : str1;
            
            if (longer.length === 0) return 1.0;
            
            const editDistance = levenshteinDistance(longer, shorter);
            return (longer.length - editDistance) / longer.length;
        }

        function levenshteinDistance(str1, str2) {
            const matrix = [];
            
            for (let i = 0; i <= str2.length; i++) {
                matrix[i] = [i];
            }
            
            for (let j = 0; j <= str1.length; j++) {
                matrix[0][j] = j;
            }
            
            for (let i = 1; i <= str2.length; i++) {
                for (let j = 1; j <= str1.length; j++) {
                    if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                        matrix[i][j] = matrix[i - 1][j - 1];
                    } else {
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j - 1] + 1,
                            matrix[i][j - 1] + 1,
                            matrix[i - 1][j] + 1
                        );
                    }
                }
            }
            
            return matrix[str2.length][str1.length];
        }

        function getScoreDescription(score) {
            if (score >= 95) return "🎉 Hoàn hảo! Phát âm xuất sắc!";
            if (score >= 90) return "⭐ Tuyệt vời! Phát âm rất chính xác!";
            if (score >= 80) return "👍 Tốt! Phát âm khá chính xác!";
            if (score >= 70) return "👌 Khá! Cần cải thiện một chút!";
            if (score >= 60) return "📈 Trung bình! Cần luyện tập thêm!";
            if (score >= 50) return "⚠️ Yếu! Cần cải thiện nhiều!";
            return "❌ Kém! Hãy nghe mẫu và thử lại!";
        }

        function updateAnalysisCard(score, original, spoken) {
            const analysisSection = document.getElementById('analysisSection');
            
            let statusIcon = 'fas fa-chart-line';
            let statusColor = '#11998e';
            let statusText = '✅ Phân tích hoàn tất';
            let subText = `Độ chính xác: ${score >= 80 ? 'Cao' : score >= 60 ? 'Trung bình' : 'Thấp'}`;
            
            if (score < 60) {
                statusIcon = 'fas fa-exclamation-triangle';
                statusColor = '#fdbb2d';
                statusText = '⚠️ Cần cải thiện';
            } else if (score < 80) {
                statusIcon = 'fas fa-thumbs-up';
                statusColor = '#667eea';
                statusText = '👍 Khá tốt';
            } else {
                statusIcon = 'fas fa-trophy';
                statusColor = '#11998e';
                statusText = '🏆 Xuất sắc';
            }
            
            analysisSection.innerHTML = `
                <i class="${statusIcon} analysis-icon" style="color: ${statusColor}"></i>
                <div class="analysis-placeholder">
                    <div style="color: ${statusColor}; font-weight: 700; font-size: 18px;">${statusText}</div>
                    <div style="margin-top: 8px; font-size: 14px; opacity: 0.8;">Điểm: ${score}% - ${subText}</div>
                </div>
            `;
        }

        function playTargetAudio(rate = 1.0) {
            if ('speechSynthesis' in window) {
                speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(targetText);
                utterance.lang = 'en-US';
                utterance.rate = rate;
                utterance.pitch = 1.0;
                utterance.volume = 1.0;
                
                // Update active button state
                updateActiveSpeedButton(rate);
                
                // Show visual feedback
                showSpeedFeedback(rate);
                
                speechSynthesis.speak(utterance);
            } else {
                showError('❌ Trình duyệt không hỗ trợ phát âm thanh');
            }
        }

        function updateActiveSpeedButton(rate) {
            // Remove active class from all buttons
            document.querySelectorAll('.audio-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to the corresponding button
            let selector = '';
            switch(rate) {
                case 0.3:
                    selector = '.speed-slow';
                    break;
                case 0.6:
                    selector = '.speed-medium-slow';
                    break;
                case 1.0:
                    selector = '.speed-normal';
                    break;
                case 1.4:
                    selector = '.speed-fast';
                    break;
                case 1.8:
                    selector = '.speed-very-fast';
                    break;
            }
            
            if (selector) {
                const activeBtn = document.querySelector(selector);
                if (activeBtn) {
                    activeBtn.classList.add('active');
                }
            }
        }

        function showSpeedFeedback(rate) {
            const feedbackTexts = {
                0.3: '🐌 Đang phát âm cực chậm...',
                0.6: '🚶 Đang phát âm chậm...',
                1.0: '🎯 Đang phát âm bình thường...',
                1.4: '🏃 Đang phát âm nhanh...',
                1.8: '⚡ Đang phát âm siêu nhanh...'
            };
            
            const feedback = feedbackTexts[rate] || '🎵 Đang phát âm...';
            
            // Temporarily update mic status to show speed feedback
            const micStatus = document.getElementById('micStatus');
            const originalText = micStatus.innerHTML;
            
            micStatus.innerHTML = feedback;
            micStatus.style.color = '#11998e';
            micStatus.style.fontWeight = '700';
            
            // Reset after 2 seconds
            setTimeout(() => {
                micStatus.innerHTML = originalText;
                micStatus.style.color = '';
                micStatus.style.fontWeight = '';
            }, 2000);
        }

        function playRecordedAudio() {
            if (!recordedAudioBlob || recordedAudioBlob.size === 0) {
                showError('❌ Không có âm thanh đã ghi để phát lại. Hãy thử ghi âm lại.');
                return;
            }

            const playBtn = document.getElementById('playRecordedBtn');
            if (!playBtn) {
                return;
            }
            
            const originalContent = playBtn.innerHTML;
            
            try {
                // Create audio URL and play
                const audioUrl = URL.createObjectURL(recordedAudioBlob);
                const audio = new Audio();
                
                // Set up audio element
                audio.src = audioUrl;
                audio.controls = false;
                audio.preload = 'auto';
                
                // Update button state
                playBtn.classList.add('playing');
                playBtn.innerHTML = '<i class="fas fa-stop"></i> Đang phát...';
                playBtn.disabled = true;
                
                // Audio event handlers
                audio.onloadeddata = function() {
                    console.log('Audio loaded successfully, duration:', audio.duration);
                };
                
                audio.onended = function() {
                    console.log('Audio playback ended');
                    resetPlayButton();
                };
                
                audio.onerror = function(e) {
                    console.error('Audio playback error:', e);
                    resetPlayButton();
                    showError('❌ Không thể phát âm thanh đã ghi. Có thể định dạng audio không được hỗ trợ.');
                };
                
                audio.oncanplay = function() {
                    console.log('Audio can start playing');
                };
                
                // Allow stopping playback
                playBtn.onclick = function() {
                    if (audio && !audio.ended && !audio.paused) {
                        audio.pause();
                        audio.currentTime = 0;
                    }
                    resetPlayButton();
                };
                
                function resetPlayButton() {
                    playBtn.classList.remove('playing');
                    playBtn.innerHTML = originalContent;
                    playBtn.disabled = false;
                    playBtn.onclick = playRecordedAudio;
                    if (audioUrl) {
                        URL.revokeObjectURL(audioUrl);
                    }
                }
                
                // Start playing with better error handling
                const playPromise = audio.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log('Audio playback started successfully');
                    }).catch(error => {
                        console.error('Error playing recorded audio:', error);
                        resetPlayButton();
                        showError('❌ Không thể phát âm thanh đã ghi. Lỗi: ' + error.message);
                    });
                }
                
            } catch (error) {
                console.error('Error in playRecordedAudio:', error);
                playBtn.classList.remove('playing');
                playBtn.innerHTML = originalContent;
                playBtn.disabled = false;
                showError('❌ Lỗi khi phát âm thanh: ' + error.message);
            }
        }

        function tryAgain() {
            // Hide results section
            document.getElementById('resultsSection').style.display = 'none';
            
            // Reset mic button
            resetMic();
            
            // Clear recorded audio
            recordedAudioBlob = null;
            audioChunks = [];
            
            // Reset analysis card
            const analysisSection = document.getElementById('analysisSection');
            analysisSection.innerHTML = `
                <i class="fas fa-chart-line analysis-icon"></i>
                <div class="analysis-placeholder">
                    <div>Chưa có dữ liệu phân tích</div>
                    <div style="margin-top: 8px; font-size: 14px; opacity: 0.8;">Hãy thử phát âm để nhận phản hồi từ AI</div>
                </div>
            `;
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function saveResult(spokenText, score) {
            $.ajax({
                url: '<?= BASE_URL("api/speaking-api.php") ?>',
                method: 'POST',
                data: {
                    action: 'save_result',
                    lesson_id: <?= $lessonId ?>,
                    spoken_text: spokenText,
                    score: score,
                    target_text: targetText
                },
                success: function(response) {
                    console.log('✅ Kết quả đã được lưu thành công');
                },
                error: function(xhr, status, error) {
                    console.error('❌ Lỗi khi lưu kết quả:', error);
                }
            });
        }

        function showError(message) {
            const resultsSection = document.getElementById('resultsSection');
            resultsSection.innerHTML = `
                <div class="results-header">
                    <h3 class="results-title" style="color: #fc466b;">
                        ⚠️ Lỗi
                    </h3>
                </div>
                <div class="error-message">
                    ${message}
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="tryAgain()">
                        <i class="fas fa-redo"></i>
                        Thử lại
                    </button>
                </div>
            `;
            resultsSection.style.display = 'block';
        }

        // Debug function to test MediaRecorder
        function testMediaRecorder() {
            console.log('=== Testing MediaRecorder ===');
            console.log('MediaRecorder exists:', !!mediaRecorder);
            console.log('AudioChunks length:', audioChunks.length);
            console.log('RecordedAudioBlob exists:', !!recordedAudioBlob);
            console.log('RecordedAudioBlob size:', recordedAudioBlob ? recordedAudioBlob.size : 'null');
            
            if (mediaRecorder) {
                console.log('MediaRecorder state:', mediaRecorder.state);
                console.log('MediaRecorder mimeType:', mediaRecorder.mimeType);
            }
        }

        // Expose to global for debugging
        window.testMediaRecorder = testMediaRecorder;

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.code === 'Space') {
                event.preventDefault();
                toggleRecording();
            } else if (event.code === 'KeyP') {
                event.preventDefault();
                playTargetAudio(1.0);
            } else if (event.code === 'KeyS') {
                event.preventDefault();
                playTargetAudio(0.6);
            } else if (event.code === 'Digit1') {
                event.preventDefault();
                playTargetAudio(0.3);
            } else if (event.code === 'Digit2') {
                event.preventDefault();
                playTargetAudio(0.6);
            } else if (event.code === 'Digit3') {
                event.preventDefault();
                playTargetAudio(1.0);
            } else if (event.code === 'Digit4') {
                event.preventDefault();
                playTargetAudio(1.4);
            } else if (event.code === 'Digit5') {
                event.preventDefault();
                playTargetAudio(1.8);
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎤 AI Speech Master initialized');
            
            // Initialize MediaRecorder early
            initializeMediaRecorder().then((success) => {
                if (success) {
                    console.log('✅ MediaRecorder ready for recording');
                } else {
                    console.warn('⚠️ Audio recording may not be available');
                }
            }).catch(error => {
                console.warn('⚠️ MediaRecorder initialization failed:', error);
            });
            
            // Removed auto-play - user will click to hear pronunciation
            console.log('📢 Trang đã sẵn sàng! Nhấn các nút tốc độ để nghe phát âm.');
        });
    </script>
</body>
</html>