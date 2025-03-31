<?php
/**
 * Displays the homepage for buyers.
 * Allows users to search for accounts and provides instructions for buyers.
 */

require_once("functions.php");

// Generate the page start and navigation bar
echo makePageStart("Home Page");
echo makeNavBar();
?>

<!-- Main content -->
<div class='container text-center'>
    <h1>SellerScore</h1>
    <!-- Search form -->
    <form class="form-inline d-flex justify-content-center" action="searchResults.php" method="GET">
        <div class="input-group" style="width: 100%;">
            <input class="form-control" type="text" name="account" id="searchInput" 
            placeholder="Account Search" aria-label="Search" autocomplete="off" style="height: 70px;">
            <div class="input-group-append">
                <button class="btn btn-success" type="submit" style="height: 70px; color: white; background-color: #116400; border-color: #116400;">
                    <strong>Search</strong>
                </button>
            </div>
        </div>
    </form>
    <!-- Search results container -->
    <div id="searchResults" class="list-group position-absolute text-center" style="width: 40%; left: 50%; transform: translateX(-50%); z-index: 1000;"></div>
</div>

<!-- Link to seller page -->
<div class="container text-center mt-3">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <a href="indexSellers.php" class="btn btn-dark btn-lg btn-block">Not a buyer? Head over to the seller page here!</a>
        </div>
    </div>
</div>

<!-- Instructions for buyers -->
<div class="container-fluid mt-5">
    <h2 class="text-center">Getting Started!</h2>
    <div class="row">
        <div class="col-12">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h3 class="card-title">For Buyers</h3>
                    <p class="card-text font-weight-bold">
                        No need to create an account! Just click on the search bar and search for the user whose reputation you wish to view.<br>
                        <img src="images/Buyer1.png" alt="Buyer Step 1" class="img-fluid mb-3" style="max-width: 75%; height: auto;"><br>
                        This can be their username here or their marketplace account username, profile, the account is linked!<br>
                        <img src="images/Buyer2.png" alt="Buyer Step 2" class="img-fluid mb-3" style="max-width: 75%; height: auto;"><br>
                        When you find the profile you wish to view, just click on the link.<br>
                        <img src="images/Buyer3.png" alt="Buyer Step 3" class="img-fluid mb-3" style="max-width: 75%; height: auto;"><br>
                        Now you can view their reputation.<br>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Generate the footer and page end
echo makeFooter();
echo makePageEnd();
?>