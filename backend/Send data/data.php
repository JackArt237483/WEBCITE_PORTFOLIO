<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json');

// Получаем сырые POST данные
$data = file_get_contents("php://input");
$formData = json_decode($data, true);

$name = $formData['name'] ?? null;
$email = $formData['email'] ?? null;
$message = $formData['message'] ?? null;

$response = [];

if ($name && $email && $message) {
    try {
        // Подключение к базе данных SQLite
        $pdo = new PDO('sqlite:' .'./identifier.sqlite');

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Подготовка и выполнение SQL запроса
        $stmt = $pdo->prepare("INSERT INTO contact_form (name, email, message) VALUES (:name, :email, :message)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message);
        $stmt->execute();

        $response = [
            'status' => 'success',
            'message' => 'Форма успешно отправлена',
            'data' => $formData
        ];
    } catch (PDOException $e) {
        $response = [
            'status' => 'error',
            'message' => 'Ошибка базы данных: ' . $e->getMessage(),
            'data' => null
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Некоторые поля пусты',
        'data' => null
    ];
}

echo json_encode($response);
?>
