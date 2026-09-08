<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$key = getenv('GEMINI_API_KEY') ?: ($_SERVER['GEMINI_API_KEY'] ?? '');
if (!$key) {
    http_response_code(503);
    echo json_encode(['error' => 'Gemini AI is not configured on this Hostinger site. The core timetable system does not require Gemini.']);
    exit;
}

$body = json_decode(file_get_contents('php://input') ?: '{}', true);
$prompt = trim((string)($body['prompt'] ?? ''));
if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

$schema = [
    'type' => 'object',
    'properties' => [
        'type' => ['type'=>'string','enum'=>['generate_timetable','schedule_course','delete_timetable_entry','validate','report','none']],
        'course_code' => ['type'=>'string'], 'course' => ['type'=>'string'], 'teacher' => ['type'=>'string'],
        'program' => ['type'=>'string'], 'semester' => ['type'=>'string'], 'section' => ['type'=>'string'],
        'day' => ['type'=>'string','enum'=>['Monday','Tuesday','Wednesday','Thursday','Friday']],
        'start_time' => ['type'=>'string'], 'duration_slots' => ['type'=>'integer','minimum'=>1,'maximum'=>3],
        'session_type' => ['type'=>'string','enum'=>['Theory','Lab']], 'resource' => ['type'=>'string'],
        'entry_id' => ['type'=>'string'], 'report' => ['type'=>'string'], 'message' => ['type'=>'string']
    ],
    'required' => ['type'],
    'additionalProperties' => false
];

$system = 'You are the UVAS Conflict-Free Timetable command interpreter. Convert the user request into ONE JSON action. You never modify data. A deterministic application validates every mutation. Hard rules: Monday-Friday only; 50-minute slots; theory may use exactly 1, 2, or 3 consecutive slots; lab is exactly 3 consecutive slots; no teacher, section, classroom or lab overlap; unavailable faculty periods are forbidden. Do not invent official course codes or IDs. If ambiguous, return type none with a concise message. For generation use generate_timetable. For a specific allocation use schedule_course and include course_code when supplied, teacher, section, day, start_time, duration_slots, session_type and resource when supplied. For deletion use delete_timetable_entry only when entry_id is explicitly provided. For workload/conflict/free-resource questions use report or validate.';

$request = [
    'systemInstruction' => ['parts' => [['text' => $system]]],
    'contents' => [[
        'role' => 'user',
        'parts' => [['text' => json_encode(['prompt'=>$prompt,'context'=>$body['data'] ?? []], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]]
    ]],
    'generationConfig' => [
        'responseMimeType' => 'application/json',
        'responseSchema' => $schema,
        'temperature' => 0
    ]
];

$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.7-flash:generateContent');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: '.$key],
    CURLOPT_POSTFIELDS => json_encode($request, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 45
]);
$response = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Gemini request failed: '.$error]);
    exit;
}

$data = json_decode($response, true);
if ($status < 200 || $status >= 300) {
    http_response_code($status ?: 502);
    echo json_encode(['error' => $data['error']['message'] ?? 'Gemini request failed']);
    exit;
}

$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
$action = json_decode($text, true);
if (!is_array($action)) {
    http_response_code(502);
    echo json_encode(['error' => 'Gemini returned malformed JSON']);
    exit;
}

echo json_encode(['action' => $action], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
