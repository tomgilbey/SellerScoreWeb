<?php
require_once("functions.php");
session_start();

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['userID']))
{
    $feedbackID = $_POST['feedbackID'] ?? '';
    $reply = trim($_POST['reply'] ?? '');

    if (empty($feedbackID) || empty($reply))
    {
        echo "Error: Missing required fields";
        exit;
    }

    try
    {
        $dbConn = getConnection();
        $SQL = "UPDATE Feedback SET Reply = :reply WHERE feedbackID = :feedbackID";
        $stmt = $dbConn->prepare($SQL);
        $stmt->execute([':reply' => $reply, ':feedbackID' => $feedbackID]);

        echo json_encode(['success' => true]);
    }
    catch (Exception $e)
    {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
