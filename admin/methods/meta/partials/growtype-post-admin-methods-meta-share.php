<?php

class Growtype_Post_Admin_Methods_Meta_Share
{
    const REDDIT = 'reddit';
    const BLOGGER = 'blogger';
    const MEDIUM = 'medium';
    const PINTEREST = 'pinterest';
    const TWITTER = 'twitter';
    const TREADS = 'treads';

    const PLATFORMS = [
        self::REDDIT => 'Reddit',
        self::BLOGGER => 'Blogger',
        self::MEDIUM => 'Medium',
        self::PINTEREST => 'Pinterest',
        self::TWITTER => 'Twitter',
//        self::TREADS => 'Treads',
    ];

    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));
            add_action('wp_ajax_growtype_post_admin_shared_on', array ($this, 'growtype_post_admin_shared_on_callback'));
            add_action('wp_ajax_growtype_post_admin_share_on', array ($this, 'growtype_post_admin_share_on_callback'));
            add_action('add_meta_boxes', array ($this, 'growtype_post_settings_meta_box'));
        }
    }

    function growtype_post_settings_meta_box()
    {
        add_meta_box(
            'growtype-post-settings-meta-box',
            __('Growtype post - Share', 'growtype-post'),
            array ($this, 'growtype_post_settings_meta_box_callback'),
            'post',
            'side',
            'default'
        );
    }

    function growtype_post_settings_meta_box_callback($post)
    {
        $shared_on_platforms = self::shared_on_platforms($post->ID);
        ?>
        <style>
            .gp-form {
                padding: 20px;
                margin-bottom: 20px;
                background: #ededed;
            }

            .gp-form:last-child {
                margin-bottom: 0;
            }

            .gp-form .b-actions {
                padding-top: 15px;
            }

            .gp-form .input-group {
                padding-top: 10px;
                display: flex;
                align-items: center;
                gap: 5px;
            }
        </style>
        <div class="gp-form" data-type="growtype-post-shared-on">
            <b>Post already shared on:</b>
            <?php foreach (self::PLATFORMS as $key => $platform) { ?>
                <div class="input-group">
                    <label><?php echo $platform ?></label>
                    <input type="checkbox" name="growtype_post_is_shared_on_platforms_<?php echo $key ?>" <?php echo checked(1, in_array($key, $shared_on_platforms)) ?>>
                </div>
                <?php
            }
            ?>
            <div class="b-actions">
                <button class="button button-primary button-submit">Save</button>
            </div>
        </div>

        <div class="gp-form" data-type="growtype-post-share-on">
            <b>Share post on platforms:</b>
            <select name="growtype_post_share_on_platform">
                <?php
                echo '<option value="all" selected>All</option>';

                foreach (self::PLATFORMS as $key => $platform) {
                    echo '<option value="' . $key . '">' . $platform . '</option>';
                }
                ?>
            </select>
            <div class="b-actions">
                <button class="button button-primary button-submit">Share</button>
            </div>
        </div>
        <?php
    }

    public static function shared_on_platforms($post_id)
    {
        $shared_on_platforms = get_post_meta($post_id, 'growtype_post_is_shared_on_platforms', true);
        $shared_on_platforms = !empty($shared_on_platforms) ? $shared_on_platforms : [];

        return $shared_on_platforms;
    }

    function growtype_post_admin_shared_on_callback()
    {
        if (isset($_POST['custom_data']) && !empty($_POST['custom_data'])) {
            $post_id = intval($_POST['post_id']);
            $responses = [];

            $shared_on_platforms = self::shared_on_platforms($post_id);

            foreach ($_POST['custom_data'] as $key => $data) {
                if (strpos($key, 'growtype_post_is_shared_on_platforms_') !== false) {
                    $existing_shared_on_platforms = str_replace('growtype_post_is_shared_on_platforms_', '', $key);

                    if (!empty($data) && !isset($shared_on_platforms[$existing_shared_on_platforms])) {
                        array_push($shared_on_platforms, $existing_shared_on_platforms);
                    } else {
                        $shared_on_platforms = array_diff($shared_on_platforms, [$existing_shared_on_platforms]);
                    }
                }
            }

            update_post_meta($post_id, 'growtype_post_is_shared_on_platforms', $shared_on_platforms);

            wp_send_json_success($responses);
        } else {
            wp_send_json_error('Invalid data.');
        }
    }

    function growtype_post_admin_share_on_callback()
    {
        if (isset($_POST['custom_data']) && !empty($_POST['custom_data'])) {
            $post_id = intval($_POST['post_id']);
            $platform = $_POST['custom_data']['growtype_post_share_on_platform'] ?? '';
            $responses = [];

            $shared_on_platforms = self::shared_on_platforms($post_id);

            if (!empty($platform)) {
                if ($platform === 'all') {
                    foreach (self::PLATFORMS as $key => $platform) {
                        if (in_array($key, $shared_on_platforms)) {
                            continue;
                        }

                        $response = Growtype_Post_Admin_Methods_Share::submit($key, $post_id);
                        $response['platform'] = $platform;
                        array_push($responses, $response);
                    }
                } else {
                    if (!in_array($platform, $shared_on_platforms)) {
                        $response = Growtype_Post_Admin_Methods_Share::submit($platform, $post_id);
                        $response['platform'] = $platform;
                        array_push($responses, $response);
                    }
                }
            }

            wp_send_json_success($responses);
        } else {
            wp_send_json_error('Invalid data.');
        }
    }

    function custom_admin_footer_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('.gp-form[data-type="growtype-post-shared-on"] .button-submit').click(function () {
                    let postId = $('#post_ID').val();
                    let customData = {};
                    $(this).closest('.gp-form').find('input,select').each(function () {
                        var name = $(this).attr('name');
                        var value = $(this).val();
                        if ($(this).attr('type') === 'checkbox') {
                            value = $(this).is(':checked') ? 1 : 0;
                        }
                        customData[name] = value;
                    });

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'growtype_post_admin_shared_on',
                            custom_data: customData,
                            post_id: postId
                        },
                        success: function (response) {
                            if (response.success) {
                                if (response.data && response.data.length > 0) {
                                    response.data.forEach(function (item) {
                                        let message = item.message ?? '';
                                        growtype_post_admin_show_notice(item);
                                    });
                                } else {
                                    growtype_post_admin_show_notice(response.data);
                                }
                            }
                        },
                        error: function (response, status, error) {
                            growtype_post_admin_show_notice(response.data);
                        }
                    });
                });

                $('.gp-form[data-type="growtype-post-share-on"] .button-submit').click(function () {
                    let postId = $('#post_ID').val();
                    let customData = {};
                    $(this).closest('.gp-form').find('input,select').each(function () {
                        var name = $(this).attr('name');
                        var value = $(this).val();
                        if ($(this).attr('type') === 'checkbox') {
                            value = $(this).is(':checked') ? 1 : 0;
                        }
                        customData[name] = value;
                    });

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'growtype_post_admin_share_on',
                            custom_data: customData,
                            post_id: postId
                        },
                        success: function (response) {
                            if (response.success) {
                                if (response.data && response.data.length > 0) {
                                    response.data.forEach(function (item) {

                                        if (item.success) {
                                            let platform = item.platform.toLowerCase();
                                            $('input[name="growtype_post_is_shared_on_platforms_' + platform + '"').prop("checked", true);
                                        }

                                        let message = item.message ?? '';
                                        growtype_post_admin_show_notice(item);
                                    });
                                } else {
                                    growtype_post_admin_show_notice(response.data);
                                }
                            }
                        },
                        error: function (response, status, error) {
                            growtype_post_admin_show_notice(response.data);
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
