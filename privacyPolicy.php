<?php
require_once __DIR__ . '/vendor/autoload.php';

$WEB_CLIENT_ID = "731126819470-jm53vv15n3nlnjpllpamji6obeqae7v1.apps.googleusercontent.com";
$client = new Google_Client(['client_id' => $WEB_CLIENT_ID]);  // Specify the WEB_CLIENT_ID of the app that accesses the backend
$payload = $client->verifyIdToken($id_token);
if ($payload) {

  // This ID is unique to each Google Account, making it suitable for use as a primary key
  // during account lookup. Email is not a good choice because it can be changed by the user.
  $userid = $payload['sub'];
  // If the request specified a Google Workspace domain
  //$domain = $payload['hd'];
  var_dump($payload);
} else {
  // Invalid ID token
}

?>

