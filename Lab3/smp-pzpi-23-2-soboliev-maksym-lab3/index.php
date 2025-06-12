<?php
// Починаємо сесію, щоб зберігати дані кошика
session_start();

// Список товарів
$products = [
    1 => ['name' => 'Coca-Cola', 'price' => 15],
    2 => ['name' => 'Fanta', 'price' => 14],
    3 => ['name' => 'Sprite', 'price' => 14],
    4 => ['name' => 'Nuts', 'price' => 25],
    5 => ['name' => 'Snickers', 'price' => 22],
];

// Обробка POST-запиту (коли користувач натискає "Купити")
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ініціалізуємо кошик, якщо його ще немає
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $has_valid_items = false;

    // Перебираємо товари, які надіслав користувач
    if (isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $productId => $quantity) {
            // Валідація: кількість має бути числом більше нуля
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
            if ($quantity && $quantity > 0) {
                // Додаємо товар у кошик або оновлюємо кількість
                $_SESSION['cart'][$productId] = ($quantity);
                $has_valid_items = true;
            }
        }
    }
    
    // Якщо хоча б один товар був доданий, перенаправляємо до кошика
    if ($has_valid_items) {
        header('Location: basket.php');
        exit;
    } else {
        // Якщо дані не валідні, зберігаємо помилку в сесію
        $_SESSION['error_message'] = "Перевірте будь ласка введені дані. Кількість має бути цілим числом більше 0.";
        header('Location: index.php');
        exit;
    }
}

// Включаємо шапку сторінки
include 'header.php';
?>

<h2>Список товарів</h2>

<?php
// Відображення повідомлення про помилку, якщо воно є
if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
    // Видаляємо повідомлення, щоб воно не з\'являлося знову
    unset($_SESSION['error_message']);
}
?>

<form method="POST" action="index.php">
    <?php foreach ($products as $id => $product): ?>
        <div class="product-item">
            <span><?php echo htmlspecialchars($product['name']); ?> - $<?php echo $product['price']; ?></span>
            <input type="number" name="quantities[<?php echo $id; ?>]" value="0" min="0">
        </div>
    <?php endforeach; ?>
    <button type="submit">Купити</button>
</form>

<?php
// Включаємо підвал сторінки
include 'footer.php';
?>
