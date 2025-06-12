#!/usr/bin/php
<?php

// =============================================================================
// Консольний застосунок «Продовольчий магазин "Весна"»
// Автор: Maksym Soboliev
// Лабораторна робота №2, Завдання 2.2
// =============================================================================

// --- Глобальні змінні та дані ---

// Асортимент товарів магазину
$products = [
    1 => ['name' => 'Молоко пастеризоване', 'price' => 12],
    2 => ['name' => 'Хліб чорний',           'price' => 9],
    3 => ['name' => 'Сир білий',              'price' => 21],
    4 => ['name' => 'Сметана 20%',            'price' => 25],
    5 => ['name' => 'Кефір 1%',               'price' => 19],
    6 => ['name' => 'Вода газована',          'price' => 18],
    7 => ['name' => 'Печиво "Весна"',         'price' => 14],
];

// Кошик користувача: [productId => quantity]
$basket = [];

// Профіль користувача
$userProfile = [
    'name' => null,
    'age' => null,
];


// --- Функції ---

/**
 * Очищує консоль (кросплатформено)
 */
function clear_screen() {
    // PHP_OS_FAMILY - константа, доступна з PHP 7.2.0
    PHP_OS_FAMILY === 'Windows' ? system('cls') : system('clear');
}

/**
 * Відображає головне меню програми
 */
function display_main_menu() {
    echo "################################\n";
    echo "# ПРОДОВОЛЬЧИЙ МАГАЗИН \"ВЕСНА\" #\n";
    echo "################################\n";
    echo "1 Вибрати товари\n";
    echo "2 Отримати підсумковий рахунок\n";
    echo "3 Налаштувати свій профіль\n";
    echo "0 Вийти з програми\n";
}

/**
 * Відображає список товарів для вибору
 */
function display_products_menu(array $products) {
    echo "№  НАЗВА                   ЦІНА\n";
    foreach ($products as $id => $product) {
        $namePadded = str_pad($product['name'], 25);
        $idPadded = str_pad($id, 2);
        echo "{$idPadded} {$namePadded} {$product['price']}\n";
    }
    echo "   -----------\n";
    echo "0  ПОВЕРНУТИСЯ\n";
}

/**
 * Відображає вміст кошика
 */
function display_basket(array $basket, array $products) {
    if (empty($basket)) {
        echo "КОШИК ПОРОЖНІЙ\n";
        return;
    }
    echo "У КОШИКУ:\n";
    echo "НАЗВА                   КІЛЬКІСТЬ\n";
    foreach ($basket as $productId => $quantity) {
        $name = $products[$productId]['name'];
        $namePadded = str_pad($name, 25);
        echo "{$namePadded} {$quantity}\n";
    }
}

/**
 * Безпечно зчитує ціле число з консолі
 * @return int|false
 */
function read_integer_input() {
    $input = trim(fgets(STDIN));
    // Перевіряємо, чи введене значення є цілим числом
    if (filter_var($input, FILTER_VALIDATE_INT) === false && $input !== '0') {
        return false;
    }
    return (int)$input;
}

/**
 * Обробляє процес вибору товарів
 */
function handle_product_selection(array &$basket, array $products) {
    while (true) {
        clear_screen();
        display_products_menu($products);
        echo "Виберіть товар: ";
        $productId = read_integer_input();

        if ($productId === 0) {
            return; // Повернення до головного меню
        }

        if ($productId === false || !array_key_exists($productId, $products)) {
            echo "ПОМИЛКА! ВКАЗАНО НЕПРАВИЛЬНИЙ НОМЕР ТОВАРУ\n";
            sleep(2); // Затримка для прочитання помилки
            continue;
        }

        echo "Вибрано: {$products[$productId]['name']}\n";
        echo "Введіть кількість, штук: ";
        $quantity = read_integer_input();

        if ($quantity === false || $quantity < 0 || $quantity >= 100) {
             echo "ПОМИЛКА! Кількість має бути числом від 0 до 99.\n";
             sleep(2);
             continue;
        }

        if ($quantity === 0) {
            if (isset($basket[$productId])) {
                unset($basket[$productId]);
                echo "ВИДАЛЯЮ З КОШИКА\n";
            }
        } else {
            $basket[$productId] = $quantity;
        }
        
        clear_screen();
        display_basket($basket, $products);
        echo "\nНатисніть Enter, щоб продовжити вибір товарів...";
        fgets(STDIN);
    }
}

