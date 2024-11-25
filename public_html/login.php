<?php
//Дебаг
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include 'db_check.php';

if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();

$contact = "";
$err_str = "";
// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contact = $_POST['contact'];
    $password = $_POST['password'];

   // Проверка пользователя в БД с учетом валидации ввода

   if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
       // Если введен email 
       if ($stmt = $conn->prepare("SELECT * FROM users WHERE email=?")) { 
           $stmt->bind_param("s", $contact); 
           $stmt->execute(); 


           $stmt->bind_result($id, $name_from_db, $phone_from_db, $email_from_db, $password_hash); 

           if ($stmt->fetch()) { 
               if (password_verify($password, $password_hash)) { 
                   $_SESSION['user_id'] = $id; 
                   header('Location: dashboard.php'); 
                   exit(); 
               } else { 
                   $err_str = "Неверный пароль."; 
               } 
           } else { 
               $err_str = "Пользователь не найден."; 
           } 
       }

   } elseif (preg_match('/^[0-9]{10}$/', $contact)) { 
       // Если введен телефон
       if ($stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE phone=?")) { 
           $stmt->bind_param("s", $contact); 
           $stmt->execute(); 

           $stmt->bind_result($id, $password_hash); 

           if ($stmt.fetch()) { 
               if (password_verify($password, $password_hash)) { 
                   $_SESSION['user_id'] = $id; 
                   header('Location: dashboard.php'); 
                   exit(); 
               } else { 
                   $err_str = "Неверный пароль."; 
               } 
           } else { 
               $err_str = "Пользователь не найден."; 
           }
       }
   } else { 
       $err_str = "Введите корректный телефон или почту."; 
   }

   if (isset($stmt)) { 
       $stmt->close(); 
   }
}
?>

<form method="POST" action="login.php">
   <input type="text" name="contact" placeholder="Телефон или почта" value = "<?php echo htmlspecialchars($contact) ?>"required>
   <input type="password" name="password" placeholder="Пароль" required>
   <button type="submit">Войти</button>
   <a href="register.php">Создать аккаунт</a>
   <h3 style="color:red;"><?php echo htmlspecialchars($err_str); ?></h3>
</form>
