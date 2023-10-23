<?php if (isset($post_is_a_link) && $post_is_a_link === false) { ?>
    <div class="<?php echo isset($post_classes) ? $post_classes : '' ?>" <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtypePostModal-' . $post->ID . '"' : '' ?> data-cat="<?php echo isset($post_terms) && !empty($post_terms) ? implode(',', array_pluck($post_terms, 'slug')) : '' ?>" <?php echo isset($post_attributes) ? $post_attributes : '' ?>>
        <?php echo growtype_post_include_view(
            $preview_file_path,
            $variables
        ); ?>
    </div>
<?php } else { ?>
    <a href="<?php echo $post_permalink ?>" class="<?php echo isset($post_classes) ? $post_classes : '' ?>" <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtypePostModal-' . $post->ID . '"' : '' ?> data-cat="<?php echo isset($post_terms) && !empty($post_terms) ? implode(',', array_pluck($post_terms, 'slug')) : '' ?>" <?php echo isset($post_attributes) ? $post_attributes : '' ?>>
        <?php echo growtype_post_include_view(
            $preview_file_path,
            $variables
        ); ?>
    </a>
<?php } ?>
