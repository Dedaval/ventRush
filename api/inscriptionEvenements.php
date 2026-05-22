<?php
require_once 'dbUtil.php';
require_once 'utils.php';
require_once 'dbFunction.php'; 

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

function getBearerToken(): ?string {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s+(.+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $token = getBearerToken() ?? ($body['token'] ?? null);

    if (!$token) {
        http_response_code(401);
        echo json_encode([ERRORS => [[FIELD => TOKEN, MESSAGE => 'token.missing']]]);
        exit;
    }

    $evenementId = isset($body['evenements_id']) ? (int) $body['evenements_id'] : null;

    if (!$evenementId) {
        http_response_code(400);
        echo json_encode([ERRORS => [[FIELD => 'evenements_id', MESSAGE => 'field.required']]]);
        exit;
    }

    $result = inscrireEvenement($token, $evenementId);
    http_response_code($result[CODE]);
    echo json_encode($result);
    exit;
}

if ($method === 'GET') {
    $evenementId = isset($_GET['id']) ? (int) $_GET['id'] : null;

    if (!$evenementId) {
        http_response_code(400);
        echo json_encode([ERRORS => [[FIELD => 'id', MESSAGE => 'field.required']]]);
        exit;
    }

    $token = getBearerToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode([ERRORS => [[FIELD => TOKEN, MESSAGE => 'token.missing']]]);
        exit;
    }

    $result = listParticipant($evenementId, $token);
    http_response_code($result[CODE]);
    echo json_encode($result);
    exit;
}

http_response_code(405);
echo json_encode([ERRORS => [[FIELD => SERVER, MESSAGE => 'method.not.allowed']]]);