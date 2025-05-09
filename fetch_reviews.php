<?php
/**
 * Fetches and displays reviews for a specific user based on filters.
 * Supports sorting and filtering by marketplace.
 */

require_once("functions.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbConn = getConnection();

    $userID = $_POST['userID'] ?? '';
    $sort = $_POST['sort'] ?? 'recent';
    $marketplace = $_POST['marketplace'] ?? 'all';

    // Determine sorting order
    $orderClause = "ORDER BY f.dateWritten DESC";
    if ($sort === 'oldest') {
        $orderClause = "ORDER BY f.dateWritten ASC";
    } elseif ($sort === 'highest') {
        $orderClause = "ORDER BY f.starRating DESC";
    } elseif ($sort === 'lowest') {
        $orderClause = "ORDER BY f.starRating ASC";
    }

    // Filter by marketplace
    $marketplaceClause = "";
    $params = [':userID' => $userID];
    if ($marketplace !== 'all') {
        $marketplaceClause = "AND m.marketplaceName = :marketplace";
        $params[':marketplace'] = $marketplace;
    }

    // Fetch reviews from the database
    $SQL = "SELECT f.*, m.marketplaceName 
            FROM Feedback f 
            LEFT JOIN Marketplace m ON f.marketplaceID = m.marketplaceID 
            WHERE f.userID = :userID $marketplaceClause 
            $orderClause";

    $stmt = $dbConn->prepare($SQL);
    $stmt->execute($params);
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display reviews
    if (!empty($feedback)) {
        foreach ($feedback as $review) {
            echo "<div class='card mb-3 shadow-sm'>\n";
            echo "<div class='card-body'>\n";
            echo "<h3 class='card-title'>" . htmlspecialchars(str_repeat("⭐", $review['starRating']), ENT_QUOTES, 'UTF-8') . "</h3>\n";
            echo "<h4 class='card-subtitle mb-2 text-dark'>From " . htmlspecialchars($review['marketplaceName'], ENT_QUOTES, 'UTF-8') . " on " . date("d M Y", strtotime($review['dateWritten'])) . "</h4>\n";
            echo "<p class='card-text'>" . htmlspecialchars($review['textFeedback'], ENT_QUOTES, 'UTF-8') . "</p>\n";
            echo "<p class='text-dark'><small>By <strong>" . htmlspecialchars($review['writtenBy'], ENT_QUOTES, 'UTF-8') . "</strong></small></p>\n";
            if (!empty($review['Reply'])) {
                echo "<p class='card-text'>Reply: " . htmlspecialchars($review['Reply'], ENT_QUOTES, 'UTF-8') . "</p>\n";
            }

            // Allow the user to reply to reviews
            if (isset($_SESSION['userID']) && $_SESSION['userID'] == $userID && empty($review['Reply'])) {
                echo "<button class='btn btn-accessible' onclick='event.preventDefault(); showReplyBox({$review['feedbackID']})'>Reply</button>\n";
                echo "<div id='reply-box-{$review['feedbackID']}' style='display:none; margin-top:10px;'>\n";
                echo "    <textarea id='reply-text-{$review['feedbackID']}' class='form-control' rows='3' placeholder='Write your reply...'></textarea>\n";
                echo "    <button class='btn btn-success mt-2' onclick='submitReply({$review['feedbackID']})'>Submit Reply</button>\n";
                echo "</div>\n";
            }
            echo "</div>\n";
            echo "</div>\n";
        }
    } else {
        echo "<p>No reviews available for the selected filters.</p>\n";
    }
}
?>
