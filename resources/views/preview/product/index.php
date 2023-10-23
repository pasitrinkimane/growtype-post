<div class="<?php echo isset($post_classes) ? $post_classes : '' ?>" data-cat="<?php echo isset($terms) && !empty($terms) ? implode(',', array_pluck($terms, 'slug')) : '' ?>">
    <?php
    if (class_exists('woocommerce')) {
        wc_get_template_part('content', 'product');
    } else {
        echo __('Please install WooCommerce plugin', 'growtype-post');
    }
    ?>
</div>
