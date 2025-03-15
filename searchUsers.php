<?php
require_once("functions.php"); // Include DB connection

if (isset($_GET['q']) && strlen($_GET['q']) >= 3) { // Check if account is set and at least 3 characters long
    $account = trim($_GET['q']); // Trim whitespace from account for security reasons

    try {
        $dbConn = getConnection();
        $sql = "SELECT Username FROM Users WHERE Username LIKE :account LIMIT 5"; // Limit results to 5
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':account' => $account . '%']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users) {
            foreach ($users as $user) { // Output results as list items
                echo '<a href="profile.php?user=' . urlencode($user['Username']) . 
                     '" class="list-group-item list-group-item-action text-center">' . 
                     htmlspecialchars($user['Username']) . '</a>'; // Output username
            }
        } else {
            echo '<div class="list-group-item text-center">No results found</div>'; // No results found
        }
    } catch (Exception $e) {
        echo '<div class="list-group-item text-danger text-center">Error: ' . $e->getMessage() . '</div>'; // Error message
    }
}
?>
