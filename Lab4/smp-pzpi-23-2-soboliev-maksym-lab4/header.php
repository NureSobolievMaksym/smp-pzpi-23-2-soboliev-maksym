<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web-магазин "Весна"</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Web-магазин "Весна"</h1>
    </header>
    <nav>
        <a href="index.php?page=products">Товари</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="index.php?page=cart">Кошик</a>
            <a href="index.php?page=profile">Профіль</a>
            <a href="logout.php">Вийти</a>
        <?php else: ?>
            <a href="index.php?page=login">Увійти</a>
        <?php endif; ?>
    </nav>
    <main>
