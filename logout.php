<?php
/**
 * Logs the user out by clearing the session and redirecting to the homepage.
 */

session_start();
require_once("functions.php");
logOut();
?>
