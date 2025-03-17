<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET"); // Allow only POST and GET requests

session_start();
$api_key = "1838VglmBNZM"; // API Key for authentication
$provided_key = $_GET['api_key'] ?? '';

if ($provided_key !== $api_key) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

try {
    $connection = new PDO("mysql:host=nuwebspace_db;dbname=w22002938", "w22002938", "exJbLChc");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $ip = $_SERVER['REMOTE_ADDR'];
    if (!isset($_SESSION[$ip])) {
        $_SESSION[$ip] = ['count' => 1, 'start' => time()];
    } else {
        $_SESSION[$ip]['count']++;
    }
    if ($_SESSION[$ip]['count'] > 10 && (time() - $_SESSION[$ip]['start'] < 60)) {
        echo json_encode(["error" => "Too many requests. Try again later."]);
        exit;
    }

    // Handle different API requests
    $action = $_GET['action'] ?? '';
    $userID = $_GET['userID'] ?? '';

    // Update Feedback
    if ($action == "updateFeedback" && $_SERVER['REQUEST_METHOD'] == "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        // Log received data for debugging
        error_log(print_r($data, true));

        // Check if $data is an array and has entries
        if (!is_array($data) || empty($data)) {
            echo json_encode(["error" => "Invalid or empty input data"]);
            exit;
        }

        foreach ($data as $entry) {
            // Ensure all required fields are present
            if (!isset($entry['userID']) || !isset($entry['feedback']) || !isset($entry['writtenBy']) || !isset($entry['feedbackOriginID'])) {
                echo json_encode(["error" => "Missing required fields in the request"]);
                exit;
            }

            // Validate Inputs
            $entry['starRating'] = filter_var($entry['starRating'], FILTER_VALIDATE_FLOAT);
            $entry['userID'] = filter_var($entry['userID'], FILTER_VALIDATE_INT);
            $entry['feedbackOriginID'] = filter_var($entry['feedbackOriginID'], FILTER_VALIDATE_INT);
            $entry['dateWritten'] = date("Y-m-d H:i:s", strtotime($entry['dateWritten']));

            if ($entry['userID'] === false || empty($entry['feedback']) || empty($entry['writtenBy']) || $entry['feedbackOriginID'] === false) {
                echo json_encode(["error" => "Invalid input data"]);
                exit;
            }

            // Insert feedback into the Feedback table
            $stmt = $connection->prepare("INSERT INTO Feedback (userID, starRating, textFeedback, marketplaceID, writtenBy, dateWritten) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$entry['userID'], $entry['starRating'], $entry['feedback'], $entry['feedbackOriginID'], $entry['writtenBy'], $entry['dateWritten']]);

            // Update lastRetrieval field in userMarketplace table
            $updateStmt = $connection->prepare("UPDATE userMarketplace SET lastRetrieval = NOW() WHERE userID = ? AND marketplaceID = ?");
            $updateStmt->execute([$entry['userID'], $entry['feedbackOriginID']]);
        }

        echo json_encode(["success" => true, "message" => "Feedback added and lastRetrieval updated!"]);
    }

    // Retrieve User Marketplace Data
    elseif ($action == "getNewUserMarketplace" && $_SERVER['REQUEST_METHOD'] == "GET") {
        $stmt = $connection->prepare("SELECT * FROM userMarketplace WHERE lastRetrieval IS NULL");
        $stmt->execute();
        $marketplaceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($marketplaceData);
    }

    elseif ($action == "getAllUserMarketplaceLinks" && $_SERVER['REQUEST_METHOD'] == "GET") {
        $stmt = $connection->prepare("SELECT * FROM userMarketplace");
        $stmt->execute();
        $marketplaceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($marketplaceData);
    }

    elseif ($action == "getUserIDs" && $_SERVER['REQUEST_METHOD'] == "GET") {
        $stmt = $connection->prepare("SELECT userID FROM Users");
        $stmt->execute();
        $userIDs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($userIDs);
    }

    elseif ($action == "getUserRatings" && $_SERVER['REQUEST_METHOD'] == "GET") {
        $stmt = $connection->prepare("SELECT starRating from Feedback WHERE userID = ?");
        $stmt->execute([$userID]);
        $userRatings = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($userRatings);
    }

    elseif ($action == "addReputations" && $_SERVER['REQUEST_METHOD'] == "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        echo json_encode(["received_data" => $data]);
        // Log received data for debugging
        error_log(print_r($data, true));

        // Check if $data is an array and has entries
        if (!is_array($data) || empty($data)) {
            echo json_encode(["error" => "Invalid or empty input data"]);
            exit;
        }

        foreach ($data as $entry) {
            // Ensure all required fields are present
            if (!isset($entry['UserID']) || !isset($entry['TotalReviews']) || !isset($entry['AverageRating'])) {
                echo json_encode(["error" => "Missing required fields in the request"]);
                exit;
            }

            // Validate Inputs using filter_var directly on $entry
            $entry['UserID'] = filter_var($entry['UserID'], FILTER_VALIDATE_INT);
            $entry['AverageRating'] = filter_var($entry['AverageRating'], FILTER_VALIDATE_FLOAT);
            $entry['TotalReviews'] = filter_var($entry['TotalReviews'], FILTER_VALIDATE_INT);

            // Check for invalid input values
            if ($entry['UserID'] === false || $entry['AverageRating'] === false || $entry['TotalReviews'] === false) {
                echo json_encode(["error" => "Invalid input data"]);
                exit;
            }

            // Insert reputation into the userReputation table
            $stmt = $connection->prepare("INSERT INTO userReputation (userID, averageRating, totalReviews, updated) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$entry['UserID'], $entry['AverageRating'], $entry['TotalReviews']]);
        }

        echo json_encode(["success" => true, "message" => "User reputations added!"]);
    }

    elseif ($action == "getUserReputation" && $_SERVER['REQUEST_METHOD'] == "GET") {
        $stmt = $connection->prepare("SELECT * FROM userReputation where UserID = ? ORDER BY ReputationID DESC LIMIT 1");
        $stmt->execute([$userID]);
        $reputation = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($reputation);
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
