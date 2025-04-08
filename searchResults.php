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

        // Fetch unique users matching the search term
        $SQL = "SELECT DISTINCT u.userID, u.username
                FROM Users u
                LEFT JOIN userMarketplace um ON u.userID = um.userID
                WHERE u.username LIKE :searchTerm OR um.marketplaceUsername LIKE :searchTerm";
        $stmt = $dbConn->prepare($SQL);
        $stmt->execute([':searchTerm' => "%$searchTerm%"]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users) {
            $userResults = [];
            foreach ($users as $user) {
                $userID = $user['userID'];

                // Fetch totalReviews and averageRating for the user
                $reputationSQL = "SELECT totalReviews, averageRating
                                  FROM userReputation
                                  WHERE userID = :userID
                                  ORDER BY reputationID DESC
                                  LIMIT 1";
                $reputationStmt = $dbConn->prepare($reputationSQL);
                $reputationStmt->execute([':userID' => $userID]);
                $reputation = $reputationStmt->fetch(PDO::FETCH_ASSOC);

                // Fetch linked marketplace accounts for the user
                $marketplaceSQL = "SELECT um.marketplaceUsername, m.marketplaceName
                                   FROM userMarketplace um
                                   LEFT JOIN Marketplace m ON um.marketplaceID = m.marketplaceID
                                   WHERE um.userID = :userID";
                $marketplaceStmt = $dbConn->prepare($marketplaceSQL);
                $marketplaceStmt->execute([':userID' => $userID]);
                $marketplaces = $marketplaceStmt->fetchAll(PDO::FETCH_ASSOC);

                // Add user details to the results
                $userResults[] = [
                    'username' => htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'),
                    'totalReviews' => htmlspecialchars($reputation['totalReviews'] ?? 0, ENT_QUOTES, 'UTF-8'),
                    'averageRating' => htmlspecialchars($reputation['averageRating'] ?? "N/A", ENT_QUOTES, 'UTF-8'),
                    'marketplaces' => array_map(function ($marketplace) {
                        return [
                            'marketplaceUsername' => htmlspecialchars($marketplace['marketplaceUsername'], ENT_QUOTES, 'UTF-8'),
                            'marketplaceName' => htmlspecialchars($marketplace['marketplaceName'], ENT_QUOTES, 'UTF-8')
                        ];
                    }, $marketplaces)
                ];
            }

            // Display sanitized search results
            echo "<p>Search results for <strong>" . htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') . "</strong></p>";
            echo "<p>Found <strong>" . count($userResults) . "</strong> results</p>";
            echo "<p> Please note that if a user has one name on one marketplace, it does not mean they have the same name on another marketplace!</p>";

            echo "<ul class='list-group'>";
            foreach ($userResults as $user) {
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