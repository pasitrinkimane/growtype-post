<?php

class Growtype_Post_Admin_Methods_Share_Twitter
{
    public static function submit($post_details)
    {
        try {
            $media_info = Growtype_Auth_Twitter::client()->uploadMedia()->upload($post_details['image']);
            Growtype_Auth_Twitter::client()->tweet()->create()
                ->performRequest([
                    'text' => $post_details['body'],
                    "media" => [
                        "media_ids" => [
                            (string)$media_info["media_id"]
                        ]
                    ]
                ]);

            return [
                'success' => 'Tweet posted successfully'
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
