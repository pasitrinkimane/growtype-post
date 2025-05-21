<?php

class Growtype_Post_Admin_Methods_Meta_Content_Adjust
{
    public function __construct()
    {
        add_action('wp_ajax_growtype_post_admin_adjust_content', array ($this, 'growtype_post_admin_adjust_content_callback'));
    }

    function growtype_post_admin_adjust_content_callback()
    {
        $generate_response = self::generate($_POST);

        if ($generate_response['success'] === false) {
            wp_send_json_error($generate_response, 500);
        }

        wp_send_json_success($generate_response, 200);
    }

    public static function generate($custom_data)
    {
        $custom_text_prompt = $custom_data['custom_text_prompt'] ?? '';
        $image_cat = $custom_data['image_cat'] ?? '';
        $selected_blocks_data = $custom_data['selected_blocks_data'] ?? '';

        if (empty($selected_blocks_data)) {
            return [
                'success' => false,
                'message' => 'Selected blocks data is required'
            ];
        }

        $response_values = [];
        foreach ($selected_blocks_data as $block_key => $block) {
            if (!empty($custom_text_prompt)) {
                $prompt = sprintf('%s - "%s"', $custom_text_prompt, strip_tags($block['content']));

                $messages = Growtype_Post_Admin_Methods_Meta_Content::format_openai_messages([$prompt]);

                $generated_answer = Growtype_Post_Service_Openai::generate($messages);

                if ($generated_answer['success'] === false) {
                    return $generated_answer;
                }

                $generated_answer_content = $generated_answer['content'];

                foreach ($generated_answer_content as $answer) {
                    $content = [
                        'content' => Growtype_Post_Admin_Methods_Meta_Content::format_message_content($answer->message->content)
                    ];

                    if (isset($response_values[$block_key])) {
                        array_push($response_values[$block_key], $content);
                    } else {
                        $response_values[$block_key] = $content;
                    }
                }
            }

            if (!empty($image_cat)) {
                $service = 'pexels';

                $image_url = Growtype_Post_Admin_Methods_Meta_Image::generate_image_urls($service, $image_cat);
                $image_url = $image_url[array_rand($image_url)]['url'] ?? '';

                if (!empty($image_url)) {
                    $response_values[$block_key]['images'][] = $image_url;
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Content updated',
            'values' => $response_values,
        ];
    }
}
