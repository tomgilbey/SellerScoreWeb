<?php
/**
 * @autho Tom Gilbey
 */
require_once("functions.php");
echo makePageStart("Manage your Account");
echo makeNavBar();
loggedIn();

if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}

$userID = $_SESSION['userID'];

try {
    $dbConn = getConnection();
    $SQL = "SELECT * FROM Users WHERE userID = :userID";
    $stmt = $dbConn->prepare($SQL);
    $stmt->execute([':userID' => $userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $marketplaceSQL = "SELECT * FROM userMarketplace WHERE userID = :userID";
    $stmt = $dbConn->prepare($marketplaceSQL);
    $stmt->execute([':userID' => $userID]);
    $marketplaceLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $marketplacesSQL = "SELECT * FROM Marketplace";
    $stmt = $dbConn->prepare($marketplacesSQL);
    $stmt->execute();
    $marketplaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<div class='container-fluid text-center mb-5'>\n";
echo "<h1 class='mb-4'>Manage your Account</h1>\n";
echo "<p>Here you can manage your account details and settings. If you wish to update your details simply edit them on the left side of the page and enter your current password and submit!</p>\n";
echo "<p>If you wish to link a marketplace account, select the marketplace from the dropdown on the right side of the page and enter your username for that marketplace and click 'Link Account'. We do the rest!</p>\n";
echo "<div class='row justify-content-center'>\n"; // Open row

// LEFT SIDE: Account Information
echo "<div class='col-md-4'>\n";
echo "<div class='card shadow'>\n";
echo "<div class='card-body'>\n";
echo "<h3 class='card-title text-center mb-3'>Account Information</h3>\n";
echo "<form action='updateAccount.php' method='post'>\n";

// Username Field
echo "<div class='mb-3'>\n";
echo "<label for='Username' class='form-label'>Username:</label>\n";
echo "<input type='text' class='form-control' id='Username' name='Username' value='" . htmlspecialchars($user['Username']) . "' required>\n";
echo "</div>\n";

// First Name Field
echo "<div class='mb-3'>\n";
echo "<label for='FirstName' class='form-label'>First Name:</label>\n";
echo "<input type='text' class='form-control' id='FirstName' name='FirstName' value='" . htmlspecialchars($user['FirstName']) . "' required>\n";
echo "</div>\n";

// Last Name Field
echo "<div class='mb-3'>\n";
echo "<label for='LastName' class='form-label'>Last Name:</label>\n";
echo "<input type='text' class='form-control' id='LastName' name='LastName' value='" . htmlspecialchars($user['Surname']) . "' required>\n";
echo "</div>\n";

// Email Field
echo "<div class='mb-3'>\n";
echo "<label for='Email' class='form-label'>Email:</label>\n";
echo "<input type='email' class='form-control' id='Email' name='Email' value='" . htmlspecialchars($user['Email']) . "' required>\n";
echo "</div>\n";

// Date of Birth Field
echo "<div class='mb-3'>\n";
echo "<label for='DateOfBirth' class='form-label'>Date of Birth:</label>\n";
echo "<input type='date' class='form-control' id='DateOfBirth' name='DateOfBirth' value='" . htmlspecialchars($user['DateOfBirth']) . "'>\n";
echo "</div>\n";

// New Password Field
echo "<div class='mb-3'>\n";
echo "<label for='NewPassword' class='form-label'>New Password:</label>\n";
echo "<input type='password' class='form-control' id='NewPassword' name='NewPassword'>\n";
echo "</div>\n";

// Confirm Current Password Field
echo "<div class='mb-3'>\n";
echo "<label for='CurrentPassword' class='form-label'>Confirm Current Password:</label>\n";
echo "<input type='password' class='form-control' id='CurrentPassword' name='CurrentPassword' required>\n";
echo "</div>\n";

// Submit Button
echo "<button type='submit' class='btn btn-primary w-100'>Update Account</button>\n";

echo "</form>\n";
echo "</div>\n"; // Close card-body
echo "</div>\n"; // Close card
echo "</div>\n"; // Close col-md-6

foreach ($marketplaces as $marketplace) {
    $marketplaceNames[$marketplace['marketplaceID']] = $marketplace['marketplaceName'];
}

// RIGHT SIDE: Marketplace Account Linking
echo "<div class='col-md-5'>\n"; 
echo "<div class='card shadow'>\n";
echo "<div class='card-body'>\n";
echo "<h3 class='card-title text-center mb-3'>Marketplace Accounts</h3>\n";

// Form to Link Marketplace Account
echo "<form action='addMarketplaceAccount.php' method='post'>\n";
echo "<div class='mb-3'>\n";
echo "<label for='MarketplaceID' class='form-label'>Select Marketplace:</label>\n";
echo "<select class='form-control' id='MarketplaceID' name='MarketplaceID' required>\n";
echo "<option value='1'>Amazon</option>\n";
echo "<option value='2'>eBay</option>\n";
echo "<option value='3'>Vinted</option>\n";
echo "<option value='4'>Etsy</option>\n";
echo "</select>\n";
echo "</div>\n";
echo "<div class='mb-3'>\n";
echo "<label for='MarketplaceUsername' class='form-label'>Marketplace Username:</label>\n";
echo "<input type='text' class='form-control' id='MarketplaceUsername' name='MarketplaceUsername' required>\n";
echo "</div>\n";
echo "<button type='submit' class='btn btn-success w-100'>Link Account</button>\n";
echo "</form>\n";

// Display Existing Linked Accounts
if (!empty($marketplaceLinks)) {
    echo "<h4 class='mt-4'>Linked Accounts</h4>\n";
    echo "<table class='table table-bordered'>\n";
    echo "<thead>\n";
    echo "<tr>\n";
    echo "<th>Marketplace</th>\n";
    echo "<th>Username</th>\n";
    echo "<th>Action</th>\n";
    echo "</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";

    foreach ($marketplaceLinks as $link) {
        $marketplaceName = $marketplaceNames[$link['marketplaceID']] ?? "Unknown";

        echo "<tr>\n";
        echo "<td>" . htmlspecialchars($marketplaceName) . "</td>\n";
        echo "<td>" . htmlspecialchars($link['marketplaceUsername']) . "</td>\n";
        echo "<td>\n";
        echo "<form action='removeMarketplaceAccount.php' method='post' style='display:inline;'>\n";
        echo "<input type='hidden' name='marketplaceID' value='" . htmlspecialchars($link['marketplaceID']) . "'>\n";
        echo "<button type='submit' class='btn btn-danger btn-sm'>Unlink</button>\n";
        echo "</form>\n";
        echo "</td>\n";
        echo "</tr>\n";
    }

    echo "</tbody>\n";
    echo "</table>\n";
} else {
    echo "<p class='text-muted'>No linked marketplace accounts.</p>\n";
}

echo "</div>\n"; // Close card-body
echo "</div>\n"; // Close card
echo "</div>\n"; // Close col-md-6

echo "</div>\n"; // Close row
echo "</div>\n"; // Close container

echo makeFooter();
echo makePageEnd();
?>