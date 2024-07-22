<?php

class Growtype_Post_Admin_Methods_Share_Treads
{
    public static function submit($post_details)
    {
        try {
            $clientId = '795241906065268';
            $redirectUri = 'https://talkiemate.com/'; // Ensure this matches your app settings
            $scope = 'threads_basic,threads_content_publish';

            $authorizationUrl = "https://threads.net/oauth/authorize?client_id=$clientId&redirect_uri=$redirectUri&scope=$scope&response_type=code";

            d($authorizationUrl);

// Redirect the user to the authorization URL
            header("Location: $authorizationUrl");
            exit;

            ////


            // Step 1: Define your app ID and app secret
            $appId = '372894732572714';
            $appSecret = '1838964c75e280f729ff7667a94de207';

// Step 2: Get the access token
            $accessTokenUrl = "https://graph.facebook.com/oauth/access_token";
            $accessTokenParams = [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'grant_type' => 'client_credentials'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $accessTokenUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($accessTokenParams));
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
                exit;
            }

            $responseData = json_decode($response, true);

            $accessToken = $responseData['access_token'];

            curl_close($ch);

            var_dump($accessToken);


            // Define your access token
//            $accessToken = 'YOUR_ACCESS_TOKEN';

// Construct the API endpoint URL
            $url = "https://graph.threads.net/v1.0/me?fields=id,username,threads_profile_picture_url,threads_biography&access_token=$accessToken";

// Initialize cURL
            $ch = curl_init();

// Set the cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the GET request
            $response = curl_exec($ch);

//            d($response);

// Check for errors
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                // Decode the response
                $responseData = json_decode($response, true);

                // Check if the user information is returned
                if (isset($responseData['id'])) {
                    $userId = $responseData['id'];
                    $username = $responseData['username'];
                    $profilePictureUrl = $responseData['threads_profile_picture_url'];
                    $biography = $responseData['threads_biography'];

                    // Output the user information
                    echo 'User ID: ' . $userId . PHP_EOL;
                    echo 'Username: ' . $username . PHP_EOL;
                    echo 'Profile Picture URL: ' . $profilePictureUrl . PHP_EOL;
                    echo 'Biography: ' . $biography . PHP_EOL;
                } else {
                    echo 'Error: Unable to get user information';
                }
            }

// Close cURL
            curl_close($ch);


            d($response);

            ///


            // Define your variables
            $threadsUserId = '@avasummersmate';
            $imageUrl = 'https://www.example.com/images/bronz-fonz.jpg';
            $text = '#BronzFonz';

// Construct the API endpoint URL
            $url = "https://graph.threads.net/v1.0/$threadsUserId/threads";

// Prepare the data to be sent in the POST request
            $data = [
                'media_type' => 'IMAGE',
                'image_url' => $imageUrl,
                'text' => $text,
                'access_token' => $accessToken
            ];

// Initialize cURL
            $ch = curl_init();

// Set the cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);

// Execute the POST request
            $response = curl_exec($ch);

// Check for errors
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                echo 'Response:' . $response;
            }

// Close cURL
            curl_close($ch);

            d($response);

            return [
                'success' => 'Posted successfully'
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
