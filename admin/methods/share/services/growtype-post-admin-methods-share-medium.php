<?php

class Growtype_Post_Admin_Methods_Share_Medium
{
    public static function submit($post_details)
    {
        $medium_token = get_option('growtype_post_medium_token');

        $author_id = self::get_author_id($medium_token);

        $title = $post_details['title'];
        $body = $post_details['body'];
        $subtitle = $post_details['subtitle'];
        $canonicalUrl = $post_details['canonicalUrl'];

        $url = "https://api.medium.com/v1/users/{$author_id}/posts";

        $headers = array (
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Bearer " . $medium_token, // Replace YOUR_ACCESS_TOKEN with your actual access token
        );

        $data = array (
            "title" => $title,
            "contentFormat" => "html",
            "content" => $body,
            "canonicalUrl" => $canonicalUrl,
            "publishStatus" => "public",
            "authorId" => $author_id,
            "tags" => $post_details['tags'],
        );

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }

    public static function get_author_id($token)
    {
        $url = "https://api.medium.com/v1/me";

        $headers = array (
            "Authorization: Bearer " . $token,
        );

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        if ($response === false) {
            echo 'Curl error: ' . curl_error($curl);
            return null;
        }

        curl_close($curl);

        $data = json_decode($response, true);

        if (isset($data['data']['id'])) {
            return $data['data']['id'];
        }

        return null;
    }
}
