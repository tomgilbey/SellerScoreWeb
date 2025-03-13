<?php
require_once("functions.php");
echo makePageStart("Search Results");
echo makeNavBar();

echo "<div class='container'>";
echo "<h2>Search Results</h2>";

if (isset($_GET['account']) && !empty(trim($_GET['account'])))
{
    $searchTerm = trim($_GET['account']);

    try {
        $dbConn = getConnection();
        $SQL = "SELECT 
                    u.userID,
                    u.username,
                    ur.totalReviews,
                    ur.averageRating
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
                WHERE u.username LIKE :searchTerm";
        
        $stmt = $dbConn->prepare($SQL);
        $stmt->execute([':searchTerm' => "%$searchTerm%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($results)
        {
            echo "<ul class='list-group'>";
            foreach ($results as $user) {
                $reviewCount = $user['totalReviews'] ?? 0;
                $averageRating = $user['averageRating'] ?? "N/A";
                echo "<li class='list-group-item'>
                        <a href='profile.php?user={$user['username']}' class='d-block text-decoration-none'>
                        <strong>{$user['username']}</strong>
                        <br>
                        <span class='text-muted'>Reviews: $reviewCount | Average Rating: $averageRating</span>
                        </a>
                        </li>";
            }
            echo "</ul>";
        }
        else
        {
            echo "<p>No results found for <strong>$searchTerm</strong></p>";
        }

    } catch (Exception $e) {
        echo "<p class='text-danger'>Error fetching search results: " . $e->getMessage() . "</p>";
    }

}
else
{
    echo "<p class='text-danger'>No search term provided</p>";
}

echo "</div>";


echo makeFooter("This is the footer");
echo makePageEnd();
?>