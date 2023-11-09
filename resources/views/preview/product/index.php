<div class="<?php echo isset($post_classes) ? $post_classes : '' ?>"
    <?php echo isset($post_terms_html) ? $post_terms_html : '' ?>
>
    <?php
    if (class_exists('woocommerce')) {
        wc_get_template_part('content', 'product');
    } else {
        echo __('Please install WooCommerce plugin', 'growtype-post');
    }
    ?>
</div>
