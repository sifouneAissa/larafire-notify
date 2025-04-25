<?php

namespace Sifouneaissa\LarafireNotify\Helpers;

use Illuminate\Support\Facades\Log;

class FirebaseAuth
{
    public function base64UrlEncode($text)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    public function getAccessToken()
    {

        $firebaseCredentials = config("larafire-notify.FIREBASE_CREDENTIALS");
        // Read service account details
        $authConfigString = file_get_contents(base_path($firebaseCredentials));

        // Parse service account details
        $authConfig = json_decode($authConfigString);

        // Read private key from service account details
        $secret = openssl_get_privatekey($authConfig->private_key);

        // Create the token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'RS256'
        ]);

        // Get seconds since 1 January 1970
        $time = time();

        $payload = json_encode([
            "iss" => $authConfig->client_email,
            "scope" => "https://www.googleapis.com/auth/firebase.messaging",
            "aud" => "https://oauth2.googleapis.com/token",
            "exp" => $time + 3600,
            "iat" => $time
        ]);

        // Encode Header
        $base64UrlHeader = $this->base64UrlEncode($header);

        // Encode Payload
        $base64UrlPayload = $this->base64UrlEncode($payload);

        // Create Signature Hash
        $result = openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $secret, OPENSSL_ALGO_SHA256);

        // Encode Signature to Base64Url String
        $base64UrlSignature = $this->base64UrlEncode($signature);

        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        //-----Request token------
        $options = array('http' => array(
            'method'  => 'POST',
            'content' => 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=' . $jwt,
            'header'  =>
            "Content-Type: application/x-www-form-urlencoded"
        ));
        $context  = stream_context_create($options);
        $responseText = file_get_contents("https://oauth2.googleapis.com/token", false, $context);

        $response = json_decode($responseText, true);
        // "access_token": .....
        // "expires_in": 3599
        // "token_type": "Bearer"
        return $response;
    }


    public function addToOrCreateGroup($userGroup, $registration_ids, $notificationKey = null)
    {

        $response = $this->getAccessToken();
        // Your FCM server key
        $SENDER_ID = config('larafire-notify.sender_id');

        $ACCESS_TOKEN = $response['access_token'];

        // Data for the request
        $requestData = [
            'operation' => 'create',
            'notification_key_name' => $userGroup,
            'registration_ids' => $registration_ids
        ];

        // Convert the request data to JSON
        $jsonRequestData = json_encode($requestData);

        // Set up cURL
        $ch = curl_init('https://fcm.googleapis.com/fcm/notification');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequestData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token_auth: true',
            'Authorization: Bearer ' . $ACCESS_TOKEN,
            "project_id: $SENDER_ID",
        ]);

        // Execute the request
        $response = curl_exec($ch);

        $response = json_decode($response, true);
        // Close cURL session
        curl_close($ch);

        // Check for errors
        if (($response['error'] ?? null) === "notification_key already exists") {

            // if (!$notificationKey) {
                $notificationKey = $this->getNotificationKey($userGroup, $ACCESS_TOKEN);
                if (!$notificationKey) {
                    Log::error("['firebase'] - notification-key-not-found", [
                        'error' => [
                            'code' => 401,
                            'file' => get_class($this),
                            'line' => 131,
                            'message' => 'Notification key not found',
                        ],
                    ]);
                    return null;
                }
            // }

            return $this->addToGroup($userGroup, $registration_ids, $notificationKey, $ACCESS_TOKEN);
        } else {
            return $response['notification_key'] ?? null;
        }
    }

    public function addToGroup($userGroup, $registration_ids, $notificationKey, $accessToken = null)
    {
        if (!$accessToken) {
            $response = $this->getAccessToken();
            $ACCESS_TOKEN = $response['access_token'];
        } else {
            $ACCESS_TOKEN = $accessToken;
        }
        // Your FCM server key
        $SENDER_ID = config('larafire-notify.sender_id');
        // Data for the request
        $requestData = [
            'operation' => 'add',
            'notification_key_name' => $userGroup,
            'notification_key' => $notificationKey,
            'registration_ids' => $registration_ids
        ];

        // Convert the request data to JSON
        $jsonRequestData = json_encode($requestData);

        // Set up cURL
        $ch = curl_init('https://fcm.googleapis.com/fcm/notification');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequestData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token_auth: true',
            'Authorization: Bearer ' . $ACCESS_TOKEN,
            "project_id: $SENDER_ID",
        ]);

        // Execute the request
        $response = curl_exec($ch);

        $response = json_decode($response);
        // Close cURL session
        curl_close($ch);

        if (is_object($response) && property_exists($response, 'notification_key')) {
            return $response->notification_key;
        }

        return null;
    }


    public function deleteFromGroup($userGroup, $registration_ids, $notificationKey, $accessToken = null)
    {
        if (!$accessToken) {
            $response = $this->getAccessToken();
            $ACCESS_TOKEN = $response['access_token'];
        } else {
            $ACCESS_TOKEN = $accessToken;
        }
        // Your FCM server key
        $SENDER_ID = config('larafire-notify.sender_id');
        // Data for the request
        $requestData = [
            'operation' => 'remove',
            'notification_key_name' => $userGroup,
            'notification_key' => $notificationKey,
            'registration_ids' => $registration_ids
        ];

        // Convert the request data to JSON
        $jsonRequestData = json_encode($requestData);

        // Set up cURL
        $ch = curl_init('https://fcm.googleapis.com/fcm/notification');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequestData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'access_token_auth: true',
            'Authorization: Bearer ' . $ACCESS_TOKEN,
            "project_id: $SENDER_ID",
        ]);

        // Execute the request
        $response = curl_exec($ch);

        $response = json_decode($response, true);

        // Close cURL session
        curl_close($ch);

        return $response['notification_key'] ?? $notificationKey;
    }

    public function getNotificationKey($groupId, $accessToken = null)
    {

        if (!$accessToken) {
            $response = $this->getAccessToken();
            $accessToken = $response['access_token'];
        }
        // Your FCM server key
        $projectId = config('larafire-notify.sender_id');


        $url = "https://fcm.googleapis.com/fcm/notification?notification_key_name=$groupId";

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'project_id: ' . $projectId,
            'access_token_auth: true',
        ];

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options for GET request
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the cURL request
        $response = curl_exec($ch);

        // // Check for errors
        // if (curl_errno($ch)) {
        //     echo 'Error:' . curl_error($ch);
        // }

        // Close cURL session
        curl_close($ch);
        // Return response
        $arr = json_decode($response, true);

        return $arr['notification_key'] ?? null;
    }
}
