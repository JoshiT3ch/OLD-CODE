<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, total_amount, status, created_at, estimated_delivery FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - FinMark</title>
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
                <a href="cart.php" class="hover:text-indigo-300">Cart</a>
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
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Your Orders</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (empty($orders)): ?>
            <p class="text-gray-600">No orders found. <a href="catalog.php" class="text-indigo-600 hover:underline">Browse services</a>.</p>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-indigo-100">
                    <tr>
                        <th class="p-4 text-left text-gray-700">Order ID</th>
                        <th class="p-4 text-left text-gray-700">Total</th>
                        <th class="p-4 text-left text-gray-700">Status</th>
                        <th class="p-4 text-left text-gray-700">Order Date</th>
                        <th class="p-4 text-left text-gray-700">Estimated Delivery</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="border-t">
                            <td class="p-4"><?php echo htmlspecialchars($order['id']); ?></td>
                            <td class="p-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($order['status']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($order['estimated_delivery']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <footer class="bg-indigo-900 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
    <script src="scripts.js"></script>
</body>
</html>