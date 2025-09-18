<?php
require_once("../../configs/config.php");
require_once("../../configs/function.php");

// Function để tạo AI response
function generateAIResponse($userMessage) {
    $message = strtolower(trim($userMessage));
    
    // Greetings
    if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $message)) {
        $responses = [
            "Hello! I'm your English learning assistant. How can I help you today?",
            "Hi there! I'm here to help you with English. What would you like to learn?",
            "Hey! Welcome to our English learning chatbot. Ask me anything about English!"
        ];
        return $responses[array_rand($responses)];
    }
    
    // Name questions
    if (preg_match('/\b(what.*your name|who are you|what.*you called)\b/', $message)) {
        return "My name is ChatBot, your English learning assistant. I'm here to help you improve your English skills!";
    }
    
    // How are you
    if (preg_match('/\b(how are you|how.*doing)\b/', $message)) {
        return "I'm doing great, thank you for asking! I'm excited to help you learn English. How are you doing with your English studies?";
    }
    
    // Thank you
    if (preg_match('/\b(thank you|thanks|thx)\b/', $message)) {
        return "You're welcome! I'm happy to help you learn English. Feel free to ask me anything else!";
    }
    
    // Goodbye
    if (preg_match('/\b(bye|goodbye|see you|farewell)\b/', $message)) {
        return "Goodbye! Keep practicing your English. See you next time!";
    }
    
    // English learning questions
    if (preg_match('/\b(how.*learn english|improve.*english|study.*english)\b/', $message)) {
        return "To improve your English, I recommend: 1) Practice speaking daily, 2) Read English books/articles, 3) Watch English movies with subtitles, 4) Use our grammar exercises, 5) Chat with me regularly! What specific area would you like to focus on?";
    }
    
    // Grammar questions
    if (preg_match('/\b(grammar|tense|verb|noun|adjective|adverb)\b/', $message)) {
        return "Grammar is very important in English! We have many grammar topics available: Present/Past/Future tenses, Articles (a/an/the), Prepositions, and more. Which grammar topic interests you most?";
    }
    
    // Vocabulary questions
    if (preg_match('/\b(vocabulary|words|meaning|definition)\b/', $message)) {
        return "Building vocabulary is key to English fluency! You can expand your vocabulary by: reading daily, keeping a word journal, using flashcards, and practicing with our vocabulary exercises. What type of vocabulary would you like to learn?";
    }
    
    // Translation requests - detect "meaning of" pattern
    if (preg_match('/\b(meaning of|what.*mean|define|translate)\b/', $message)) {
        return "I'd be happy to help with word meanings! For detailed translations and definitions, please use our dictionary feature or ask me about specific English words. For example, you can ask 'What does happiness mean?'";
    }
    
    // Specific word meanings
    $commonWords = [
        'hello' => "Hello means a greeting used when meeting someone or starting a conversation.",
        'love' => "Love means a strong feeling of affection or care for someone or something.",
        'happy' => "Happy means feeling joy, pleasure, or satisfaction.",
        'study' => "Study means to learn about something by reading, memorizing, attending classes, etc.",
        'english' => "English is a language spoken by many people around the world, originating from England.",
        'friend' => "Friend means a person you know well and like, who is not part of your family.",
        'book' => "Book means a written or printed work consisting of pages bound together.",
        'teacher' => "Teacher means a person who teaches students in a school or gives private lessons.",
        'student' => "Student means a person who is learning at a school, college, or university.",
        'learn' => "Learn means to get knowledge or skill by studying, practicing, or being taught."
    ];
    
    foreach ($commonWords as $word => $definition) {
        if (strpos($message, $word) !== false) {
            return $definition;
        }
    }
    
    // Help questions
    if (preg_match('/\b(help|support|assist)\b/', $message)) {
        return "I'm here to help you with English learning! I can assist with: vocabulary meanings, grammar explanations, study tips, practice conversations, and more. What do you need help with?";
    }
    
    // Default response for unrecognized messages
    $defaultResponses = [
        "That's an interesting question! Could you be more specific about what you'd like to learn in English?",
        "I'm still learning to understand all questions. Could you rephrase that or ask about English vocabulary, grammar, or study tips?",
        "I'd love to help you with that! Try asking me about English words, grammar rules, or how to improve your English skills.",
        "I'm your English learning assistant. Feel free to ask me about vocabulary meanings, grammar questions, or study advice!"
    ];
    
    return $defaultResponses[array_rand($defaultResponses)];
}



if (empty($_POST['type'])) {
    $result = array(
        'status' => 'error',
        'message' => 'Dữ liệu không tồn tại'
    );
    return die(json_encode($result));
}
if (!isset($_SESSION["account"])) {
    $result = array(
        'status' => 'error',
        'message' => getMessageError2('Vui lòng đăng nhập vào hệ thống')
    );
    return die(json_encode($result));
}
checkAccountExist();

