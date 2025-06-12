<?php
$profile_file = 'profile_data.php';
$profile_data = include $profile_file;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Отримання даних з форми
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $birthDate = trim($_POST['birthDate'] ?? '');
    $about = trim($_POST['about'] ?? '');

    // Валідація
    if (empty($firstName) || empty($lastName) || empty($birthDate) || empty($about)) {
        $error_message = 'Всі поля повинні бути заповнені.';
    } elseif (strlen($firstName) <= 1 || strlen($lastName) <= 1) {
        $error_message = 'Ім\'я та прізвище повинні містити більше одного символу.';
    } elseif (strlen($about) < 50) {
        $error_message = 'Інформація про себе повинна містити не менше 50 символів.';
    } else {
        // Валідація віку (не менше 16 років)
        $today = new DateTime();
        $birth = new DateTime($birthDate);
        $age = $today->diff($birth)->y;
        if ($age < 16) {
            $error_message = 'Користувачеві має бути не менше 16 років.';
        } else {
            // Обробка завантаження файлу
            $photo_path = $profile_data['photo']; // Зберігаємо старий шлях
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['photo'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($file['tmp_name']);
                
                if (in_array($file_type, $allowed_types)) {
                    $upload_dir = 'uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $filename = uniqid() . '-' . basename($file['name']);
                    $destination = $upload_dir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $photo_path = $destination; // Оновлюємо шлях, якщо завантаження успішне
                    } else {
                        $error_message = 'Помилка під час завантаження файлу.';
                    }
                } else {
                    $error_message = 'Недопустимий тип файлу. Дозволені формати: jpeg, png, gif.';
                }
            }

            if (empty($error_message)) {
                // Створення нового масиву даних для збереження
                $new_data = [
                    'firstName' => $firstName,
                    'lastName'  => $lastName,
                    'birthDate' => $birthDate,
                    'about'     => $about,
                    'photo'     => $photo_path
                ];
                
                // Збереження даних у файл
                file_put_contents($profile_file, '<?php return ' . var_export($new_data, true) . ';');
                $success_message = 'Профіль успішно оновлено!';
                $profile_data = $new_data; // Оновлюємо дані на сторінці
            }
        }
    }
}
?>

<h2>Профіль користувача</h2>

<?php if ($error_message): ?>
    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<form method="POST" action="index.php?page=profile" enctype="multipart/form-data">
    <div class="profile-photo">
        <?php if (!empty($profile_data['photo']) && file_exists($profile_data['photo'])): ?>
            <img src="<?php echo htmlspecialchars($profile_data['photo']); ?>" alt="Фото профілю" width="150">
        <?php else: ?>
            <p>Фото не завантажено</p>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="firstName">Ім'я:</label>
        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($profile_data['firstName'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="lastName">Прізвище:</label>
        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($profile_data['lastName'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="birthDate">Дата народження:</label>
        <input type="date" id="birthDate" name="birthDate" value="<?php echo htmlspecialchars($profile_data['birthDate'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="about">Про себе:</label>
        <textarea id="about" name="about" rows="5"><?php echo htmlspecialchars($profile_data['about'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="photo">Завантажити нове фото:</label>
        <input type="file" id="photo" name="photo">
    </div>
    <button type="submit">Зберегти</button>
</form>
