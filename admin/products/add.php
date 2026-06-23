<?php
    $categories = selectDb($conn, 'categories', '*', [], 'ORDER BY id ASC');
?>

<div class="modal-container" id="modal">
    <div class="modal" style="width: 700px; max-height: 90vh; overflow-y: auto;"> 
        <button class="close_btn" id="close">X</button>
        <div class="modal_content">
            <h1 style="margin-bottom: 20px;">Thêm sản phẩm mới</h1>
            
            <form action="add_work.php" class="modal_form" method="post" enctype="multipart/form-data">
                
                <h3 style="font-size: 16px; border-bottom: 2px solid var(--border-color); padding-bottom: 5px; margin-bottom: 15px;">Thông tin cơ bản</h3>
                
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label for="prod_name">Tên sản phẩm *</label>
                        <input type="text" name="prod_name" required id="prod_name" placeholder="VD: iPhone 15 Pro Max" class="form_input">
                    </div>
                    
                    <div style="flex: 1;">
                        <label for="category_id">Danh mục / Nhãn hiệu *</label>
                        <select name="category_id" id="category_id" required class="form_input" style="width: 100%; height: 40px;">
                            <option value="">-- Chọn danh mục --</option>
                            <?php
                            if (!empty($categories)) {
                                foreach ($categories as $cat) {
                                    $prefix = ($cat['parent_id'] != null) ? " --- " : "";
                                    echo "<option value='{$cat['id']}'>{$prefix}{$cat['name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                

                <div style="display: flex; gap: 15px; margin-top: 15px; margin-bottom: 25px;">
                    <div style="flex: 1;">
                        <label for="prod_img">Ảnh Bìa (Hiển thị ở danh sách) *</label>
                        <input type="file" name="prod_img" required id="prod_img" accept="image/png, image/jpeg, image/jpg" class="form_input" style="padding: 8px;">
                    </div>
                    
                    <div style="flex: 1;">
                        <label for="gallery">Bộ ảnh chi tiết (Có thể chọn nhiều ảnh)</label>
                        <input type="file" name="gallery[]" id="gallery" multiple accept="image/png, image/jpeg, image/jpg" class="form_input" style="padding: 8px;">
                        <small style="color: var(--text-muted); display: block; margin-top: 5px;">* Giữ phím Ctrl để chọn nhiều ảnh cùng lúc.</small>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px; padding: 15px; border: 1px dashed #ccc; border-radius: 8px;">
                    <label style="font-weight: bold; margin-bottom: 10px; display: block;">⚙️ Thông số kỹ thuật chi tiết</label>
                    
                    <div id="dynamic_specs">
                        <div class="spec-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="text" name="spec_keys[]" placeholder="Tên thông số (VD: CPU, Công suất...)" class="form_input" style="flex: 1;">
                            <input type="text" name="spec_values[]" placeholder="Giá trị (VD: Apple A17, 20W...)" class="form_input" style="flex: 2;">
                            <button type="button" onclick="removeSpec(this)" style="background: var(--accent-error); color: white; border: none; padding: 0 15px; border-radius: 5px; cursor: pointer;">Xóa</button>
                        </div>
                    </div>

                    <button type="button" onclick="addSpec()" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; margin-top: 10px;">
                        + Thêm thông số khác
                    </button>
                </div>

                <script>
                    function addSpec() {
                        // Tạo ra một đoạn HTML chứa 2 ô input mới
                        var newRow = `
                            <div class="spec-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <input type="text" name="spec_keys[]" placeholder="Tên thông số..." class="form_input" style="flex: 1;">
                                <input type="text" name="spec_values[]" placeholder="Giá trị..." class="form_input" style="flex: 2;">
                                <button type="button" onclick="removeSpec(this)" style="background: var(--accent-error); color: white; border: none; padding: 0 15px; border-radius: 5px; cursor: pointer;">Xóa</button>
                            </div>
                        `;
                        // Nhét nó vào cuối thẻ div #dynamic_specs
                        document.getElementById('dynamic_specs').insertAdjacentHTML('beforeend', newRow);
                    }

                    function removeSpec(buttonElement) {
                        // Xóa dòng chứa nút bấm đó
                        buttonElement.parentElement.remove();
                    }
                </script>
                <input type="submit" name="btn_add" value="Thêm sản phẩm" class="add_button" style="margin-top: 25px; width: 100%; height: 45px; font-size: 16px;">
            </form>
        </div>
    </div>
</div>