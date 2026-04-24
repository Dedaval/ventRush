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

$data = json_decode(file_get_contents('php://input'), true);

$errors = errorEmpty([EMAIL, PASSWORD], $data);
sendError($errors);

if (!emailValidate($data[EMAIL])) {
    $errors[] = [
        CODE => 400,
        ERRORS => [
            [FIELD => EMAIL, MESSAGE => "invalid.format"]
        ]
    ];
}
if (!passwordValidate($data[PASSWORD])) {
    $errors[] = [
        CODE => 400,
        ERRORS => [
            [FIELD => PASSWORD, MESSAGE => "invalid.format"]
        ]
    ];
}
sendError($errors);

$dbResult = createUserDb($data);
sendResultDb($dbResult, 201, TOKEN ?? null);