<?php
// Якщо користувач вже залогінений, перенаправляємо його
if (isset($_SESSION['user'])) {
    header('Location: index.php?page=products');
    exit;
}

require_once 'credential.php'; // Підключаємо файл з обліковими даними

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Перевірка, чи поля не порожні
    if (empty($username) || empty($password)) {
        $error_message = 'Будь ласка, заповніть обидва поля.';
    } 
    // Перевірка облікових даних
    elseif ($username === $credentials['userName'] && $password === $credentials['password']) {
        // Успішний вхід
        $_SESSION['user'] = [
            'username' => $username,
            'login_time' => date('Y-m-d H:i:s')
        ];
        header('Location: index.php?page=products');
        exit;
    } else {
        // Неправильні дані
        $error_message = 'Неправильне ім\'я користувача або пароль.';
    }
}
?>

<h2>Вхід в систему</h2>

<?php if (!empty($error_message)): ?>
    <div class="error-message"><?php echo $error_message; ?></div>
<?php endif; ?>

<form method="POST" action="index.php?page=login">
    <div class="form-group">
        <label for="username">Ім'я користувача:</label>
        <input type="text" id="username" name="username">
    </div>
    <div class="form-group">
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password">
    </div>
    <button type="submit">Увійти</button>
</form>
