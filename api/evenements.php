<?php
include 'header.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: OPTIONS, GET');
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([ERRORS => 'Method no allow']);
    exit;
}
$errors = [];
$authorization = getElementInHeader('Authorization');
$token = getToken($authorization);
if (is_null($token))
    $errors[] = tokenInvalide();
sendError($errors);
$dbResult = getAllAds($token);

sendResultDb($dbResult, 200, AD, false);