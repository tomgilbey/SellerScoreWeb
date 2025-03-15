<?php
/**
 * @autho Tom Gilbey
 */
require_once("functions.php");
echo makePageStart("Home Page");
echo makeNavBar();
?>
<div class='container text-center'>
    <h1>SellerScore</h1>
    <form class="form-inline d-flex justify-content-center" action="searchResults.php" method="GET">
        <div class="input-group" style="width: 100%;">
            <input class="form-control" type="text" name="account" id="searchInput" 
            placeholder="Account Search" aria-label="Search" autocomplete="off" style="height: 70px;">
            <div class="input-group-append">
                <button class="btn btn-outline-success" type="submit" style="height: 70px;">Search</button>
            </div>
        </div>
    </form>
    <div id="searchResults" class="list-group position-absolute text-center" style="width: 40%; left: 50%; transform: translateX(-50%); z-index: 1000;"></div>
</div>

<div class="container-fluid mt-5">
    <h2 class="text-center">Getting Started!</h2>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">For Sellers</h5>
                    <p class="card-text font-weight-bold">
                        Start by creating a new account.<br>
                        <img src="images/Seller1.png" alt="Seller Step 1" class="img-fluid mb-3" style="max-width: 75%; height: auto;"><br>
                        Then, log in with your account.<br>
                        <img src="images/Seller2.png" alt="Seller Step 2" class="img-fluid mb-3" style="max-width: 75%; height: auto;"><br>
                        Head over to the manage account section and link your marketplace accounts with the relative username!<br>
                        <img src="images/Seller3.png" alt="Seller Step 3" class="img-fluid mb-3" style="max-width: 75%; height: auto;"><br>
                        When you have linked your accounts, we do the rest!<br>
                        <img src="images/Seller4.png" alt="Seller Step 4" class="img-fluid mb-3" style="max-width: 75%; height: auto;">
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">For Buyers</h5>
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
echo makeFooter();
echo makePageEnd();
?>