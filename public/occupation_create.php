<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/occupation_functions.php";

// load posts for selection
try {
    $posts = loadPosts($pdo);
} catch (\PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// WLF characters
$wlfCharacters = ["Abby", "Manny", "Nora", "Mel", "Owen", "Isaac", "Jordan", "Leah"];

// zombie types options
$zombieTypeOptions = [
    "RUNNER" => "Runners",
    "STALKER" => "Stalkers",
    "CLICKER" => "Clickers",
    "BLOATER" => "Bloaters",
    "RAT_KING" => "Rat King"
];

$errors = [];

function addError(array &$errors, string $field, string $message): void
{
    if (!isset($errors[$field])) {
        $errors[$field] = [];
    }
    $errors[$field][] = $message;
}

// default
$postId = $_POST["post_id"] ?? "";
$occupationType = $_POST["occupation_type"] ?? "";
$characterName = trim($_POST["character_name"] ?? "");
$weaponsText = trim($_POST["weapons"] ?? "");
$selectedZombies = isset($_POST["zombie_types"]) ? (array)$_POST["zombie_types"] : [];
$observation = trim($_POST["observation"] ?? "");

$isEdit = false;
$formTitle = "Create Occupation";
$submitLabel = "Save occupation";

if (isset($_POST["save"]) && !isset($_POST["update_type"])) {
    // validations
    if ($postId === "") {
        addError($errors, "post_id", "Post is required.");
    } else {
        $validPostIds = array_column($posts, "id_posts");
        if (!in_array($postId, $validPostIds)) {
            addError($errors, "post_id", "Selected post is invalid.");
        }
    }
    
    if ($occupationType === "") {
        addError($errors, "occupation_type", "Occupation type is required.");
    } else {
        if ($postId !== "") {
            try {
                if (occupationTypeExistsInPost($pdo, $postId, $occupationType)) {
                    addError($errors, "occupation_type", "This post already has an occupation of this type. Each post can only have one occupation of each type.");
                }
            } catch (PDOException $e) {
                addError($errors, "general", 'Error checking occupation type: ' . $e->getMessage());
            }
        }
    }

    if ($occupationType === "WLF") {
        if ($characterName === "") {
            addError($errors, "character_name", "Character name is required for WLF occupation.");
        }
    }

    $weaponsArray = [];
    if ($occupationType === "SERAPHITES") {
        if ($weaponsText === "") {
            addError(
                $errors,
                "weapons",
                "At least you must write a weapon for the Seraphites. Use commas if you want more than one."
            );
        } else {
            $parts = explode(",", $weaponsText);
            foreach ($parts as $part) {
                $weapon = trim($part);
                if ($weapon !== "") {
                    $weaponsArray[] = $weapon;
                }
            }
            if (empty($weaponsArray)) {
                addError($errors, "weapons", "You must provide at least one weapon for the Seraphites.");
            }
        }
    }

    if ($occupationType === "INFECTED_NEST") {
        if (empty($selectedZombies)) {
            addError($errors, "zombie_types", "You must choose at least one zombie type for the infected nest.");
        } else {
            if (in_array("RAT_KING", $selectedZombies)) {
                try {
                    $ratKingCount = countRatKing($pdo);
                    if ($ratKingCount >= 1) {
                        addError($errors, "zombie_types", "There's already a Rat King in the city, calm down.");
                    }
                } catch (PDOException $e) {
                    addError($errors, "general", 'Error checking Rat King constraint: ' . $e->getMessage());
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($occupationType === 'WLF') {
                $weaponsArray = [];
                $selectedZombies = [];
            } elseif ($occupationType === 'SERAPHITES') {
                $characterName = null;
                $selectedZombies = [];
            } elseif ($occupationType === 'INFECTED_NEST') {
                $characterName = null;
                $weaponsArray = [];
            }
            
            createOccupationGroup($pdo, $postId, $occupationType, $characterName, $weaponsArray, $selectedZombies, $observation);
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Error creating occupation: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../views/occupation_form.php';
