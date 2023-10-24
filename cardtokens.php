<?php
require 'vendor/autoload.php';
//require __DIR__ . '/vendor/autoload.php';



use phpseclib\Crypt\RSA;
use GuzzleHttp\Client;

// Constants
define('BASE_URL', 'https://api.cardtokens.io');
define('API_KEY', '95f734793a424ea4ae8d9dc0b8c1a4d7');
define('MERCHANTID', '523ca9d5eb9d4ce0a60b2a3f5eb3119d');
define('PEMPUBLICKEY', 'LS0tLS1CRUdJTiBSU0EgUFVCTElDIEtFWS0tLS0tDQpNSUlDQ2dLQ0FnRUExbXN0TVByRlJWZDhUTUdyWTMyNDJwcTQ2aFlFMFBieXcrTnB0MnRDSjBpRHkrWkxQWWJGDQoydVhOSDVQT2d4aTN0NUhVNjYxVVNBOFg5enk3aklPMDlpOGxRMkdoN1dpejlqZXpFVDBpVmNvUGovSFFrV1N1DQorMDljREhSTmpQMmhpa0hZQTA5SVlzTm96ajd4dHYwTnJxbjZacWZ5amhOS1NrN2RUeUVVQ0xoaEwvTUVFRTZ0DQpBRERVUllvS0hVcWtWaXRwWXNwTUdqaUo2QUFJWVVlYU1DdkZ2cnhaSkFNSW5FbnY3THNhTHVBV21pdzRrOXM5DQozTHUxd2gzcDVuNHVrT2lValFYRnk2b003MzBuWm9vU2R2U2lYUlR2UlFwMDkyZDAzbnY5Zk55cWgwM3ZoM2l5DQpMUmNrdGhWdzZmSU83enhyS2NNemhWaHMrd2hQZW0zOURhU05oSjFrZUx4bzcyaDJIL01FMzRuQzNOSUhCUEhQDQpnU0F4dUNKOUJxdVVFbWJ1d0YxNzR4OUY5SEVibmNGWVRTd1hmS3diN1cxZ0F1U1RlWmhKVXc1eDZ6a3ZUTmRTDQp6NFZoUWNPTnVKMnpqbVV0Z1IrcVdzU2M5SHY3VEZESDlQbCt5NmQxeVJ0Rmp2TmlqeGZQUmo5a1dKbVJvcnBVDQpUTFQxOHZ1OGVvODVpY0tNVjVWaVp0MzB4bGlzVFVOMDJOWkxjNG83TVdraHE1eGhGcXhmZDdTZXZEc1FLa0VpDQp6eVFuL3M5Slk2azJsS1BQbjBNeTVSN1RFa0FkSFVERUlIc09qTXlrZnpwYVdoNldMK2RmRlRFVzE4MFNkRHdXDQpsQVdpa2lhaEVPU0NEZUUySlZMOW4yNjdDMmRzRklkNjVPczJKVjE5anl5b2VGQkhOQm11MFBjQ0F3RUFBUT09DQotLS0tLUVORCBSU0EgUFVCTElDIEtFWS0tLS0tDQo=');

// Headers
$headers = [
    'x-api-key' => API_KEY,
    'x-request-id' => uniqid(),
    'Content-Type' => 'application/json',
    'User-Agent' => 'Cardtokens/1'
];

$client = new Client(['headers' => $headers]);

function create_card_token() {
    global $client;

    $url = BASE_URL . '/api/token';

    $card = [
        "pan" => "5555341244441115",
        "expmonth" => 12,
        "expyear" => 2029,
        "securitycode" => "000"
    ];

    $json_card = json_encode($card);
    $json_bytes = utf8_encode($json_card);

    $decoded_pem_key_bytes = base64_decode(PEMPUBLICKEY);

    $rsa = new RSA();
    $rsa->loadKey($decoded_pem_key_bytes);
    $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
    $encrypted_card = $rsa->encrypt($json_bytes);

    $payload = [
        "enccard" => base64_encode($encrypted_card),
        "clientwalletaccountemailaddress" => "noreply@cardtokens.io",
        "merchantid" => MERCHANTID
    ];

    $response = $client->post($url, ['body' => json_encode($payload)]);
    return json_decode($response->getBody(), true);
}

function get_card_token($tokenid) {
    global $client;

    $url = BASE_URL . '/api/token/' . $tokenid . '/status';

    $response = $client->get($url);
    return json_decode($response->getBody(), true);
}

function get_cryptogram($tokenid) {
    global $client;

    $url = BASE_URL . '/api/token/' . $tokenid . '/cryptogram';

    $payload = [
        "reference" => "test-cryptogram",
        "transactiontype" => "ecom",
        "unpredictablenumber" => "12345678"
    ];

    $response = $client->post($url, ['body' => json_encode($payload)]);
    return json_decode($response->getBody(), true);
}

function delete_token($tokenid) {
    global $client;

    $url = BASE_URL . '/api/token/' . $tokenid . '/delete';

    $response = $client->delete($url);
    return $response->getStatusCode();
}

$card_token = create_card_token();
print_r($card_token);
$tokenid = $card_token['tokenid'];
$token = $card_token['token'];

if (strlen($token) == 0) {
    throw new Exception("Network token is 0 long");
}

$tokendata = get_card_token($tokenid);
print_r($tokendata);

$status = $tokendata["status"];
if ($status != "ACTIVE") {
    throw new Exception("Status is not ACTIVE");
}

$cryptogramdata = get_cryptogram($tokenid);
print_r($cryptogramdata);

$cryptogram = $cryptogramdata["cryptogram"];
if (strlen($cryptogram) == 0) {
    throw new Exception("Cryptogram is 0 long");
}

$statuscode = delete_token($tokenid);
if ($statuscode != 200) {
    throw new Exception("Could not delete the token!");
}

?>