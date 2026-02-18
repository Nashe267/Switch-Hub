<?php
/**
 * Admin Products Management
 *
 * Full CRUD for products - edit from dashboard
 * Stores in wp_options for simplicity
 *
 * @package SwitchBusinessHub
 * @version 1.9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBHA_Admin_Products {

    public function __construct() {
        add_action('wp_ajax_sbha_save_product', array($this, 'ajax_save_product'));
        add_action('wp_ajax_sbha_delete_product', array($this, 'ajax_delete_product'));
        add_action('wp_ajax_sbha_save_variation', array($this, 'ajax_save_variation'));
        add_action('wp_ajax_sbha_delete_variation', array($this, 'ajax_delete_variation'));
    }

    /**
     * Render products management page
     */
    public function render() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-sbha-products.php';
        $products = SBHA_Products::get_all();
        $categories = SBHA_Products::get_categories();
        $currency = 'R';
        
        // Handle form submission
        if (isset($_POST['sbha_save_product']) && wp_verify_nonce($_POST['_wpnonce'], 'sbha_product')) {
            $this->save_product($_POST);
            echo '<div class="notice notice-success"><p>Product saved!</p></div>';
            $products = SBHA_Products::get_all(); // Refresh
        }
        
        $editing = isset($_GET['edit']) ? sanitize_text_field($_GET['edit']) : '';
        $edit_product = $editing ? ($products[$editing] ?? null) : null;
        ?>
        <div class="wrap sbha-admin-wrap">
            <h1 class="sbha-admin-title">
                <span class="sbha-logo">📦 Products & Pricing</span>
                <a href="<?php echo admin_url('admin.php?page=sbha-products&edit=new'); ?>" class="page-title-action">+ Add Product</a>
            </h1>
            
            <style>
                .sbha-products-layout{display:grid;grid-template-columns:350px 1fr;gap:20px;margin-top:20px}
                @media(max-width:1200px){.sbha-products-layout{grid-template-columns:1fr}}
                .sbha-product-list{background:#fff;border-radius:10px;box-shadow:0 2px 5px rgba(0,0,0,0.1);max-height:80vh;overflow-y:auto}
                .sbha-product-list-header{padding:15px 20px;border-bottom:1px solid #eee;position:sticky;top:0;background:#fff;z-index:10}
                .sbha-product-list-header input{width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:6px}
                .sbha-product-item{display:flex;justify-content:space-between;align-items:center;padding:12px 20px;border-bottom:1px solid #f0f0f0;cursor:pointer;transition:background 0.2s}
                .sbha-product-item:hover{background:#fff7ed}
                .sbha-product-item.active{background:#fff7ed;border-left:3px solid #FF6600}
                .sbha-product-item-info h4{margin:0 0 4px;font-size:14px}
                .sbha-product-item-info p{margin:0;font-size:12px;color:#666}
                .sbha-product-item-price{font-weight:700;color:#FF6600}
                .sbha-category-header{background:#f8f9fa;padding:10px 20px;font-weight:600;font-size:12px;text-transform:uppercase;color:#666;position:sticky;top:50px}
                
                .sbha-product-editor{background:#fff;border-radius:10px;box-shadow:0 2px 5px rgba(0,0,0,0.1);padding:25px}
                .sbha-editor-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #eee}
                .sbha-form-row{margin-bottom:20px}
                .sbha-form-row label{display:block;font-weight:600;margin-bottom:6px;font-size:13px}
                .sbha-form-row input,.sbha-form-row select,.sbha-form-row textarea{width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px}
                .sbha-form-row textarea{min-height:80px}
                .sbha-form-row-inline{display:grid;grid-template-columns:1fr 1fr;gap:15px}
                
                .sbha-variations-table{width:100%;border-collapse:collapse;margin-top:15px}
                .sbha-variations-table th,.sbha-variations-table td{padding:10px;text-align:left;border-bottom:1px solid #eee}
                .sbha-variations-table th{background:#f8f9fa;font-size:12px;text-transform:uppercase}
                .sbha-variations-table input{width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px}
                .sbha-variations-table .cost-input{width:80px}
                .sbha-variations-table .price-input{width:80px}
                .sbha-profit-cell{font-weight:600}
                .sbha-profit-good{color:#059669}
                .sbha-profit-low{color:#dc2626}
                .sbha-add-var-btn{background:#f0f0f0;border:2px dashed #ccc;padding:10px;text-align:center;cursor:pointer;border-radius:6px;margin-top:10px;color:#666}
                .sbha-add-var-btn:hover{border-color:#FF6600;color:#FF6600}
                .sbha-delete-btn{color:#dc2626;background:none;border:none;cursor:pointer;font-size:16px}
                
                .sbha-btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:14px}
                .sbha-btn-primary{background:#FF6600;color:#fff}
                .sbha-btn-danger{background:#dc2626;color:#fff}
                .sbha-btn-outline{background:#fff;border:2px solid #ddd;color:#333}
                
                .sbha-no-edit{text-align:center;padding:60px;color:#666}
                .sbha-no-edit h3{margin-bottom:10px}
                
                .sbha-type-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;text-transform:uppercase}
                .sbha-type-design{background:#fce7f3;color:#db2777}
                .sbha-type-digital{background:#dbeafe;color:#2563eb}
                .sbha-type-physical{background:#d1fae5;color:#059669}
            </style>
            
            <div class="sbha-products-layout">
                <!-- Product List -->
                <div class="sbha-product-list">
                    <div class="sbha-product-list-header">
                        <input type="text" id="productSearch" placeholder="🔍 Search products..." onkeyup="filterProductList()">
                    </div>
                    
                    <?php 
                    $current_cat = '';
                    foreach ($products as $key => $product): 
                        $cat = $product['category'];
                        if ($cat !== $current_cat):
                            $current_cat = $cat;
                            $cat_info = $categories[$cat] ?? array('name' => ucfirst($cat), 'emoji' => '📦');
                    ?>
                    <div class="sbha-category-header"><?php echo esc_html($cat_info['emoji'] . ' ' . $cat_info['name']); ?></div>
                    <?php endif; ?>
                    
                    <div class="sbha-product-item <?php echo $editing === $key ? 'active' : ''; ?>" 
                         onclick="location.href='<?php echo admin_url('admin.php?page=sbha-products&edit=' . urlencode($key)); ?>'"
                         data-name="<?php echo esc_attr(strtolower($product['name'])); ?>">
                        <div class="sbha-product-item-info">
                            <h4><?php echo esc_html($product['name']); ?></h4>
                            <p><?php echo count($product['variations'] ?? array()); ?> variations</p>
                        </div>
                        <div class="sbha-product-item-price"><?php echo $currency; ?><?php echo number_format(SBHA_Products::get_min_price($product)); ?>+</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Product Editor -->
                <div class="sbha-product-editor">
                    <?php if ($editing === 'new'): ?>
                        <form method="post">
                            <?php wp_nonce_field('sbha_product'); ?>
                            <input type="hidden" name="product_key" value="">
                            
                            <div class="sbha-editor-header">
                                <h2>➕ Add New Product</h2>
                            </div>
                            
                            <div class="sbha-form-row">
                                <label>Product Name *</label>
                                <input type="text" name="name" required placeholder="e.g. A5 Flyers">
                            </div>
                            
                            <div class="sbha-form-row-inline">
                                <div class="sbha-form-row">
                                    <label>Category *</label>
                                    <select name="category" required>
                                        <?php foreach ($categories as $key => $cat): ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($cat['emoji'] . ' ' . $cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="sbha-form-row">
                                    <label>Product Type</label>
                                    <select name="product_type">
                                        <option value="physical">📦 Physical (needs delivery)</option>
                                        <option value="digital">💻 Digital (no delivery)</option>
                                        <option value="design">🎨 Design Service</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="sbha-form-row">
                                <label>Description</label>
                                <textarea name="description" placeholder="Product description with emoji..."></textarea>
                            </div>

                            <div class="sbha-form-row">
                                <label>Product Image</label>
                                <div style="display:flex;gap:8px">
                                    <input type="text" name="image_url" id="image_url" placeholder="https://...">
                                    <button type="button" class="button" onclick="pickProductImage()">Select</button>
                                </div>
                                <div style="margin-top:8px">
                                    <img id="productImagePreview" src="" alt="" style="max-width:160px;max-height:100px;display:none;border-radius:8px;border:1px solid #ddd">
                                </div>
                            </div>
                            
                            <div class="sbha-form-row-inline">
                                <div class="sbha-form-row">
                                    <label>Source/Supplier</label>
                                    <input type="text" name="source" placeholder="e.g. Vistaprint">
                                </div>
                                <div class="sbha-form-row">
                                    <label>Source URL</label>
                                    <input type="url" name="source_url" placeholder="https://...">
                                </div>
                            </div>
                            
                            <div class="sbha-form-row">
                                <label>Variations (add after creating)</label>
                                <p style="color:#666;font-size:13px">Save the product first, then add variations.</p>
                            </div>
                            
                            <button type="submit" name="sbha_save_product" class="sbha-btn sbha-btn-primary">💾 Save Product</button>
                        </form>
                        
                    <?php elseif ($edit_product): ?>
                        <form method="post">
                            <?php wp_nonce_field('sbha_product'); ?>
                            <input type="hidden" name="product_key" value="<?php echo esc_attr($editing); ?>">
                            
                            <div class="sbha-editor-header">
                                <h2>✏️ Edit: <?php echo esc_html($edit_product['name']); ?></h2>
                                <div>
                                    <?php 
                                    $type = $edit_product['product_type'] ?? (!empty($edit_product['is_design_service']) ? 'design' : 'physical');
                                    $type_class = 'sbha-type-' . $type;
                                    ?>
                                    <span class="sbha-type-badge <?php echo $type_class; ?>"><?php echo esc_html($type); ?></span>
                                </div>
                            </div>
                            
                            <div class="sbha-form-row">
                                <label>Product Name *</label>
                                <input type="text" name="name" required value="<?php echo esc_attr($edit_product['name']); ?>">
                            </div>
                            
                            <div class="sbha-form-row-inline">
                                <div class="sbha-form-row">
                                    <label>Category *</label>
                                    <select name="category" required>
                                        <?php foreach ($categories as $key => $cat): ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($edit_product['category'], $key); ?>><?php echo esc_html($cat['emoji'] . ' ' . $cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="sbha-form-row">
                                    <label>Product Type</label>
                                    <select name="product_type">
                                        <option value="physical" <?php selected($type, 'physical'); ?>>📦 Physical (needs delivery)</option>
                                        <option value="digital" <?php selected($type, 'digital'); ?>>💻 Digital (no delivery)</option>
                                        <option value="design" <?php selected($type, 'design'); ?>>🎨 Design Service</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="sbha-form-row">
                                <label>Description</label>
                                <textarea name="description"><?php echo esc_textarea($edit_product['description']); ?></textarea>
                            </div>

                            <div class="sbha-form-row">
                                <label>Product Image</label>
                                <div style="display:flex;gap:8px">
                                    <input type="text" name="image_url" id="image_url" value="<?php echo esc_url($edit_product['image_url'] ?? ''); ?>" placeholder="https://...">
                                    <button type="button" class="button" onclick="pickProductImage()">Select</button>
                                </div>
                                <div style="margin-top:8px">
                                    <img id="productImagePreview" src="<?php echo esc_url($edit_product['image_url'] ?? ''); ?>" alt="" style="max-width:160px;max-height:100px;<?php echo empty($edit_product['image_url']) ? 'display:none;' : ''; ?>border-radius:8px;border:1px solid #ddd">
                                </div>
                            </div>
                            
                            <div class="sbha-form-row-inline">
                                <div class="sbha-form-row">
                                    <label>Source/Supplier</label>
                                    <input type="text" name="source" value="<?php echo esc_attr($edit_product['source'] ?? ''); ?>">
                                </div>
                                <div class="sbha-form-row">
                                    <label>Source URL</label>
                                    <input type="url" name="source_url" value="<?php echo esc_url($edit_product['source_url'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <!-- Variations -->
                            <div class="sbha-form-row">
                                <label>Variations & Pricing</label>
                                <table class="sbha-variations-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th style="width:100px">Cost (<?php echo $currency; ?>)</th>
                                            <th style="width:100px">Price (<?php echo $currency; ?>)</th>
                                            <th style="width:80px">Profit</th>
                                            <th style="width:50px">SKU</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="variationsBody">
                                        <?php 
                                        $vars = $edit_product['variations'] ?? array();
                                        foreach ($vars as $i => $v): 
                                            $profit = ($v['price'] ?? 0) - ($v['cost'] ?? 0);
                                            $profit_class = $profit >= ($v['cost'] ?? 0) * 0.3 ? 'sbha-profit-good' : 'sbha-profit-low';
                                        ?>
                                        <tr>
                                            <td><input type="text" name="var_name[]" value="<?php echo esc_attr($v['name']); ?>"></td>
                                            <td><input type="number" step="0.01" name="var_cost[]" value="<?php echo esc_attr($v['cost'] ?? 0); ?>" class="cost-input" onchange="updateProfit(this)"></td>
                                            <td><input type="number" step="0.01" name="var_price[]" value="<?php echo esc_attr($v['price']); ?>" class="price-input" onchange="updateProfit(this)"></td>
                                            <td class="sbha-profit-cell <?php echo $profit_class; ?>"><?php echo $currency; ?><?php echo number_format($profit, 2); ?></td>
                                            <td><input type="text" name="var_sku[]" value="<?php echo esc_attr($v['sku'] ?? ''); ?>" style="width:60px"></td>
                                            <td><button type="button" class="sbha-delete-btn" onclick="removeVariation(this)">🗑️</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="sbha-add-var-btn" onclick="addVariation()">+ Add Variation</div>
                            </div>
                            
                            <div style="display:flex;gap:10px;margin-top:20px">
                                <button type="submit" name="sbha_save_product" class="sbha-btn sbha-btn-primary">💾 Save Changes</button>
                                <a href="<?php echo admin_url('admin.php?page=sbha-products'); ?>" class="sbha-btn sbha-btn-outline">Cancel</a>
                                <button type="button" class="sbha-btn sbha-btn-danger" onclick="if(confirm('Delete this product?'))location.href='<?php echo admin_url('admin.php?page=sbha-products&delete=' . urlencode($editing) . '&_wpnonce=' . wp_create_nonce('delete_product')); ?>'">🗑️ Delete</button>
                            </div>
                        </form>
                        
                    <?php else: ?>
                        <div class="sbha-no-edit">
                            <h3>👈 Select a product to edit</h3>
                            <p>Or <a href="<?php echo admin_url('admin.php?page=sbha-products&edit=new'); ?>">add a new product</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <script>
            function filterProductList() {
                const q = document.getElementById('productSearch').value.toLowerCase();
                document.querySelectorAll('.sbha-product-item').forEach(item => {
                    const name = item.dataset.name;
                    item.style.display = name.includes(q) ? '' : 'none';
                });
            }
            
            function addVariation() {
                const tbody = document.getElementById('variationsBody');
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="text" name="var_name[]" placeholder="e.g. 100 Cards"></td>
                    <td><input type="number" step="0.01" name="var_cost[]" value="0" class="cost-input" onchange="updateProfit(this)"></td>
                    <td><input type="number" step="0.01" name="var_price[]" value="0" class="price-input" onchange="updateProfit(this)"></td>
                    <td class="sbha-profit-cell">R0.00</td>
                    <td><input type="text" name="var_sku[]" style="width:60px"></td>
                    <td><button type="button" class="sbha-delete-btn" onclick="removeVariation(this)">🗑️</button></td>
                `;
                tbody.appendChild(row);
            }
            
            function removeVariation(btn) {
                if (confirm('Remove this variation?')) {
                    btn.closest('tr').remove();
                }
            }
            
            function updateProfit(input) {
                const row = input.closest('tr');
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const profit = price - cost;
                const cell = row.querySelector('.sbha-profit-cell');
                cell.textContent = 'R' + profit.toFixed(2);
                cell.className = 'sbha-profit-cell ' + (profit >= cost * 0.3 ? 'sbha-profit-good' : 'sbha-profit-low');
            }

            let sbhaProductMedia;
            function pickProductImage(){
                if(typeof wp === 'undefined' || !wp.media){return;}
                if(sbhaProductMedia){sbhaProductMedia.open();return;}
                sbhaProductMedia = wp.media({
                    title: 'Select Product Image',
                    button: { text: 'Use image' },
                    multiple: false
                });
                sbhaProductMedia.on('select', function(){
                    const attachment = sbhaProductMedia.state().get('selection').first().toJSON();
                    const input = document.getElementById('image_url');
                    const preview = document.getElementById('productImagePreview');
                    if(input){ input.value = attachment.url; }
                    if(preview){ preview.src = attachment.url; preview.style.display = ''; }
                });
                sbhaProductMedia.open();
            }

            document.getElementById('image_url')?.addEventListener('change', function(){
                const preview = document.getElementById('productImagePreview');
                if(!preview){ return; }
                if(this.value){
                    preview.src = this.value;
                    preview.style.display = '';
                }else{
                    preview.style.display = 'none';
                }
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Save product to database
     */
    private function save_product($data) {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-sbha-products.php';
        
        $key = sanitize_text_field($data['product_key'] ?? '');
        $name = sanitize_text_field($data['name'] ?? '');
        
        if (empty($name)) return false;
        
        // Generate key if new
        if (empty($key)) {
            $key = sanitize_title($name);
            $key = str_replace('-', '_', $key);
        }
        
        // Get existing products from options or default
        $products = get_option('sbha_custom_products', array());
        
        // Build product
        $product = array(
            'name' => $name,
            'category' => sanitize_text_field($data['category'] ?? 'services'),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'image_url' => esc_url_raw($data['image_url'] ?? ''),
            'source' => sanitize_text_field($data['source'] ?? 'Switch Graphics Internal'),
            'source_url' => esc_url_raw($data['source_url'] ?? ''),
            'product_type' => sanitize_text_field($data['product_type'] ?? 'physical'),
            'is_design_service' => ($data['product_type'] ?? '') === 'design',
            'variations' => array()
        );
        
        // Build variations
        if (!empty($data['var_name'])) {
            foreach ($data['var_name'] as $i => $vname) {
                if (empty($vname)) continue;
                $product['variations'][] = array(
                    'name' => sanitize_text_field($vname),
                    'cost' => floatval($data['var_cost'][$i] ?? 0),
                    'price' => floatval($data['var_price'][$i] ?? 0),
                    'sku' => sanitize_text_field($data['var_sku'][$i] ?? '')
                );
            }
        }
        
        $products[$key] = $product;
        update_option('sbha_custom_products', $products);
        
        return true;
    }
}
