<?php
/**
 * Admin Portfolio Management
 *
 * Upload and manage portfolio images
 * Matching rashaadsallie.com layout
 *
 * @package SwitchBusinessHub
 * @version 1.9.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBHA_Admin_Portfolio {

    public function __construct() {
        add_action('wp_ajax_sbha_save_portfolio_item', array($this, 'ajax_save_item'));
        add_action('wp_ajax_sbha_delete_portfolio_item', array($this, 'ajax_delete_item'));
    }

    public function render() {
        // Handle form submission
        if (isset($_POST['sbha_save_portfolio']) && wp_verify_nonce($_POST['_wpnonce'], 'sbha_portfolio')) {
            $this->save_item($_POST);
        }
        
        if (isset($_GET['delete']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_portfolio')) {
            $this->delete_item(intval($_GET['delete']));
        }
        
        $items = get_option('sbha_portfolio_items', array());
        $categories = $this->get_categories($items);
        $edit_idx = isset($_GET['edit']) ? intval($_GET['edit']) : -1;
        $edit_item = ($edit_idx >= 0 && isset($items[$edit_idx])) ? $items[$edit_idx] : null;
        
        // Enqueue media uploader
        wp_enqueue_media();
        ?>
        <div class="wrap sbha-admin-wrap">
            <h1 class="sbha-admin-title">
                <span class="sbha-logo">🖼️ Portfolio</span>
                <a href="?page=sbha-portfolio&edit=new" class="page-title-action">+ Add Item</a>
            </h1>
            
            <style>
                .sbha-portfolio-layout{display:grid;grid-template-columns:300px 1fr;gap:20px;margin-top:20px}
                @media(max-width:900px){.sbha-portfolio-layout{grid-template-columns:1fr}}
                
                .sbha-portfolio-form{background:#fff;border-radius:10px;padding:20px;box-shadow:0 2px 5px rgba(0,0,0,0.1)}
                .sbha-form-row{margin-bottom:15px}
                .sbha-form-row label{display:block;font-weight:600;margin-bottom:5px;font-size:13px}
                .sbha-form-row input,.sbha-form-row select{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px}
                
                .sbha-image-upload{border:2px dashed #ddd;border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:all 0.2s}
                .sbha-image-upload:hover{border-color:#FF6600}
                .sbha-image-upload img{max-width:100%;max-height:200px;border-radius:8px}
                .sbha-image-upload .placeholder{color:#999;font-size:40px}
                
                .sbha-portfolio-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px}
                .sbha-portfolio-item{background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 5px rgba(0,0,0,0.1);transition:transform 0.2s}
                .sbha-portfolio-item:hover{transform:translateY(-5px)}
                .sbha-portfolio-item-img{height:180px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;overflow:hidden}
                .sbha-portfolio-item-img img{width:100%;height:100%;object-fit:cover}
                .sbha-portfolio-item-img .placeholder{font-size:60px;color:#ddd}
                .sbha-portfolio-item-info{padding:15px}
                .sbha-portfolio-item-info h4{margin:0 0 5px;font-size:14px}
                .sbha-portfolio-item-info span{display:inline-block;border:1px solid #ddd;padding:2px 8px;font-size:10px;border-radius:3px}
                .sbha-portfolio-item-actions{padding:0 15px 15px;display:flex;gap:10px}
                .sbha-portfolio-item-actions a{font-size:12px;text-decoration:none;color:#666}
                .sbha-portfolio-item-actions a:hover{color:#FF6600}
                .sbha-portfolio-item-actions .delete{color:#dc2626}
                
                .sbha-btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px}
                .sbha-btn-primary{background:#FF6600;color:#fff}
                
                .sbha-cat-filter{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
                .sbha-cat-filter button{padding:8px 16px;border:2px solid #ddd;background:#fff;border-radius:20px;font-size:12px;cursor:pointer}
                .sbha-cat-filter button.active{border-color:#FF6600;background:#FF6600;color:#fff}
            </style>
            
            <div class="sbha-portfolio-layout">
                <!-- Form -->
                <div class="sbha-portfolio-form">
                    <h3><?php echo $edit_item ? 'Edit Item' : (isset($_GET['edit']) && $_GET['edit'] === 'new' ? 'Add New Item' : 'Portfolio Items'); ?></h3>
                    
                    <?php if ($edit_item || (isset($_GET['edit']) && $_GET['edit'] === 'new')): ?>
                    <form method="post">
                        <?php wp_nonce_field('sbha_portfolio'); ?>
                        <input type="hidden" name="item_idx" value="<?php echo $edit_idx; ?>">
                        
                        <div class="sbha-form-row">
                            <label>Title *</label>
                            <input type="text" name="title" value="<?php echo esc_attr($edit_item['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="sbha-form-row">
                            <label>Category *</label>
                            <input type="text" name="category" value="<?php echo esc_attr($edit_item['category'] ?? 'Brand Design'); ?>" list="categories" required>
                            <datalist id="categories">
                                <option value="Brand Design">
                                <option value="Logo Design">
                                <option value="Websites">
                                <option value="Business Cards">
                                <option value="Flyers & Posters">
                                <option value="Banners">
                                <option value="Vehicle Branding">
                            </datalist>
                        </div>
                        
                        <div class="sbha-form-row">
                            <label>Image</label>
                            <div class="sbha-image-upload" onclick="selectImage()">
                                <?php if (!empty($edit_item['image'])): ?>
                                <img src="<?php echo esc_url($edit_item['image']); ?>" id="previewImg">
                                <?php else: ?>
                                <div class="placeholder" id="previewPlaceholder">🖼️</div>
                                <img src="" id="previewImg" style="display:none">
                                <?php endif; ?>
                                <p style="margin:10px 0 0;font-size:12px;color:#666">Click to upload</p>
                            </div>
                            <input type="hidden" name="image" id="imageUrl" value="<?php echo esc_url($edit_item['image'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" name="sbha_save_portfolio" class="sbha-btn sbha-btn-primary" style="width:100%">💾 Save Item</button>
                        
                        <?php if ($edit_item): ?>
                        <a href="?page=sbha-portfolio" style="display:block;text-align:center;margin-top:10px;color:#666">Cancel</a>
                        <?php endif; ?>
                    </form>
                    
                    <script>
                    function selectImage() {
                        const frame = wp.media({title: 'Select Image', multiple: false, library: {type: 'image'}});
                        frame.on('select', function() {
                            const attachment = frame.state().get('selection').first().toJSON();
                            document.getElementById('imageUrl').value = attachment.url;
                            document.getElementById('previewImg').src = attachment.url;
                            document.getElementById('previewImg').style.display = 'block';
                            const placeholder = document.getElementById('previewPlaceholder');
                            if (placeholder) placeholder.style.display = 'none';
                        });
                        frame.open();
                    }
                    </script>
                    <?php else: ?>
                    <p style="color:#666;text-align:center;padding:20px">
                        Select an item to edit or <a href="?page=sbha-portfolio&edit=new">add a new one</a>
                    </p>
                    <?php endif; ?>
                </div>
                
                <!-- Portfolio Grid -->
                <div>
                    <div class="sbha-cat-filter">
                        <button class="active" onclick="filterPortfolio('all')">All</button>
                        <?php foreach ($categories as $cat): ?>
                        <button onclick="filterPortfolio('<?php echo esc_js($cat); ?>')"><?php echo esc_html($cat); ?></button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="sbha-portfolio-grid" id="portfolioGrid">
                        <?php if (empty($items)): ?>
                        <p style="grid-column:1/-1;text-align:center;padding:40px;color:#666">
                            No portfolio items yet. <a href="?page=sbha-portfolio&edit=new">Add your first item!</a>
                        </p>
                        <?php else: foreach ($items as $idx => $item): ?>
                        <div class="sbha-portfolio-item" data-category="<?php echo esc_attr($item['category'] ?? ''); ?>">
                            <div class="sbha-portfolio-item-img">
                                <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                                <?php else: ?>
                                <span class="placeholder">🎨</span>
                                <?php endif; ?>
                            </div>
                            <div class="sbha-portfolio-item-info">
                                <h4><?php echo esc_html($item['title']); ?></h4>
                                <span><?php echo esc_html(strtoupper($item['category'] ?? '')); ?></span>
                            </div>
                            <div class="sbha-portfolio-item-actions">
                                <a href="?page=sbha-portfolio&edit=<?php echo $idx; ?>">✏️ Edit</a>
                                <a href="?page=sbha-portfolio&delete=<?php echo $idx; ?>&_wpnonce=<?php echo wp_create_nonce('delete_portfolio'); ?>" class="delete" onclick="return confirm('Delete this item?')">🗑️ Delete</a>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
            
            <script>
            function filterPortfolio(cat) {
                document.querySelectorAll('.sbha-cat-filter button').forEach(b => b.classList.remove('active'));
                event.target.classList.add('active');
                document.querySelectorAll('.sbha-portfolio-item').forEach(item => {
                    if (cat === 'all' || item.dataset.category === cat) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }
            </script>
            
            <div style="margin-top:30px;padding:20px;background:#f8f9fa;border-radius:10px">
                <h3>📌 Shortcode</h3>
                <p>Use this shortcode to display your portfolio on any page:</p>
                <code style="display:block;padding:15px;background:#fff;border-radius:6px;margin-top:10px">[switch_portfolio]</code>
                <p style="margin-top:10px;color:#666;font-size:13px">Optional: Filter by category: <code>[switch_portfolio category="Brand Design"]</code></p>
            </div>
        </div>
        <?php
    }
    
    private function get_categories($items) {
        $cats = array();
        foreach ($items as $item) {
            if (!empty($item['category']) && !in_array($item['category'], $cats)) {
                $cats[] = $item['category'];
            }
        }
        return $cats;
    }
    
    private function save_item($data) {
        $items = get_option('sbha_portfolio_items', array());
        $idx = isset($data['item_idx']) ? intval($data['item_idx']) : -1;
        
        $item = array(
            'title' => sanitize_text_field($data['title'] ?? ''),
            'category' => sanitize_text_field($data['category'] ?? ''),
            'image' => esc_url_raw($data['image'] ?? '')
        );
        
        if ($idx >= 0 && isset($items[$idx])) {
            $items[$idx] = $item;
        } else {
            $items[] = $item;
        }
        
        update_option('sbha_portfolio_items', $items);
    }
    
    private function delete_item($idx) {
        $items = get_option('sbha_portfolio_items', array());
        if (isset($items[$idx])) {
            array_splice($items, $idx, 1);
            update_option('sbha_portfolio_items', $items);
        }
    }
}
