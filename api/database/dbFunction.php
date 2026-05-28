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
    $nom = $data['title'];
    $description = $data['description'] ?? null;
    $nbMaxUtilisateurs = $data['nbMaxUtilisateurs'] ?? null;
    $date = $data['date'];

    $db = getDb();

    try {
        $sql = "SELECT id FROM utilisateurs WHERE token = :token";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [
                CODE => 400,
                ERRORS => [MESSAGE => 'invalid.token']
            ];
        }

        $user_id = (int) $user['id'];

        $sql = "INSERT INTO evenement(nom, date, description, nbMaxUtilisateurs) 
                VALUES (:nom, :date, :description, :nbMaxUtilisateurs)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':nbMaxUtilisateurs', $nbMaxUtilisateurs, PDO::PARAM_INT);
        $stmt->execute();

        $event_id = (int) $db->lastInsertId();

        $sql = "INSERT INTO evenement_utilisateurs(utilisateurs_id, evenements_id)
                VALUES (:user_id, :event_id)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->execute();

        return [
            CODE => 201,
            ID_AD => $event_id
        ];

    } catch (\Throwable $th) {
        return [
            CODE => 500,
            ERRORS => [MESSAGE => 'server.error']
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
            ERRORS => [MESSAGE => 'invalid.token']
        ];
    }

    $currentUserId = (int) $currentUser['id'];

    $sql = "SELECT id, nom, date, description, nbMaxUtilisateurs FROM evenement";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($events as $event) {
        $eventId = (int) $event['id'];

        $stmtCheck = $db->prepare("
            SELECT 1 FROM evenement_utilisateurs 
            WHERE utilisateurs_id = :uid AND evenements_id = :eid
        ");
        $stmtCheck->bindParam(':uid', $currentUserId, PDO::PARAM_INT);
        $stmtCheck->bindParam(':eid', $eventId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $isParticipant = $stmtCheck->fetch() ? true : false;

        $result[] = [
            ID_AD => $eventId,
            EDITABLE => $isParticipant,
            PARTICIPANT => $isParticipant,
            TITLE => $event['nom'],
            DATE => $event['date'],
            DESCRIPTION => $event['description'],
            MAX_USERS => (int) $event['nbMaxUtilisateurs']
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

    $sql = "SELECT e.id FROM evenement e
            INNER JOIN evenement_utilisateurs eu ON eu.evenements_id = e.id
            WHERE e.id = :idAd AND eu.utilisateurs_id = :userId";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':idAd', $adId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event)
        return [
            CODE => 400,
            ERRORS => [[FIELD => ID_AD, MESSAGE => 'not.found']]
        ];

    $sql = "UPDATE evenement 
            SET nom = :nom, date = :date, description = :description, nbMaxUtilisateurs = :nbMaxUtilisateurs
            WHERE id = :idAd";
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nom', $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(':date', $data['date'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':nbMaxUtilisateurs', $data['nbMaxUtilisateurs'], PDO::PARAM_INT);
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
function inscrireEvenement(string $token, int $evenementId): array
{
    $db = getDb();
 
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE token = :token");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if (!$user) {
        return [CODE => 401, ERRORS => [[FIELD => TOKEN, MESSAGE => 'token.invalid']]];
    }
 
    $userId = (int) $user['id'];
 
    $stmt = $db->prepare("SELECT id, nbMaxUtilisateurs FROM evenement WHERE id = :id");
    $stmt->bindParam(':id', $evenementId, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if (!$event) {
        return [CODE => 404, ERRORS => [[FIELD => 'evenements_id', MESSAGE => 'not.found']]];
    }
 
    $stmt = $db->prepare(
        "SELECT 1 FROM evenement_utilisateurs 
         WHERE utilisateurs_id = :uid AND evenements_id = :eid"
    );
    $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':eid', $evenementId, PDO::PARAM_INT);
    $stmt->execute();
 
    if ($stmt->fetch()) {
        return [CODE => 400, ERRORS => [[FIELD => 'evenements_id', MESSAGE => 'already.registered']]];
    }
 
    if ($event['nbMaxUtilisateurs'] !== null) {
        $stmtCount = $db->prepare(
            "SELECT COUNT(*) AS nb FROM evenement_utilisateurs WHERE evenements_id = :eid"
        );
        $stmtCount->bindParam(':eid', $evenementId, PDO::PARAM_INT);
        $stmtCount->execute();
        $count = (int) $stmtCount->fetchColumn();
 
        if ($count >= (int) $event['nbMaxUtilisateurs']) {
            return [CODE => 400, ERRORS => [[FIELD => 'evenements_id', MESSAGE => 'event.full']]];
        }
    }
 
    try {
        $stmt = $db->prepare(
            "INSERT INTO evenement_utilisateurs(utilisateurs_id, evenements_id) 
             VALUES (:uid, :eid)"
        );
        $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':eid', $evenementId, PDO::PARAM_INT);
        $stmt->execute();
 
        return [CODE => 201];
    } catch (\Throwable $th) {
        return [CODE => 500, ERRORS => [[FIELD => SERVER, MESSAGE => 'server.error']]];
    }
}
function listParticipant(int $evenementId, string $token): array
{
    $db = getDb();

    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE token = :token");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    if (!$stmt->fetch()) {
        return [CODE => 401, ERRORS => [[FIELD => TOKEN, MESSAGE => 'token.invalid']]];
    }
 
    $stmt = $db->prepare("SELECT id FROM evenement WHERE id = :id");
    $stmt->bindParam(':id', $evenementId, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetch()) {
        return [CODE => 404, ERRORS => [[FIELD => 'evenements_id', MESSAGE => 'not.found']]];
    }

    $stmt = $db->prepare(
        "SELECT u.nom, u.prenom
         FROM utilisateurs u
         INNER JOIN evenement_utilisateurs eu ON eu.utilisateurs_id = u.id
         WHERE eu.evenements_id = :evenementId
         ORDER BY u.nom, u.prenom"
    );
    $stmt->bindParam(':evenementId', $evenementId, PDO::PARAM_INT);
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    return [CODE => 200, 'participants' => $participants];
}

 function getAd(int $id) {
    try{
        $db = getDb();
        $sql = "SELECT id, nom, date, description, nbMaxUtilisateurs FROM evenement WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();        
        $adId = $stmt->fetch(PDO::FETCH_ASSOC);
        return $adId;
    }
    catch (\Throwable $th) {
        return [CODE => 500, ERRORS => [[FIELD => SERVER, MESSAGE => 'server.error']]];
    }
    }