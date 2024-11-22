<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'users_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]);
    exit;
}

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($login) && !empty($password)) {
        try {
            // Проверка уникальности логина
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Логин уже используется.']);
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Добавление пользователя
                $stmt = $pdo->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
                $stmt->execute([$login, $hashedPassword]);

                echo json_encode(['status' => 'success', 'message' => 'Регистрация успешна!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Все поля обязательны для заполнения.']);
    }
}
?>
