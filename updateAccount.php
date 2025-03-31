<?php
/**
 * Handles updating user account details.
 * Validates input, checks the current password, and updates the database.
 */

require_once "functions.php";
session_start();
loggedIn();

$userID = $_SESSION['userID'];

// Retrieve form input
$username = trim($_POST['Username']);
$firstName = trim($_POST['FirstName']);
$lastName = trim($_POST['LastName']);
$email = trim($_POST['Email']);
$dateOfBirth = trim($_POST['DateOfBirth']);
$newPassword = trim($_POST['NewPassword']);
$currentPassword = trim($_POST['CurrentPassword']);

// Validate required fields
if (empty($username) || empty($firstName) || empty($lastName) || empty($email) || empty($currentPassword)) {
    $_SESSION['error'] = "All fields except 'New Password' are required.";
    header("Location: manageAccount.php");
    exit();
}

$dbConn = getConnection();

// Verify the current password
$SQL = "SELECT HashedPassword FROM Users WHERE userID = :userID";
$stmt = $dbConn->prepare($SQL);
$stmt->execute([':userID' => $userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($currentPassword, $user['HashedPassword'])) {
    $_SESSION['error'] = "Incorrect password.";
    header("Location: manageAccount.php");
    exit();
}

// Update account details
if (!empty($newPassword)) {
    // Update with a new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $SQL = "UPDATE Users SET Username = :username, FirstName = :firstName, Surname = :lastName, Email = :email, DateOfBirth = :dateOfBirth, HashedPassword = :hashedPassword WHERE userID = :userID";
    $stmt = $dbConn->prepare($SQL);
    $stmt->execute([':username' => $username, ':firstName' => $firstName, ':lastName' => $lastName, ':email' => $email, ':dateOfBirth' => $dateOfBirth, ':hashedPassword' => $hashedPassword, ':userID' => $userID]);
} else {
    // Update without a new password
    $SQL = "UPDATE Users SET Username = :username, FirstName = :firstName, Surname = :lastName, Email = :email, DateOfBirth = :dateOfBirth WHERE userID = :userID";
    $stmt = $dbConn->prepare($SQL);
    $stmt->execute([':username' => $username, ':firstName' => $firstName, ':lastName' => $lastName, ':email' => $email, ':dateOfBirth' => $dateOfBirth, ':userID' => $userID]);
}

// Provide feedback to the user
if ($stmt->rowCount()) {
    $_SESSION['success'] = "Account updated successfully.";
    header("Location: manageAccount.php");
    exit();
} else {
    $_SESSION['error'] = "Account could not be updated.";
    header("Location: manageAccount.php");
    exit();
}
?>