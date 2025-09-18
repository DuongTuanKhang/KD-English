<?php
/**
 * check-word.php
 * POST JSON: { "words": ["CAR","BEE", ...] }
 * Response: { "ok": true, "results": { "CAR": true, "BEE": true, ... } }
 */

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$words = isset($input['words']) && is_array($input['words']) ? $input['words'] : [];

$results = [];
if (!$words) {
    echo json_encode(['ok' => true, 'results' => $results]);
    exit;
}

/** ========= 1) BASIC GUARD + LOCAL MINI BACKUP ========= */
$clean = [];
foreach ($words as $w) {
    $W = strtoupper(trim($w));
    // only English letters 2..15
    if (!preg_match('/^[A-Z]{2,15}$/', $W)) {
        $results[$W] = false;
        continue;
    }
    $clean[] = $W;
}

$mini = [
    "GO",
    "DO",
    "TO",
    "BE",
    "AT",
    "LEARN",
    "READ",
    "WRITE",
    "PLAY",
    "GAME",
    "WORD",
    "CAR",
    "CAT",
    "DOG",
    "BEE",
    "EEL",
    "TEA",
    "EAR",
    "ARE",
    "RAT",
    "RATE",
    "IRON",
    "NOTE",
    "TONE"
];

/** ========= 2) CALL GEMINI (ONE SHOT FOR ALL WORDS) ========= */
function gemini_validate($words)
{
    // TODO: put your key here
    $API_KEY = 'AIzaSyBfkI0hnNo-FqyuqfAxGwg11tm30kbaZ4Q';

    // If no key, fall back to local
    if ($API_KEY === 'PUT_YOUR_GEMINI_API_KEY_HERE' || !$API_KEY) {
        $out = [];
        foreach ($words as $w)
            $out[$w] = null; // null = unknown
        return $out;
    }

    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . urlencode($API_KEY);

    $prompt = "You are a Scrabble dictionary checker. For the following UPPERCASE English words, return pure JSON mapping each word to true/false depending on whether it is a valid standalone English word (not names, not abbreviations). Only output JSON, no explanations.\n\nWords:\n- " . implode("\n- ", $words) . "\n\nOutput format:\n{\"WORD1\":true,\"WORD2\":false,...}";

    $payload = [
        "contents" => [
            [
                "parts" => [["text" => $prompt]]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.0,
            "topK" => 1,
            "topP" => 1
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $data = json_decode($resp, true);
    // Gemini returns candidates[0].content.parts[0].text
    $jsonText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    // Some models wrap JSON in ```...```
    $jsonText = trim($jsonText);
    $jsonText = preg_replace('/^```json|```$/m', '', $jsonText);
    $jsonText = trim($jsonText);

    $map = json_decode($jsonText, true);
    if (!is_array($map))
        return null;

    // Normalize keys to uppercase
    $out = [];
    foreach ($map as $k => $v)
        $out[strtoupper($k)] = !!$v;
    return $out;
}

$gem = gemini_validate($clean);

/** ========= 3) MERGE RESULTS (Gemini -> fallback -> strict) ========= */
foreach ($clean as $w) {
    if (isset($gem[$w]) && ($gem[$w] === true || $gem[$w] === false)) {
        $results[$w] = (bool) $gem[$w];
    } else {
        // fallback mini list if Gemini not available
        $results[$w] = in_array($w, $mini, true);
    }
}

// words that failed the regex are already false in $results

echo json_encode(['ok' => true, 'results' => $results], JSON_UNESCAPED_UNICODE);
