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
function growtype_post_get_limited_content($initial_content, $length = 125)
{
    if (empty($length)) {
        $length = apply_filters('growtype_post_limited_content_length', 125);
    }

    $content = __($initial_content);

    if (strlen($initial_content) > $length) {

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
    function growtype_post_include_view($file_path, $variables = array ())
    {
        $fallback_view = GROWTYPE_POST_PATH . 'resources/views/' . str_replace('.', '/', $file_path) . '.php';
        $child_blade_view = get_stylesheet_directory() . '/views/' . GROWTYPE_POST_TEXT_DOMAIN . '/' . str_replace('.', '/', $file_path) . '.blade.php';
        $child_view = get_stylesheet_directory() . '/views/' . GROWTYPE_POST_TEXT_DOMAIN . '/' . str_replace('.', '/', $file_path) . '.php';

        $template_path = $fallback_view;

        if (file_exists($child_blade_view) && function_exists('App\template')) {
            return App\template($child_blade_view, $variables);
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
            extract($variables);
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
    function growtype_post_render_single($template_path, $post, $parameters)
    {
        return Growtype_Post_Shortcode::render_single($template_path, $post, $parameters);
    }
}

/**
 * @param $post
 * @param $size
 * @return mixed|null
 */
function growtype_post_get_featured_image_url($post, $size = 'medium', $default_img_id = null)
{
    $feat_img_url = isset(wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size)[0]) ? wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size)[0] : null;
    $feat_img_url = empty($feat_img_url) && !empty($default_img_id) ? wp_get_attachment_image_url($default_img_id) : $feat_img_url;

    return $feat_img_url;
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
        return get_option('growtype_post_date_format', 'Y m d');
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
