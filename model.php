<?php
const CATALOG_FILE = __DIR__ . '/data/catalog.json';

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

function save_catalog(array $catalog, string $file = CATALOG_FILE): bool
{
    $json = json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    // @ — не показываем системное предупреждение: факт неудачи возвращаем кодом.
    return @file_put_contents($file, $json, LOCK_EX) !== false;
}

function restore_default_catalog(string $file): array
{
    if (is_file($file)) {
        @rename($file, $file . '.broken.' . date('YmdHis'));
    }
    $catalog = default_catalog();
    save_catalog($catalog, $file);
    return $catalog;
}

function load_catalog(string $file = CATALOG_FILE): array
{
    if (!is_file($file) || !is_readable($file)) {
        return restore_default_catalog($file);
    }

    $json = file_get_contents($file);
    if ($json === false) {
        return restore_default_catalog($file);
    }

    if (!json_validate($json)) {
        return restore_default_catalog($file);
    }

    $catalog = json_decode($json, true);

    if (!is_array($catalog) || !array_is_list($catalog)) {
        return restore_default_catalog($file);
    }

    return $catalog;
}

function catalog_stats(array $catalog): array
{
    $stock = 0;
    $value = 0.0;
    foreach ($catalog as $product) {
        $stock += (int) $product['stock'];
        $value += (float) $product['price'] * (int) $product['stock'];
    }
    return [
        'products' => count($catalog),
        'stock'    => $stock,
        'value'    => $value,
    ];
}