/**
 * Обробляє та виводить підсумковий рахунок
 */
function handle_checkout(array $basket, array $products) {
    clear_screen();
    if (empty($basket)) {
        echo "КОШИК ПОРОЖНІЙ. Нічого сплачувати.\n";
        return;
    }

    echo "№  НАЗВА                   ЦІНА  КІЛЬКІСТЬ  ВАРТІСТЬ\n";
    $totalSum = 0;
    $itemNumber = 1;

    foreach ($basket as $productId => $quantity) {
        $product = $products[$productId];
        $cost = $product['price'] * $quantity;
        $totalSum += $cost;

        $numPadded = str_pad($itemNumber, 2);
        $namePadded = str_pad($product['name'], 25);
        $pricePadded = str_pad($product['price'], 5);
        $quantityPadded = str_pad($quantity, 9);

        echo "{$numPadded} {$namePadded} {$pricePadded} {$quantityPadded} {$cost}\n";
        $itemNumber++;
    }
    echo "---------------------------------------------------------\n";
    echo "РАЗОМ ДО CПЛАТИ: {$totalSum}\n";
}

/**
 * Обробляє налаштування профілю користувача
 */
function handle_profile_setup(array &$profile) {
    clear_screen();
    echo "--- НАЛАШТУВАННЯ ПРОФІЛЮ ---\n";

    // Введення та валідація імені
    while (true) {
        echo "Ваше ім'я: ";
        $name = trim(fgets(STDIN));
        // Перевірка, що ім'я не порожнє і містить хоча б одну літеру (кирилиця або латиниця)
        if (!empty($name) && preg_match('/[a-zA-Zа-яА-ЯіїІЇєЄ]/u', $name)) {
            $profile['name'] = $name;
            break;
        } else {
            echo "ПОМИЛКА! Ім'я не може бути порожнім і повинно містити літери.\n";
        }
    }

    // Введення та валідація віку
    while (true) {
        echo "Ваш вік: ";
        $age = read_integer_input();
        if ($age !== false && $age >= 7 && $age <= 150) {
            $profile['age'] = $age;
            break;
        } else {
            echo "ПОМИЛКА! Вік має бути числом від 7 до 150.\n";
        }
    }
    echo "Профіль оновлено! Ваше ім'я: {$profile['name']}, ваш вік: {$profile['age']}.\n";
}

// --- Головний цикл програми ---
clear_screen();
while (true) {
    display_main_menu();
    echo "Введіть команду: ";
    $command = read_integer_input();

    if ($command === false) {
        clear_screen();
        echo "ПОМИЛКА! Будь ласка, введіть число.\n\n";
        continue;
    }

    switch ($command) {
        case 1:
            handle_product_selection($basket, $products);
            clear_screen();
            break;
        case 2:
            handle_checkout($basket, $products);
            echo "\nНатисніть Enter, щоб повернутися до головного меню...";
            fgets(STDIN);
            clear_screen();
            break;
        case 3:
            handle_profile_setup($userProfile);
            echo "\nНатисніть Enter, щоб повернутися до головного меню...";
            fgets(STDIN);
            clear_screen();
            break;
        case 0:
            echo "Дякуємо, що завітали до нас! До побачення!\n";
            exit(0);
        default:
            clear_screen();
            echo "ПОМИЛКА! Введіть правильну команду\n\n";
            break;
    }
}

