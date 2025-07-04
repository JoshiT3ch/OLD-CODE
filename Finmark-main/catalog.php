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
    $params = [];
    if ($search) {
        $params[':search'] = "%$search%";
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $products = $stmt->fetchAll();

    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products " . $where);
    if ($search) {
        $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $per_page);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "An error occurred while fetching products. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        nav a {
            transition: color 0.3s ease, transform 0.3s ease;
        }
        nav a:hover {
            transform: scale(1.1);
        }
        .pagination a {
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease;
        }
        .pagination a:hover {
            transform: scale(1.05);
        }
        .btn {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        input {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 font-sans">
    <header class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white sticky top-0 z-50 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark</h1>
            <nav class="flex space-x-6">
                <span class="text-gray-200 font-medium">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="dashboard.php" class="hover:text-indigo-200">Dashboard</a>
                <a href="catalog.php" class="hover:text-indigo-200">Services</a>
                <a href="cart.php" class="hover:text-indigo-200">Cart</a>
                <a href="orders.php" class="hover:text-indigo-200">Orders</a>
                <a href="feedback.php" class="hover:text-indigo-200">Feedback</a>
                <a href="profile.php" class="hover:text-indigo-200">Profile</a>
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin.php" class="hover:text-indigo-200">Admin</a>
                    <a href="reports.php" class="hover:text-indigo-200">Reports</a>
                <?php endif; ?>
                <a href="logout.php" class="hover:text-indigo-200">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8 animate-fade-in">
        <h2 class="text-4xl font-extrabold text-gray-800 mb-8">Service Catalog</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="mb-6">
            <form method="GET" action="" class="flex space-x-3">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search services..." class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:ring-indigo-200 focus:border-indigo-600">
                <button type="submit" class="bg-indigo-600 text-white p-3 rounded-2xl hover:bg-indigo-700 btn">Search</button>
            </form>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($products)): ?>
                <p class="text-gray-600 col-span-full text-center">No services available. <?php if ($_SESSION['is_admin']): ?><a href="admin.php" class="text-indigo-600 hover:underline">Add services as admin</a>.<?php endif; ?></p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="bg-white p-6 rounded-2xl shadow-lg card">
                        <h3 class="text-xl font-semibold text-gray-700"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="mt-4 text-indigo-600 font-bold">$<?php echo number_format($product['price'], 2); ?></p>
                        <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 btn">Add to Cart</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center space-x-2 pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-4 py-2 rounded-xl <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-indigo-500 hover:text-white"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
    <footer class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
</body>
</html>