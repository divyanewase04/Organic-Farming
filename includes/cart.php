<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function getCart(): array
{
    $cart = $_SESSION['cart'] ?? [];

    return is_array($cart) ? $cart : [];
}

function saveCart(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function cartAdd(int $productId, int $quantity = 1): void
{
    if ($productId <= 0 || $quantity <= 0) {
        return;
    }

    $cart = getCart();
    $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
    saveCart($cart);
}

function cartUpdate(int $productId, int $quantity): void
{
    $cart = getCart();

    if ($quantity <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $quantity;
    }

    saveCart($cart);
}

function cartRemove(int $productId): void
{
    $cart = getCart();
    unset($cart[$productId]);
    saveCart($cart);
}

function cartClear(): void
{
    unset($_SESSION['cart']);
}

function cartItemCount(): int
{
    return array_sum(getCart());
}

function cartSummary(PDO $pdo): array
{
    $cart = getCart();
    if ($cart === []) {
        return [
            'items' => [],
            'subtotal' => 0.0,
        ];
    }

    $productIds = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $query = "SELECT id, name, price, stock_qty, image_url FROM products WHERE id IN ($placeholders) AND is_active = 1";
    $statement = $pdo->prepare($query);
    $statement->execute($productIds);
    $products = $statement->fetchAll();

    $productsById = [];
    foreach ($products as $product) {
        $productsById[(int) $product['id']] = $product;
    }

    $items = [];
    $subtotal = 0.0;

    foreach ($cart as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if (!isset($productsById[$productId])) {
            continue;
        }

        $product = $productsById[$productId];
        $unitPrice = (float) $product['price'];
        $lineTotal = $unitPrice * $quantity;
        $subtotal += $lineTotal;

        $items[] = [
            'id' => $productId,
            'name' => $product['name'],
            'price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
            'stock_qty' => (int) $product['stock_qty'],
            'image_url' => $product['image_url'],
        ];
    }

    return [
        'items' => $items,
        'subtotal' => $subtotal,
    ];
}
