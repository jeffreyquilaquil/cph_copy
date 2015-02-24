<?php

/*
 * Author: Ramazan APAYDIN
 * Website: http://ramazanapaydin.com
 * Versiyon: 1.0
*/

error_reporting(0);
header('Content-type: application/json');
require_once("OAuth.php");

$cc_key = $_GET['ck'];
$cc_secret = $_GET['csk'];
$url = "https://vimeo.com/api/rest/v2";
$args = array(
    'format'    => 'json',
    'method'    => $_GET['type'],
    'channel_id'=> $_GET['channelname'],
);

if($_GET['per_page']) {
    $args['per_page'] = $_GET['per_page'];
    $args['summary_response'] = 1;
}

if($_GET['page']) {
    $args['page'] = $_GET['page'];
}

$consumer = new OAuthConsumer($cc_key, $cc_secret);
$request = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", $url, $args);
$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);
$url = sprintf("%s?%s", $url, OAuthUtil::build_http_query($args));
$getUrl = $request->__toString();
$results = file_get_contents($getUrl);
echo $results;
?>
