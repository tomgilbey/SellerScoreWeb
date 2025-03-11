<?php
require_once("functions.php");
echo makePageStart("Home Page");
echo makeNavBar();
echo"<div class='container'>\n";
echo"<div class='box'>\n";

$errors = [];
$input = [
    'first_name' => '',
    'surname' => '',
    'username' => '',
    'email' => '',
    'dob' => '',
    'password' => ''    
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    list($input, $errors) = validate_registration();
    
    if (empty($errors)) {
        header("Location: login.php"); // Redirect to homepage
        exit();
    }

}
?>

<form class="form-signin" id="registerForm" method="post">
    <h1 class="h3 mb-3 font-weight-normal">Create a new Account!</h1>

    <?php
        if(!empty($errors)) {
            echo "<div class='alert alert-danger' role='alert'>\n";
            foreach ($errors as $error) {
              echo "<p>$error</p>\n";
            }
            echo "</div>";
        }
        ?>
    <div class="mb-3">
    <label for="inputFirstName" class="sr-only">First Name</label>
    <input type="text" name="first_name" id="inputFirstName" class="form-control" placeholder="First Name"
        value="<?php echo htmlspecialchars($input['first_name']); ?>" required autofocus>
    </div>

    <div class="mb-3">
        <label for="inputSurname" class="sr-only">Surname</label>
        <input type="text" name="surname" id="inputSurname" class="form-control" placeholder="Surname"
            value="<?php echo htmlspecialchars($input['surname']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="inputUsername" class="sr-only">Username</label>
        <input type="text" name="username" id="inputUsername" class="form-control" placeholder="Username"
            value="<?php echo htmlspecialchars($input['username']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address"
            value="<?php echo htmlspecialchars($input['email']); ?>" required>
    </div>

    <div class="mb-3">
        <label for="inputDOB">Date of Birth:</label>
        <input type="date" name="dob" id="inputDOB" class="form-control"
            value="<?php echo htmlspecialchars($input['dob'] ?: '2000-01-01'); ?>" required>
    </div>

    <div class="mb-3">
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
    </div>

    <button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>

</form>

<?php
echo "</div>\n";
echo "</div>\n";
echo makeFooter("This is the footer");
echo makePageEnd();
?>