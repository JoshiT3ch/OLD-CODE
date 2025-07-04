<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $where = $search ? "WHERE name LIKE :search" : "";
    $stmt = $pdo->prepare("SELECT * FROM products " . $where . " LIMIT :limit OFFSET :offset");
    $params = [':limit' => $per_page, ':offset' => $offset];
    if ($search) $params[':search'] = "%$search%";
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products " . $where);
    $stmt->execute($search ? [':search' => "%$search%"] : []);
    $total_items = $stmt->fetchColumn();
    $total_pages = ceil($total_items / $per_page);
} catch (PDOException $e) {
    $error = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog - FinMark</title>
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
                <a href="cart.php" class="hover:text-indigo-300">Cart</a>
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
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Service Catalog</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="mb-4">
            <form method="GET" action="" class="flex space-x-2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search services..." class="w-full p-2 border rounded focus:ring focus:ring-indigo-200">
                <button type="submit" class="bg-indigo-600 text-white p-2 rounded hover:bg-indigo-700">Search</button>
            </form>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($products)): ?>
                <p class="text-gray-600 col-span-full text-center">No services available. <?php if ($_SESSION['is_admin']): ?><a href="admin.php" class="text-indigo-600 hover:underline">Add services as admin</a>.<?php endif; ?></p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <h3 class="text-xl font-semibold text-gray-700"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="mt-4 text-indigo-600 font-bold">$<?php echo number_format($product['price'], 2); ?></p>
                        <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add to Cart</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mt-6">
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center space-x-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1 rounded <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-indigo-500 hover:text-white transition-colors"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer class="bg-indigo-900 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
    <script src="scripts.js"></script>
</body>
</html>