<?php

class Growtype_Post_Admin_Methods_Meta_Image
{
    const SERVICES = [
        'pexels' => 'Pexels',
        'unsplash' => 'Unsplash',
        'pixabay' => 'Pixabay',
        'external_url' => 'External Url'
    ];

    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            add_action('current_screen', function ($screen) {
                if ($screen->base === 'post') {
                    add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));
                    add_action('add_meta_boxes', array ($this, 'growtype_post_settings_meta_box'));
                }
            });

            add_action('wp_ajax_growtype_post_admin_generate_featured_image', array ($this, 'growtype_post_admin_generate_featured_image_callback'));
            add_action('wp_ajax_growtype_post_admin_set_featured_image', array ($this, 'growtype_post_admin_set_featured_image_callback'));
        }
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
                $attachment_id = growtype_post_upload_image_from_url($suggested_f_img_url);

                set_post_thumbnail($post_id, $attachment_id);

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
            __('Growtype Post - Images', 'growtype-post'),
            array ($this, 'growtype_post_settings_meta_box_img_callback'),
            'post',
            'normal',
            'default'
        );
    }

    function growtype_post_settings_meta_box_img_callback($post)
    {
        ?>
        <style>
            .gp-form[data-type="growtype-post-image"] .img-wrapper {
                cursor: pointer;
            }

            .gp-form[data-type="growtype-post-image"] .img-wrapper.is-active img {
                border: 5px solid #3fa459;
            }
        </style>

        <div class="gp-form" style="position: relative;" data-type="growtype-post-image">
            <p><b>Featured image:</b></p>

            <div class="featured-image-preview-wrapper" style="display: grid;grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));gap: 10px;">
                <div class="img-wrapper" style="min-height: 100px;background: grey;display: flex;margin-bottom: 5px;color: white;align-items: center;justify-content: center;">
                    Featured image preview
                </div>
            </div>

            <div class="d-flex" style="display: flex;gap: 20px;">
                <div style="width: 100%;">
                    <p>Generate image:</p>

                    <div style="width: 100%;display: flex;gap: 5px;">
                        <select class="gp_g_img_service">
                            <?php
                            foreach (self::SERVICES as $key => $service) {
                                echo '<option value="' . $key . '">' . $service . '</option>';
                            }
                            ?>
                        </select>

                        <input type="number" style="max-width:50px;" name="growtype-post-featured-img-page" value="<?= get_transient('growtype_post_generate_image_last_entered_data')['featured_image_page'] ?? 1 ?>" placeholder="page">

                        <input type="text" style="width: 100%;" name="growtype-post-featured-img-cat" value="<?= get_transient('growtype_post_generate_image_last_entered_data')['featured_image_cat'] ?? '' ?>" placeholder="Search tags (separated by comma)">
                    </div>

                    <div class="b-actions">
                        <button class="button button-secondary button-generate-image">Generate Image</button>
                    </div>
                </div>

                <div style="display: none;">
                    <p>Get image from external url:</p>

                    <input type="text" name="growtype-post-featured-img-external-url" value="" placeholder="External image url">

                    <div class="b-actions">
                        <button class="button button-secondary button-get-image">Get image</button>
                    </div>
                </div>
            </div>

            <div class="b-actions" style="padding-top: 15px;border-top: 1px solid #bababa;margin-top: 20px;">
                <button class="button button-primary button-set-featured-image">Set featured image</button>
            </div>

            <input type="hidden" name="growtype-post-featured-img-url">
        </div>
        <?php
    }

    public static function get_images_from_external_service($service, $cat, $orientation = 'landskape', $page = 1, $amount = 20)
    {
        if ($service === 'pexels') {
            $url = 'https://api.pexels.com/v1/search?query=' . urlencode($cat) . '&per_page=' . $amount . '&orientation=' . $orientation . '&page=' . $page;

            $headers = [
                'Authorization: 46iOT1V5o545lL9CQELimjxmlGO2MFElYv3mmgeoH4tS1PrMq0R2H0KW'
            ];
        } elseif ($service === 'pixabay') {
            $api_key = '47898579-fce65db77a6e512ba1fc36de5';
            $url = 'https://pixabay.com/api/?key=' . $api_key
                . '&q=' . urlencode($cat)
                . '&image_type=photo'
                . '&orientation=horizontal'
                . '&safesearch=false'
                . '&order=popular'
                . '&page=' . $page
                . '&per_page=' . $amount;
        } elseif ($service === 'unsplash') {
            $accessKey = 'bMEZvnL85BtGMsXfLuo6dhIu1TQT-4mrfj28oEZqRmo';
            $headers = [
                "Authorization: Client-ID $accessKey"
            ];
            $url = "https://api.unsplash.com/search/photos?query=" . urlencode($cat) . "&per_page=" . $amount . "&page=" . $page . "&orientation=" . $orientation;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (isset($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            return [
                'success' => false,
                'message' => sprintf('Failed to fetch images from %s', $service)
            ];
        }

        curl_close($ch);

        $data = json_decode($response, true);

        return $data;
    }

    function growtype_post_admin_generate_featured_image_callback()
    {
        if (isset($_POST['custom_data'])) {
            $service = $_POST['custom_data']['service_name'];
            $external_image_url = $_POST['custom_data']['external_image_url'] ?? '';
            $featured_image_cat = !empty($_POST['custom_data']['featured_image_cat']) ? $_POST['custom_data']['featured_image_cat'] : 'article';
            $featured_image_page = !empty($_POST['custom_data']['featured_image_page']) ? $_POST['custom_data']['featured_image_page'] : 1;
            $orientation = isset($_POST['custom_data']['orientation']) && !empty($_POST['custom_data']['orientation']) ? $_POST['custom_data']['orientation'] : 'landscape';

            if (filter_var($featured_image_cat, FILTER_VALIDATE_URL)) {
                $external_image_url = $featured_image_cat;
            }

            set_transient('growtype_post_generate_image_last_entered_data', $_POST['custom_data'], 60 * 60 * 24);

            if (!empty($external_image_url)) {
                $urls = [
                    [
                        'url' => $external_image_url
                    ]
                ];
            } else {
                $urls = self::generate_image_urls($service, $featured_image_cat, $orientation, $featured_image_page);
            }

            wp_send_json_success($urls, 200);
        }
    }

    public static function generate_image_urls($service, $cat, $orientation = 'landscape', $page = 1)
    {
        $collected_urls = [];

        if ($service === 'unsplash') {
            $photos = self::get_images_from_external_service($service, $cat, $orientation, $page);
            $photos = $photos['results'] ?? [];

            foreach ($photos as $photo) {
                array_push($collected_urls, [
                    'url' => $photo['urls']['regular']
                ]);
            }
        } elseif ($service === 'pexels') {
            $photos = self::get_images_from_external_service($service, $cat, $orientation, $page);
            $photos = $photos['photos'] ?? [];

            foreach ($photos as $photo) {
                array_push($collected_urls, [
                    'url' => $photo['src']['large2x']
                ]);
            }
        } elseif ($service === 'pixabay') {
            $photos = self::get_images_from_external_service($service, $cat, $orientation, $page);
            $photos = $photos['hits'] ?? [];

            foreach ($photos as $photo) {
                array_push($collected_urls, [
                    'url' => $photo['largeImageURL']
                ]);
            }
        }

        return $collected_urls;
    }

    function custom_admin_footer_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('.gp-form[data-type="growtype-post-image"] .button-generate-image, .gp-form[data-type="growtype-post-image"] .button-get-image').click(function () {
                    let button = $(this);
                    let previewWrapper = button.closest('.gp-form').find('.featured-image-preview-wrapper');

                    let customData = {
                        'service_name': button.closest('.gp-form').find('.gp_g_img_service').val(),
                        'featured_image_cat': button.hasClass('button-generate-image') ? button.closest('.gp-form').find('input[name="growtype-post-featured-img-cat"]').val() : '',
                        'featured_image_page': button.hasClass('button-generate-image') ? button.closest('.gp-form').find('input[name="growtype-post-featured-img-page"]').val() : '',
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
                            previewWrapper.html('');

                            if (response.data.length) {
                                response.data.map(function (item) {
                                    button.closest('.gp-form').find('.featured-image-preview-wrapper').append('<div class="img-wrapper"><img src="' + item.url + '" style="max-width: 100%;"/></div>');

                                    $('input[name="growtype-post-featured-img-url"').val(item.url);
                                });
                            } else {
                                previewWrapper.html('<b>Unfortunately no images found</b>');
                            }

                            growtypePostInitImageTrigger();
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
                            response.data.map(function (item) {
                                growtypePostAdminShowNotice(item);

                                wp.data.dispatch('core/editor').editPost({
                                    featured_media: item['attachment_id']
                                });
                            });
                        },
                        error: function (response, status, error) {
                            growtypePostAdminShowNotice(response.data);
                        }
                    });
                });

                function growtypePostInitImageTrigger() {
                    $('.gp-form[data-type="growtype-post-image"] .img-wrapper').click(function () {
                        let src = $(this).find('img').attr('src');

                        $('.gp-form[data-type="growtype-post-image"] .img-wrapper').removeClass('is-active');

                        $(this).closest('.gp-form').find('input[name="growtype-post-featured-img-url"]').val(src);

                        $(this).addClass('is-active');
                    });
                }

                $('input[name="growtype-post-featured-img-cat"]').each(function () {
                    const originalPlaceholder = $(this).attr('placeholder');
                    $(this).data('original-placeholder', originalPlaceholder); // Save it using jQuery's data method
                });

                $('.gp_g_img_service').change(function () {
                    const selectedValue = $(this).val(); // Get the selected value

                    console.log(selectedValue, 'selectedValue');

                    const inputField = $(this).closest('.gp-form').find('input[name="growtype-post-featured-img-cat"]');

                    if (selectedValue === 'external_url') {
                        // Clear the input field and set a new placeholder
                        inputField.val('');
                        inputField.attr('placeholder', 'External url');

                        console.log('suveikel');
                    } else {
                        // Reset to the original placeholder
                        const originalPlaceholder = inputField.data('original-placeholder');
                        inputField.attr('placeholder', originalPlaceholder);
                    }
                });

                $('.gp-form').closest('form').on('keydown', function (event) {
                    if (event.key === 'Enter' || event.keyCode === 13) {
                        event.preventDefault(); // Prevent form submission
                        console.log('Form submission disabled on Enter key.');
                    }
                });
            });

        </script>
        <?php
    }
}
