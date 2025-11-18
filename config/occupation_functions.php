<?php
// load all summary rows from v_post_summary view
function loadSummaryRows($pdo)
{
    $sql = "SELECT occupation_id, post_number, location, occupation_type, name, weapons, zombie_types, observation FROM v_post_summary ORDER BY post_number, occupation_type";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}
// load all posts in the table
function loadPosts($pdo)
{
    $stmt = $pdo->query("SELECT id_posts, location FROM posts ORDER BY id_posts");
    return $stmt->fetchAll();
}
// count how many entries are in the city for ratking
function countRatKing($pdo)
{
    $sql = "SELECT COUNT(*) AS total FROM occupation_zombie_type WHERE zombie_type = 'RAT_KING'";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch();
    return $row ? (int)$row["total"] : 0;
}
// check if occupation type already exists in a post
function occupationTypeExistsInPost($pdo, $postId, $occupationType)
{
    $sql = "SELECT COUNT(*) AS total FROM occupations WHERE post_id = :post_id AND occupation_type = :occupation_type";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":post_id" => $postId,
        ":occupation_type" => $occupationType
    ]);
    $row = $stmt->fetch();
    return $row && (int)$row["total"] > 0;
}
// check if a WLF character is already assigned to another post
function wlfCharacterExistsInAnotherPost($pdo, $characterName)
{
    $sql = "SELECT p.location FROM occupations o JOIN posts p ON o.post_id = p.id_posts WHERE o.occupation_type = 'WLF' AND o.character_name = :character_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":character_name" => $characterName]);
    $row = $stmt->fetch();
    return $row ? $row["location"] : null;
}
// create occupations and their related zombie types or weapons
/*
$occupationType will be 'WLF','SERAPHITES' or 'INFECTED_NEST'
$characterName for WLF
$weaponsArray for Seraphites
$selectedZombies for infected nest
*/
function createOccupationGroup($pdo, $postId, $occupationType, $characterName, $weaponsArray, $selectedZombies, $observation)
{
    if ($occupationType === "WLF") {
        $weaponsArray = [];
        $selectedZombies = [];
    } elseif ($occupationType === "SERAPHITES") {
        $characterName = null;
        $selectedZombies = [];
    } elseif ($occupationType === "INFECTED_NEST") {
        $characterName = null;
        $weaponsArray = [];
    }

    $insertOccupation = $pdo->prepare("INSERT INTO occupations (post_id, occupation_type, character_name, observation) VALUES (:post_id, :occupation_type, :character_name, :observation)");
    $insertWeapon = $pdo->prepare("INSERT INTO occupation_weapons (occupation_id, weapon_name) VALUES (:occupation_id, :weapon_name)");
    $insertZombieType = $pdo->prepare("INSERT INTO occupation_zombie_type (occupation_id, zombie_type) VALUES (:occupation_id, :zombie_type)");

    // insert occupation
    $insertOccupation->execute([
        ":post_id" => $postId,
        ":occupation_type" => $occupationType,
        ":character_name" => $characterName,
        ":observation" => $observation
    ]);
    $occupationId = $pdo->lastInsertId();

    // seraphites weapons
    if ($occupationType === "SERAPHITES" && is_array($weaponsArray)) {
        foreach ($weaponsArray as $weapon) {
            $insertWeapon->execute([
                ":occupation_id" => $occupationId,
                ":weapon_name" => $weapon
            ]);
        }
    }
    // infected nest zombie types
    if ($occupationType === "INFECTED_NEST" && is_array($selectedZombies)) {
        foreach ($selectedZombies as $zombieType) {
            $insertZombieType->execute([
                ":occupation_id" => $occupationId,
                ":zombie_type" => $zombieType
            ]);
        }
    }
}

// delete occupation by id
function deleteOccupation($pdo, $occupationId)
{
    $sql = "DELETE FROM occupations WHERE id_occupations = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $occupationId]);

    return $stmt->rowCount() > 0;
}

function countRatKingExcluding($pdo, $occupationId)
{
    $sql = "SELECT COUNT(*) AS total FROM occupation_zombie_type WHERE zombie_type = 'RAT_KING' AND occupation_id <> :occupation_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':occupation_id' => $occupationId]);
    $row = $stmt->fetch();
    return $row ? (int)$row["total"] : 0;
}
function getOccupationWithDetails($pdo, $occupationId)
{
    $sql = "SELECT id_occupations, post_id, occupation_type, character_name, observation FROM occupations WHERE id_occupations = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $occupationId]);
    $occupation = $stmt->fetch();

    if (!$occupation) {
        return null;
    }

    $details = [
        'id_occupations' => $occupation['id_occupations'],
        'post_id' => $occupation['post_id'],
        'occupation_type' => $occupation['occupation_type'],
        'character_name' => $occupation['character_name'],
        'observation' => $occupation['observation'],
        'weapons' => [],
        'zombie_types' => [],
    ];

    // weapons
    if ($occupation['occupation_type'] === 'SERAPHITES') {
        $stmtW = $pdo->prepare("SELECT weapon_name FROM occupation_weapons WHERE occupation_id = :id");
        $stmtW->execute([':id' => $occupationId]);
        $details['weapons'] = array_column($stmtW->fetchAll(), 'weapon_name');
    }

    // zombie types
    if ($occupation['occupation_type'] === 'INFECTED_NEST') {
        $stmtZ = $pdo->prepare("SELECT zombie_type FROM occupation_zombie_type WHERE occupation_id = :id");
        $stmtZ->execute([':id' => $occupationId]);
        $details['zombie_types'] = array_column($stmtZ->fetchAll(), 'zombie_type');
    }

    return $details;
}

function updateOccupation($pdo, $occupationId, $postId, $occupationType, $characterName, array $weaponsArray, array $selectedZombies, $observation)
{
    $pdo->beginTransaction();

    try {
        $characterForThisType = null;
        if ($occupationType === 'WLF') {
            $characterForThisType = $characterName;
        }

        // update main occupation row
        $stmt = $pdo->prepare("UPDATE occupations SET post_id = :post_id, occupation_type = :occupation_type, character_name = :character_name, observation = :observation WHERE id_occupations = :id");
        $stmt->execute([
            ':post_id' => $postId,
            ':occupation_type' => $occupationType,
            ':character_name' => $characterForThisType,
            ':observation' => $observation,
            ':id' => $occupationId,
        ]);


        $stmtDelW = $pdo->prepare("DELETE FROM occupation_weapons WHERE occupation_id = :id");
        $stmtDelW->execute([':id' => $occupationId]);

        $stmtDelZ = $pdo->prepare("DELETE FROM occupation_zombie_type WHERE occupation_id = :id");
        $stmtDelZ->execute([':id' => $occupationId]);

        if ($occupationType === 'SERAPHITES' && !empty($weaponsArray)) {
            $stmtW = $pdo->prepare("INSERT INTO occupation_weapons (occupation_id, weapon_name) VALUES (:occupation_id, :weapon_name)");
            foreach ($weaponsArray as $weapon) {
                $stmtW->execute([
                    ':occupation_id' => $occupationId,
                    ':weapon_name' => $weapon,
                ]);
            }
        }

        if ($occupationType === 'INFECTED_NEST' && !empty($selectedZombies)) {
            $stmtZ = $pdo->prepare("INSERT INTO occupation_zombie_type (occupation_id, zombie_type) VALUES (:occupation_id, :zombie_type)");
            foreach ($selectedZombies as $zombieType) {
                $stmtZ->execute([
                    ':occupation_id' => $occupationId,
                    ':zombie_type' => $zombieType,
                ]);
            }
        }

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}
