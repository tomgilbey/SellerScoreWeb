<?php
require_once("functions.php");
echo makePageStart("Profile");
echo makeNavBar();

$userHasReputation = false;

if (isset($_GET['user']) && !empty(trim($_GET['user']))) {
    $username = trim($_GET['user']);

    try {
        $dbConn = getConnection();
        $SQL = "SELECT userID, Username, joinDate, verifiedSeller FROM Users WHERE Username = :username";
        $stmt = $dbConn->prepare($SQL);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "<div class='container text-center mb-5'>";
            echo "<h2 class='mb-4'>Account: $username does not exist, please try again!</h2>";
            echo "</div>";
        } else {
            echo "<div class='container-fluid text-center mb-5'>";
            if (isset($_SESSION['userID']) && $_SESSION['userID'] === $user['userID']) {
                echo "<div class='alert alert-info' role='alert'>";
                echo "<h2 class='mb-4'>Your Profile</h2>";
                echo "</div>";
            } else {
                echo "<h2 class='mb-4'>$username's Reputation</h2>";
            }

            echo "<div class='row justify-content-center'>";
            echo "<div class='col-md-5'>";
            echo "<p><strong>Member Since: </strong>" . date("F Y", strtotime($user['joinDate'])) . "</p>";

            $userID = $user['userID'];

            $SQL = "SELECT totalReviews, averageRating, updated
                    FROM userReputation
                    WHERE userID = :userID
                    ORDER BY reputationID DESC
                    LIMIT 10";
            $stmt = $dbConn->prepare($SQL);
            $stmt->execute([':userID' => $userID]);
            $reputations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($reputations)) {
                $userHasReputation = true;
                $latestRep = $reputations[0];

                echo "<div class='card mb-4'>";
                echo "<div class='card-body'>";

                echo "<div class='row mb-3'>";
                echo "<div class='col-md-6'>";
                echo "<p><strong>Last Updated: </strong>" . date("d F Y", strtotime($latestRep['updated'])) . "</p>";
                echo "</div>";

                echo "<div class='col-md-6'>";
                echo "<p><strong>Average Rating: </strong>" . $latestRep['averageRating'] . "⭐</p>";
                echo "</div>";
                echo "</div>";

                echo "<div class='row mb-3'>";
                echo "<div class='col-md-6'>";
                echo "<p><strong>Total Reviews: </strong>" . $latestRep['totalReviews'] . "</p>";
                echo "</div>";

                $verified = $user['verifiedSeller'] ? "Yes" : "No";
                echo "<div class='col-md-6'>";
                if ($user['verifiedSeller']) {
                    echo "<p><strong>Verified Seller: </strong>Yes ✔</p>";
                } else {
                    echo "<p><strong>Verified Seller: </strong>No ╳</p>";
                }
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";

                if (count($reputations) >= 10) {
                    $latestRep = $reputations[0];
                    $tenthRep = $reputations[9];

                    $ratingChange = $latestRep['averageRating'] - $tenthRep['averageRating'];
                    $changeDisplay = '';

                    if ($ratingChange > 0) {
                        $changeDisplay = "<span class='badge bg-success'>↑ +" . number_format($ratingChange, 1) . "⭐</span>";
                    } elseif ($ratingChange < 0) {
                        $changeDisplay = "<span class='badge bg-danger'>↓ " . number_format(abs($ratingChange), 1) . "⭐</span>";
                    } else {
                        $changeDisplay = "<span class='badge bg-secondary'>→ No Change</span>";
                    }

                    echo "<h3>Reputation Change Over Time</h3>";
                    echo "<div class='row'>";

                    echo "<div class='col-12 col-md-6 mb-3'>";
                    echo "<div class='card'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>Most Recent Reputation</h5>";
                    echo "<p><strong>Average Rating: </strong>" . $latestRep['averageRating'] . "⭐</p>";
                    echo "<p><strong>Total Reviews: </strong>" . $latestRep['totalReviews'] . "</p>";
                    echo "<p><small><strong>Updated: </strong>" . date("d F Y", strtotime($latestRep['updated'])) . "</small></p>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

                    echo "<div class='col-12 col-md-6 mb-3'>";
                    echo "<div class='card'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>10th Last Reputation</h5>";
                    echo "<p><strong>Average Rating: </strong>" . $tenthRep['averageRating'] . "⭐</p>";
                    echo "<p><strong>Total Reviews: </strong>" . $tenthRep['totalReviews'] . "</p>";
                    echo "<p><small><strong>Updated: </strong>" . date("d F Y", strtotime($tenthRep['updated'])) . "</small></p>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

                    echo "</div>";

                    echo "<h4>Reputation Change: </h4>";
                    echo "<p>" . $changeDisplay . "</p>";
                } else {
                    echo "<p>Not enough reputation data available for comparison (requires at least 10 entries).</p>";
                }

                $SQL = "SELECT u.*, m.marketplaceName
                        FROM userMarketplace u
                        LEFT JOIN Marketplace m ON u.marketplaceID = m.marketplaceID
                        WHERE u.userID = :userID";
                $stmt = $dbConn->prepare($SQL);
                $stmt->execute([':userID' => $userID]);
                $marketplaceLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($marketplaceLinks)) {
                    echo "<h2 class='mt-4'>Linked Accounts</h2>";
                    echo "<div class='list-group'>";
                    foreach ($marketplaceLinks as $link) {
                        echo "<div class='list-group-item d-flex justify-content-between align-items-center'>";
                        echo "<span>{$link['marketplaceName']}: {$link['marketplaceUsername']}</span>";
                        echo "<span class='badge bg-secondary'>Active</span>";
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>No marketplace links available for $username</p>";
                }
                echo "</div>";

                echo "<div class='col-md-5'>";
                echo "<h2 class='mt-4'>User Reviews</h2>";
                echo "<form id='reviewFilters' class='mb-3'>";
                echo "<label for='sortReviews' class='form-label'><strong>Sort by:</strong></label>";
                echo "<select id='sortReviews' class='form-select mb-2' name='sort'>";
                echo "<option value='recent'>Most Recent</option>";
                echo "<option value='oldest'>Oldest</option>";
                echo "<option value='highest'>Highest Rating</option>";
                echo "<option value='lowest'>Lowest Rating</option>";
                echo "</select>";

                echo "<label for='marketplaceFilter' class='form-label'><strong>Filter by Marketplace:</strong></label>";
                echo "<select id='marketplaceFilter' class='form-select mb-2' name='marketplace'>";
                echo "<option value='all'>All Marketplaces</option>";
                echo "<option value='ebay'>eBay</option>";
                echo "<option value='amazon'>Amazon</option>";
                echo "<option value='vinted'>Vinted</option>";
                echo "<option value='etsy'>Etsy</option>";
                echo "</select>";

                echo "<button type='submit' class='btn btn-primary'>Apply Filters</button>";
                echo "</form>";

                echo "<div id='reviewsContainer' class='overflow-auto' style='max-height: 400px; padding-right: 10px; margin-bottom:50px'>";
                // Reviews will be loaded here dynamically
                echo "</div>";

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

                        function showReplyBox(feedbackID) {
                            document.getElementById('reply-box-' + feedbackID).style.display = 'block';
                        }

                        function submitReply(feedbackID)
                        {
                            const replyText = document.getElementById('reply-text-' + feedbackID).value;

                            if (replyText.trim() === '')
                            {
                                alert('Please enter a reply before submitting!');
                                return;
                            }
                            
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', 'submit_reply.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                            xhr.onload = function()
                            {
                                if (xhr.status === 200)
                                {
                                    try 
                                    {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.success)
                                        {
                                            alert('Reply submitted successfully!');
                                            document.getElementById('reply-box-' + feedbackID).innerHTML = '<p>Reply: ' + replyText + '</p>';
                                        }
                                        else
                                        {
                                            alert('Error response: ' + response.message);
                                        }
                                    }
                                    catch(e)
                                    {
                                        alert('Error parsing JSON: ' + e.message);
                                    }
                                }
                                else
                                {
                                    alert('Error submitting reply: ' + xhr.status);
                                }
                                    
                            };
                            xhr.send('feedbackID=' + feedbackID + '&reply=' + encodeURIComponent(replyText));
                        }
                        </script>";
            } else {
                echo "<p>No reputation data available for $username</p>";
            }
            echo "</div>"; // Close col-md-5
            echo "</div>"; // Close row justify-content-center
            echo "</div>"; // Close container-fluid
        }
    } catch (Exception $e) {
        echo "<div class='container text-center mb-5'>";
        echo "<p class='text-danger'>Error fetching user reputation: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
} else {
    echo "<div class='container text-center mb-5'>";
    echo "<h2>Please search for a user using the search bar!</h2>";
    echo "</div>";
}

if (!$userHasReputation) {
    echo "<div class='container text-center mb-5'>";
    echo "<p>No reputation data available for this user.</p>";
    echo "</div>";
}

echo makeFooter();
echo makePageEnd();
?>