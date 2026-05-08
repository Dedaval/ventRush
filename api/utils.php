<?php
define('EMAIL', 'email');
define('PASSWORD', 'password');
define('TOKEN', 'token');
define('TITLE', 'title');
define('DESCRIPTION', 'description');
define('AMOUNT', 'amount');
define('DATE', 'date');
define('CURRENCY', 'currency');
define('ACCEPT_CURRENCY', ['CHF', 'EUR', 'USD']);
define('MIN_CHARACTERS_PASSWORD', 5);
define('MIN_CHARACTERS_TITLE', 2);
define('MIN_AMOUNT', 0);
define('FIELD', 'field');
define('MESSAGE', 'message');
define('CODE', 'code');
define('ERRORS', 'errors');
define('SERVER', 'server');
define('AD', 'ad');
define('BASE_TOKEN', 'Token ');
define('PRICE', 'price');
define('ID_AD', 'idAd');
define('EDITABLE', 'editable');
define('PARAMETER', 'parameter');
define('PARTICIPANT', 'participant');
define('MAX_USERS', 'maxUsers');
function emailValidate(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function passwordValidate(string $password): bool
{
    return strlen($password) >= MIN_CHARACTERS_PASSWORD;
}
function generateToken($length = 32): string
{
    return md5(random_bytes($length));
}
function titleValidate(string $title): bool
{
    return strlen($title) >= MIN_CHARACTERS_TITLE;
}
function amountValidate($amount): bool
{
    return is_numeric($amount) && $amount >= MIN_AMOUNT;
}
function currencyValidate(string $currency): bool
{
    return in_array($currency, ACCEPT_CURRENCY);
}
function sendError(array $result): void
{
    if (empty($result))
        return;
    http_response_code(400);
    echo json_encode(array_merge(...array_column($result, ERRORS)));
    exit;
}

function getElementInHeader(string $key): ?string
{
    $headers = apache_request_headers();
    return $headers[$key] ?? null;
}
function getToken($token): string
{
    if (str_contains($token, BASE_TOKEN))
        return str_replace(BASE_TOKEN, '', $token);
    return null;
}
function errorEmpty(array $requiredField, array $search): array
{
    $errors = [];
    foreach ($requiredField as $field) {
        if (empty($search[$field])) {
            $errors[] = [
                CODE => 400,
                ERRORS => [
                    [FIELD => $field, MESSAGE => "invalid.format"]
                ]
            ];
        }
    }
    return $errors;
}
function sendResultDb(array $dbResult, int $code, ?string $key, bool $withKey = true): void
{
    if ($dbResult[CODE] === $code) {
        http_response_code(is_null($key) ? 204 : $code);
        if ($key && isset($dbResult[$key]))
            echo json_encode($withKey ? [$key => $dbResult[$key]] : $dbResult[$key]);
        exit;
    }
    http_response_code($dbResult[CODE]);
    echo json_encode($dbResult[ERRORS]);
    exit;
}
function tokenInvalide(): array
{
    return [
        CODE => 401,
        ERRORS => [
            [MESSAGE => "invalid.format"]
        ]
    ];
}
function sendResultDbPostMan(array $dbResult, int $code, ?string $key, bool $withKey = true): void
{
    if ($dbResult[CODE] === $code) {
        http_response_code(is_null($key) ? 204 : $code);
        if ($key && isset($dbResult[$key]))
            echo json_encode($withKey ? [$key => $dbResult[$key]] : $dbResult[$key]);
        exit;
    }
    http_response_code($dbResult[CODE]);
    echo json_encode($dbResult[ERRORS]);
    exit;
}
