<?php

/**
 * @return bool
 */
function growtype_post_is_front_post()
{
    return get_option('page_on_front') == growtype_post_get_id(get_post());
}

/**
 * @param $post
 * @return int|null
 */
function growtype_post_get_id($post)
{
    if (empty($post)) {
        return null;
    }

    $post_id = $post->ID;

    if (empty($post_id)) {
        $post_name = $post->post_name;
        if (!empty($post_name)) {
            $post = get_page_by_path($post_name);
            $post_id = $post->ID;
        }
    }

    return $post_id;
}

/**
 * @param $initial_content
 * @param int $length
 * @return string
 */
function growtype_post_get_limited_content($initial_content, $length = 125, $sentences_amount = null)
{
    if (empty($length)) {
        $length = apply_filters('growtype_post_limited_content_length', 125);
    }

    $content = __($initial_content);

    if (!empty($sentences_amount)) {
        $text_only = strip_tags($content);
        $sentence_pattern = '/(?<=[.!?])\s+/';
        $sentences = preg_split($sentence_pattern, $text_only, ((int)$sentences_amount + 1), PREG_SPLIT_NO_EMPTY);
        $content = implode(' ', array_slice($sentences, 0, (int)$sentences_amount));
    } elseif (strlen($initial_content) > $length) {
        $removed_content = str_replace(substr($content, 0, $length), '', $content);

        if (preg_match("/<[^<]+>/", $removed_content, $m) != 0) {
            $content = strip_shortcodes($content);
            $content = strip_tags($content);
        }

        $content = substr($content, 0, $length);
        $content = substr($content, 0, strripos($content, " "));
        $content = trim(preg_replace('/\s+/', ' ', $content));
        $content = $content . '...';
    }

    return $content;
}

/**
 * Include custom view
 */
if (!function_exists('growtype_post_include_view')) {
    function growtype_post_include_view($file_path, $args = array ())
    {
        if (empty($file_path)) {
            error_log('Growtype post - File path is empty');
            return '';
        }

        $fallback_view = GROWTYPE_POST_PATH . 'resources/views/' . str_replace('.', '/', $file_path) . '.php';
        $child_blade_view = get_stylesheet_directory() . '/views/' . GROWTYPE_POST_TEXT_DOMAIN . '/' . str_replace('.', '/', $file_path) . '.blade.php';
        $child_view = get_stylesheet_directory() . '/views/' . GROWTYPE_POST_TEXT_DOMAIN . '/' . str_replace('.', '/', $file_path) . '.php';

        $template_path = $fallback_view;

        if (file_exists($child_blade_view) && function_exists('App\template')) {
            return App\template($child_blade_view, $args);
        } elseif (file_exists($child_view)) {
            $template_path = $child_view;
        }

        if (str_contains($template_path, 'index') && !file_exists($template_path)) {
            $path_parts = explode('/', $template_path);
            $preview_type = !empty($path_parts) && isset(array_reverse($path_parts)[1]) ? array_reverse($path_parts)[1] : null;

            /**
             * Replace template with default 'basic' template, replace only plugin path part
             */
            if (!empty($preview_type)) {
                $template_path_split = explode('growtype-post', $template_path);
                $template_path_split_plugin_part = str_replace($preview_type, 'basic', $template_path_split[1]);
                $template_path = $template_path_split[0] . 'growtype-post' . $template_path_split_plugin_part;
            }
        }

        if (file_exists($template_path)) {
            extract($args);
            ob_start();
            include $template_path;
            $output = ob_get_clean();
        }

        return isset($output) ? $output : '';
    }
}

/**
 * Include custom view
 */
if (!function_exists('growtype_post_get_pagination_offset')) {
    function growtype_post_get_pagination_offset($posts_per_page)
    {
        $current_page = max(1, get_query_var('paged'));
        return $current_page === 1 ? 0 : ($current_page - 1) * $posts_per_page;
    }
}

/**
 * Display posts
 */
function growtype_post_render_all($the_query = null, $parameters = null)
{
    return Growtype_Post_Shortcode::render_all($the_query, $parameters);
}

/**
 * Display single post
 *
 * growtype_post_render_single('preview.basic.index', $post, [
 * 'post_classes' => 'growtype-post-single growtype-post-basic growtype-post-location'
 * ]);
 *
 */
if (!function_exists('growtype_post_render_single')) {
    function growtype_post_render_single($post, $parameters)
    {
        return Growtype_Post_Shortcode::render_single($post, $parameters);
    }
}

/**
 * @param $post
 * @param $size
 * @return mixed|null
 */
if (!function_exists('growtype_post_get_featured_image_url')) {
    function growtype_post_get_featured_image_url($post, $size = 'medium', $default_img_id = null)
    {
        $feat_img_url = isset(wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size)[0]) ? wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size)[0] : null;
        $feat_img_url = empty($feat_img_url) && !empty($default_img_id) ? wp_get_attachment_image_url($default_img_id) : $feat_img_url;

        return apply_filters('growtype_post_get_featured_image_url', $feat_img_url, $post, $size, $default_img_id);
    }
}

/**
 * mainly for ajax translations
 */
if (!function_exists('growtype_post_load_textdomain')) {
    function growtype_post_load_textdomain($lang)
    {
        load_textdomain('growtype-post', GROWTYPE_POST_PATH . 'languages/growtype-post-' . $lang . '_LT.mo');
    }
}


