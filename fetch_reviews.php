<?php
require_once("functions.php");
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbConn = getConnection();

    $userID = $_POST['userID'] ?? '';
    $sort = $_POST['sort'] ?? 'recent';
    $marketplace = $_POST['marketplace'] ?? 'all';

    $orderClause = "ORDER BY f.dateWritten DESC";
    if ($sort === 'oldest') {
        $orderClause = "ORDER BY f.dateWritten ASC";
    } elseif ($sort === 'highest') {
        $orderClause = "ORDER BY f.starRating DESC";
    } elseif ($sort === 'lowest') {
        $orderClause = "ORDER BY f.starRating ASC";
    }

    $marketplaceClause = "";
    $params = [':userID' => $userID];

    if ($marketplace !== 'all') {
        $marketplaceClause = "AND m.marketplaceName = :marketplace";
        $params[':marketplace'] = $marketplace;
    }

    $SQL = "SELECT f.*, m.marketplaceName 
            FROM Feedback f 
            LEFT JOIN Marketplace m ON f.marketplaceID = m.marketplaceID 
            WHERE f.userID = :userID $marketplaceClause 
            $orderClause";

    $stmt = $dbConn->prepare($SQL);
    $stmt->execute($params);
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($feedback)) {
        foreach ($feedback as $review) {
            echo "<div class='card mb-3 shadow-sm'>\n";
            echo "<div class='card-body'>\n";
            echo "<h5 class='card-title'>" . str_repeat("⭐", $review['starRating']) . "</h5>\n";
            echo "<h6 class='card-subtitle mb-2 text-dark'>From {$review['marketplaceName']}  on " . date("d M Y", strtotime($review['dateWritten'])) . "</h6>\n";      
            echo "<p class='card-text'>{$review['textFeedback']}</p>\n";
            echo "<p class='text-dark'><small>By <strong>{$review['writtenBy']}</strong></small></p>\n";
            if (!empty($review['Reply'])) {
                echo "<p class= 'card-text'>Reply: {$review['Reply']}</p>\n";            
            }
            
            if (isset($_SESSION['userID']) && $_SESSION['userID'] == $userID && empty($review['Reply'])) {
                echo "<button class='btn btn-primary' onclick='showReplyBox({$review['feedbackID']})'>Reply</button>\n";
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
