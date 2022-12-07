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
    $post_id = $post->ID ?? null;

    if (empty($post_id)) {
        $post_name = $post->post_name ?? null;
        $post = get_page_by_path($post_name);
        $post_id = $post->ID ?? null;
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

    $content = $initial_content;

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
 * Display posts
 */
function growtype_post_render_all($posts, $parameters = null)
{
    return Growtype_Post_Shortcode::render_all($posts, $parameters);
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
function growtype_post_get_featured_image_url($post, $size = 'medium')
{
    return isset(wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size)[0]) ? wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size)[0] : null;
}

/**
 * @param $array
 * @param $key
 * @return array
 */
function growtype_post_array_pluck($array, $key)
{
    return array_map(function ($v) use ($key) {
        return is_object($v) ? $v->$key : $v[$key];
    }, $array);
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
