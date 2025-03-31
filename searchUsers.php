<?php
/**
 * Provides search suggestions for user accounts.
 * Returns a list of usernames matching the search query.
 */

require_once("functions.php");

if (isset($_GET['q']) && strlen($_GET['q']) >= 3) {
    $account = trim($_GET['q']);

    try {
        $dbConn = getConnection();

        // Fetch matching usernames
        $sql = "SELECT Username FROM Users WHERE Username LIKE :account LIMIT 5";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([':account' => $account . '%']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Display suggestions
        if ($users) {
            foreach ($users as $user) {
                echo '<a href="profile.php?user=' . urlencode($user['Username']) . 
                     '" class="list-group-item list-group-item-action text-center">' . 
                     htmlspecialchars($user['Username']) . '</a>';
            }
        } else {
            echo '<div class="list-group-item text-center">No results found</div>';
        }
    } catch (Exception $e) {
        echo '<div class="list-group-item text-danger text-center">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
