<?php
// Список товарів (потрібен для отримання назви та ціни)
$products = [
    1 => ['name' => 'Coca-Cola', 'price' => 15],
    2 => ['name' => 'Fanta', 'price' => 14],
    3 => ['name' => 'Sprite', 'price' => 14],
    4 => ['name' => 'Nuts', 'price' => 25],
    5 => ['name' => 'Snickers', 'price' => 22],
];

// Обробка видалення товару
if (isset($_GET['remove'])) {
    $productIdToRemove = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$productIdToRemove])) {
        unset($_SESSION['cart'][$productIdToRemove]);
    }
    header('Location: index.php?page=cart');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
?>

<h2>Ваш кошик</h2>

<?php if (empty($cart)): ?>
    <p class="cart-empty">Кошик порожній. <a href="index.php?page=products">Перейти до покупок</a>.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>Назва</th>
                <th>Ціна</th>
                <th>Кількість</th>
                <th>Сума</th>
                <th>Дія</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalSum = 0;
            foreach ($cart as $productId => $quantity):
                if (isset($products[$productId])):
                    $product = $products[$productId];
                    $sum = $product['price'] * $quantity;
                    $totalSum += $sum;
            ?>
                <tr>
                    <td><?php echo $productId; ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>$<?php echo $product['price']; ?></td>
                    <td><?php echo $quantity; ?></td>
                    <td>$<?php echo $sum; ?></td>
                    <td><a href="index.php?page=cart&remove=<?php echo $productId; ?>">Вилучити</a></td>
                </tr>
            <?php 
                endif;
            endforeach;
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">РАЗОМ ДО СПЛАТИ:</td>
                <td colspan="2" style="font-weight: bold;">$<?php echo $totalSum; ?></td>
            </tr>
        </tfoot>
    </table>
<?php endif; ?>
