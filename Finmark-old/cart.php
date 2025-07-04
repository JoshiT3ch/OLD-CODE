<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'checkout') {
    try {
        $stmt = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.price FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll();
        $total_amount = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $cart_items));

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total_amount]);
        $order_id = $pdo->lastInsertId();

        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $success = "Checkout successful! Order ID: $order_id. <a href='https://stripe.com' target='_blank' class='text-indigo-600 hover:underline'>Complete payment with Stripe</a>";
    } catch (PDOException $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        header("Location: cart.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
}

try {
    $stmt = $pdo->prepare("SELECT p.*, ci.quantity FROM products p LEFT JOIN cart_items ci ON p.id = ci.product_id AND ci.user_id = ? WHERE ci.quantity > 0");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    $total = array_sum(array_map(fn($item) => $item['price'] * ($item['quantity'] ?: 0), $cart_items));
} catch (PDOException $e) {
    $error = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-indigo-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark</h1>
            <nav class="flex space-x-4">
                <span class="text-gray-300">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="dashboard.php" class="hover:text-indigo-300">Dashboard</a>
                <a href="catalog.php" class="hover:text-indigo-300">Services</a>
                <a href="orders.php" class="hover:text-indigo-300">Orders</a>
                <a href="feedback.php" class="hover:text-indigo-300">Feedback</a>
                <a href="profile.php" class="hover:text-indigo-300">Profile</a>
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin.php" class="hover:text-indigo-300">Admin</a>
                    <a href="reports.php" class="hover:text-indigo-300">Reports</a>
                <?php endif; ?>
                <a href="logout.php" class="hover:text-indigo-300">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Shopping Cart</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (empty($cart_items)): ?>
            <p class="text-gray-600">Your cart is empty. <a href="catalog.php" class="text-indigo-600 hover:underline">Browse services</a>.</p>
        <?php else: ?>
            <table class="w-full mb-4">
                <thead class="bg-indigo-100">
                    <tr>
                        <th class="p-4 text-left text-gray-700">Service</th>
                        <th class="p-4 text-left text-gray-700">Quantity</th>
                        <th class="p-4 text-left text-gray-700">Price</th>
                        <th class="p-4 text-left text-gray-700">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr class="border-t">
                            <td class="p-4"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td class="p-4">$<?php echo number_format($item['price'], 2); ?></td>
                            <td class="p-4">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="border-t font-bold">
                        <td colspan="3" class="p-4 text-right">Total</td>
                        <td class="p-4">$<?php echo number_format($total, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            <form method="POST" action="" class="text-center">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                <input type="hidden" name="action" value="checkout">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Proceed to Checkout</button>
            </form>
        <?php endif; ?>
    </main>
    <footer class="bg-indigo-900 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
    <script src="scripts.js"></script>
</body>
</html>