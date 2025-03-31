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
                <button class="btn btn-success" type="submit" style="height: 70px; color: white; background-color: #116400; border-color: #116400;">
                    <strong>Search</strong>
                </button>
            </div>
        </div>
    </form>
    <div id="searchResults" class="list-group position-absolute text-center" style="width: 40%; left: 50%; transform: translateX(-50%); z-index: 1000;"></div>
</div>

<div class="container text-center mt-3">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <a href="index.php" class="btn btn-dark btn-lg btn-block">Not a seller? Head over to the buyer page here!</a>
        </div>
    </div>
</div>

<div class="container-fluid mt-5">
    <h2 class="text-center">Getting Started!</h2>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <h3 class="card-title">For Sellers</h3>
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
    </div> 
</div> 

<?php
echo makeFooter();
echo makePageEnd();
?>