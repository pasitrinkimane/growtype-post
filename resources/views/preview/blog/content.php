<div class="growtype-post-single-inner">
    <div class="b-content" style="background-image: url(<?php echo growtype_post_get_featured_image_url($post) ?>);background-position: center;background-size: cover;background-repeat: no-repeat;">
        <div class="b-content-inner">
            <?php
            $terms = wp_get_post_terms($post->ID, get_post_type($post) . '_tax');
            $terms = !is_wp_error($terms) && !empty($terms) ? implode(', ', array_pluck($terms, 'name')) : '';

            if (!empty($terms)) { ?>
                <p class="e-terms"><?php echo $terms ?></p>
            <?php } ?>
            <?php if (!empty($post->post_date)) { ?>
                <p class="e-date"><?php echo date_format(date_create($post->post_date), growtype_post_date_format()) ?></p>
            <?php } ?>
            <?php if (!empty($post->post_title)) { ?>
                <h4 class="e-title"><?php echo $post->post_title ?></h4>
            <?php } ?>
            <?php if (!empty($post->post_excerpt)) { ?>
                <div class="e-excerpt">
                    <?php echo growtype_post_get_limited_content($post->post_excerpt, isset($intro_content_length) && !empty($intro_content_length) ? $intro_content_length : null) ?>
                </div>
            <?php } ?>

            <div class="b-actions">
                <button class="btn btn-primary">
                    <?php echo isset($cta_label) ? $cta_label : __('Continue reading', 'growtype-post'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
