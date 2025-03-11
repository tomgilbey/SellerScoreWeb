<?php
require_once("functions.php");

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
            echo "<h6 class='card-subtitle mb-2 text-muted'>From {$review['marketplaceName']}</h6>\n";      
            echo "<p class='card-text'>{$review['textFeedback']}</p>\n";
            echo "<p class='text-muted'><small>By <strong>{$review['writtenBy']}</strong> on " . date("d M Y", strtotime($review['dateWritten'])) . "</small></p>\n";
            echo "</div>\n";
            echo "</div>\n";
        }
    } else {
        echo "<p>No reviews available for the selected filters.</p>\n";
    }
}
?>
