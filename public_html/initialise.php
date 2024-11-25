<?php
//Дебаг
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Подключение к MySQL
$conn = new mysqli('localhost', 'root', '', '');

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Создание базы данных, если она не существует
$sql = "CREATE DATABASE IF NOT EXISTS php_db";
if ($conn->query($sql) === TRUE) {
    echo "База данных php_db успешно создана или уже существует.<br>";
} else {
    echo "Ошибка создания базы данных: " . $conn->error . "<br>";
}

// Использование базы данных
$conn->select_db('php_db');

// Создание таблицы, если она не существует
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) !== TRUE) {
    echo "Ошибка создания таблицы: " . $conn->error . "<br>";
    $conn->close();
} else {
    echo "Таблица users успешно создана или уже существует.<br>";
    $conn->close();
    if (isset($_GET['redirect'])) {
        // Перенаправление обратно на исходную страницу
        header('Location: ' . urldecode($_GET['redirect']));
        exit();
    }
}

?>
