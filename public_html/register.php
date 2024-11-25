<?php
include 'db_check.php';

// Дебаг
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

$err_str = "";
$name = "";
$phone = "";
$email = "";

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Валидация паролей, имейла, телефона
    if ($password !== $confirm_password){
        $err_str = "Пароли не совпадают";
    } 
    else if(!preg_match('/^\+?[0-9]\d{10}$/', $phone)){
        $err_str = "Введите корректный номер телефона";
    }
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $err_str = "Введите корректный email.";
    }
    else{
        // Проверяем, используется ли email, телефон и логин
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? OR phone=? or name=?");
        $stmt->bind_param("sss", $email, $phone, $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['email'] === $email) {
                    $err_str = "Email уже используется.";
                }
                else if ($row['phone'] === $phone) {
                    $err_str = "Телефон уже используется.";
                }
                else if($row['name'] === $name){
                    $err_str = "Логин уже используется.";
                }
            }
        } else {
            // Сохранение пользователя в БД
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);

            if ($stmt->execute()) {
                // Получаем id нового пользователя
                $stmt = $conn->prepare("SELECT id FROM users WHERE phone=?");
                $stmt->bind_param("s", $phone);
                $stmt->execute();
                $stmt->bind_result($id);
                $stmt->fetch();
                $stmt->close(); 
                $_SESSION['user_id'] = $id;
                header('Location: dashboard.php'); 
            } else {
                $err_str = "Ошибка регистрации: " . $stmt->error;
            }
        }
    }
}
?>

<form method="POST" action="register.php">
    <input type="text" name="name" placeholder="Имя" value="<?php echo htmlspecialchars($name); ?>" required>
    <input type="text" name="phone" placeholder="Телефон" value="<?php echo htmlspecialchars($phone); ?>" required>
    <input type="text" name="email" placeholder="Почта" value="<?php echo htmlspecialchars($email); ?>" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
    <button type="submit">Зарегистрироваться</button>
    <a href="login.php">Есть аккаунт? Авторизуйтесь!</a>
    <h3 style="color:red;"><?php echo htmlspecialchars($err_str); ?></h3>
</form>
