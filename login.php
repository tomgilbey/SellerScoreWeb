<?php
/**
 * Handles user login.
 * Validates user credentials and starts a session upon successful login.
 */

require_once("functions.php");
echo makePageStart("Login");
echo makeNavBar();
echo "<div class='container'>";
echo "<div class='box'>";

$errors = [];
$input = ['username' => '', 'password' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate login credentials
    list($input, $errors) = validate_login();

    if (empty($errors)) {
        $_SESSION['logged-in'] = true; 
        header("Location: index.php"); // Redirect to homepage
        exit();
    }
}
?>
<form class="form-signin" id="loginForm" method="post">
    <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
      
    <?php
        // Display errors if any
        if (!empty($errors)) {
            echo "<div class='alert alert-danger' role='alert'>";
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
            echo "</div>";
        }
    ?>
    <label for="inputUsername" class="sr-only">Username</label>
    <input type="text" name="username" id="inputUsername" class="form-control" placeholder="Username"
        value="<?php echo htmlspecialchars($input['username']); ?>" required autofocus>

    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
      
    <button class="btn btn-lg btn-accessible btn-block" type="submit">Sign in</button>
    </form>

<?php
echo "</div>";
echo "</div>";
echo makeFooter();
echo makePageEnd();
?>