/**
 * mainly for ajax translations
 */
if (!function_exists('growtype_post_date_format')) {
    function growtype_post_date_format()
    {
        $default_date_format = get_option('date_format');
        $date_format = get_option('growtype_post_date_format', 'Y m d');

        return !empty($date_format) ? $date_format : $default_date_format;
    }
}

/**
 * Post reading time
 */
if (!function_exists('growtype_post_reading_time')) {
    function growtype_post_reading_time($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        $content = get_post_field('post_content', $post_id);
        $word_count = str_word_count(strip_tags($content));
        $reading_time = ceil($word_count / 200);

        if ($reading_time == 1) {
            $timer = " min";
        } else {
            $timer = " min";
        }

        return $reading_time . $timer . ' ' . __('read', 'growtype');
    }
}

/**
 * Post reading time
 */
if (!function_exists('growtype_post_get_ip_key')) {
    function growtype_post_get_ip_key()
    {
        $HTTP_X_FORWARDED_FOR = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
        $REMOTE_ADDR = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        return $HTTP_X_FORWARDED_FOR . $REMOTE_ADDR;
    }
}

/**
 * Post like
 */
if (!function_exists('growtype_post_like_post')) {
    function growtype_post_like_post($post_id, $dislike_enabled = true)
    {
        $ip_key = growtype_post_get_ip_key();
        $likes = growtype_post_get_post_likes($post_id);

        if (!empty($post_id)) {
            if (!in_array($ip_key, $likes)) {
                array_push($likes, $ip_key);
                update_post_meta($post_id, 'growtype_post_likes', $likes);
            } elseif ($dislike_enabled) {
                $likes = array_diff($likes, [$ip_key]);
                update_post_meta($post_id, 'growtype_post_likes', $likes);
            }
        }

        return $likes;
    }
}

/**
 * Post likes
 */
if (!function_exists('growtype_post_get_post_likes')) {
    function growtype_post_get_post_likes($post_id)
    {
        $likes = get_post_meta($post_id, 'growtype_post_likes', true);

        if (empty($likes)) {
            $likes = [];
        }

        return $likes;
    }
}

/**
 * Post excerpt
 */
if (!function_exists('growtype_post_get_excerpt')) {
    function growtype_post_get_excerpt($post_id, $intro_content_length = null)
    {
        $post = get_post($post_id);
        $excerpt = apply_filters('growtype_post_get_excerpt', $post->post_excerpt, $post_id);

        return growtype_post_get_limited_content($excerpt, $intro_content_length);
    }
}

/**
 * @param $post_id
 * @param $image_url
 * @param $check_if_exists
 * @return int|WP_Error
 */
function growtype_child_set_featured_image_from_url($post_id, $image_url, $check_if_exists = true)
{
    if (empty($image_url)) {
        return new WP_Error('invalid_image_url', 'Image URL is empty.');
    }

    $file_type = wp_check_filetype(basename($image_url), null);
    $post_mime_type = !isset($file_type['type']) || !$file_type['type'] ? 'image/jpeg' : $file_type['type'];
    $file_ext = !isset($file_type['ext']) || !$file_type['ext'] ? 'jpeg' : $file_type['ext'];
    $upload_dir = wp_upload_dir();
    $image_name = isset(parse_url($image_url)['path']) && !empty(parse_url($image_url)['path']) ? str_replace('/', '', parse_url($image_url)['path']) : wp_generate_password();
    $image_name_full = $image_name;

    if (strpos($image_name, '.' . $file_ext) === false) {
        $image_name_full = $image_name . '.' . $file_ext;
    }

    $upload_path = $upload_dir['path'] . '/' . $image_name_full;

    if ($check_if_exists) {
        $attachment_file = substr($upload_path, strlen(wp_upload_dir()['basedir']) + 1);

        if (file_exists($upload_path)) {
            $existing_attachment = get_posts(array (
                'post_type' => 'attachment',
                'posts_per_page' => 1,
                'post_status' => 'inherit',
                'meta_query' => array (
                    array (
                        'key' => '_wp_attached_file',
                        'value' => $attachment_file,
                        'compare' => '='
                    )
                )
            ));

            if (!empty($existing_attachment)) {
                $existing_attachment_id = $existing_attachment[0]->ID;
                set_post_thumbnail($post_id, $existing_attachment_id);
                return $existing_attachment_id;
            }
        }
    }

    $image_data = file_get_contents($image_url);

    file_put_contents($upload_path, $image_data);

    if (!$image_data || !$upload_path) {
        return new WP_Error('image_download_failed', 'Failed to download the image from the URL.');
    }

    list($width, $height) = getimagesize($upload_path);

    $attachment = array (
        'post_mime_type' => $post_mime_type,
        'post_title' => sanitize_file_name(pathinfo($upload_path, PATHINFO_FILENAME)),
        'post_content' => '',
        'post_status' => 'inherit',
        'width' => $width,
        'height' => $height,
    );

    $attach_id = wp_insert_attachment($attachment, $upload_path, $post_id);

    wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload_path));

    if (!is_wp_error($attach_id)) {
        set_post_thumbnail($post_id, $attach_id);
        return $attach_id;
    } else {
        return $attach_id;
    }
}
