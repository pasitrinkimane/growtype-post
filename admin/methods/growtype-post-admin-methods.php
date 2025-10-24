<?php

class Growtype_Post_Admin_Methods
{
    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            include_once GROWTYPE_POST_PATH . '/admin/methods/share/growtype-post-admin-methods-share.php';
            new Growtype_Post_Admin_Methods_Share();

            if (is_admin()) {
                include_once GROWTYPE_POST_PATH . '/admin/methods/meta/growtype-post-admin-methods-meta.php';
                new Growtype_Post_Admin_Methods_Meta();

                add_filter('manage_posts_columns', array ($this, 'add_custom_columns_callback'));

                add_action('manage_posts_custom_column', array ($this, 'manage_custom_columns_callback'), 10, 2);

                add_action('admin_head', array ($this, 'add_inline_script_to_admin_head'));
            }
        }
    }

    function add_inline_script_to_admin_head()
    {
        ?>
        <script>
            let isDefaultTemplateSet = false;
            if (typeof wp !== 'undefined') {
                wp.data.subscribe(() => {
                    const {getEditedPostAttribute} = wp.data.select('core/editor');
                    const {editPost} = wp.data.dispatch('core/editor');

                    const currentTemplate = getEditedPostAttribute('template');
                    const postType = getEditedPostAttribute('type');
                    const postId = getEditedPostAttribute('id');

                    if (!isDefaultTemplateSet && currentTemplate === '' && postType === 'post') {
                        console.log(postId, 'postId');
                        console.log(postType, 'postType');
                        console.log('Default template set to: template-article.php');

                        // Set the default template
                        editPost({template: 'template-article.php'});

                        // Mark the default template as set
                        isDefaultTemplateSet = true;
                    }
                });
            }

        </script>
        <?php
    }

    function add_custom_columns_callback($columns)
    {
        $screen = get_current_screen();

        if (isset($screen->post_type) && $screen->post_type === 'post') {
            $columns['already_shared_on_platforms'] = __('Shared on platforms', 'growtype-post');
        }

        return $columns;
    }

    function manage_custom_columns_callback($column_name, $post_id)
    {
        if ($column_name === 'already_shared_on_platforms') {
            $is_already_shared_on_platforms = get_post_meta($post_id, 'growtype_post_is_already_shared_on_platforms', true);
            $is_already_shared_on_platforms = !empty($is_already_shared_on_platforms) ? array_keys($is_already_shared_on_platforms) : [];

            if (empty($is_already_shared_on_platforms)) {
                $is_already_shared_on_platforms = get_post_meta($post_id, 'growtype_post_is_shared_on_platforms', true);
                $is_already_shared_on_platforms = !empty($is_already_shared_on_platforms) ? $is_already_shared_on_platforms : [];
            }

            $formatted_platforms = implode(',', $is_already_shared_on_platforms);

            echo $formatted_platforms;
        }
    }
}
