<?php
// Список товарів
$products = [
    1 => ['name' => 'Coca-Cola', 'price' => 15],
    2 => ['name' => 'Fanta', 'price' => 14],
    3 => ['name' => 'Sprite', 'price' => 14],
    4 => ['name' => 'Nuts', 'price' => 25],
    5 => ['name' => 'Snickers', 'price' => 22],
];

// Обробка POST-запиту
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $has_valid_items = false;
    foreach ($_POST['quantities'] as $productId => $quantity) {
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
        if ($quantity && $quantity > 0) {
            $_SESSION['cart'][$productId] = ($quantity);
            $has_valid_items = true;
        }
    }
    
    if ($has_valid_items) {
        header('Location: index.php?page=cart');
        exit;
    } else {
        $_SESSION['error_message'] = "Перевірте будь ласка введені дані. Кількість має бути цілим числом більше 0.";
        header('Location: index.php?page=products');
        exit;
    }
}
?>

<h2>Список товарів</h2>

<?php
if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>

<form method="POST" action="index.php?page=products">
    <?php foreach ($products as $id => $product): ?>
        <div class="product-item">
            <span><?php echo htmlspecialchars($product['name']); ?> - $<?php echo $product['price']; ?></span>
            <input type="number" name="quantities[<?php echo $id; ?>]" value="0" min="0">
        </div>
    <?php endforeach; ?>
    <button type="submit">Купити</button>
</form>
