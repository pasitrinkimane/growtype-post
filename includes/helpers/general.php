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
 * @param $path
 * @param null $data
 * @return mixed
 * Include view
 */
if (!function_exists('growtype_post_include_view')) {
    function growtype_post_include_view($file_path, $variables = array (), $print = false)
    {
        $output = null;

        $fallback_view = GROWTYPE_POST_PATH . 'resources/views/' . str_replace('.', '/', $file_path) . '.php';
        $child_blade_view = get_stylesheet_directory() . '/growtype-post/' . str_replace('.', '/', $file_path) . '.blade.php';
        $child_view = get_stylesheet_directory() . '/growtype-post/' . str_replace('.', '/', $file_path) . '.php';

        $template_path = $fallback_view;

        if (file_exists($child_blade_view)) {
            return App\template($child_blade_view, $variables);
        } elseif (file_exists($child_view)) {
            $template_path = $child_view;
        }

        if (file_exists($template_path)) {
            // Extract the variables to a local namespace
            extract($variables);

            // Start output buffering
            ob_start();

            // Include the template file
            include $template_path;

            // End buffering and return its contents
            $output = ob_get_clean();
        }

        if ($print) {
            print $output;
        }

        return $output;
    }
}

/**
 * Display posts
 */
function growtype_post_render_all($posts, $preview_style, $columns, $post_link = true, $parent_class = '', $slider = false, $parent_id = '', $pagination = null)
{
    return Growtype_Post_Shortcode::render_all($posts, $preview_style, $columns, $post_link, $parent_class, $slider);
}

/**
 * Display post
 */
function growtype_post_render_single($template_path, $post, $post_link = true, $post_classes = '')
{
    return Growtype_Post_Shortcode::render_single($template_path, $post, $post_link, $post_classes);
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
