    <?php
require_once 'dbUtil.php';
require_once 'utils.php';
function createUserDb(array $data): array
{
    $email = $data[EMAIL];
    $password = password_hash($data[PASSWORD], PASSWORD_DEFAULT);
    $token = generateToken();
    $db = getDb();
    $sql = "INSERT INTO utilisateurs(nom, prenom, email, mdp, token) VALUES (:nom, :prenom, :email, :mdp, :token)";

    $db->beginTransaction();
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nom', $data["nom"], PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $data["prenom"], PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':mdp', $password, PDO::PARAM_STR);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $db->commit();
        return [CODE => 201, TOKEN => $token];
    } catch (PDOException $e) {
        $db->rollBack();
        if ($e->getCode() == '23000') {
            return [
                CODE => 400,
                ERRORS => [
                    [FIELD => EMAIL, MESSAGE => 'already.used']
                ]
            ];
        }
        return [
            CODE => 500,
            ERRORS => [
                [FIELD => SERVER, MESSAGE => 'server.error']
            ]
        ];
    } catch (Exception $e) {
        $db->rollBack();
        return [
            CODE => 500,
            ERRORS => [
                [FIELD => SERVER, MESSAGE => 'server.error']
            ]
        ];
    }
}

function checkUser(array $data): array
{
    $email = $data[EMAIL];
    $password = $data[PASSWORD];
    $token = generateToken();
    $db = getDb();

    try {
        $stmt = $db->prepare("SELECT mdp FROM utilisateurs WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mdp'])) {
            $update = $db->prepare("UPDATE utilisateurs SET token = :token WHERE email = :email");
            $update->bindParam(':token', $token, PDO::PARAM_STR);
            $update->bindParam(':email', $email, PDO::PARAM_STR);
            $update->execute();
            return [CODE => 200, TOKEN => $token];
        }
        return [CODE => 400, ERRORS => [MESSAGE => 'invalid.credentials']];
    } catch (\Throwable $th) {
        return [CODE => 500, ERRORS => [FIELD => SERVER, MESSAGE => 'server.error']];
    }
}

function logoutUser(string $token): array
{
    $db = getDb();
    $sql = "UPDATE utilisateurs SET token = NULL WHERE token = :token";
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return [CODE => 204];
        }
        return [
            CODE => 400,
            ERRORS => [
                [FIELD => TOKEN, MESSAGE => 'invalid.token']
            ]
        ];

    } catch (\Throwable $th) {
        return [
            CODE => 500,
            ERRORS => [
                [FIELD => SERVER, MESSAGE => 'server.error']
            ]
        ];
    }
}
function createAd(array $data): array
{
    $token = $data['token'];
    $title = $data['title'];
    $description = $data['description'] ?? null;
    $amount = $data[PRICE]['amount'];
    $currency = $data[PRICE]['currency'];

    $db = getDb();

    $sql = "SELECT id FROM utilisateurs WHERE token = :token";
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return [
                CODE => 400,
                ERRORS => [
                    MESSAGE => 'invalid.token'
                ]
            ];
        }
        $user_id = $user['id'];


        $sql = "INSERT INTO ads(user_id, title, description, amount, currency) VALUES (:user_id, :title, :description, :amount, :currency)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
        $stmt->bindParam(':currency', $currency, PDO::PARAM_STR);
        $stmt->execute();

        return [CODE => 201, ID_AD => intval($db->lastInsertId())];

    } catch (\Throwable $th) {
        return [
            CODE => 500,
            ERRORS => [
                [FIELD => SERVER, MESSAGE => 'server.error']
            ]
        ];
    }
}
function getAllAds(string $token): array
{
    $db = getDb();

    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE token = :token");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        return [
            CODE => 400,
            ERRORS => [
                MESSAGE => 'invalid.token'
            ]
        ];
    }

    $currentUserId = (int) $currentUser['id'];

    $sql = "SELECT id, user_id, title, description, amount, currency FROM ads";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($ads as $ad) {
        $result[] = [
            ID_AD => (int) $ad['id'],
            EDITABLE => ((int) $ad['user_id'] === $currentUserId),
            TITLE => $ad['title'],
            DESCRIPTION => $ad['description'],
            PRICE => [
                AMOUNT => (int) $ad['amount'],
                CURRENCY => $ad['currency']
            ]
        ];
    }

    return [CODE => 200, AD => $result];
}
function updateAd(int $adId, array $data): array
{
    $db = getDb();
    $token = $data[TOKEN];

    $sql = "SELECT id FROM utilisateurs WHERE token = :token";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        return [CODE => 401, ERRORS => [[FIELD => TOKEN, MESSAGE => 'token.invalid']]];
    }

    $currentUserId = (int) $currentUser['id'];

    $sql = "SELECT user_id FROM ads WHERE id = :idAd";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':idAd', $adId, PDO::PARAM_INT);
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad)
        return [
            CODE => 400,
            ERRORS => [[FIELD => ID_AD, MESSAGE => 'not.found']]
        ];

    if ((int) $ad['user_id'] !== $currentUserId)
        return [
            CODE => 400,
            ERRORS => [[FIELD => ID_AD, MESSAGE => 'not.editable']]
        ];

    $sql = "UPDATE ads 
            SET title = :title, description = :description, amount = :amount, currency = :currency
            WHERE id = :idAd";
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':amount', $data['price']['amount'], PDO::PARAM_INT);
        $stmt->bindParam(':currency', $data['price']['currency'], PDO::PARAM_STR);
        $stmt->bindParam(':idAd', $adId, PDO::PARAM_INT);
        $stmt->execute();

        return [CODE => 200];
    } catch (\Throwable $th) {
        return [CODE => 500, ERRORS => [[FIELD => SERVER, MESSAGE => 'server.error']]];
    }
}
function deleteAd(string $token, int $adId): array
{
    $db = getDb();

    $sql = "SELECT id FROM utilisateurs WHERE token = :token";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        return [CODE => 401, ERRORS => [[FIELD => TOKEN, MESSAGE => 'token.invalid']]];
    }

    $currentUserId = (int) $currentUser['id'];

    $sql = "SELECT user_id FROM ads WHERE id = :idAd";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':idAd', $adId, PDO::PARAM_INT);
    $stmt->execute();
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad)
        return [
            CODE => 404,
            ERRORS => [[FIELD => ID_AD, MESSAGE => 'not.found']]
        ];

    if ((int) $ad['user_id'] !== $currentUserId)
        return [
            CODE => 403,
            ERRORS => [[FIELD => ID_AD, MESSAGE => 'not.editable']]
        ];


    $sql = "DELETE FROM ads WHERE id = :idAd";
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':idAd', $adId, PDO::PARAM_INT);
        $stmt->execute();

        return [CODE => 200, AD => ["idAd" => $adId, "deleted" => true]];
    } catch (\Throwable $th) {
        return [CODE => 500, ERRORS => [[FIELD => SERVER, MESSAGE => 'server.error']]];
    }
}