<?php

class Growtype_Post_Admin_Methods_Meta_Share
{
    const REDDIT = 'reddit';
    const BLOGGER = 'blogger';
    const MEDIUM = 'medium';
    const PINTEREST = 'pinterest';
    const TWITTER = 'twitter';
    const THREADS = 'threads';
    const TUMBLR = 'tumblr';

    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            add_action('current_screen', function ($screen) {
                if ($screen->base === 'post') {
                    add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));
                    add_action('add_meta_boxes', array ($this, 'growtype_post_settings_meta_box'));
                    add_action('admin_enqueue_scripts', array ($this, 'enqueue_chosen_admin_assets'));
                }
            });

            add_action('wp_ajax_growtype_post_admin_shared_on', array ($this, 'growtype_post_admin_shared_on_callback'));
            add_action('wp_ajax_growtype_post_admin_share_on', array ($this, 'growtype_post_admin_share_on_callback'));
        }
    }

    function enqueue_chosen_admin_assets($hook_suffix)
    {
        // Enqueue Chosen CSS
        wp_enqueue_style(
            'chosen-css',
            'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css',
            [],
            '1.8.7'
        );

        // Enqueue Chosen JS
        wp_enqueue_script(
            'chosen-js',
            'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js',
            ['jquery'],
            '1.8.7',
            true
        );
    }

    public static function get_platforms()
    {
        $available_platforms = class_exists('Growtype_Auth') ? [
            self::REDDIT => [
                'label' => self::REDDIT,
                'icon' => GROWTYPE_POST_URL_PUBLIC . '/icons/reddit.svg',
                'growtype_auth_values_key' => 'subreddits',
            ],
            self::BLOGGER => [
                'label' => self::BLOGGER,
                'icon' => GROWTYPE_POST_URL_PUBLIC . '/icons/blogger.svg',
                'growtype_auth_service_key' => Growtype_Auth::SERVICE_GOOGLE,
                'growtype_auth_values_key' => 'blogger_blogs_ids',
            ],
            self::MEDIUM => [
                'label' => self::MEDIUM,
                'icon' => GROWTYPE_POST_URL_PUBLIC . '/icons/medium.svg'
            ],
            self::TWITTER => [
                'label' => 'x',
                'icon' => GROWTYPE_POST_URL_PUBLIC . '/icons/twitter.svg',
//                'growtype_auth_values_key' => 'account_id',
            ],
            self::TUMBLR => [
                'label' => self::TUMBLR,
                'icon' => GROWTYPE_POST_URL_PUBLIC . '/icons/tumblr.svg',
                'growtype_auth_values_key' => 'blog_name',
            ],
            self::THREADS => [
                'label' => self::THREADS,
                'icon' => GROWTYPE_POST_URL_PUBLIC . '/icons/threads.svg',
                'growtype_auth_values_key' => 'available_users',
            ]
        ] : [];

        return apply_filters('growtype_post_admin_methods_meta_share_platforms', $available_platforms);
    }

    public static function get_platform_accounts($platform)
    {
        return Growtype_Auth::credentials($platform);
    }

    function growtype_post_settings_meta_box()
    {
        add_meta_box(
            'growtype-post-settings-meta-box',
            __('Growtype Post - Share', 'growtype-post'),
            array ($this, 'growtype_post_settings_meta_box_callback'),
            'post',
            'normal',
            'default'
        );
    }

    function growtype_post_settings_meta_box_callback($post)
    {
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

            .gp-form .gp-form-fields {
                padding-top: 10px;
                display: flex;
                /*align-items: center;*/
                gap: 5px;
            }
        </style>
        <?php
        $platforms = self::get_platforms();
        $user_entered_latest_generate_content_data = Growtype_Post_Admin_Methods_Meta::get_user_entered_latest_generate_content_data();
        $last_platforms = $user_entered_latest_generate_content_data['last_platforms'] ?? [];
        $last_platforms = isset($last_platforms) && is_array($last_platforms) ? $last_platforms : [];
        $cta_url = $user_entered_latest_generate_content_data['cta_url'] ?? '';
        $cta_url = !empty($cta_url) ? $cta_url : home_url() . str_replace(' ', '-', strtolower(get_the_title()));
        ?>

        <div class="gp-form" data-type="growtype-post-share-on">
            <div class="gp-form-fields-wrapper">
                <h3 style="margin:0;">Share on platforms:</h3>
                <div class="gp-form-fields">
                    <div class="gp-form-fields-group">
                        <div class="gp-form-fields-group-header">
                            <input type="checkbox" id="select-all">
                            <label for="select-all">Select All</label>
                        </div>
                    </div>
                    <?php
                    foreach ($platforms as $platform_key => $platform) {
                        echo '<div class="gp-form-fields-group">';

                        echo '<div class="gp-form-fields-group-header">';
                        echo (isset($platform['icon']) && !empty($platform['icon'])
                                ? '<img width="20" src="' . htmlspecialchars($platform['icon']) . '" alt="' . htmlspecialchars($platform['label']) . '"/> '
                                : '') . htmlspecialchars(ucfirst($platform['label']));
                        echo '</div>';

                        echo '<div class="inputs-grouped">';

                        $service_key = $platform['growtype_auth_service_key'] ?? $platform_key;

                        if (!empty($service_key)) {
                            $platform_accounts = self::get_platform_accounts($service_key);

                            if (!empty($platform_accounts)) {
                                foreach ($platform_accounts as $platform_account_key => $platform_account) {
                                    $values_key = $platform['growtype_auth_values_key'] ?? '';
                                    $available_values = $platform_account[$values_key] ?? [];
                                    $available_values = is_string($available_values) ? explode(',', $available_values) : [];
                                    $main_key = htmlspecialchars($platform_key) . '_' . htmlspecialchars($values_key) . '_' . htmlspecialchars($platform_account_key);
                                    $checkbox_id = 'growtype_post_share_on_platforms_checkbox_' . $main_key;
                                    $select_id = 'growtype_post_share_on_platforms_select_' . $main_key;

                                    echo '<div class="group-wrapper">';

                                    echo '<div class="group-wrapper-input">';
                                    $checked = in_array($platform_account_key, $last_platforms) ? 'checked' : ''; // Individual check
                                    echo '<input type="checkbox" id="' . $checkbox_id . '" class="platform-checkbox" name="growtype_post_share_on_platforms[' . htmlspecialchars($platform_key) . '][' . htmlspecialchars($values_key) . '][]" value="' . htmlspecialchars($platform_account_key) . '" ' . $checked . '>';
                                    echo '<label for="' . $checkbox_id . '">' . htmlspecialchars($platform_account_key) . '</label>';
                                    echo '</div>';

                                    if (!empty($available_values)) {
                                        echo '<div class="auth-settings">';
                                        echo '<select class="chosen-select" multiple name="growtype_post_share_on_accounts[' . htmlspecialchars($platform_key) . '][' . htmlspecialchars($values_key) . '][' . htmlspecialchars($platform_account_key) . ']" id="' . $select_id . '">';
                                        foreach ($available_values as $available_value) {
                                            echo '<option value="' . htmlspecialchars($available_value) . '">' . htmlspecialchars($available_value) . '</option>';
                                        }
                                        echo '</select>';
                                        echo '</div>';
                                    }

                                    echo '</div>';
                                }
                            }
                        }

                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="gp-form-fields">
                <label for="cta-url">Main CTA url</label>
                <input type="text" id="cta-url" name="cta_url" value="<?= htmlspecialchars($cta_url ?? '') ?>">
            </div>
            <div class="b-actions">
                <button class="button button-primary button-submit">Share</button>
            </div>
        </div>

        <div class="gp-form" data-type="growtype-post-shared-on">
            <div class="gp-form-fields-wrapper">
                <h3 style="margin:0;">Already shared on:</h3>
                <div class="gp-form-fields">
                    <?php
                    foreach ($platforms as $platform_key => $platform) {
                        echo '<div class="gp-form-fields-group">';

                        echo '<div class="gp-form-fields-group-header">';
                        echo (isset($platform['icon']) && !empty($platform['icon'])
                                ? '<img width="20" src="' . htmlspecialchars($platform['icon']) . '" alt="' . htmlspecialchars($platform['label']) . '"/> '
                                : '') . htmlspecialchars(ucfirst($platform['label']));
                        echo '</div>';

                        echo '<div class="inputs-grouped">';

                        $service_key = $platform['growtype_auth_service_key'] ?? $platform_key;

                        if (!empty($service_key)) {
                            $platform_accounts = self::get_platform_accounts($service_key);

                            if (!empty($platform_accounts)) {
                                foreach ($platform_accounts as $platform_account_key => $platform_account) {
                                    $values_key = $platform['growtype_auth_values_key'] ?? '';
                                    $available_values = $platform_account[$values_key] ?? [];
                                    $available_values = is_string($available_values) ? explode(',', $available_values) : [];
                                    $main_key = htmlspecialchars($platform_key) . '_' . htmlspecialchars($values_key) . '_' . htmlspecialchars($platform_account_key);
                                    $checkbox_id = 'platform-checkbox-' . $main_key;
                                    $select_id = 'platform-select-' . $main_key;

                                    echo '<div class="group-wrapper">';

                                    echo '<div class="group-wrapper-input">';
                                    if (empty($available_values)) {
                                        $is_checked = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post->ID, $platform_key, $platform_account_key, $values_key);
                                        echo '<input type="checkbox" id="' . $checkbox_id . '" class="platform-checkbox" name="growtype_post_is_already_shared_on_platforms[' . htmlspecialchars($platform_key) . '][' . htmlspecialchars($values_key) . '][]" value="' . htmlspecialchars($platform_account_key) . '" ' . ($is_checked ? 'checked' : '') . '>';
                                    }
                                    echo '<label for="' . $checkbox_id . '">' . htmlspecialchars($platform_account_key) . '</label>';
                                    echo '</div>';

                                    if (!empty($available_values)) {
                                        echo '<div class="group-wrapper">';
                                        foreach ($available_values as $available_value) {
                                            $id = htmlspecialchars($platform_key) . '_' . htmlspecialchars($values_key) . '_' . htmlspecialchars($platform_account_key) . '_' . $available_value;
                                            $is_checked = Growtype_Post_Admin_Methods_Share::check_if_post_is_already_shared_on_platform($post->ID, $platform_key, $platform_account_key, $available_value);
                                            $url = Growtype_Post_Admin_Methods_Share::get_shared_post_external_url_for_platform($post->ID, $platform_key, $platform_account_key, $available_value);
                                            $id = 'growtype_post_is_already_shared_on_platforms_' . $id;
                                            echo '<div class="group-wrapper-input">';
                                            echo '<input type="checkbox" id="' . $id . '" class="platform-checkbox-shared" value="' . $available_value . '" name="growtype_post_is_already_shared_on_platforms[' . htmlspecialchars($platform_key) . '][' . htmlspecialchars($platform_account_key) . '][' . htmlspecialchars($available_value) . ']" ' . ($is_checked ? 'checked' : '') . '><label for="' . $id . '">' . $available_value . '</label>';

                                            if (!empty($url)) {
                                                echo sprintf('<a href="%s" target="_blank">Preview url</a>', $url);
                                            }

                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }

                                    echo '</div>';
                                }
                            }
                        }

                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <div class="b-actions">
                <button class="button button-primary button-submit">Save</button>
            </div>
        </div>
        <?php
    }

    public static function already_shared_on_platforms($post_id)
    {
        $already_shared_on_platforms = get_post_meta($post_id, 'growtype_post_is_already_shared_on_platforms', true);
        $already_shared_on_platforms = !empty($already_shared_on_platforms) ? $already_shared_on_platforms : [];

        return $already_shared_on_platforms;
    }

    function growtype_post_admin_share_on_callback()
    {
        $share_data = isset($_POST['share_data']) && !empty($_POST['share_data']) ? $_POST['share_data'] : [];

        if (!empty($share_data)) {
            $post_id = intval($_POST['post_id']);
            $share_data['post_id'] = $post_id;
            $share_on_platforms = $share_data['growtype_post_share_on_platforms'] ?? [];
            $share_on_accounts = $share_data['growtype_post_share_on_accounts'] ?? [];

            Growtype_Post_Admin_Methods_Meta::update_user_entered_latest_generate_content_data($share_data);

            $valid_platforms = [];
            foreach ($share_on_platforms as $platform_service_key => $platform) {
                foreach ($platform as $platform_account_key => $platform_accounts) {
                    if (is_array($platform_accounts)) {
                        foreach ($platform_accounts as $platform_account) {
                            if (!empty($platform_account)) {
                                $values = $share_on_accounts[$platform_service_key][$platform_account_key][$platform_account] ?? [];
                                if (!empty($values)) {
                                    $valid_platforms[$platform_service_key][$platform_account] = $values;
                                }
                            }
                        }
                    } else {
                        if (!empty($platform_accounts)) {
                            $valid_platforms[$platform_service_key][$platform_accounts][] = [$platform_accounts];
                        }
                    }
                }
            }

            if (empty($valid_platforms)) {
                wp_send_json_error([
                    'message' => 'No platforms selected.',
                ], 500);
            }

            $responses = [];
            $shared_on_all_platforms = [];
            foreach ($valid_platforms as $platform => $account_details) {
                $submit_details = Growtype_Post_Admin_Methods_Share::submit($platform, $account_details, $post_id);

                $response['platform'] = $platform;
                $response['submit_details'] = $submit_details;

                $responses[] = $response;
                $shared_on_all_platforms[$platform] = false;
            }

            wp_send_json_success($responses);
        } else {
            wp_send_json_error('Invalid data.');
        }
    }

    function growtype_post_admin_shared_on_callback()
    {
        $share_data = isset($_POST['share_data']) && !empty($_POST['share_data']) ? $_POST['share_data'] : [];

        if (!empty($share_data)) {
            $post_id = intval($_POST['post_id']);
            $shared_on_platforms = $share_data['growtype_post_is_already_shared_on_platforms'];

            $valid_platforms = [];
            foreach ($shared_on_platforms as $platform_service_key => $platform) {
                foreach ($platform as $platform_account_key => $platform_accounts) {
                    if (is_array($platform_accounts)) {
                        foreach ($platform_accounts as $platform_account) {
                            if (!empty($platform_account)) {
                                $valid_platforms[$platform_service_key][$platform_account_key][] = $platform_account;
                            }
                        }
                    } else {
                        if (!empty($platform_accounts)) {
                            $valid_platforms[$platform_service_key][$platform_accounts][] = $platform_accounts;
                        }
                    }
                }
            }

            Growtype_Post_Admin_Methods_Share::update_already_shared_on_platforms_details($post_id, $valid_platforms, true);

            wp_send_json_success([
                'message' => 'Data successfully updated'
            ]);
        } else {
            wp_send_json_error('Invalid data.');
        }
    }

    function custom_admin_footer_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                // Initialize Chosen Select Dropdown
                $(".chosen-select").chosen({
                    disable_search_threshold: 10, // Hide search bar if fewer than 10 options
                    no_results_text: "No matches found!", // Message for no results
                    width: "100%" // Set dropdown width
                });

                /**
                 * Parse form inputs into a structured object
                 * @param {Object} form - The form element to parse
                 * @returns {Object} - Structured object containing form data
                 */
                function parseFormInputs(form) {
                    const customData = {};
                    form.find('input, select').each(function () {
                        const name = $(this).attr('name');
                        if (!name) return;

                        let value;

                        if ($(this).is(':checkbox')) {
                            // Handle checkboxes
                            value = null;

                            if ($(this).is(':checked')) {
                                value = $(this).val();
                            }

                            if (name.endsWith('[]')) {
                                growtypePostParseNestedKeys(customData, name.slice(0, -2), value, true);
                            } else {
                                growtypePostParseNestedKeys(customData, name, value);
                            }
                        } else if ($(this).is('select[multiple]') && $(this).hasClass('chosen-select')) {
                            // Handle multiple select with Chosen
                            value = $(this).val() || [];
                            growtypePostParseNestedKeys(customData, name, value, true);
                        } else {
                            // Handle other input types
                            value = $(this).val();
                            if (name.endsWith('[]')) {
                                growtypePostParseNestedKeys(customData, name.slice(0, -2), value, true);
                            } else {
                                growtypePostParseNestedKeys(customData, name, value);
                            }
                        }
                    });
                    return customData;
                }

                /**
                 * Parse nested input names into a structured object
                 * @param {Object} obj - The target object to populate
                 * @param {String} name - The name attribute (e.g., "settings[option][value]")
                 * @param {Any} value - The value to assign
                 * @param {Boolean} isArray - Whether the value should be stored in an array
                 */
                function growtypePostParseNestedKeys(obj, name, value, isArray = false) {
                    const keys = name.split('[').map(key => key.replace(/\]$/, ''));
                    let current = obj;

                    keys.forEach((key, index) => {
                        if (index === keys.length - 1) {
                            // Assign value to the final key
                            if (isArray) {
                                current[key] = current[key] || [];
                                current[key].push(value);
                            } else {
                                current[key] = value;
                            }
                        } else {
                            // Create nested objects for intermediate keys
                            current[key] = current[key] || {};
                            current = current[key];
                        }
                    });
                }

                /**
                 * Toggle input group visibility based on selected checkboxes
                 */
                function toggleInputGroup() {
                    const selectedValues = $('input[name="growtype_post_share_on_platforms[]"]:checked')
                        .map(function () {
                            return $(this).val();
                        })
                        .get();

                    if (selectedValues.includes('threads')) {
                        $('select[name="username"]').parent().show();
                    } else {
                        $('select[name="username"]').parent().hide();
                    }
                }

                /**
                 * Submit form and handle response
                 * @param {Object} form - The form element
                 * @param {String} action - The AJAX action
                 * @param {String} postId - The post ID
                 */
                function handleSubmit(form, action, postId) {
                    const customData = parseFormInputs(form);

                    console.log(customData, 'customData');

                    growtypePostAdminFormShowLoader(form, false);

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: action,
                            share_data: customData,
                            post_id: postId
                        },
                        success: function (response) {
                            console.log(response, 'success');

                            if (response.success) {
                                if (response.data.message) {
                                    growtypePostAdminRenderNotice(response.data, true, true);

                                    console.log('suveike sitas')
                                }

                                if (Array.isArray(response.data)) {
                                    response.data.forEach(function (item) {
                                        item.submit_details?.forEach(function (details) {
                                            growtypePostAdminRenderNotice(details);

                                            if (details.success) {
                                                const platform = item.platform.toLowerCase();
                                                let inputName;

                                                if (details.auth_group_key && details.account_channel) {
                                                    inputName = `growtype_post_is_already_shared_on_platforms[${platform}][${details.auth_group_key}][${details.account_channel}]`;
                                                    $(`input[name="${inputName}"]`).prop('checked', true);
                                                } else {
                                                    inputName = `growtype_post_is_already_shared_on_platforms[${platform}][][]`;
                                                    $(`input[name="${inputName}"][value="${details.auth_group_key}"]`).prop('checked', true);
                                                }

                                                console.log(inputName)
                                                console.log($(`input[name="${inputName}"]`))
                                            }
                                        });
                                    });
                                }
                            }

                            growtypePostAdminFormHideLoader(form);
                        },
                        error: function (response) {
                            console.error(response.responseJSON?.data || 'An error occurred.', 'error');
                            growtypePostAdminRenderNotice(response.responseJSON?.data || 'An error occurred.', false);
                            growtypePostAdminFormHideLoader(form);
                        }
                    });
                }

                // Attach event handlers
                $('.gp-form[data-type="growtype-post-shared-on"] .button-submit').click(function () {
                    const form = $(this).closest('.gp-form');
                    const postId = $('#post_ID').val();
                    handleSubmit(form, 'growtype_post_admin_shared_on', postId);
                });

                $('.gp-form[data-type="growtype-post-share-on"] .button-submit').click(function () {
                    const form = $(this).closest('.gp-form');
                    const postId = $('#post_ID').val();
                    handleSubmit(form, 'growtype_post_admin_share_on', postId);
                });

                // Initialize toggles on page load and attach change event handlers
                toggleInputGroup();
                $('input[name="growtype_post_share_on_platforms[]"]').on('change', toggleInputGroup);
            });
        </script>
        <?php
    }
}
