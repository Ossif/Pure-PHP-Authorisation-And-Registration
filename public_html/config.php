<?php
//Получение данных из .env
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath);
    foreach ($lines as $line) {
        // Пропускаем пустые строки
        if (trim($line) === '') {
            continue;
        }

        // Убираем комментарии
        $line = explode('#', $line)[0]; // Убираем все после #
        
        // Разделяем ключ и значение
        list($key, $value) = explode('=', trim($line), 2);
        $key = trim($key);
        $value = trim($value);

        putenv("$key=$value");
        $_ENV[$key] = $value; 
    }
}

loadEnv(__DIR__ . '/.env');
?>