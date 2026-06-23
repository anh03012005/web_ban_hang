<?php
require_once("../includes/check_admin_alive.php");
require_once("../../includes/connect_db.php");
require_once("../../includes/db_helper.php");

// 1. Bắt ID sản phẩm cần sửa
if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}
$edit_id = (int)$_GET['id'];

// 2. Lấy dữ liệu sản phẩm hiện tại
$product_data = selectDb($conn, 'products', '*', ['id' => $edit_id]);
if (empty($product_data)) {
    echo "Lỗi: Sản phẩm không tồn tại!";
    exit();
}
$p = $product_data[0];

// 3. Giải nén chuỗi JSON cấu hình thành Mảng PHP
$specs = json_decode($p['description'], true);
if (!is_array($specs)) {
    $specs = []; // Đề phòng trường hợp lỗi JSON thì gán mảng rỗng
}

// Lấy danh sách danh mục để đổ vào thẻ <select>
$categories = selectDb($conn, 'categories', '*', [], 'ORDER BY id ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/web_ban_hang/admin/assets/css/style_main.css">
    <title>Sửa Sản Phẩm</title>
</head>
<body>
    <?php include("../includes/topbar.php"); ?>
    
    <section>
        <?php include("../includes/sidebar.php") ?>
        
        <div id="content">
            <div id="product_head" style="margin-bottom: 20px;">
                <a href="list.php" style="text-decoration: none; color: var(--text-muted); font-size: 14px;">⬅ Quay lại danh sách Sản phẩm</a>
                <h3 style="margin-top: 10px;">Sửa Sản Phẩm: <span style="color: var(--primary-color);"><?php echo $p['name']; ?></span></h3>
            </div>

            <div style="background: var(--bg-admin, #fff); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color, #444); margin-bottom: 20px;">
                <form action="edit_work.php" method="post" enctype="multipart/form-data">
                    
                    <input type="hidden" name="edit_id" value="<?php echo $p['id']; ?>">
                    
                    <h3 style="font-size: 16px; border-bottom: 2px solid var(--border-color); padding-bottom: 5px; margin-bottom: 15px;">Thông tin cơ bản</h3>
                    
                    <div style="display: flex; gap: 15px;">
                        <div style="flex: 1;">
                            <label for="prod_name">Tên sản phẩm *</label>
                            <input type="text" name="prod_name" required id="prod_name" value="<?php echo $p['name']; ?>" class="form_input" style="width: 100%;">
                        </div>
                        
                        <div style="flex: 1;">
                            <label for="category_id">Danh mục / Nhãn hiệu *</label>
                            <select name="category_id" id="category_id" required class="form_input" style="width: 100%; height: 40px;">
                                <?php
                                if (!empty($categories)) {
                                    foreach ($categories as $cat) {
                                        $prefix = ($cat['parent_id'] != null) ? " --- " : "";
                                        // Kiểm tra xem ID danh mục nào khớp với sản phẩm thì thêm chữ 'selected'
                                        $selected = ($cat['id'] == $p['category_id']) ? "selected" : "";
                                        echo "<option value='{$cat['id']}' {$selected}>{$prefix}{$cat['name']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 15px; margin-bottom: 25px;">
                        <div style="flex: 1;">
                            <label>Ảnh Bìa Hiện Tại</label>
                            <div style="margin-bottom: 10px;">
                                <?php if($p['image']): ?>
                                    <img src="/web_ban_hang/assets/uploads/<?php echo $p['image']; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <span style="color: red;">Chưa có ảnh</span>
                                <?php endif; ?>
                            </div>
                            <label for="prod_img">Chọn ảnh mới (Bỏ trống nếu muốn giữ ảnh cũ)</label>
                            <input type="file" name="prod_img" id="prod_img" accept="image/png, image/jpeg, image/jpg" class="form_input" style="padding: 8px; width: 100%;">
                        </div>
                        
                        <div style="flex: 1;">
                            <label>Bộ ảnh chi tiết (Bỏ trống để giữ bộ ảnh cũ)</label>
                            <input type="file" name="gallery[]" id="gallery" multiple accept="image/png, image/jpeg, image/jpg" class="form_input" style="padding: 8px; width: 100%;">
                            <small style="color: var(--text-muted); display: block; margin-top: 5px;">* Nếu bạn tải ảnh mới lên đây, toàn bộ ảnh chi tiết cũ sẽ bị xóa và thay thế.</small>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px; padding: 15px; border: 1px dashed #ccc; border-radius: 8px;">
                    <label style="font-weight: bold; margin-bottom: 10px; display: block;">⚙️ Chỉnh sửa Thông số kỹ thuật</label>
                    
                    <div id="dynamic_specs">
                        <?php if (!empty($specs)): ?>
                            <?php foreach ($specs as $key => $value): ?>
                                <div class="spec-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                    <input type="text" name="spec_keys[]" value="<?= htmlspecialchars($key) ?>" placeholder="Tên thông số..." class="form_input" style="flex: 1;">
                                    <input type="text" name="spec_values[]" value="<?= htmlspecialchars($value) ?>" placeholder="Giá trị..." class="form_input" style="flex: 2;">
                                    <button type="button" onclick="removeSpec(this)" style="background: var(--accent-error); color: white; border: none; padding: 0 15px; border-radius: 5px; cursor: pointer;">Xóa</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="spec-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <input type="text" name="spec_keys[]" placeholder="Tên thông số (VD: CPU, Công suất...)" class="form_input" style="flex: 1;">
                                <input type="text" name="spec_values[]" placeholder="Giá trị (VD: Apple A17, 20W...)" class="form_input" style="flex: 2;">
                                <button type="button" onclick="removeSpec(this)" style="background: var(--accent-error); color: white; border: none; padding: 0 15px; border-radius: 5px; cursor: pointer;">Xóa</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" onclick="addSpec()" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-top: 10px;">
                        + Thêm thông số khác
                    </button>
                </div>

                    <script>
                        function addSpec() {
                            var newRow = `
                                <div class="spec-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                    <input type="text" name="spec_keys[]" placeholder="Tên thông số..." class="form_input" style="flex: 1;">
                                    <input type="text" name="spec_values[]" placeholder="Giá trị..." class="form_input" style="flex: 2;">
                                    <button type="button" onclick="removeSpec(this)" style="background: var(--accent-error); color: white; border: none; padding: 0 15px; border-radius: 5px; cursor: pointer;">Xóa</button>
                                </div>
                            `;
                            document.getElementById('dynamic_specs').insertAdjacentHTML('beforeend', newRow);
                        }

                        function removeSpec(buttonElement) {
                            buttonElement.parentElement.remove();
                        }
                    </script>
                    
                    <input type="submit" name="btn_edit" value="Lưu thay đổi" class="add_button" style="margin-top: 25px; width: 100%; height: 45px; font-size: 16px;">
                </form>
            </div>
            
            <?php include("../includes/footer.php") ?>
        </div>
    </section>
</body>
</html>