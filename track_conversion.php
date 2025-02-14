<?php
require_once dirname(__FILE__) . '/kclient.php';

session_start();  // Запускаем сессию

// Проверяем, передан ли clickid в GET-запросе
if (isset($_GET['clickid'])) {
    $_SESSION['clickid'] = $_GET['clickid']; // Сохраняем Click ID в сессию
}

// Проверяем, есть ли clickid в сессии
if (!isset($_SESSION['clickid'])) {
    die(json_encode(["status" => "error", "message" => "Click ID не найден."]));
}

$clickid = $_SESSION['clickid'];
$event_type = $_GET['event_type'] ?? '';  // Получаем тип события через GET

// Определяем статус и сумму конверсии в зависимости от события
switch ($event_type) {
    case 'registration':
        $status = 'lead';
        $revenue = 0;
        break;
    case 'purchase':
        $status = 'approved';
        $revenue = $_GET['amount'] ?? 50;
        break;
    case 'spend_credits':
        $status = 'credit_spent';
        $revenue = $_GET['credits_spent'] ?? 10;
        break;
    default:
        die(json_encode(["status" => "error", "message" => "Неизвестный тип события."]));
}

// Формируем данные для отправки в Keitaro
$params = array(
    "subid" => $clickid,
    "status" => $status,
    "payout" => $revenue
);

// Отправляем POST-запрос на Keitaro
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://traffichot.store/postback");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_POST, 1);
$output = curl_exec($ch);
curl_close($ch);

// Логируем запрос и ответ
file_put_contents('log.txt', date("Y-m-d H:i:s") . " - Sent: " . json_encode($params) . " - Response: " . $output . "\n", FILE_APPEND);

echo json_encode([
    "status" => "success",
    "message" => "Conversion is recorded",
    "event_type" => $event_type,
    "sent_data" => $params,
    "keitaro_response" => $output
]);
