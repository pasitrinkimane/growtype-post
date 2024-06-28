<?php

class Growtype_Post_Admin_Methods_Share_Pinterest
{
    public static function setup_pinterest()
    {
        $auth_code = get_user_meta(get_current_user_id(), 'growtype_post_pinterest_auth_code', true);

        $auth_url = 'https://www.pinterest.com/oauth/';
        $token_url = 'https://api.pinterest.com/v5/oauth/token';
        $client_id = '1498548';
        $client_secret = 'c25351ddafd26d8ffda990223e97a5b92e64e8b9';

        $redirect_uri = home_url() . '/' . Growtype_Post_Admin::pinterest_auth_redirect_path();

        $params = array (
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'scope' => 'boards:read boards:write pins:read pins:write',
            'state' => $_SERVER['REQUEST_URI'] ?? ''
        );

        $redirect_url = $auth_url . '?' . http_build_query($params);

        if (empty($auth_code)) {
            return [
                'success' => false,
                'message' => 'Redirecting to Pinterest for authorization',
                'redirectURL' => $redirect_url
            ];
        } else {
            $redirect_uri = home_url() . '/' . Growtype_Post_Admin::pinterest_auth_redirect_path();
            $authorization = base64_encode($client_id . ':' . $client_secret);

            $params = array (
                'grant_type' => 'authorization_code',
                'code' => $auth_code,
                'redirect_uri' => $redirect_uri
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $token_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                'Authorization: Basic ' . $authorization,
                'Content-Type: application/x-www-form-urlencoded'
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            curl_close($ch);

            $response = json_decode($response, true);

            $access_token = $response['access_token'] ?? '';

            if (!empty($access_token)) {
                update_user_meta(get_current_user_id(), 'growtype_post_pinterest_auth_access_token', $access_token);

                return $access_token;
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token',
                    'redirectURL' => $redirect_url
                ];
            }
        }
    }

    public static function post_to_pinterest($access_token, $pin_data)
    {
        $api_url = 'https://api.pinterest.com/v5/pins';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pin_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array (
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response, true);
    }
}
