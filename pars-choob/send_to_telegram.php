<?php
// ===============================
// ارسال نظر سایت به ربات تلگرام
// فایل را کنار index.html قرار دهید.
// ===============================

// 1) توکن ربات را از @BotFather بگیرید و اینجا قرار دهید:

// 2) Chat ID تلگرام خودتان را اینجا قرار دهید:
$CHAT_ID = "amiralimhdii";

header("Content-Type: application/json; charset=utf-8");

// فقط درخواست POST پذیرفته می‌شود
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Method Not Allowed"]);
    exit;
}

$name = trim($_POST["name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$comment = trim($_POST["comment"] ?? "");

if ($comment === "") {
    http_response_code(400);
    echo json_encode(["ok" => false, "message" => "متن نظر خالی است."]);
    exit;
}

// محدودیت طول برای جلوگیری از سوءاستفاده
$name = mb_substr($name, 0, 100);
$phone = mb_substr($phone, 0, 50);
$comment = mb_substr($comment, 0, 4000);

$text = "📩 نظر جدید از سایت\n\n";
if ($name !== "")  $text .= "👤 نام: " . $name . "\n";
if ($phone !== "") $text .= "📱 تلفن: " . $phone . "\n";
$text .= "\n💬 نظر:\n" . $comment;

$url = "https://t.me/" . $CHAT_ID . "/sendMessage";

$postData = [
    "chat_id" => $CHAT_ID,
    "text" => $text
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error || $httpCode < 200 || $httpCode >= 300) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => "ارسال پیام به تلگرام انجام نشد."
    ]);
    exit;
}

$result = json_decode($response, true);

if (!empty($result["ok"])) {
    echo json_encode([
        "ok" => true,
        "message" => "نظر شما با موفقیت ارسال شد."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => "تلگرام پیام را قبول نکرد."
    ]);
}
?>
