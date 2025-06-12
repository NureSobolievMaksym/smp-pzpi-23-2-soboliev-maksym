<?php
session_start(); // Запускаємо сесію, щоб отримати до неї доступ
session_destroy(); // Знищуємо всі дані сесії
header('Location: index.php?page=login'); // Перенаправляємо на сторінку входу
exit;
