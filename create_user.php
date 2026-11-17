<?php

$grupo_web_address = 'https://chat.yuvabharathierp.org'; 

$post_fields=[
  'api_secret_key' => '9fJAnkiFC3DW',
  'add' => 'site_users',
  'full_name' => 'test parent',
  'username' => 'testparent',
  'email_address' => 'parentuser2@email.com',
  'password' => 'password',
  'avatarURL' => 'https://avatar_image_url',
  'site_role' => '3',
  'custom_field_1' => 'About Me',
];

$api_request_url = rtrim($grupo_web_address, '/').'/'.'api_request/';

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $api_request_url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $post_fields,
  CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0'
));


$response = curl_exec($curl);

curl_close($curl);

if (!empty($response)) {
    $response = json_decode($response);
    if (!empty($response)) {
        if ($response->success) {
            echo "User Account Created";
        } else {
            echo $response->error_message;
        }
    }
}
?>