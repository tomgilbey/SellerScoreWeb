<?php
require_once("functions.php");
echo makePageStart("Home Page");
loggedIn();
echo makeNavBar();
echo "<div class='container mt-5'>";
echo "<h1>My Breakdown</h1>";
echo "<p>Here you can see a more detailed breakdown of your reputation.</p>";

$userID = $_SESSION['userID'];

try
{
    $dbConn = getConnection();
    $reviewSQL = "SELECT starRating, COUNT(*) as count FROM Feedback WHERE userID = :userID GROUP BY starRating";
    $stmt = $dbConn->prepare($reviewSQL);
    $stmt->execute([':userID' => $userID]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $reputationSQL = "SELECT averageRating, totalReviews, updated FROM userReputation WHERE userID = :userID ORDER BY updated DESC";
    $stmt = $dbConn->prepare($reputationSQL);
    $stmt->execute([':userID' => $userID]);
    $reputations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $latestRep = $reputations[0];

    $historyData = array_slice($reputations, 1);
}
catch (Exception $e)
{
    echo "Error: " . $e->getMessage();
}

$starRatings = [];
$ratingCounts = [];

foreach ($reviews as $review)
{
    $starRatings[] = $review['starRating'];
    $ratingCounts[] = $review['count'];
}

$historyTimestamps = [];
$historyRatings = [];

foreach ($historyData as $history)
{
    $historyTimestamps[] = $history['updated'];
    $historyRatings[] = $history['averageRating'];
}

$averageRating = $latestRep['averageRating'] ?? 0;
$totalReviews = $latestRep['totalReviews'] ?? 0;

echo "<h2 class='mt-4'>Review Score Distributions</h2>";
echo "<canvas id='reviewChart' class='mb-4'></canvas>";

echo "<h2 class='mt-4'>Overall Reputation</h2>";
echo "<p>Average Rating: <strong>" . number_format($averageRating, 1) . "</strong></p>";
echo "<p>Total Reviews: <strong>" . $totalReviews . "</strong></p>";

echo "<h2 class='mt-4'>Averages History</h2>";
echo "<div class='table-responsive'>";
echo "<table class='table table-striped table-bordered'>";
echo "<thead class='thead-dark'><tr><th>Timestamp</th><th>Average Rating</th></tr></thead>";
echo "<tbody>";

foreach ($historyData as $row) {
    echo "<tr><td>" . date("d M Y H:i", strtotime($row['updated'])) . "</td><td>" . number_format($row['averageRating'], 2) . "</td></tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</div>";
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const reviewStarChart = document.getElementById('reviewChart').getContext('2d');
    new Chart(reviewStarChart, 
    {
        type: 'bar',
        data: 
        {
            labels: <?php echo json_encode($starRatings); ?>,
            datasets: [
                {
                label: 'Number of Reviews',
                data: <?php echo json_encode($ratingCounts); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: 
        {
            scales: 
            {
                y: 
                { 
                    beginAtZero: true 
                }
            }
        }
    });    
</script>
<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>