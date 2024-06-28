<?php

class Growtype_Post_Admin_Methods_Meta_Image
{
    const IMAGE_SERVICE = [
        'pexels' => 'pexels',
        'unsplash' => 'unsplash',
    ];

    public function __construct()
    {
        add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));
        add_action('add_meta_boxes', array ($this, 'growtype_post_settings_meta_box'));
        add_action('wp_ajax_growtype_post_admin_generate_featured_image', array ($this, 'growtype_post_admin_generate_featured_image_callback'));
        add_action('wp_ajax_growtype_post_admin_set_featured_image', array ($this, 'growtype_post_admin_set_featured_image_callback'));
    }

    function growtype_post_admin_set_featured_image_callback()
    {
        if (isset($_POST['custom_data']) && !empty($_POST['custom_data'])) {
            $post_id = intval($_POST['post_id']);
            $suggested_f_img_url = $_POST['custom_data']['growtype-post-featured-img-url'] ?? '';
            $responses = [];

            if (empty($post_id)) {
                wp_send_json_error('Invalid post ID.');
            }

            /**
             * Set featured image from URL
             */
            if (!empty($suggested_f_img_url)) {
                $attachment_id = growtype_child_set_featured_image_from_url($post_id, $suggested_f_img_url);
                $responses[] = [
                    'attachment_id' => $attachment_id,
                    'message' => 'Featured image was set.',
                    'url' => str_replace('&amp;', '&', get_edit_post_link($attachment_id))
                ];
            }

            wp_send_json_success($responses);
        } else {
            wp_send_json_error('Invalid data.');
        }
    }

    function growtype_post_settings_meta_box()
    {
        add_meta_box(
            'growtype-post-settings-meta-box-img',
            __('Growtype post - Images', 'growtype-post'),
            array ($this, 'growtype_post_settings_meta_box_img_callback'),
            'post',
            'side',
            'default'
        );
    }

    function growtype_post_settings_meta_box_img_callback($post)
    {
        ?>
        <div class="gp-form" style="position: relative;" data-type="growtype-post-image">
            <p><b>Featured image:</b></p>

            <div class="img-wrapper" style="max-width: 250px;min-height: 100px;background: grey;display: flex;margin-bottom: 20px;color: white;align-items: center;justify-content: center;">
                F image will be showed here
            </div>

            <div class="d-flex" style="display: flex;gap: 20px;">
                <div>
                    <p>Generate image:</p>

                    <select class="gp_g_img_service">
                        <?php
                        foreach (self::IMAGE_SERVICE as $key => $service) {
                            echo '<option value="' . $key . '">' . $service . '</option>';
                        }
                        ?>
                    </select>

                    <input type="text" name="growtype-post-featured-img-cat" value="<?= get_transient('growtype_post_generate_image_last_entered_data')['featured_image_cat'] ?? '' ?>" placeholder="Search tags (separated by comma)">

                    <div class="b-actions">
                        <button class="button button-secondary button-generate-image">Generate image</button>
                    </div>
                </div>

                <div>
                    <p>Get image from external url:</p>

                    <input type="text" name="growtype-post-featured-img-external-url" value="" placeholder="External image url">

                    <div class="b-actions">
                        <button class="button button-secondary button-get-image">Get image</button>
                    </div>
                </div>
            </div>

            <div class="b-actions" style="padding-top: 15px;border-top: 1px solid #bababa;margin-top: 20px;">
                <button class="button button-primary button-set-featured-image">Set f image</button>
            </div>

            <input type="hidden" name="growtype-post-featured-img-url">
        </div>
        <?php
    }

    public static function pexels_get_images($cat, $orientation = 'landskape', $amount = 20, $try_again_if_not_found = true)
    {
        $url = 'https://api.pexels.com/v1/search?query=' . urlencode($cat) . '&per_page=' . $amount . '&orientation=' . $orientation . '&page=' . rand(1, 3);

        $headers = [
            'Authorization: 46iOT1V5o545lL9CQELimjxmlGO2MFElYv3mmgeoH4tS1PrMq0R2H0KW'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('Failed to fetch image from Pexels');
        }

        curl_close($ch);

        $data = json_decode($response, true);

        $photos = $data['photos'] ?? [];

        shuffle($photos);

        if (empty($photos) && $try_again_if_not_found) {
            $photos = self::pexels_get_images($cat, $orientation, $amount, false);
        }

        return $photos;
    }

    function growtype_post_admin_generate_featured_image_callback()
    {
        if (isset($_POST['custom_data'])) {
            $service = $_POST['custom_data']['service_name'];
            $external_image_url = $_POST['custom_data']['external_image_url'] ?? '';
            $featured_image_cat = !empty($_POST['custom_data']['featured_image_cat']) ? $_POST['custom_data']['featured_image_cat'] : 'article';
            $orientation = isset($_POST['custom_data']['orientation']) && !empty($_POST['custom_data']['orientation']) ? $_POST['custom_data']['orientation'] : 'landscape';

            set_transient('growtype_post_generate_image_last_entered_data', $_POST['custom_data'], 60 * 60 * 24);

            if (!empty($external_image_url)) {
                $url = $external_image_url;
            } else {
                $url = self::generate_image_url($service, $featured_image_cat, $orientation);
            }

            wp_send_json_success(
                [
                    'url' => $url,
                ],
                200
            );
        }
    }

    public static function generate_image_url($service, $cat, $orientation = 'landscape')
    {
        if ($service === 'unsplash') {
            $url = 'https://source.unsplash.com/random/?' . urlencode($cat);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if ($response === false) {
                throw new Exception('Failed to fetch image from Unsplash');
            }

            curl_close($ch);

            $url = filter_var($response, FILTER_VALIDATE_URL) ? $response : '';
        } elseif ($service === 'pexels') {
            $photos = self::pexels_get_images($cat, $orientation);

            $url = $photos[0]['src']['large2x'] ?? '';
        }

        return $url;
    }

    function custom_admin_footer_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('.gp-form[data-type="growtype-post-image"] .button-generate-image, .gp-form[data-type="growtype-post-image"] .button-get-image').click(function () {
                    let button = $(this);

                    let customData = {
                        'service_name': button.closest('.gp-form').find('.gp_g_img_service').val(),
                        'featured_image_cat': button.hasClass('button-generate-image') ? button.closest('.gp-form').find('input[name="growtype-post-featured-img-cat"]').val() : '',
                        'external_image_url': button.hasClass('button-get-image') ? button.closest('.gp-form').find('input[name="growtype-post-featured-img-external-url"]').val() : '',
                    };

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'growtype_post_admin_generate_featured_image',
                            custom_data: customData,
                        },
                        success: function (response) {
                            let suggestedFImgUrl = response.data.url;

                            if (suggestedFImgUrl) {
                                button.closest('.gp-form').find('.img-wrapper').html('<img src="' + suggestedFImgUrl + '" style="max-width: 100%;">');
                                button.closest('.gp-form').find('input[name="growtype-post-featured-img-url"]').val(suggestedFImgUrl);
                            } else {
                                button.closest('.gp-form').prepend('<div class="error" style="margin: 0;position: absolute;top: 0;left: 0;">Failed to fetch image.</div>');
                                setTimeout(function () {
                                    button.closest('.gp-form').find('.error').remove();
                                }, 2000);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(error);
                        }
                    });
                });

                $('.gp-form[data-type="growtype-post-image"] .button-set-featured-image').click(function () {
                    let button = $(this);
                    let postId = $('#post_ID').val();

                    let customData = {
                        'growtype-post-featured-img-url': $(this).closest('.gp-form').find('input[name="growtype-post-featured-img-url"]').val(),
                    };

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'growtype_post_admin_set_featured_image',
                            post_id: postId,
                            custom_data: customData,
                        },
                        success: function (response) {
                            console.log(response, 'response')

                            response.data.map(function (item) {
                                growtype_post_admin_show_notice(item);
                            });
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
