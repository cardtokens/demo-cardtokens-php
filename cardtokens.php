<?php
//
// Initialize the autoload
//
require 'vendor/autoload.php';

//
// Use GuzzleHttp for communicating to Cardtokens
//
use GuzzleHttp\Client;

//
// Constants
//

//
// The endpoint to Cardtokens
//
define('BASE_URL', 'https://api.cardtokens.io');

//
// The API key used to communicate towards Cardtokens
//
define('API_KEY', '95f734793a424ea4ae8d9dc0b8c1a4d7');

//
// The merchantid used to generate the token against
//
define('MERCHANTID', '523ca9d5eb9d4ce0a60b2a3f5eb3119d');

//
// The public key used to encrypt the card object when creating tokens
//
define('PEMPUBLICKEY', 'LS0tLS1CRUdJTiBSU0EgUFVCTElDIEtFWS0tLS0tDQpNSUlDQ2dLQ0FnRUExbXN0TVByRlJWZDhUTUdyWTMyNDJwcTQ2aFlFMFBieXcrTnB0MnRDSjBpRHkrWkxQWWJGDQoydVhOSDVQT2d4aTN0NUhVNjYxVVNBOFg5enk3aklPMDlpOGxRMkdoN1dpejlqZXpFVDBpVmNvUGovSFFrV1N1DQorMDljREhSTmpQMmhpa0hZQTA5SVlzTm96ajd4dHYwTnJxbjZacWZ5amhOS1NrN2RUeUVVQ0xoaEwvTUVFRTZ0DQpBRERVUllvS0hVcWtWaXRwWXNwTUdqaUo2QUFJWVVlYU1DdkZ2cnhaSkFNSW5FbnY3THNhTHVBV21pdzRrOXM5DQozTHUxd2gzcDVuNHVrT2lValFYRnk2b003MzBuWm9vU2R2U2lYUlR2UlFwMDkyZDAzbnY5Zk55cWgwM3ZoM2l5DQpMUmNrdGhWdzZmSU83enhyS2NNemhWaHMrd2hQZW0zOURhU05oSjFrZUx4bzcyaDJIL01FMzRuQzNOSUhCUEhQDQpnU0F4dUNKOUJxdVVFbWJ1d0YxNzR4OUY5SEVibmNGWVRTd1hmS3diN1cxZ0F1U1RlWmhKVXc1eDZ6a3ZUTmRTDQp6NFZoUWNPTnVKMnpqbVV0Z1IrcVdzU2M5SHY3VEZESDlQbCt5NmQxeVJ0Rmp2TmlqeGZQUmo5a1dKbVJvcnBVDQpUTFQxOHZ1OGVvODVpY0tNVjVWaVp0MzB4bGlzVFVOMDJOWkxjNG83TVdraHE1eGhGcXhmZDdTZXZEc1FLa0VpDQp6eVFuL3M5Slk2azJsS1BQbjBNeTVSN1RFa0FkSFVERUlIc09qTXlrZnpwYVdoNldMK2RmRlRFVzE4MFNkRHdXDQpsQVdpa2lhaEVPU0NEZUUySlZMOW4yNjdDMmRzRklkNjVPczJKVjE5anl5b2VGQkhOQm11MFBjQ0F3RUFBUT09DQotLS0tLUVORCBSU0EgUFVCTElDIEtFWS0tLS0tDQo=');

// Headers
$headers = [
    'x-api-key' => API_KEY,
    'x-request-id' => uniqid(),
    'Content-Type' => 'application/json',
    'User-Agent' => 'Cardtokens/1'
];

//
// The http client to communicate to Cardtokens
//
$client = new Client(['headers' => $headers]);

//
// Use this funtion to create a test-token
//
function create_card_token() {
    //
    // Reference to the global client object
    //
    global $client;

    //
    // The URL to create a token
    //
    $url = BASE_URL . '/api/token';

    //
    // The test PAN to create a token on behalf of
    //
    $card = [
        "pan" => "5555341244441115",
        "expmonth" => 12,
        "expyear" => 2029,
        "securitycode" => "000"
    ];

    //
    // Encode the object into a json string
    //
    $json_card = json_encode($card);

    //
    // Load the public PEM and decode from b64
    //
    $decoded_pem_key_bytes = base64_decode(PEMPUBLICKEY);

    //
    // Use openssl to encrypt the $card json string - the result will be placed in $encrypted_card
    //
    openssl_public_encrypt($json_card, $encrypted_card, $decoded_pem_key_bytes);

    //
    // Now generate the request object including the encrypted card
    //
    $payload = [
        "enccard" => base64_encode($encrypted_card),
        "clientwalletaccountemailaddress" => "noreply@cardtokens.io",
        "merchantid" => MERCHANTID
    ];

    //
    // Call Cardtokens and return the response body
    //
    $response = $client->post($url, ['body' => json_encode($payload)]);
    return json_decode($response->getBody(), true);
}

//
// Use this function to get the status of a token
//
function get_card_token($tokenid) {
    //
    // Reference to the global client object
    //
    global $client;

    //
    // Build up the url to ge the status
    //
    $url = BASE_URL . '/api/token/' . $tokenid . '/status';

    //
    // Request Cardtokens and return the response
    //
    $response = $client->get($url);
    return json_decode($response->getBody(), true);
}

//
// Use this function to fetch a cyptogram from a token
//
function get_cryptogram($tokenid) {
    //
    // Reference to the global client object
    //
    global $client;

    //
    // The URL to generate the cryptogram
    //
    $url = BASE_URL . '/api/token/' . $tokenid . '/cryptogram';

    //
    // The request payload including some test data
    //
    $payload = [
        "reference" => "test-cryptogram",
        "transactiontype" => "ecom",
        "unpredictablenumber" => "12345678"
    ];

    //
    // Call Cardtokens and return the response
    // 
    $response = $client->post($url, ['body' => json_encode($payload)]);
    return json_decode($response->getBody(), true);
}

//
// Use this function to delete a token
//
function delete_token($tokenid) {
    //
    // Reference to the global client object
    //
    global $client;

    //
    // Build up the URL to delete the token
    // 
    $url = BASE_URL . '/api/token/' . $tokenid . '/delete';

    //
    // Call Cardtokens and return the statuscode
    //
    $response = $client->delete($url);
    return $response->getStatusCode();
}

//
// Create a token
//
$card_token = create_card_token();
print_r($card_token);
$tokenid = $card_token['tokenid'];
$token = $card_token['token'];

//
// Validate that the scheme token is longer than 0 bytes long
//
if (strlen($token) == 0) {
    throw new Exception("Network token is 0 long");
}

//
// Now fetch the token to check the status
//
$tokendata = get_card_token($tokenid);
print_r($tokendata);
$status = $tokendata["status"];

//
// Verify that the token is ACTIVE
//
if ($status != "ACTIVE") {
    throw new Exception("Status is not ACTIVE");
}

//
// Fetch a cryptogram
//
$cryptogramdata = get_cryptogram($tokenid);
print_r($cryptogramdata);
$cryptogram = $cryptogramdata["cryptogram"];

//
// Validate that the length of the cryptogram is longer than 0 bytes
//
if (strlen($cryptogram) == 0) {
    throw new Exception("Cryptogram is 0 long");
}

//
// Delete the token and verify by the statuscode that the 
// token is deleted
//
$statuscode = delete_token($tokenid);
if ($statuscode != 200) {
    throw new Exception("Could not delete the token!");
}
?>