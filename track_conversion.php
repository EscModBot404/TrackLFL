<?php
require_once dirname(__FILE__) . '/kclient.php';

session_start();  // Запускаем сессию

$client = new KClient('https://traffichot.store/', 'zfsw6vb8cwsdrg2bms6jnvkqqrhpymvs');

$client->restoreFromSession();  // Восстанавливаем данные клика из сессии

// Проверяем, есть ли clickid в сессии
if (!isset($_SESSION['clickid'])) {
    die(json_encode(["status" => "error", "message" => "Click ID не найден."]));
}

$clickid = $_SESSION['clickid'];
$event_type = $_POST['event_type'] ?? '';  // Получаем тип события

// Определяем статус и сумму конверсии в зависимости от события
switch ($event_type) {
    case 'registration':
        $status = 'lead';
        $revenue = 0;
        break;
    case 'purchase':
        $status = 'approved';
        $revenue = $_POST['amount'] ?? 50;
        break;
    case 'spend_credits':
        $status = 'credit_spent';
        $revenue = $_POST['credits_spent'] ?? 10;
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

echo json_encode(["status" => "success", "message" => "Конверсия записана.", "keitaro_response" => $output]);
