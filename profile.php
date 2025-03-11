<?php
require_once("functions.php");
echo makePageStart("Home Page");
echo makeNavBar();

echo "<div class='container'>\n";


if (isset($_GET['user']) && !empty(trim($_GET['user'])))
{
    $username = trim($_GET['user']);
    
    try
    {
        $dbConn = getConnection(); 
        $SQL = "SELECT userID, Username, joinDate, verifiedSeller FROM Users WHERE Username = :username";
        $stmt = $dbConn->prepare($SQL);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user)
        {
          echo "<h2 class='mb-4'>Account: $username does not exist, please try again!</h2>\n";
        }
        else
        {
            echo "<div class='container mt-4'>\n";
            echo "<h2 class='mb-4'>$username's Reputation</h2>\n";
            echo "<p><strong>Member Since: </strong>" . date("F Y", strtotime($user['joinDate'])) . "</p>\n";

            $userID = $user['userID'];

            $SQL = "SELECT totalReviews, averageRating, updated
                    FROM userReputation
                    WHERE userID = :userID
                    ORDER BY reputationID DESC
                    LIMIT 10";
            $stmt = $dbConn->prepare($SQL);
            $stmt->execute([':userID' => $userID]);
            $reputations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($reputations))
            {
                $latestRep = $reputations[0];

                echo "<div class='card mb-4'>\n";
                echo "<div class='card-body'>\n";

                echo "<div class='row mb-3'>\n";
                echo "<div class='col-md-6'>\n";
                echo "<p><strong>Last Updated: </strong>" . date("d F Y", strtotime($latestRep['updated'])) . "</p>\n";
                echo "</div>\n";

                echo "<div class='col-md-6'>\n";
                echo "<p><strong>Average Rating: </strong>" . $latestRep['averageRating'] . "⭐</p>\n";
                echo "</div>\n";
                echo "</div>\n";


                echo "<div class='row mb-3'>\n";
                echo "<div class='col-md-6'>\n";
                echo "<p><strong>Total Reviews: </strong>" . $latestRep['totalReviews'] . "</p>\n";
                echo "</div>";
                
                $verified = $user['verifiedSeller'] ? "Yes" : "No";
                echo "<div class='col-md-6'>\n";
                if ($user['verifiedSeller'])
                {
                    echo "<p><strong>Verified Seller: </strong>Yes ✔</p>\n";
                }
                else
                {
                    echo "<p><strong>Verified Seller: </strong>No ╳</p>\n";
                }
                echo "</div>\n";
                echo "</div>\n";
                echo "</div>\n";
                echo "</div>\n";

                if (count($reputations) >= 10) {  
                    $latestRep = $reputations[0];  
                    $tenthRep = $reputations[9];  
                
                    
                    $ratingChange = $latestRep['averageRating'] - $tenthRep['averageRating'];
                    $changeDisplay = '';
                
                    
                    if ($ratingChange > 0) {
                        $changeDisplay = "<span class='badge bg-success'>↑ +" . number_format($ratingChange, 1) . "⭐</span>\n";
                    } elseif ($ratingChange < 0) {
                        $changeDisplay = "<span class='badge bg-danger'>↓ " . number_format(abs($ratingChange), 1) . "⭐</span>\n"; 
                    } else {
                        $changeDisplay = "<span class='badge bg-secondary'>→ No Change</span>\n";
                    }
                
                    
                    echo "<h3>Reputation Change Over Time</h3>\n";
                    echo "<div class='row'>\n";
                    
                    
                    echo "<div class='col-12 col-md-6 mb-3'>\n";
                    echo "<div class='card'>\n";
                    echo "<div class='card-body'>\n";
                    echo "<h5 class='card-title'>Most Recent Reputation</h5>\n";
                    echo "<p><strong>Average Rating: </strong>" . $latestRep['averageRating'] . "⭐</p>\n";
                    echo "<p><strong>Total Reviews: </strong>" . $latestRep['totalReviews'] . "</p>\n";
                    echo "<p><small><strong>Last Updated: </strong>" . date("d F Y", strtotime($latestRep['updated'])) . "</small></p>\n";
                    echo "</div>\n";
                    echo "</div>\n";
                    echo "</div>\n";  
                
                    
                    echo "<div class='col-12 col-md-6 mb-3'>\n";
                    echo "<div class='card'>\n";
                    echo "<div class='card-body'>\n";
                    echo "<h5 class='card-title'>10th Last Reputation</h5>\n";
                    echo "<p><strong>Average Rating: </strong>" . $tenthRep['averageRating'] . "⭐</p>\n";
                    echo "<p><strong>Total Reviews: </strong>" . $tenthRep['totalReviews'] . "</p>\n";
                    echo "<p><small><strong>Last Updated: </strong>" . date("d F Y", strtotime($tenthRep['updated'])) . "</small></p>\n";
                    echo "</div>\n";
                    echo "</div>\n";
                    echo "</div>\n";  
                
                    echo "</div>\n"; 
                
                    
                    echo "<h4>Reputation Change: </h4>\n";
                    echo "<p>" . $changeDisplay . "</p>\n";
                } else {
                    echo "<p>Not enough reputation data available for comparison (requires at least 10 entries).</p>\n";
                }

                $SQL = "SELECT u.*, m.marketplaceName
                    FROM userMarketplace u
                    LEFT JOIN Marketplace m ON u.marketplaceID = m.marketplaceID
                    WHERE u.userID = :userID";
                $stmt = $dbConn->prepare($SQL);
                $stmt->execute([':userID' => $userID]);
                $marketplaceLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($marketplaceLinks))
                {
                    echo "<h2 class='mt-4'>Linked Accounts</h2>\n";
                    echo "<div class='list-group'>\n";
                    foreach ($marketplaceLinks as $link) {
                        echo "<div class='list-group-item d-flex justify-content-between align-items-center'>\n";
                        echo "<span>{$link['marketplaceName']}: {$link['marketplaceUsername']}</span>\n";
                        echo "<span class='badge bg-secondary'>Active</span>\n";
                        echo "</div>\n";
                    }
                    echo "</div>\n";
                }
                else
                {
                    echo "<p>No marketplace links available for $username</p>\n";
                }

                echo "<h2 class='mt-4'>User Reviews</h2>\n";
                echo "<form id='reviewFilters' class='mb-3'>\n";
                echo "<label for='sortReviews' class='form-label'><strong>Sort by:</strong></label>\n";
                echo "<select id='sortReviews' class='form-select mb-2' name='sort'>\n";
                echo "<option value='recent'>Most Recent</option>\n";
                echo "<option value='oldest'>Oldest</option>\n";
                echo "<option value='highest'>Highest Rating</option>\n";
                echo "<option value='lowest'>Lowest Rating</option>\n";
                echo "</select>\n";

                echo "<label for='marketplaceFilter' class='form-label'><strong>Filter by Marketplace:</strong></label>\n";
                echo "<select id='marketplaceFilter' class='form-select mb-2' name='marketplace'>\n";
                echo "<option value='all'>All Marketplaces</option>\n";
                echo "<option value='ebay'>eBay</option>\n";
                echo "<option value='amazon'>Amazon</option>\n";
                echo "<option value='vinted'>Vinted</option>\n";
                echo "<option value='etsy'>Etsy</option>\n";
                echo "</select>\n";


                echo "<button type='submit' class='btn btn-primary'>Apply Filters</button>\n";
                echo "</form>\n";

                echo "<div id='reviewsContainer' class='overflow-auto' style='max-height: 400px; padding-right: 10px; margin-bottom:50px'>\n"; // Overflow auto to allow scrolling
                // Reviews will be loaded here dynamically
                echo "</div>\n";

                echo "<script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const reviewForm = document.getElementById('reviewFilters');
                            const reviewsContainer = document.getElementById('reviewsContainer');

                            function fetchReviews() {
                                const formData = new FormData(reviewForm);
                                formData.append('userID', " . json_encode($userID) . ");

                                fetch('fetch_reviews.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.text())
                                .then(data => {
                                    reviewsContainer.innerHTML = data;
                                })
                                .catch(error => console.error('Error fetching reviews:', error));
                            }

                            reviewForm.addEventListener('submit', function (e) {
                                e.preventDefault(); // Prevent page reload
                                fetchReviews();
                            });

                            fetchReviews(); // Load reviews initially
                        });
                        </script>\n";

                
            }  
            else
            {
                echo "<p>No reputation data available for $username</p>\n";
            }
            echo "</div>\n";
        }
        
        
    }
    catch (Exception $e)
    {
        echo "<p class='text-danger'>Error fetching user reputation: " . $e->getMessage() . "</p>\n";
    }
}
else
{
    echo "<h2>Please search for a user using the search bar!</h2>\n";
}

echo "</div>\n";

echo makeFooter("This is the footer");
echo makePageEnd();
?>