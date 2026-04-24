<?php
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: OPTIONS, POST, DELETE');
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $errors = errorEmpty([EMAIL, PASSWORD], $data);
    sendError($errors);

    $dbResult = checkUser($data);
    sendResultDb($dbResult, 200, TOKEN);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $errors = [];
    $authorization = getElementInHeader('Authorization');
    $token = getToken($authorization);
    if (is_null($token))
        $errors[] = tokenInvalide();

    sendError($errors);

    $dbResult = logoutUser($token);
    sendResultDb($dbResult, 204, null);
}

http_response_code(405);
echo json_encode([ERRORS => 'Method no allow']);
exit;