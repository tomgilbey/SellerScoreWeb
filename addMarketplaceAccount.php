<?php
require_once "functions.php";
session_start();
loggedIn();

$userID = $_SESSION['userID'];
$marketplaceID = trim($_POST['MarketplaceID']);
$marketplaceUsername = trim($_POST['MarketplaceUsername']);

if (empty($marketplaceID) || empty($marketplaceUsername)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: manageAccount.php");
    exit();
}

$dbConn = getConnection();
$SQL = "SELECT * FROM userMarketplace WHERE userID = :userID AND marketplaceID = :marketplaceID";
$stmt = $dbConn->prepare($SQL);
$stmt->execute([':userID' => $userID, ':marketplaceID' => $marketplaceID]);
$marketplace = $stmt->fetch(PDO::FETCH_ASSOC);

if ($marketplace) {
    $_SESSION['error'] = "You have already added this marketplace.";
    header("Location: manageAccount.php");
    exit();
}

$SQL = "INSERT INTO userMarketplace (userID, marketplaceID, marketplaceUsername) VALUES (:userID, :marketplaceID, :marketplaceUsername)";
$stmt = $dbConn->prepare($SQL);
$stmt->execute([':userID' => $userID, ':marketplaceID' => $marketplaceID, ':marketplaceUsername' => $marketplaceUsername]);

if ($stmt->rowCount())
{
    $_SESSION['success'] = "Marketplace account added successfully.";
    header("Location: manageAccount.php");
    exit();
}
else
{
    $_SESSION['error'] = "Marketplace account could not be added.";
    header("Location: manageAccount.php");
    exit();
}