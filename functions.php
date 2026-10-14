<?php

const CATALOG_FILE = __DIR__ . '/data/catalog.json';
const PER_PAGE = 5;

function default_catalog(): array
{
    return [
        ['id' => 1, 'title' => 'Ноутбук Lenovo IdeaPad',   'category' => 'Техника',    'price' => 54990, 'stock' => 12, 'discount' => 10],
        ['id' => 2, 'title' => 'Смартфон Xiaomi Redmi',    'category' => 'Техника',    'price' => 21990, 'stock' => 30, 'discount' => 5],
        ['id' => 3, 'title' => 'Наушники JBL Tune',        'category' => 'Аксессуары', 'price' => 3490,  'stock' => 0,  'discount' => 0],
        ['id' => 4, 'title' => 'Клавиатура Logitech',      'category' => 'Аксессуары', 'price' => 2790,  'stock' => 45, 'discount' => 15],
        ['id' => 5, 'title' => 'Мышь беспроводная',        'category' => 'Аксессуары', 'price' => 1290,  'stock' => 80, 'discount' => 0],
        ['id' => 6, 'title' => 'Игровая приставка PS5',    'category' => 'Игры',       'price' => 47990, 'stock' => 6,  'discount' => 0],
        ['id' => 7, 'title' => 'Настольная игра «Мафия»',  'category' => 'Игры',       'price' => 1590,  'stock' => 3,  'discount' => 5],
        ['id' => 8, 'title' => 'Зарядное устройство USB',  'category' => 'Аксессуары', 'price' => 890,   'stock' => 50, 'discount' => 0],
    ];
}

function e(mixed $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function search_by_name(array $catalog, string $query): array
{
    $query = mb_strtolower(trim($query));
    if ($query === ''){
        return $catalog;
    }

    $result = [];
    foreach ($catalog as $product) {
        if (str_contains(mb_strtolower($product['title']), $query)) {
            $result[] = $product;
        }
    }
    return $result;
}

function filter_by_category(array $catalog, ?string $category): array
{
    if ($category === null || $category === '' || $category === 'all') {
        return $catalog;
    }

    $result = [];
    foreach ($catalog as $product) {
        if ($product['category'] === $category) {
            $result[] = $product;
        }
    }
    return $result;
}

function unique_categories(array $catalog): array
{
    $categories = [];
    foreach ($catalog as $product) {
        $categories[$product['category']] = true;
    }
    return array_keys($categories);
}

function validate_product(array $data): array
{
    $errors = [];

    $title = trim((string) ($data['title'] ?? ''));
    if ($title === '') {
        $errors['title'] = 'Укажите название товара.';
    } elseif (mb_strlen($title) < 3) {
        $errors['title'] = 'Название должно быть не короче 3 символов.';
    }

    $category = trim((string) ($data['category'] ?? ''));
    if ($category === '') {
        $errors['category'] = 'Выберите категорию.';
    }

    $price = $data['price'] ?? '';
    if (!is_numeric($price)) {
        $errors['price'] = 'Цена должна быть числом.';
    } elseif ((float) $price <= 0) {
        $errors['price'] = 'Цена должна быть больше нуля.';
    }

    $stock = $data['stock'] ?? '';
    if ($stock !== '' && !is_numeric($stock)) {
        $errors['stock'] = 'Остаток — целое число.';
    } elseif ((int) $stock < 0) {
        $errors['stock'] = 'Остаток не может быть отрицательным.';
    }

    $discount = $data['discount'] ?? '';
    if ($discount !== '' && !is_numeric($discount)) {
        $errors['discount'] = 'Скидка — число в процентах.';
    } elseif ((float) $discount < 0 || (float) $discount > 100) {
        $errors['discount'] = 'Скидка должна быть от 0 до 100 %.';
    }

    return $errors;
}

function next_id(array $catalog): int
{
    $max = 0;
    foreach ($catalog as $product) {
        $max = max($max, (int) ($product['id'] ?? 0));
    }
    return $max + 1;
}

function sanitize_product(array $data, int $id): array
{
    return [
        'id'       => $id,
        'title'    => trim((string) ($data['title'] ?? '')),
        'category' => trim((string) ($data['category'] ?? '')),
        'price'    => (int) round((float) ($data['price'] ?? 0)),
        'stock'    => (int) ($data['stock'] ?? 0),
        'discount' => (int) ($data['discount'] ?? 0),
    ];
}

function price_with_discount(float $price, int $discount): float
{
    if ($discount <= 0) {
        return $price;
    }
    return round($price * (100 - $discount) / 100, 2);
}

function stock_label(int $stock): string
{
    return match (true) {
        $stock === 0 => 'Нет в наличии',
        $stock <= 5  => 'Мало',
        default      => 'В наличии',
    };
}