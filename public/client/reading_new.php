<?php
require_once(__DIR__ . "/../../config/function.php");

$title = 'Reading Practice - WebNgoaiNgu';

// Check if user is logged in
if (!isset($_SESSION['account'])) {
    redirect(BASE_URL("Page/Login"));
}

// Get lesson info from URL parameters
$maKhoaHoc = isset($_GET['maKhoaHoc']) ? (int)$_GET['maKhoaHoc'] : 1;
$maBaiHoc = isset($_GET['maBaiHoc']) ? (int)$_GET['maBaiHoc'] : 1;

// Get lesson details
$khoaHoc = $Database->get_row("
    SELECT bh.*, kh.TenKhoaHoc 
    FROM baihoc bh 
    JOIN khoahoc kh ON bh.MaKhoaHoc = kh.MaKhoaHoc 
    WHERE bh.MaKhoaHoc = '$maKhoaHoc' AND bh.MaBaiHoc = '$maBaiHoc'
");

if (!$khoaHoc) {
    redirect(BASE_URL("Page/Home"));
}

// Map lesson IDs to topics for Reading (đảm bảo đúng với admin)
$topicMapping = [
    1 => 'Traffic',       // Bài 1: Giao thông
    2 => 'Family',        // Bài 2: Gia đình  
    3 => 'Food',          // Bài 3: Thức ăn
    4 => 'Health',        // Bài 4: Sức khỏe
    5 => 'Technology',    // Bài 5: Công nghệ
    6 => 'Travel',        // Bài 6: Du lịch
    7 => 'Education',     // Bài 7: Giáo dục
    8 => 'Business'       // Bài 8: Kinh doanh
];

$currentTopic = isset($topicMapping[$maBaiHoc]) ? $topicMapping[$maBaiHoc] : 'General';

// Get Reading lessons for current topic from database
$readingLessons = [];

// First, try to get real data from reading_lessons table
try {
    $realLessons = $Database->get_list("
        SELECT 
            MaBaiDoc,
            TieuDe,
            NoiDungBaiDoc,
            MucDo,
            ThoiGianTao,
            ChuDe
        FROM reading_lessons 
        WHERE ChuDe = '$currentTopic'
        ORDER BY MucDo ASC, ThoiGianTao DESC
    ");

    if (count($realLessons) > 0) {
        $readingLessons = $realLessons;
        
        // Get questions for each lesson
        foreach ($readingLessons as &$lesson) {
            $questions = $Database->get_list("
                SELECT * FROM reading_questions 
                WHERE MaBaiDoc = " . $lesson['MaBaiDoc'] . "
                ORDER BY ThuTu ASC
            ");
            $lesson['questions'] = $questions;
            $lesson['SoCauHoi'] = count($questions);
        }
    }
} catch (Exception $e) {
    // If database error, we'll use sample data
    $readingLessons = [];
}

// If no real lessons found, use sample data for the current topic
if (count($readingLessons) == 0) {
    // Create sample reading lessons based on topic
    $sampleLessons = [
        'Traffic' => [
            [
                'MaBaiDoc' => 101,
                'TieuDe' => 'Traffic Safety and Rules',
                'NoiDungBaiDoc' => "Traffic safety is one of the most important concerns in modern society. Every day, millions of people use various means of transportation to get to work, school, or other destinations.\n\nTraffic rules are established to protect all road users, including drivers, passengers, pedestrians, and cyclists. These rules include speed limits, traffic signals, road signs, and right-of-way regulations.\n\nOne of the most critical aspects of traffic safety is wearing seat belts while driving. Seat belts can reduce the risk of serious injury or death by up to 50% in case of an accident. Similarly, motorcycle riders should always wear helmets.\n\nAnother important factor is avoiding distractions while driving. Using mobile phones, eating, or other activities that take attention away from the road can lead to accidents.",
                'MucDo' => 'Medium',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Traffic',
                'SoCauHoi' => 3
            ],
            [
                'MaBaiDoc' => 102,
                'TieuDe' => 'Public Transportation Benefits',
                'NoiDungBaiDoc' => "Public transportation plays a crucial role in urban development and environmental protection. Buses, trains, and subways help reduce traffic congestion and air pollution in cities.\n\nUsing public transport has many advantages. It is more economical than owning a private car, especially when considering fuel costs, parking fees, and maintenance expenses. Additionally, it allows people to relax, read, or work during their commute instead of focusing on driving.\n\nMany cities are investing in modern public transportation systems to attract more users. These include electric buses, high-speed trains, and smart payment systems that make traveling more convenient and efficient.",
                'MucDo' => 'Easy',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Traffic',
                'SoCauHoi' => 2
            ]
        ],
        'Family' => [
            [
                'MaBaiDoc' => 201,
                'TieuDe' => 'The Importance of Family Values',
                'NoiDungBaiDoc' => "Family is the foundation of society and plays a vital role in shaping individuals' character and values. Strong family bonds provide emotional support, security, and guidance throughout life.\n\nFamily traditions and customs help preserve cultural heritage and create lasting memories. Regular family gatherings, holiday celebrations, and shared activities strengthen relationships between family members.\n\nIn today's busy world, it's important to maintain family connections despite work and other commitments. Simple activities like family dinners, weekend outings, or even video calls can help keep families close together.",
                'MucDo' => 'Easy',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Family',
                'SoCauHoi' => 3
            ]
        ],
        'Food' => [
            [
                'MaBaiDoc' => 301,
                'TieuDe' => 'Healthy Eating Habits',
                'NoiDungBaiDoc' => "Maintaining a healthy diet is essential for physical and mental well-being. A balanced diet should include a variety of foods from different food groups: fruits, vegetables, whole grains, lean proteins, and dairy products.\n\nFruits and vegetables provide essential vitamins, minerals, and fiber. It's recommended to eat at least five servings of fruits and vegetables daily. Whole grains offer sustained energy and help maintain stable blood sugar levels.\n\nLimiting processed foods, sugary drinks, and excessive salt intake is also important for long-term health. Drinking plenty of water throughout the day helps maintain proper hydration and supports various body functions.",
                'MucDo' => 'Medium',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Food',
                'SoCauHoi' => 3
            ]
        ],
        'Health' => [
            [
                'MaBaiDoc' => 401,
                'TieuDe' => 'Exercise and Physical Fitness',
                'NoiDungBaiDoc' => "Regular exercise is crucial for maintaining good health and preventing various diseases. Physical activity strengthens the heart, improves circulation, and helps control weight.\n\nThere are many types of exercises to choose from: cardiovascular exercises like running and swimming, strength training with weights, and flexibility exercises like yoga and stretching.\n\nExperts recommend at least 150 minutes of moderate-intensity exercise per week for adults. This can be achieved through daily activities like walking, cycling to work, or taking the stairs instead of elevators.",
                'MucDo' => 'Medium',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Health',
                'SoCauHoi' => 3
            ]
        ],
        'Technology' => [
            [
                'MaBaiDoc' => 501,
                'TieuDe' => 'The Impact of Social Media',
                'NoiDungBaiDoc' => "Social media has revolutionized the way people communicate and share information. Platforms like Facebook, Twitter, and Instagram connect billions of users worldwide, enabling instant communication across great distances.\n\nWhile social media offers many benefits, such as staying connected with friends and accessing news quickly, it also presents challenges. Excessive use can lead to addiction, decreased face-to-face social skills, and mental health issues.\n\nIt's important to use social media responsibly by limiting screen time, verifying information before sharing, and maintaining privacy settings to protect personal data.",
                'MucDo' => 'Hard',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Technology',
                'SoCauHoi' => 4
            ]
        ],
        'Travel' => [
            [
                'MaBaiDoc' => 601,
                'TieuDe' => 'Benefits of Traveling',
                'NoiDungBaiDoc' => "Traveling opens our minds to new experiences, cultures, and perspectives. It allows us to break away from daily routines and discover different ways of life.\n\nWhen we travel, we learn about history, art, cuisine, and traditions of other places. This exposure broadens our understanding of the world and helps us become more tolerant and open-minded individuals.\n\nTraveling also provides opportunities for personal growth, building confidence, and creating lifelong memories. Whether it's a weekend trip to a nearby city or an international adventure, every journey offers valuable lessons and experiences.",
                'MucDo' => 'Easy',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Travel',
                'SoCauHoi' => 3
            ]
        ],
        'Education' => [
            [
                'MaBaiDoc' => 701,
                'TieuDe' => 'Online Learning Revolution',
                'NoiDungBaiDoc' => "The rise of online education has transformed traditional learning methods. Digital platforms now offer courses on virtually any subject, making education more accessible to people worldwide.\n\nOnline learning provides flexibility that traditional classrooms cannot match. Students can study at their own pace, access materials anytime, and learn from expert instructors regardless of geographical location.\n\nHowever, online education also requires strong self-discipline and time management skills. Students must be motivated to complete assignments and participate in virtual discussions without direct supervision.",
                'MucDo' => 'Medium',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Education',
                'SoCauHoi' => 3
            ]
        ],
        'Business' => [
            [
                'MaBaiDoc' => 801,
                'TieuDe' => 'Entrepreneurship in the Digital Age',
                'NoiDungBaiDoc' => "The digital revolution has created unprecedented opportunities for entrepreneurs. Starting a business today requires less capital and fewer resources than ever before, thanks to online tools and platforms.\n\nE-commerce platforms allow entrepreneurs to reach global markets without physical storefronts. Social media marketing enables small businesses to compete with larger corporations by building strong customer relationships.\n\nSuccessful digital entrepreneurs must understand online marketing, customer analytics, and digital payment systems. They also need to adapt quickly to changing technologies and consumer preferences.",
                'MucDo' => 'Hard',
                'ThoiGianTao' => date('Y-m-d H:i:s'),
                'ChuDe' => 'Business',
                'SoCauHoi' => 4
            ]
        ]
    ];

    // Use sample lessons for the current topic
    if (isset($sampleLessons[$currentTopic])) {
        $readingLessons = $sampleLessons[$currentTopic];
        
        // Add sample questions for each lesson
        foreach ($readingLessons as &$lesson) {
            $lesson['questions'] = [
                [
                    'CauHoi' => 'What is the main topic of this reading passage?',
                    'DapAnA' => 'Science and technology',
                    'DapAnB' => 'The specific topic discussed',
                    'DapAnC' => 'Mathematics and physics',
                    'DapAnD' => 'Art and literature',
                    'DapAnDung' => 'B',
                    'GiaiThich' => 'Đoạn văn tập trung vào chủ đề cụ thể được thảo luận.'
                ],
                [
                    'CauHoi' => 'According to the passage, what is important to remember?',
                    'DapAnA' => 'Always follow the main points',
                    'DapAnB' => 'Practice regularly',
                    'DapAnC' => 'Read carefully and understand',
                    'DapAnD' => 'All of the above',
                    'DapAnDung' => 'D',
                    'GiaiThich' => 'Tất cả các điểm A, B, C đều quan trọng theo đoạn văn.'
                ]
            ];
        }
    }
}

require_once(__DIR__ . "/../../public/client/header.php");
?>

<div class="grid">
    <div class="row main-page">
        <div class="nav-container">
            <?php include_once(__DIR__ . "/../../public/client/navigation.php"); ?>
        </div>
        
        <div class="main_content-container">
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
                        <h2>Chọn bài Reading - Chủ đề: <?= $currentTopic ?></h2>
                        
                        <?php if (count($readingLessons) > 0): ?>
                            <div class="reading-items">
                                <?php foreach ($readingLessons as $index => $lesson): ?>
                                    <div class="reading-item" onclick="startReading(<?= $lesson['MaBaiDoc'] ?>)">
                                        <div class="reading-item-number"><?= $index + 1 ?></div>
                                        <div class="reading-item-header">
                                            <h3><?= htmlspecialchars($lesson['TieuDe']) ?></h3>
                                            <span class="reading-level level-<?= strtolower($lesson['MucDo']) ?>">
                                                <?= $lesson['MucDo'] ?>
                                            </span>
                                        </div>
                                        
                                        <div class="reading-preview">
                                            <?= htmlspecialchars(mb_substr(strip_tags($lesson['NoiDungBaiDoc']), 0, 150)) ?>...
                                        </div>
                                        
                                        <div class="reading-meta">
                                            <span><i class="fas fa-question-circle"></i> <?= $lesson['SoCauHoi'] ?> câu hỏi</span>
                                            <span><i class="fas fa-tag"></i> <?= $lesson['ChuDe'] ?></span>
                                            <span><i class="fas fa-clock"></i> <?= date('d/m/Y', strtotime($lesson['ThoiGianTao'])) ?></span>
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
                            <h3><i class="fas fa-question-circle"></i> Câu hỏi</h3>
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
    </div>
</div>

<style>
.reading-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.reading-header {
    margin-bottom: 30px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.breadcrumb {
    margin-bottom: 15px;
}

.breadcrumb a {
    color: #007bff;
    text-decoration: none;
    margin-right: 10px;
    font-weight: 500;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.reading-title h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 2.2em;
}

.reading-title p {
    color: #666;
    font-size: 1.1em;
    margin-bottom: 15px;
}

.topic-info {
    display: flex;
    gap: 15px;
    align-items: center;
}

.topic-badge {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9em;
}

.lesson-count {
    background: #28a745;
    color: white;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.85em;
    font-weight: 500;
}

.reading-content > div {
    display: none;
}

.reading-content > div.active {
    display: block;
}

.reading-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
}

.reading-item {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 15px;
    padding: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.reading-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.reading-item:hover::before {
    transform: scaleX(1);
}

.reading-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.reading-item-number {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9em;
}

.reading-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    margin-right: 45px;
}

.reading-item-header h3 {
    color: #333;
    margin: 0;
    flex: 1;
    margin-right: 10px;
    font-size: 1.3em;
    line-height: 1.4;
}

.reading-level {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
}

.level-easy {
    background: #d4edda;
    color: #155724;
}

.level-medium {
    background: #fff3cd;
    color: #856404;
}

.level-hard {
    background: #f8d7da;
    color: #721c24;
}

.reading-preview {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 0.95em;
}

.reading-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    color: #888;
    font-size: 0.85em;
    flex-wrap: wrap;
}

.reading-meta i {
    margin-right: 5px;
    color: #007bff;
}

.btn-start {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    font-size: 1em;
}

.btn-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.no-reading {
    text-align: center;
    padding: 80px 20px;
    color: #666;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.no-reading i {
    font-size: 5em;
    margin-bottom: 25px;
    color: #ddd;
}

.no-reading h3 {
    font-size: 1.8em;
    margin-bottom: 15px;
    color: #333;
}

.no-reading p {
    font-size: 1.1em;
    margin-bottom: 10px;
}

.reading-practice {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 30px;
}

.reading-passage {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.passage-header {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f8f9fa;
}

.btn-back {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    margin-right: 20px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.passage-info h2 {
    margin: 0;
    color: #333;
    font-size: 1.6em;
}

.current-topic {
    background: #007bff;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
    margin-top: 8px;
    display: inline-block;
}

.passage-content {
    line-height: 1.8;
    font-size: 1.1em;
    color: #333;
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    border-left: 4px solid #007bff;
}

.reading-questions {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    height: fit-content;
    max-height: 85vh;
    overflow-y: auto;
}

.reading-questions h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 1.4em;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 10px;
}

.question-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
}

.question-text {
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
    font-size: 1.05em;
}

.option-item {
    margin: 12px 0;
    padding: 12px;
    border-radius: 8px;
    transition: background-color 0.2s;
    cursor: pointer;
    border: 1px solid #e9ecef;
}

.option-item:hover {
    background-color: #e9ecef;
    border-color: #007bff;
}

.option-item input[type="radio"] {
    margin-right: 12px;
    transform: scale(1.2);
}

.reading-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 1em;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.reading-result {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
}

@media (max-width: 768px) {
    .reading-practice {
        grid-template-columns: 1fr;
    }
    
    .reading-items {
        grid-template-columns: 1fr;
    }
    
    .passage-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .reading-header {
        padding: 20px;
    }
    
    .reading-title h1 {
        font-size: 1.8em;
    }
    
    .topic-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
let currentReadingLesson = null;
let currentQuestions = [];
let readingLessonsData = <?= json_encode($readingLessons) ?>;

console.log('Reading data loaded for topic: <?= $currentTopic ?>', readingLessonsData);

function startReading(lessonId) {
    console.log('Starting reading lesson:', lessonId);
    
    // Find lesson data
    const lesson = readingLessonsData.find(l => l.MaBaiDoc == lessonId);
    if (!lesson) {
        alert('Không tìm thấy bài học');
        return;
    }
    
    currentReadingLesson = lesson;
    currentQuestions = lesson.questions || [];
    
    console.log('Current lesson:', currentReadingLesson);
    console.log('Questions:', currentQuestions);
    
    showReadingPractice();
}

function showReadingPractice() {
    // Hide list, show practice
    document.getElementById('reading-list').classList.remove('active');
    document.getElementById('reading-practice').classList.add('active');
    
    // Set title and content
    document.getElementById('reading-title').textContent = currentReadingLesson.TieuDe;
    document.getElementById('passage-content').innerHTML = currentReadingLesson.NoiDungBaiDoc.replace(/\n/g, '<br><br>');
    
    // Render questions
    renderQuestions();
}

function renderQuestions() {
    const container = document.getElementById('questions-container');
    let html = '';
    
    if (currentQuestions.length === 0) {
        html = '<div style="text-align: center; padding: 20px; color: #666;"><i class="fas fa-info-circle"></i> Chưa có câu hỏi cho bài này</div>';
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
}

function submitReading() {
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
    // Hide practice, show result
    document.getElementById('reading-practice').classList.remove('active');
    document.getElementById('reading-result').classList.add('active');
    
    let resultHtml = `
        <div style="padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: ${score >= 70 ? '#28a745' : '#dc3545'}; font-size: 2.2em; margin-bottom: 15px;">
                    <i class="fas ${score >= 70 ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                    ${score >= 70 ? 'Chúc mừng!' : 'Cần cố gắng thêm!'}
                </h2>
                <div style="background: linear-gradient(135deg, ${score >= 70 ? '#28a745' : '#dc3545'} 0%, ${score >= 70 ? '#20c997' : '#c82333'} 100%); color: white; padding: 20px; border-radius: 15px; margin: 20px 0;">
                    <h3 style="margin: 0; font-size: 2em;">Điểm số: ${score}%</h3>
                    <p style="margin: 10px 0 0 0; font-size: 1.2em;">
                        Bạn đã trả lời đúng <strong>${correct}/${total}</strong> câu hỏi
                    </p>
                </div>
            </div>
            
            <div style="background: #f8f9fa; padding: 25px; border-radius: 12px; margin: 25px 0; text-align: left;">
                <h3 style="color: #333; margin-bottom: 20px; font-size: 1.4em;">
                    <i class="fas fa-clipboard-list"></i> Chi tiết kết quả:
                </h3>
    `;
    
    details.forEach(detail => {
        const iconClass = detail.isCorrect ? 'fa-check' : 'fa-times';
        const iconColor = detail.isCorrect ? '#28a745' : '#dc3545';
        resultHtml += `
            <div style="margin: 15px 0; padding: 20px; background: white; border-radius: 10px; border-left: 4px solid ${detail.isCorrect ? '#28a745' : '#dc3545'}; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <div style="font-weight: bold; margin-bottom: 10px; font-size: 1.1em;">
                    <i class="fas ${iconClass}" style="color: ${iconColor}; margin-right: 8px;"></i>
                    Câu ${detail.questionIndex}: ${detail.question}
                </div>
                <div style="margin: 8px 0; padding: 8px; background: #f8f9fa; border-radius: 5px;">
                    <strong>Bạn chọn:</strong> <span style="color: ${detail.isCorrect ? '#28a745' : '#dc3545'}; font-weight: 600;">${detail.userAnswer || 'Không trả lời'}</span>
                </div>
                <div style="margin: 8px 0; padding: 8px; background: #e8f5e8; border-radius: 5px;">
                    <strong>Đáp án đúng:</strong> <span style="color: #28a745; font-weight: 600;">${detail.correctAnswer}</span>
                </div>
                ${detail.explanation ? `<div style="font-style: italic; color: #666; margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 5px; border-left: 3px solid #ffc107;"><strong>Giải thích:</strong> ${detail.explanation}</div>` : ''}
            </div>
        `;
    });
    
    resultHtml += `
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button onclick="backToList()" class="btn btn-primary" style="font-size: 1.1em; padding: 15px 30px;">
                    <i class="fas fa-list"></i> Làm bài khác
                </button>
                <a href="<?= BASE_URL("Page/BaiHoc/$maKhoaHoc/$maBaiHoc") ?>" class="btn btn-secondary" style="font-size: 1.1em; padding: 15px 30px;">
                    <i class="fas fa-arrow-left"></i> Quay lại bài học
                </a>
            </div>
        </div>
    `;
    
    document.getElementById('reading-result').innerHTML = resultHtml;
}
</script>

<?php
include_once(__DIR__ . "/../../public/client/menu_right.php");
include_once(__DIR__ . "/../../public/client/navigation_mobile.php");
require_once(__DIR__ . "/../../public/client/footer.php");
?>
