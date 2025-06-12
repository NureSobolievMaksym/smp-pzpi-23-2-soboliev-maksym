<?php
session_start();

// Перевіряємо, чи користувач залогінений
$is_logged_in = isset($_SESSION['user']);

// Визначаємо, яку сторінку завантажувати
$page = $_GET['page'] ?? 'products';

// Якщо користувач не залогінений, дозволяємо доступ тільки до сторінки логіну
if (!$is_logged_in && $page !== 'login') {
    $page = 'page404';
}

// Включаємо шапку сайту
include 'header.php';

// Використовуємо switch для підключення потрібної сторінки
switch ($page) {
    case 'cart':
        require_once('cart.php');
        break;
    case 'profile':
        require_once('profile.php');
        break;
    case 'login':
        require_once('login.php');
        break;
    case 'products':
        require_once('products.php');
        break;
    default:
        require_once('page404.php');
        break;
}

// Включаємо підвал сайту
include 'footer.php';
