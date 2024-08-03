<?php
//  this is where the reverse engineering of the API will be done for access to the mcDonalds API that will tell me about my order and other data 



addEndpoints(array(
    "mcdonalds/backend/V1/Api/getOrders" => "McdonaldsApi_getOrders",
    "mcdonalds/backend/V1/Api/uploadAccessToken" => "McdonaldsApi_uploadAccessToken",
    "mcdonalds/backend/V1/Api/uploadRefreshToken" => "McdonaldsApi_uploadRefreshToken",
));

function McdonaldsApi_getJWT()
{
    // $temprefrsh = "e855185d-ad37-4c74-b4c6-e9262e98f03f";
    $tempjwt = $GLOBALS["redis_Storage"]->get('mcdonalds_jwt');

    // check if we have a JWT in tempjwt 
    if ($tempjwt == null) {
        $temprefrsh = $GLOBALS["redis_Storage"]->get('mcdonalds_Refreshkey');
        $temprefrsh = json_decode($temprefrsh, true);
        $JWT = McdonaldsApi_refreshJWT($temprefrsh["accessToken"], $temprefrsh["refreshToken"]);
        return $JWT;
    }
    return $tempjwt;
}

function McdonaldsApi_refreshJWT($JWT, $refreshToken)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://eu-prod.api.mcd.com/exp/v1/customer/login/refresh',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{"refreshToken":"' . $refreshToken . '"}',
        CURLOPT_HTTPHEADER => array(
            'Host: eu-prod.api.mcd.com',
            'mcd-sourceapp: GMA',
            'Cache-Control: true',
            'User-Agent: MCDSDK/8.4.0 (iPhone; 18.1; en-US) GMA/8.4.0',
            'mcd-clientsecret: HodcWznEVtyk2EH1A0crCLQl7VkNl4MP',
            'newrelic: ewoiZCI6IHsKImFjIjogIjczNDA1NiIsCiJhcCI6ICIyMTgxNTYyNzciLAoiaWQiOiAiYzk3OTlmMDZhYmNjYTMxYiIsCiJ0aSI6IDE3MjI2Mjk4MDA4MTMsCiJ0ayI6ICIxMjQ4MzM5IiwKInRyIjogImE2MWVjYjgyZDhmNWU3NzNiN2NjMzU0NGMyNjE0NTQ3IiwKInR5IjogIk1vYmlsZSIKfSwKInYiOiBbCjAsCjIKXQp9',
            // 'Content-Length: 55',
            'mcd-uuid: 74968107-CC5E-4CBD-B4D9-4C028126A135',
            'Connection: keep-alive',
            'Authorization: Bearer ' . $JWT,
            'tracestate: 1248339@nr=0-2-734056-218156277-c9799f06abcca31b--0--1722629800813',
            'mcd-clientid: 1QL44pFIYjpKqIR39rkFIwGd1XEczAeK',
            'X-acf-sensor-data: 4,i,HTG3Qk40zCFEmAzFttOUvffSW7DR8lvDfANZfDPablv9z9tG6mP6rOGpA0RR/4yYF5gZFlMLQy38Wrcz3GI37vtzPdhQjkmd2bWriyClV8i7HAtxXzJtuh22VGge0pRbObWuHrt6oUz4YzbBjTWfqKhzP7MxzOmBLJndCxgviTs=,P+eMASve19aorqsQT2FEyuIxHofMdU4rmh3hQy0Tll0+54+Z2v31birBQX/FbsZIRGG0iqODSoiM+k/3ZnHcNYdeRyn9+QHLlcQPFhUrnrifNsTMTwdm627JwvKcm4L1DydsnrIjLgqiB0cfO9/akVKQj/56QD5SiWEYIHjinio=$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$271,2,5$$',
            'Accept-Language: en-GB',
            'accept-charset: utf-8',
            'Accept: application/json',
            'mcd-marketid: UK',
            'traceparent: 00-a61ecb82d8f5e773b7cc3544c2614547-c9799f06abcca31b-00',
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    if ($data["status"]["code"] == 20000) {
        $accessToken = $data["response"]["accessToken"];
        $refreshToken = $data["response"]["refreshToken"];
        $GLOBALS["redis_Storage"]->set('mcdonalds_Refreshkey', json_encode(array("refreshToken" => $refreshToken, "accessToken" => $accessToken)));
        $GLOBALS["redis_Storage"]->set('mcdonalds_jwt', $accessToken, 300);
        return $accessToken;
    } else {
        echo json_encode($data, JSON_PRETTY_PRINT);
        die();
    }
    return null;
}
function McdonaldsApi_getOrders($userinfo, $maxOrdersToReturn = 5)
{
    $curl = curl_init();

    $jwtToken = McdonaldsApi_getJWT();
    $url = 'https://eu-prod.api.mcd.com/uk/gma/api/v1/orders?filterMethod=INCLUDE_ONLY_SPECIFIED_STATES&maxOrdersToReturn=' . $maxOrdersToReturn . '&newerThanOrderIDOnly=&startAtOrderID=&states=FULFILLED';

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Host: eu-prod.api.mcd.com',
            'mcd-sourceapp: GMA',
            'Cache-Control: true',
            'User-Agent: MCDSDK/8.4.0 (iPhone; 18.1; en-US) GMA/8.4.0',
            'mcd-clientsecret: HodcWznEVtyk2EH1A0crCLQl7VkNl4MP',
            'mcd-correlation-id: B94C7AE0-E397-4F8B-9358-8F592F60C90C',
            'newrelic: ewoiZCI6IHsKImFjIjogIjczNDA1NiIsCiJhcCI6ICIyMTgxNTYyNzciLAoiaWQiOiAiNGIyNmIzNjJlNDJmODRhYiIsCiJ0aSI6IDE3MjI2MjYxNzAxNzMsCiJ0ayI6ICIxMjQ4MzM5IiwKInRyIjogIjQ1NDIzYWNjZWJiNjY1NjQ0NTQyM2FjY2ViYjY2NTY0IiwKInR5IjogIk1vYmlsZSIKfSwKInYiOiBbCjAsCjIKXQp9',
            'mcd-uuid: 86C13126-A0FD-4D8B-B0B7-AEFE2A5DA4DF',
            'Connection: keep-alive',
            'tracestate: 1248339@nr=0-2-734056-218156277-4b26b362e42f84ab--0--1722626170173',
            'mcd-clientid: 1QL44pFIYjpKqIR39rkFIwGd1XEczAeK',
            'Accept-Language: en-GB',
            'accept-charset: utf-8',
            'Accept: application/json',
            'traceparent: 00-45423accebb6656445423accebb66564-4b26b362e42f84ab-00',
            'X-NewRelic-ID: UwUDUVNVGwEBXFBXAQMAUQ==',
            'mcd-marketid: UK',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $jwtToken,
        ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response, true);
    curl_close($curl);
    //status code 20000 means success
    if ($response["status"]["code"] == 20000) {
        return $response["response"]["orders"];
    } else {
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}


//  function to upload the accessToken to the redis storage

function McdonaldsApi_uploadAccessToken($userinfo,$accessToken)
{
    $GLOBALS["redis_Storage"]->set('mcdonalds_jwt', $accessToken, 300);
}
//  function to upload the refreshToken to the redis storage
function McdonaldsApi_uploadRefreshToken($userinfo,$data)
{
    $data = json_decode($data, true);
    $GLOBALS["redis_Storage"]->set('mcdonalds_Refreshkey', json_encode(array("refreshToken" => $data["response"]["refreshToken"], "accessToken" => $data["response"]["accessToken"])));
    McdonaldsApi_uploadAccessToken($userinfo,$data["response"]["accessToken"]);
}
