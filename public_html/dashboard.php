<?php
// Дебаг
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_check.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$err_str = "";

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Получение данных пользователя
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$stmt->bind_result($id, $name_from_db, $phone_from_db, $email_from_db, $password_hash);

if (!$stmt->fetch()) { 
    echo "Ошибка получения данных пользователя."; 
    exit(); 
}
$stmt->close();

// Инициализация переменных для формы
$name = $name_from_db;
$phone = $phone_from_db;
$email = $email_from_db;

// Обработка формы изменения данных
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Проверка на изменения
    if ($name === $name_from_db && $phone === $phone_from_db && $email === $email_from_db) {
        $err_str = "Нет изменений для сохранения.";
    } else {
        // Валидация введенных данных
        if (!preg_match('/^\+?[0-9]\d{10}$/', $phone)) {
            $err_str = "Введите корректный номер телефона";
            $phone = $phone_from_db;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err_str = "Введите корректный email.";
            $email = $email_from_db;
        } else {
            // Проверяем, используется ли email, телефон и логин другим пользователем
            $stmt = $conn->prepare("SELECT * FROM users WHERE (email=? OR phone=? OR name=?) AND id!=?");
            $stmt->bind_param("sssi", $email, $phone, $name, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($row['email'] === $email) {
                        $err_str = "Email уже используется.";
                        $name = $name_from_db;
                        $phone = $phone_from_db;
                        $email = $email_from_db;
                    } elseif ($row['phone'] === $phone) {
                        $err_str = "Телефон уже используется.";
                        $name = $name_from_db;
                        $phone = $phone_from_db;
                        $email = $email_from_db;
                    } elseif ($row['name'] === $name) {
                        $err_str = "Логин уже используется.";
                        $name = $name_from_db;
                        $phone = $phone_from_db;
                        $email = $email_from_db;
                    }
                }
            } else {
                // Обновление данных пользователя
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
                $stmt->bind_param("ssii", $name, $email, $phone, $user_id);

                if ($stmt->execute()) {
                    // Обновляем данные пользователя в сессии
                    $_SESSION['user_name'] = $name;
                    $err_str = "Данные успешно обновлены!";
                } else {
                    $err_str = "Ошибка обновления данных: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    // Установка цвета сообщения
    if ($err_str == "Данные успешно обновлены!") {
        $msg_color = "black";
    } else {
        $msg_color = "red";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Панель управления</title>
</head>
<body>
   <h1>Добро пожаловать, <?php echo htmlspecialchars($name); ?>!</h1>

   <form method="POST" action="dashboard.php">
       <input type="text" name="name" placeholder="Имя" value="<?php echo htmlspecialchars($name); ?>" required>
       <input type="text" name="phone" placeholder="Телефон" value="<?php echo htmlspecialchars($phone); ?>" required>
       <input type="text" name="email" placeholder="Почта" value="<?php echo htmlspecialchars($email); ?>" required>
       <button type="submit">Сохранить изменения</button>
   </form>

   <a href="logout.php">Выйти</a>
   <h3 style="color:<?php echo htmlspecialchars($msg_color); ?>;"><?php echo htmlspecialchars($err_str); ?></h3>
</body>
</html>
