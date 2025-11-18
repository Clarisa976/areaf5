<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/occupation_functions.php";

try {
    $posts = loadPosts($pdo);
} catch (\PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// WLF characters
$wlfCharacters = ["Abby", "Manny", "Nora", "Mel", "Owen", "Isaac", "Jordan", "Leah"];

// zombie types options
$zombieTypeOptions = [
    "RUNNERS" => "Runners",
    "STALKERS" => "Stalkers",
    "CLICKERS" => "Clickers",
    "BLOATERS" => "Bloaters",
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

$isEdit = true;
$formTitle = "Edit occupation";
$submitLabel = "Update occupation";

if (!isset($_POST['save']) && !isset($_POST['update_type'])) {
    if (!isset($_GET['id'])) {
        header("Location: index.php");
        exit();
    }

    $occupationId = (int)$_GET['id'];
    if ($occupationId <= 0) {
        header("Location: index.php");
        exit();
    }

    try {
        $details = getOccupationWithDetails($pdo, $occupationId);
    } catch (PDOException $e) {
        die("Error loading occupation: " . $e->getMessage());
    }

    if (!$details) {
        header("Location: index.php");
        exit();
    }

    $postId = $details['post_id'];
    $occupationType = $details['occupation_type'];
    $characterName = $details['character_name'] ?? "";
    $weaponsText = !empty($details['weapons']) ? implode(", ", $details['weapons']) : "";
    $selectedZombies = $details['zombie_types'];
    $observation = $details['observation'] ?? "";


} elseif (isset($_POST['update_type'])) {
    $occupationId = isset($_POST['occupation_id']) ? (int)$_POST['occupation_id'] : 0;
    $postId = $_POST["post_id"] ?? "";
    $occupationType = $_POST["occupation_type"] ?? "";
    $characterName = trim($_POST["character_name"] ?? "");
    $weaponsText = trim($_POST["weapons"] ?? "");
    $selectedZombies = isset($_POST["zombie_types"]) ? (array)$_POST["zombie_types"] : [];
    $observation = trim($_POST["observation"] ?? "");

    if ($occupationId <= 0) {
        header("Location: index.php");
        exit();
    }

} else {
    
    $occupationId = isset($_POST['occupation_id']) ? (int)$_POST['occupation_id'] : 0;
    $postId = $_POST["post_id"] ?? "";
    $occupationType = $_POST["occupation_type"] ?? "";
    $characterName = trim($_POST["character_name"] ?? "");
    $weaponsText = trim($_POST["weapons"] ?? "");
    $selectedZombies = isset($_POST["zombie_types"]) ? (array)$_POST["zombie_types"] : [];
    $observation = trim($_POST["observation"] ?? "");

    if ($occupationId <= 0) {
        header("Location: index.php");
        exit();
    }



    if ($postId === "") {
        addError($errors, "post_id", "Post is required.");
    } else {
        $validPostIds = array_column($posts, 'id_posts');
        if (!in_array($postId, $validPostIds)) {
            addError($errors, "post_id", "Selected post is invalid.");
        }
    }

    if ($occupationType === "") {
        addError($errors, "occupation_type", "Occupation type is required.");
    }


    if ($occupationType === "WLF") {
        if ($characterName === "") {
            addError($errors, "character_name", "Character name is required for WLF occupation.");
        } else {
            try {
                $existingLocation = wlfCharacterExistsInAnotherPost($pdo, $characterName);
                if ($existingLocation !== null) {
                    addError($errors, "character_name", "This character is already assigned to " . $existingLocation . ". Each WLF character can only be in one post.");
                }
            } catch (PDOException $e) {
                addError($errors, "general", "Error checking WLF character: " . $e->getMessage());
            }
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
                    $ratKingCount = countRatKingExcluding($pdo, $occupationId);
                    if ($ratKingCount > 0) {
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
            updateOccupation($pdo, $occupationId, $postId, $occupationType, $characterName, $weaponsArray, $selectedZombies, $observation);

            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            addError($errors, "general", 'Error updating occupation: ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/../views/occupation_form.php';
