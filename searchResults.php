<?php
require_once("functions.php");
echo makePageStart("Search Results");
echo makeNavBar();

echo "<div class='container'>";
echo "<h2>Search Results</h2>";

if (isset($_GET['account']) && !empty(trim($_GET['account']))) {
    $searchTerm = trim($_GET['account']);

    try {
        $dbConn = getConnection();
        $SQL = "SELECT 
                    u.userID,
                    u.username,
                    ur.totalReviews,
                    ur.averageRating,
                    um.marketplaceUsername,
                    m.marketplaceName
                FROM Users u
                LEFT JOIN ( 
                    SELECT userID, totalReviews, averageRating
                    FROM userReputation
                    WHERE ReputationID IN (
                        SELECT MAX(ReputationID) 
                        FROM userReputation 
                        GROUP BY userID
                    )
                ) ur ON u.userID = ur.userID
                LEFT JOIN userMarketplace um ON u.userID = um.userID
                LEFT JOIN Marketplace m ON um.marketplaceID = m.marketplaceID
                WHERE u.username LIKE :searchTerm
                OR um.marketplaceUsername LIKE :searchTerm";
        
        $stmt = $dbConn->prepare($SQL);
        $stmt->execute([':searchTerm' => "%$searchTerm%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            $users = [];
            foreach ($results as $user) {
                $userID = $user['userID'];
                if (!isset($users[$userID])) {
                    $users[$userID] = [
                        'username' => $user['username'],
                        'totalReviews' => $user['totalReviews'] ?? 0,
                        'averageRating' => $user['averageRating'] ?? "N/A",
                        'marketplaces' => []
                    ];
                }
                if ($user['marketplaceUsername'] && $user['marketplaceName'] && stripos($user['marketplaceUsername'], $searchTerm) !== false) {
                    $users[$userID]['marketplaces'][] = [
                        'marketplaceUsername' => $user['marketplaceUsername'],
                        'marketplaceName' => $user['marketplaceName']
                    ];
                }
            }

            echo "<ul class='list-group'>";
            foreach ($users as $user) {
                echo "<li class='list-group-item'>
                        <a href='profile.php?user={$user['username']}' class='d-block text-decoration-none'>
                        <strong class='text-dark'>{$user['username']}</strong>
                        <br>
                        <span class='text-dark'>Reviews: {$user['totalReviews']} | Average Rating: {$user['averageRating']}</span>";

                if (!empty($user['marketplaces'])) {
                    echo "<br><span class='text-dark'>Marketplaces:</span>";
                    foreach ($user['marketplaces'] as $marketplace) {
                        echo "<br><span class='text-dark'>Username: {$marketplace['marketplaceUsername']} | Marketplace: {$marketplace['marketplaceName']}</span>";
                    }
                }

                echo "</a>
                      </li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No results found for <strong>$searchTerm</strong></p>";
        }

    } catch (Exception $e) {
        echo "<p class='text-danger'>Error fetching search results: " . $e->getMessage() . "</p>";
    }

} else {
    echo "<p class='text-danger'>No search term provided</p>";
}

echo "</div>";

echo makeFooter();
echo makePageEnd();
?>