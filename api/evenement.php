<?php
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: OPTIONS, POST, PUT, DELETE');
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $dbResult = createAd(checkAdd());
    sendResultDb($dbResult, 201, ID_AD);
}
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $idAd = filter_input(INPUT_GET, 'idAd', FILTER_SANITIZE_NUMBER_INT);
    $errors = [];
    if ($idAd == false)
        $errors[] = [
            CODE => 400,
            ERRORS => [
                [FIELD => PARAMETER, MESSAGE => "invalid"]
            ]
        ];
    sendError($errors);

    $dbResult = updateAd( $idAd, checkAdd());
    sendResultDb($dbResult, 200, null);
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $adId = filter_input(INPUT_GET, 'idAd', FILTER_SANITIZE_NUMBER_INT);

    $errors = [];
    if (!$adId) {
        $errors[] = [
            CODE => 400,
            ERRORS => [
                [FIELD => PARAMETER, MESSAGE => "invalid"]
            ]
        ];
    }

    $authorization = getElementInHeader('Authorization');
    $token = getToken($authorization);
    if (is_null($token)) {
        $errors[] = [
            CODE => 400,
            ERRORS => [
                [MESSAGE => "invalid.format"]
            ]
        ];
    }

    sendError($errors);

    $dbResult = deleteAd($token, $adId);
    sendResultDb($dbResult, 200, null);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET'){
    $adId = filter_input(INPUT_GET, 'idAd', FILTER_SANITIZE_NUMBER_INT);

    $errors = [];
    if (!$adId) {
        $errors[] = [
            CODE => 400,
            ERRORS => [
                [FIELD => PARAMETER, MESSAGE => "invalid"]
            ]
        ];
    }

    $dbResult = listParticipant($adId);
    sendResultDb($dbResult, 200, null);

}




http_response_code(405);
echo json_encode([ERRORS => 'Method no allow']);
exit;

function checkAdd(){
    $data = json_decode(file_get_contents('php://input'), true);

    //$errors = errorEmpty([TITLE, DESCRIPTION, DATE], $data);
//    if (!isset($data[PRICE][AMOUNT]) || !isset($data[PRICE][CURRENCY]))
   //     $errors = tokenInvalide();
    //sendError($errors);
    $errors = [];
    $authorization = getElementInHeader('Authorization');
    $token = getToken($authorization);
    if (is_null($token))
        $errors[] = tokenInvalide();

    if (!titleValidate($data[TITLE])) {
        $errors[] = [
            CODE => 400,
            ERRORS => [
                [FIELD => TITLE, MESSAGE => 'min' . MIN_CHARACTERS_TITLE . 'chars']
            ]
        ];
    }
    sendError($errors);

    $data[TOKEN] = $token;
    return $data;
}