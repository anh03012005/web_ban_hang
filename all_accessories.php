<?php
require_once("includes/connect_db.php");

// 1. Nhận các tham số lọc từ URL (nếu có)
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filter_min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$filter_max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : 0;
$filter_sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// 2. Xây dựng câu lệnh SQL tự động lấy Phụ Kiện (ID = 2)
$sql = "SELECT p.id, p.name, p.image, MIN(pv.price) as min_price 
        FROM products p 
        LEFT JOIN product_variants pv ON p.id = pv.product_id 
        WHERE (p.category_id = 2 OR p.category_id IN (SELECT id FROM categories WHERE parent_id = 2)) ";

// Lọc thêm theo danh mục con nếu người dùng chọn
if ($filter_category > 0) {
    $sql .= " AND p.category_id = $filter_category ";
}

$sql .= " GROUP BY p.id ";

// Lọc theo giá
$having_clauses = [];
if ($filter_min_price > 0) {
    $having_clauses[] = "min_price >= $filter_min_price";
}
if ($filter_max_price > 0) {
    $having_clauses[] = "min_price <= $filter_max_price";
}

if (!empty($having_clauses)) {
    $sql .= " HAVING " . implode(" AND ", $having_clauses);
}

// Lọc sắp xếp
if ($filter_sort == 'price_asc') {
    $sql .= " ORDER BY min_price ASC ";
} elseif ($filter_sort == 'price_desc') {
    $sql .= " ORDER BY min_price DESC ";
} else {
    $sql .= " ORDER BY p.id DESC ";
}

$result = mysqli_query($conn, $sql);

// 3. Truy vấn lấy danh sách Category cho Cột bộ lọc 
// (Chỉ lấy các danh mục là con của Phụ kiện: parent_id = 2)
$sql_cats = "SELECT id, name FROM categories WHERE parent_id = 2 ORDER BY id ASC";
$result_cats = mysqli_query($conn, $sql_cats);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tất cả Phụ kiện</title>
    <script src="https://kit.fontawesome.com/da1a483940.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/style_client.css">
</head>

<body>
    <?php
    include "includes/header.php";
    include "includes/nav.php";
    ?>

    <main class="main-content all-products-page">
        <div class="container-fluid" style="max-width: 1200px; margin: 20px auto; padding: 0 15px; display: flex; gap: 20px;">

            <aside class="filter-sidebar">
                <h3><i class="fa-solid fa-filter"></i> Lọc Phụ Kiện</h3>
                <form action="all_accessories.php" method="GET">
                    <div class="filter-group">
                        <label>Loại phụ kiện</label>
                        <select name="category">
                            <?php
                            if ($result_cats && mysqli_num_rows($result_cats) > 0) {
                                // Nếu có dữ liệu trong DB thì hiện "Tất cả" và danh sách
                                echo '<option value="0">Tất cả loại</option>';
                                while ($cat = mysqli_fetch_assoc($result_cats)) {
                                    $selected = ($filter_category == $cat['id']) ? 'selected' : '';
                                    echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                                }
                            } else {
                                // BẮT LỖI: Nếu DB chưa có phụ kiện nào thì in ra dòng này và làm mờ đi (disabled)
                                echo '<option value="0" disabled selected>Chưa có loại phụ kiện nào</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Mức giá (VNĐ)</label>
                        <div class="price-inputs">
                            <input type="number" name="min_price" placeholder="Từ..." value="<?= $filter_min_price > 0 ? $filter_min_price : '' ?>">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Đến..." value="<?= $filter_max_price > 0 ? $filter_max_price : '' ?>">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Sắp xếp theo</label>
                        <select name="sort">
                            <option value="newest" <?= $filter_sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="price_asc" <?= $filter_sort == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                            <option value="price_desc" <?= $filter_sort == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-filter-submit">Áp dụng lọc</button>
                    <a href="all_accessories.php" class="btn-filter-clear">Xóa lọc</a>
                </form>
            </aside>

            <section class="products-area">
                <div class="section-header" style="margin-bottom: 15px;">
                    <h2>TẤT CẢ PHỤ KIỆN</h2>
                </div>

                <div class="product-grid">
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $current_price = $row['min_price'] ? $row['min_price'] : 0;
                            $old_price = $current_price * 1.1;
                            $discount_percent = 10;
                            $img_src = !empty($row['image']) ? 'assets/uploads/' . $row['image'] : 'https://via.placeholder.com/300x300?text=No+Image';
                    ?>
                            <a href="detail.php?id=<?= $row['id'] ?>" class="product-card" style="text-decoration: none; color: inherit;">
                                <div class="card-badges">
                                    <span class="badge-discount">Giảm <?= $discount_percent ?>%</span>
                                </div>
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                </div>
                                <h3 class="product-name"><?= htmlspecialchars($row['name']) ?></h3>
                                <div class="product-price">
                                    <span class="price-current">
                                        <?= $current_price != 0 ? number_format($current_price, 0, ',', '.') . "đ" : "Liên hệ" ?>
                                    </span>
                                    <span class="price-old"><?= number_format($old_price, 0, ',', '.') ?>đ</span>
                                </div>
                                <div class="product-shipping" style="margin-top:auto;">
                                    <i class="fa-solid fa-truck-fast"></i> Giao siêu tốc 2h
                                </div>
                            </a>
                    <?php
                        }
                    } else {
                        echo "<p style='grid-column: 1 / -1; text-align: center; padding: 50px; font-size: 16px;'>Chưa có phụ kiện nào thuộc danh mục này.</p>";
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <?php include "includes/footer.php"; ?>
    <script src="assets/js/script.js"></script>
</body>

</html>