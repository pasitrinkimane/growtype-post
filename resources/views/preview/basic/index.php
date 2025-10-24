<?php if (isset($post_is_a_link) && $post_is_a_link === true) { ?>
    <a href="<?php echo $post_permalink ?>"
       class="<?php echo isset($post_classes) ? $post_classes : '' ?>"
       data-id="<?= $post->ID ?>"
        <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtype-post-modal-' . $post->ID . '"' : '' ?>
        <?php echo isset($post_terms_html) ? $post_terms_html : '' ?>
        <?php echo isset($post_attributes) ? $post_attributes : '' ?>
    >
        <?php echo growtype_post_include_view(
            'preview.' . $args['preview_style'] . '.content',
            $args
        ); ?>
    </a>
<?php } else { ?>
    <div class="<?php echo isset($post_classes) ? $post_classes : '' ?>"
         data-id="<?= $post->ID ?>"
        <?php echo isset($post_in_modal) && $post_in_modal ? 'data-bs-toggle="modal" data-bs-target="#growtype-post-modal-' . $post->ID . '"' : '' ?>
        <?php echo isset($post_terms_html) ? $post_terms_html : '' ?>
        <?php echo isset($post_attributes) ? $post_attributes : '' ?>
    >
        <?php echo growtype_post_include_view(
            'preview.' . $args['preview_style'] . '.content',
            $args
        ); ?>
    </div>
<?php } ?>