if ($_POST['type'] == 'TaoRoom') {
    try {
        $taiKhoan = $_SESSION["account"];
        $checkRoom = $Database->get_row("SELECT * FROM chatbot_room WHERE TaiKhoan = '" . $taiKhoan . "'");
        if ($checkRoom > 0) {
            throw new Exception(getMessageError2('Bạn đã mở room chat bot rồi'));
        }
        $Database->insert("chatbot_room", [
            'TaiKhoan' => $taiKhoan
        ]);
        // thêm vào hoạt động 
        $HoatDong->insertHoatDong([
            'MaLoaiHoatDong' => 1,
            'TenHoatDong' => 'Tạo phòng chat bot',
            'NoiDung' => 'Tạo phòng chat bot mới',
            'TaiKhoan' => $taiKhoan
        ]);
        $result = array(
            'status' => 'success',
            'message' => getMessageSuccess2('Thành công'),
        );
        return die(json_encode($result));
    } catch (Exception $err) {
        $result = array(
            'status' => 'error',
            'message' => $err->getMessage()
        );
        return die(json_encode($result));
    }
}
if ($_POST['type'] == 'UpdateChatBotResponse') {
    try {
        $taiKhoan = $_SESSION["account"];
        $content = ($_POST["content"]);
        $thoiGian = $_POST["thoiGian"];
        $room = $_POST["room"];

        $checkRoom = $Database->get_row("SELECT * FROM chatbot_room WHERE TaiKhoan = '" . $taiKhoan . "' and MaRoom = '" . $room . "' ");
        if ($checkRoom <= 0) {
            throw new Exception(getMessageError2('Bạn chưa mở room chat bot'));
        }
        // Thêm câu trả lời của chat bot vào database
        $Database->insert("message_chatbot_room", [
            'MaRoom' => $room,
            'NoiDung' => $content,
            'ThoiGian' => $thoiGian,
            'Role' => 'assistant'
        ]);


        $result = array(
            'status' => 'success',
            'message' => getMessageSuccess2('Thành công'),
        );
        return die(json_encode($result));
    } catch (Exception $err) {
        $result = array(
            'status' => 'error',
            'message' => $err->getMessage()
        );
        return die(json_encode($result));
    }
}
if ($_POST['type'] == 'SendMessage') {
    try {
        $taiKhoan = $_SESSION["account"];
        $content = check_string($_POST["content"]);
        $room = $_POST["room"];
        if (empty($content) || empty($room)) {
            throw new ErrorException(getMessageError2('Vui lòng nhập đầy đủ dữ liệu'));
        }
        $checkChatBotRoom = $Database->get_row("select * from chatbot_room where TaiKhoan = '" . $_SESSION["account"] . "' and MaRoom = '" . $room . "' ");
        if ($checkChatBotRoom <= 0) {
            throw new ErrorException(getMessageError2('Phòng chat bot không tồn tại'));
        }
        $getTime =  getTime();
        
        // Insert user message vào lịch sử chat
        $insertResult =  $Database->insert("message_chatbot_room", [
            'MaRoom' => $room,
            'NoiDung' => ($content),
            'ThoiGian' =>  $getTime,
            'Role' => 'user'
        ]);
        
        // Generate AI response
        $aiResponse = generateAIResponse($content);
        $responseTime = getTime();
        
        // Insert AI response vào database
        $Database->insert("message_chatbot_room", [
            'MaRoom' => $room,
            'NoiDung' => $aiResponse,
            'ThoiGian' => $responseTime,
            'Role' => 'assistant'
        ]);
        
        // thêm vào hoạt động 
        $HoatDong->insertHoatDong([
            'MaLoaiHoatDong' => 1,
            'TenHoatDong' => 'Hỏi ChatBot',
            'NoiDung' => 'Hỏi chat bot về câu hỏi: "' . ($content) . '"',
            'TaiKhoan' => $taiKhoan
        ]);
        
        $result = array(
            'status' => 'success',
            'message' => getMessageSuccess2('Thành công'),
            'data' => array(
                'userMessage' => $content,
                'aiResponse' => $aiResponse,
                'userTime' => $getTime,
                'responseTime' => $responseTime
            )
        );
        return die(json_encode($result));
    } catch (Exception $err) {
        $result = array(
            'status' => 'error',
            'message' => $err->getMessage()
        );
        return die(json_encode($result));
    }
}

if ($_POST['type'] == 'DeleteMessages') {
    try {
        $taiKhoan = $_SESSION["account"];
        $room = $_POST["room"];
        if (empty($room)) {
            throw new ErrorException(getMessageError2('Vui lòng nhập phòng chat'));
        }
        $checkChatBotRoom = $Database->get_row("select * from chatbot_room where TaiKhoan = '" . $_SESSION["account"] . "' and MaRoom = '" . $room . "' ");
        if ($checkChatBotRoom <= 0) {
            throw new ErrorException(getMessageError2('Phòng chat bot không tồn tại'));
        }
        // Xóa tin nhắn
        $Database->query("delete from message_chatbot_room where MaRoom = '" . $room . "' ");
        // thêm vào hoạt động 
        $HoatDong->insertHoatDong([
            'MaLoaiHoatDong' => 1,
            'TenHoatDong' => 'Xóa tin nhắn DungDepTrai',
            'NoiDung' => 'Xóa tin nhắn DungDepTrai',
            'TaiKhoan' => $taiKhoan
        ]);
        $result = array(
            'status' => 'success',
            'message' => getMessageSuccess2('Thành công'),
        );
        return die(json_encode($result));
    } catch (Exception $err) {
        $result = array(
            'status' => 'error',
            'message' => $err->getMessage()
        );
        return die(json_encode($result));
    }
}

if ($_POST['type'] == 'GetHistoryMessages') {
    try {
        $room = $_POST["room"];
        $checkChatBotRoom = $Database->get_row("select * from chatbot_room where TaiKhoan = '" . $_SESSION["account"] . "' and MaRoom = '" . $room . "' ");
        if ($checkChatBotRoom <= 0) {
            throw new ErrorException(getMessageError2('Phòng chat bot không tồn tại'));
        }
        $data = $Database->get_list("select * from message_chatbot_room where MaRoom = '" . $room . "' order by ThoiGian asc ");
        $result = array(
            'status' => 'success',
            'message' => getMessageSuccess2('Thành công'),
            'data' => $data
        );
        return die(json_encode($result));
    } catch (Exception $err) {
        $result = array(
            'status' => 'error',
            'message' => $err->getMessage()
        );
        return die(json_encode($result));
    }
}
