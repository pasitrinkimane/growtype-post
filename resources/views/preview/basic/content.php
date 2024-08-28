<div class="growtype-post-single-inner">
    <?php if (!empty(growtype_post_get_featured_image_url($post))) { ?>
        <div class="e-img" style="background-image: url(<?php echo growtype_post_get_featured_image_url($post) ?>);background-position: center;background-size: cover;background-repeat: no-repeat;"></div>
    <?php } ?>
    <div class="b-content">
        <?php
        $terms = !empty($post_terms) && isset(array_values($post_terms)[0]) ? implode(', ', array_pluck(array_values($post_terms)[0], 'name')) : '';
        if (!empty($terms)) { ?>
            <p class="e-terms"><?php echo $terms ?></p>
        <?php } ?>
        <?php if (get_theme_mod('growtype_post_preview_date_enabled', true) && !empty($post->post_date)) { ?>
            <p class="e-date"><?php echo date_format(date_create($post->post_date), growtype_post_date_format()) ?></p>
        <?php } ?>
        <?php if (!empty($post->post_title)) { ?>
            <h4 class="e-title"><?php echo $post->post_title ?></h4>
        <?php } ?>
        <?php if (!empty(growtype_post_get_excerpt($post->ID))) { ?>
            <div class="e-excerpt">
                <?php echo growtype_post_get_excerpt($post->ID, isset($intro_content_length) && !empty($intro_content_length) ? $intro_content_length : null) ?>
            </div>
        <?php } ?>
        <?php echo growtype_post_render_cta($post->ID); ?>
    </div>

    <?php if (get_theme_mod('growtype_post_preview_actions_enabled', true)) { ?>
        <div class="b-actions">
            <button class="btn btn-primary">
                <?php echo isset($cta_label) ? $cta_label : __('Continue reading', 'growtype-post'); ?>
            </button>
        </div>
    <?php } ?>
</div>
