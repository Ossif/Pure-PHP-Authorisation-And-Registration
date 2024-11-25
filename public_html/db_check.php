<?php
// Функция получения информации о БД из файла .env
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath);
    foreach ($lines as $line) {
        if (trim($line) === '' || strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', trim($line), 2);
        $key = trim($key);
        $value = trim($value);

        putenv("$key=$value");
        $_ENV[$key] = $value; 
    }
}

loadEnv(__DIR__ . '/.env');

// Проверяем, существует ли соединение
if (!isset($conn)) {
    $conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), "");
    
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
}

// Проверка существования базы данных
$result = $conn->query("SHOW DATABASES LIKE '" . getenv('DB_NAME') . "'");

if (!$result || $result->num_rows <= 0) {
    header('Location: initialise.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
} else {
    $db_selected = $conn->select_db(getenv('DB_NAME'));
}
?>
