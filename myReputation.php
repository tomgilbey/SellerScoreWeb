<?php
require_once("functions.php");
echo makePageStart("My Reputation");
loggedIn();
echo makeNavBar();
echo "<div class='container mt-5'>";
echo "<h1>My Reputation</h1>";
echo "<p>Here you can see a more detailed breakdown of your reputation.</p>";

$userID = $_SESSION['userID'];

try
{
    $dbConn = getConnection();
    $reviewSQL = "SELECT starRating, marketplaceID, COUNT(*) as count FROM Feedback WHERE userID = :userID GROUP BY starRating, marketplaceID";
    $stmt = $dbConn->prepare($reviewSQL);
    $stmt->execute([':userID' => $userID]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $marketplaceSQL = "SELECT * FROM Marketplace";
    $stmt = $dbConn->prepare($marketplaceSQL);
    $stmt->execute();
    $marketplaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $reputationSQL = "SELECT averageRating, totalReviews, updated FROM userReputation WHERE userID = :userID ORDER BY updated DESC";
    $stmt = $dbConn->prepare($reputationSQL);
    $stmt->execute([':userID' => $userID]);
    $reputations = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $latestRep = $reputations[0];

    $historyData = array_slice($reputations, 1);

    $starRatings = [1, 2, 3, 4, 5]; 
    $marketplaceNames = array_column($marketplaces, 'marketplaceName', 'marketplaceID');
    $marketplaceIDs = array_keys($marketplaceNames);

    $reviewData = [];
    foreach ($starRatings as $star)
    {
        $reviewData[$star] = array_fill_keys($marketplaceIDs, 0);
        $reviewData[$star]['Total'] = 0;
    }

    foreach ($reviews as $review)
    {
        $marketplaceID = $review['marketplaceID'];
        $count = $review['count'];
        $reviewData[$review['starRating']][$marketplaceID] = $count;
        $reviewData[$review['starRating']]['Total'] += $count;
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
}
catch (Exception $e)
{
    echo "Error: " . $e->getMessage();
}



echo "<h2 class='mt-4'>Review Score Distributions</h2>";
echo "<canvas id='reviewChart' class='mb-4'></canvas>";

echo "<h2 class='mt-4'>Overall Reputation</h2>";
echo "<p>Average Rating: <strong>" . number_format($averageRating, 1) . "</strong></p>";
echo "<p>Total Reviews: <strong>" . $totalReviews . "</strong></p>";

echo "<h2 class='mt-4'>Averages History</h2>";
echo "<div id='reviewsContainer' class='overflow-auto' style='max-height: 400px; padding-right: 10px; margin-bottom:50px'>\n"; // Overflow auto to allow scrolling
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
echo "</div>";
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('reviewChart').getContext('2d');

    const starRatings = <?php echo json_encode($starRatings); ?>;
    const marketplaces = <?php echo json_encode(array_values($marketplaceNames)); ?>;
    const reviewData = <?php echo json_encode($reviewData); ?>;

    const colorPalette = [
    'rgba(0, 0, 255, 0.6)',  
    'rgba(0, 255, 0, 0.6)',  
    'rgba(255, 0, 0, 0.6)',  
    'rgba(255, 165, 0, 0.6)',
];

const borderColorPalette = [
    'rgba(0, 0, 255, 1)',    
    'rgba(0, 255, 0, 1)',    
    'rgba(255, 0, 0, 1)',    
    'rgba(255, 165, 0, 1)',  
];

    const datasets = marketplaces.map((name, index) => {
        const marketplaceID = Object.keys(<?php echo json_encode($marketplaceNames); ?>)[index];
        return {
            label: name,
            data: starRatings.map(star => reviewData[star][marketplaceID] || 0),
            backgroundColor: colorPalette[index] || 'rgba(0, 0, 0, 0.6)',
            borderColor: borderColorPalette[index] || 'rgba(0, 0, 0, 1)',
            borderWidth: 1
        };
    });

    
    datasets.push({
        label: "Total",
        data: starRatings.map(star => reviewData[star]['Total']),
        backgroundColor: 'rgba(61, 58, 58, 0.6)',
        borderColor: 'rgba(0, 0, 0, 1)',
        borderWidth: 1
    });

    new Chart(ctx, {
    type: 'bar',
    data: {
        labels: starRatings.map(star => `${star} Stars`),
        datasets: datasets
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    title: function () {
                        return '';
                    },
                    label: function (tooltipItem) {
                        let datasetLabel = tooltipItem.dataset.label || ''; 
                        let value = tooltipItem.raw || 0; 
                        return `${datasetLabel}: ${value}`;
                    }
                }
            }
        },
        scales: {
            y: {
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