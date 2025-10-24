<div class="growtype-post-single-inner">
    <div class="b-content" style="background-image: url(<?php echo esc_url(growtype_post_get_featured_image_url($post)); ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
        <div class="b-content-inner">
            <?php
            if (isset($post->ID)) {
                $taxonomy = get_post_type($post) . '_tax';
                $terms = taxonomy_exists($taxonomy) ? wp_get_post_terms($post->ID, $taxonomy) : [];

                if (!is_wp_error($terms) && !empty($terms)) {
                    $terms = implode(', ', wp_list_pluck($terms, 'name'));
                } else {
                    $terms = '';
                }

                if (!get_theme_mod('growtype_post_preview_categories_enabled', true)) {
                    $terms = '';
                }

                if (!empty($terms)) {
                    echo '<p class="e-terms">' . esc_html($terms) . '</p>';
                }
            }

            if (!empty($post->post_date)) {
                echo '<p class="e-date">' . esc_html(date_format(date_create($post->post_date), growtype_post_date_format())) . '</p>';
            }

            if (!empty($post->post_title)) {
                echo '<h4 class="e-title">' . esc_html($post->post_title) . '</h4>';
            }

            if (!empty($post->post_excerpt)) {
                echo '<div class="e-excerpt">' .
                    growtype_post_get_limited_content(
                        $post->post_excerpt,
                        isset($intro_content_length) && !empty($intro_content_length) ? $intro_content_length : null
                    ) .
                    '</div>';
            }
            ?>

            <div class="b-actions">
                <button class="btn btn-primary">
                    <?php echo esc_html($cta_label ?? ''); ?>
                </button>
            </div>
        </div>
    </div>
</div>
