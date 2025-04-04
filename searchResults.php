<?php
/**
 * Displays search results for user accounts.
 * Searches by username or linked marketplace usernames.
 */

require_once("functions.php");
echo makePageStart("Search Results");
echo makeNavBar();

echo "<div class='container'>";
echo "<h2>Search Results</h2>";

// Check if the 'account' parameter is provided and not empty
if (isset($_GET['account']) && !empty(trim($_GET['account']))) {
    // Sanitize the search term to prevent SQL injection
    $searchTerm = htmlspecialchars(trim($_GET['account']), ENT_QUOTES, 'UTF-8');

    try {
        $dbConn = getConnection();

        // Fetch search results with a prepared statement to prevent SQL injection
        $SQL = "SELECT u.userID, u.username, ur.totalReviews, ur.averageRating, um.marketplaceUsername, m.marketplaceName
                FROM Users u
                LEFT JOIN userReputation ur ON u.userID = ur.userID
                LEFT JOIN userMarketplace um ON u.userID = um.userID
                LEFT JOIN Marketplace m ON um.marketplaceID = m.marketplaceID
                WHERE u.username LIKE :searchTerm OR um.marketplaceUsername LIKE :searchTerm";
        $stmt = $dbConn->prepare($SQL);
        $stmt->execute([':searchTerm' => "%$searchTerm%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            $users = [];
            foreach ($results as $user) {
                $userID = $user['userID'];
                if (!isset($users[$userID])) {
                    $users[$userID] = [
                        'username' => htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'),
                        'totalReviews' => htmlspecialchars($user['totalReviews'] ?? 0, ENT_QUOTES, 'UTF-8'),
                        'averageRating' => htmlspecialchars($user['averageRating'] ?? "N/A", ENT_QUOTES, 'UTF-8'),
                        'marketplaces' => []
                    ];
                }
                if ($user['marketplaceUsername'] && $user['marketplaceName'] && stripos($user['marketplaceUsername'], $searchTerm) !== false) {
                    $users[$userID]['marketplaces'][] = [
                        'marketplaceUsername' => htmlspecialchars($user['marketplaceUsername'], ENT_QUOTES, 'UTF-8'),
                        'marketplaceName' => htmlspecialchars($user['marketplaceName'], ENT_QUOTES, 'UTF-8')
                    ];
                }
            }

            // Display sanitized search results
            echo "<p>Search results for <strong>" . htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') . "</strong></p>";
            echo "<p>Found <strong>" . count($users) . "</strong> results</p>";
            echo "<p> Please note that if a user has one name on one marketplace, it does not mean they have the same name on another marketplace!</p>";

            echo "<ul class='list-group'>";
            foreach ($users as $user) {
                echo "<li class='list-group-item'>
                        <a href='profile.php?user={$user['username']}' class='d-block text-decoration-none'>
                        <strong class='text-dark'>" . $user['username'] . "</strong>
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
            echo "<p>No results found for <strong>" . htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') . "</strong></p>";
        }

    } catch (Exception $e) {
        // Sanitize the error message to prevent XSS
        echo "<p class='text-danger'>Error fetching search results: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    }

} else {
    echo "<p class='text-danger'>No search term provided</p>";
}

echo "</div>";

echo makeFooter();
echo makePageEnd();
?>