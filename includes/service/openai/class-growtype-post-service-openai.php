<?php

class Growtype_Post_Service_Openai
{
    const DEFAULT_MODEL_SETTINGS = [
        'model' => 'gpt-3.5-turbo-16k', // Larger context for detailed articles
        'temp' => 0.65, // Balanced creativity with factual reliability
        'top_p' => 0.95, // Broader exploration of potential responses
        'freq_penalty' => 0.2, // Stronger discouragement of repetitive phrases
        'presence_penalty' => 0.5, // Encourages unique and fresh perspectives
        'n' => 1, // Single output for clarity and focus
    ];

    public static function api_key()
    {
        $credentials = Growtype_Auth::credentials(Growtype_Auth::SERVICE_OPENAI);
        return $credentials['growtype_post']['api_key'];
    }

    public static function generate($messages, $params = [])
    {
        if (empty(self::api_key())) {
            wp_send_json_error([
                'message' => 'OpenAI Api Key is missing. Please add it to Growtype_Auth'
            ], 500);
        }

        $params = array_merge([
            "model" => self::DEFAULT_MODEL_SETTINGS['model'],
            "temperature" => self::DEFAULT_MODEL_SETTINGS['temp'],
            "top_p" => self::DEFAULT_MODEL_SETTINGS['top_p'],
            "frequency_penalty" => self::DEFAULT_MODEL_SETTINGS['freq_penalty'],
            "presence_penalty" => self::DEFAULT_MODEL_SETTINGS['presence_penalty'],
            "n" => 1,
        ], $params);

        $params['messages'] = $messages;

        $json_str = json_encode($params);

        $endpoint = 'v1/chat/completions';

        $url = 'https://api.openai.com/' . $endpoint;

        $args = array (
            'timeout' => 500,
            'redirection' => 10,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array (
                'Authorization' => 'Bearer ' . self::api_key(),
                'Content-Type' => 'application/json'
            ),
            'body' => $json_str,
            'cookies' => array ()
        );

        $response = wp_remote_post($url, $args);

        $resArr = json_decode(wp_remote_retrieve_body($response), true);

        $content = $resArr['choices'] ?? [];

        $error = $resArr['error'] ?? [];
        $error_message = isset($error['message']) ? $error['message'] : '';

        return [
            'success' => !empty($error_message) ? false : true,
            'content' => $content,
            'message' => !empty($error_message) ? $error_message : ''
        ];
    }
}
