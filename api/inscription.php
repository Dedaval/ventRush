<?php
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: OPTIONS, POST');
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([ERRORS => 'invalid.method']);
    exit;
}

$authHeader = getElementInHeader('Authorization');
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(tokenInvalide()[ERRORS]);
    exit;
}

$token = getToken($authHeader);
if (!$token) {
    http_response_code(401);
    echo json_encode(tokenInvalide()[ERRORS]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$errors = errorEmpty(['evenements_id'], $data);
sendError($errors);

$evenementId = intval($data['evenements_id']);

$dbResult = inscrireEvenement($token, $evenementId);
sendResultDb($dbResult, 201, null);
