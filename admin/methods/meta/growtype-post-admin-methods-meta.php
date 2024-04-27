<?php

class Growtype_Post_Admin_Methods_Meta
{
    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            add_action('add_meta_boxes', array ($this, 'growtype_post_settings_meta_box'));
            add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));
            add_action('wp_ajax_growtype_post_admin_save_data', array ($this, 'growtype_post_admin_save_data'));
        }
    }

    function growtype_post_admin_save_data()
    {
        if (isset($_POST['custom_data']) && !empty($_POST['custom_data'])) {
            $post_id = intval($_POST['post_id']);

            if (isset($_POST['custom_data']['share_platform'])) {
                Growtype_Post_Admin_Methods_Share::share_post($_POST['custom_data']['share_platform'], $post_id);
            }

        } else {
            wp_send_json_error('Invalid data.');
        }
    }

    function custom_admin_footer_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#growtype-post-share').click(function () {
                    let postId = $('#post_ID').val();
                    let customData = {};
                    $(this).closest('.form').find('input,select').each(function () {
                        var name = $(this).attr('name');
                        var value = $(this).val();
                        customData[name] = value;
                    });

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl, // WordPress AJAX handler URL
                        data: {
                            action: 'growtype_post_admin_save_data',
                            custom_data: customData,
                            post_id: postId
                        },
                        success: function (response) {
                            console.log('Data submitted successfully!')
                        },
                        error: function (xhr, status, error) {
                            console.error(error);
                        }
                    });
                });
            });
        </script>
        <?php
    }

    function growtype_post_settings_meta_box()
    {
        add_meta_box(
            'growtype-post-settings-meta-box',
            __('Growtype post', 'growtype-post'),
            array ($this, 'growtype_post_settings_meta_box_callback'),
            'post',
            'side',
            'default'
        );
    }

    function growtype_post_settings_meta_box_callback($post)
    {
        ?>
        <div class="form">
            <label for="">Share post on platform:</label>
            <select name="share_platform">
                <option value="medium">Medium</option>
            </select>
            <button class="button button-primary" id="growtype-post-share">Submit Data</button>
        </div>
        <?php
    }
}
