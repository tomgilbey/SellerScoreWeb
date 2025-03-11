<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function show_errors($errors) {//function to show errors, parameter should be array of errors or empty
  echo "<h1 class='error-heading'>Errors</h1>\n";
  $output = "";
  foreach ($errors as $error) {
      $output .= "<p class='error-message'>$error</p>\n";//Concatenates each error into an error message and displays on screen.
  }
  return $output;
}

function getConnection(){ //function to get the connection to the database, allows users to query
  try{
      $connection = new PDO("mysql:host=nuwebspace_db; dbname=w22002938","w22002938", "exJbLChc");//PDO is data abstraction layer
      $connection ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//sets attributes on PDO connection. Turns errors and exception reporting on.
      return $connection;

  }catch(Exception $e) {
      throw new Exception("Connection error".$e->getMessage(), 0 ,$e);
  }
}

function makePageStart($title) 
{
  session_start();
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>$title</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap 4.5 CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

        <!-- jQuery library (required for Bootstrap 4) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

        <!-- Bootstrap 4.5 JS (including Popper.js for dropdowns) -->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

        <!--Taken from Bootstrap nav-bar template: view-source:https://getbootstrap.com/docs/4.0/examples/navbar-static/-->
        <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">

        <!--<link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/navbar-static/">-->

        <!-- Custom styles for this template -->
        <link href="navbar-top.css" rel="stylesheet">
        <link href="sticky-footer.css" rel="stylesheet">
        <link href="signin.css" rel="stylesheet">
        
    </head>
    <body>
HTML;
}

function makeNavBar() {
  // Get current page name
  $currentPage = basename($_SERVER['PHP_SELF']); // Returns the filename of the currently executing script

  // Start of the HTML structure
  $output = <<<HTML
  <nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
      <a class="navbar-brand" href="#">SellerScore</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" 
              aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarCollapse">
          
          <!-- Left Side: Navigation Links -->
          <ul class="navbar-nav mr-auto">
HTML;

  // Add navigation links with proper formatting
  $output .= "\n    <li class='nav-item" . ($currentPage == 'index.php' ? ' active' : '') . "'>
                  <a class='nav-link' href='index.php'>Home</a>
                </li>\n";
  $output .= "    <li class='nav-item" . ($currentPage == 'reputation.php' ? ' active' : '') . "'>
                  <a class='nav-link' href='reputation.php'>My Reputation</a>
                </li>\n";
  $output .= "    <li class='nav-item" . ($currentPage == 'manage.php' ? ' active' : '') . "'>
                  <a class='nav-link' href='manage.php'>Manage Account</a>
                </li>\n";

  // Continue navbar HTML
  $output .= <<<HTML
          </ul>

          <!-- Right Side: Search Bar + Login/Register -->
          <ul class="navbar-nav ml-auto">
              <li class="nav-item position-relative">
                <form class="form-inline d-flex" action="searchResults.php" method="GET">
                    <input class="form-control mr-sm-2" type="text" name="account" id="searchInput" 
                          placeholder="Account Search" aria-label="Search" autocomplete="off">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                </form>
                <div id="searchResults" class="list-group position-absolute w-100" style="z-index: 1000;"></div> <!-- Search results -->
              </li>
HTML;

  // Add dynamic login/logout links
  if (check_login()) {
      $output .= "\n    <li class='nav-item" . ($currentPage == 'profile.php' ? ' active' : '') . "'>
                      <a class='nav-link' href='profile.php'>Profile</a>
                    </li>\n";
      $output .= "    <li class='nav-item'>
                      <a class='nav-link' href='logout.php'>Logout</a>
                    </li>\n";
  } else {
      $output .= "\n    <li class='nav-item" . ($currentPage == 'login.php' ? ' active' : '') . "'>
                      <a class='nav-link' href='login.php'>Login</a>
                    </li>\n";
      $output .= "    <li class='nav-item" . ($currentPage == 'register.php' ? ' active' : '') . "'>
                      <a class='nav-link' href='register.php'>Register</a>
                    </li>\n";
  }

  // Closing HTML
  $output .= <<<HTML
          </ul>
      </div>
  </nav>
HTML;

  return $output;
}


function makeFooter($footerText) {
    return <<<HTML
    <footer class="footer">
      <div class="container">
        <span class="text-muted">$footerText</span>
      </div>
      <script> // JavaScript for search bar
      document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("searchInput"); // Get search input field
        const searchResults = document.getElementById("searchResults"); // Get search results div

        searchInput.addEventListener("keyup", function() { // Listen for keyup events
        let query = searchInput.value.trim(); // Trim whitespace from input

        if (query.length < 3) { 
            searchResults.innerHTML = ""; // Clear results if less than 3 chars
            return;
        }

        // Send AJAX request to searchUsers.php
        fetch("searchUsers.php?q=" + encodeURIComponent(query))
            .then(response => response.text()) // Parse response as text
            .then(data => {
                searchResults.innerHTML = data; // Insert results
            })
            .catch(error => console.error("Error:", error));
     });

    // Hide results when clicking outside
      document.addEventListener("click", function(event) {
      if (!searchResults.contains(event.target) && event.target !== searchInput) {
          searchResults.innerHTML = "";
      }
    });
});
</script>

    </footer>

HTML;
}

function makePageEnd() {
    return <<<HTML
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const burger = document.querySelector('.navbar-burger');
                const menu = document.querySelector('.navbar-menu');
                
                burger.addEventListener('click', () => {
                    burger.classList.toggle('is-active');
                    menu.classList.toggle('is-active');
                });
            });
        </script>
    </body>
</html>
HTML;
}

function set_session($key, $value){//Function that takes two parameters to specify the session key and the corresponding value
  $_SESSION[$key]=$value;//Assigns provided value to the session variable thats identified by $key.
  return true;
}

function get_session($key){//Function designed to get the value of session variable based on key.
  if (isset($_SESSION[$key]))//Checks if session variable with the given key exists
  {
      return $_SESSION[$key];//If session exists, it will return the correct value.
  }
  return null;
}

function check_login(){//Checks if user is logged-in based on session variable "logged-in".
  if (get_session('logged-in')){//Checks if session variable with key "logged-in" exists and if it is true.
      return true;//Returns true if user is logged in
  }
  return false;//WIll return false if user isnt logged in

}

function loggedIn(){//Function that redirects users to loginform.php if they are not logged in.
  if (!check_login())
      {
          header('Location: login.php');//Redirects user
          exit();//Terminates script
      }
}

function logOut(){//Function to log out of account
  $_SESSION = array();//Reset session array
  session_destroy();//Destroys current session
  header('Location: index.php');//Redirects user to home page.
  exit();//Terminates script
}

function validate_login() {
  $input = array();
  $errors = array();
  $input['username'] = $_POST['username'] ?? '';
  $input['password'] = $_POST['password'] ?? '';
  $input['username'] = trim($input['username']);
  $input['password'] = trim($input['password']);

  try {
      $dbConn = getConnection();
      $sqlQuery = "SELECT userID, username, HashedPassword FROM Users WHERE username = :username";
      $stmt = $dbConn->prepare($sqlQuery);
      $stmt->execute(array(':username' => $input['username']));

      $user = $stmt->fetchObject();
      if ($user) {
          if (password_verify($input['password'], $user->HashedPassword)) {
              $_SESSION['username'] = $user->username;
              $_SESSION['userID'] = $user->userID;  // Optionally store the user ID

          } else {
              $errors[] = "Login Details are incorrect.";
          }
      } else {
          $errors[] = "Login Details are incorrect.";
      }
  } catch (Exception $e) {
      echo "There was a problem: " . $e->getMessage();
  }
  return array($input, $errors);
}

function validate_registration()
{
  $errors = [];
  $input = [
    'first_name' => trim($_POST['first_name']),
    'surname' => trim($_POST['surname']),
    'username' => trim($_POST['username']),
    'email' => trim($_POST['email']),
    'dob' => trim($_POST['dob']),
    'password' => trim($_POST['password'])
  ];

  if (empty($input['first_name'])) {
    $errors[] = "First Name is required.";
  }
  if (empty($input['surname'])) {
    $errors[] = "Surname is required.";
  }
  if (empty($input['username'])) {
    $errors[] = "Username is required.";
  }
  elseif (strlen($input['username']) < 5) {
    $errors[] = "Username must be at least 5 characters.";
  }
  if (empty($input['email'])) {
    $errors[] = "Email is required.";
  }
  if (empty($input['dob'])) {
    $errors[] = "Date of Birth is required.";
  }
  if (empty($input['password']) || strlen($input['password']) < 6) {
    $errors[] = "Password must be at least 6 characters.";
}

  if (empty($errors)) {
    try {
      $dbConn = getConnection();

      $stmt = $dbConn->prepare("SELECT COUNT(*) FROM Users WHERE Username = :username");
      $stmt->execute([':username' => $input['username']]);
      if ($stmt->fetchColumn() > 0) {
        $errors[] = "Username already exists. Please choose another.";
      }
      
      $stmt = $dbConn->prepare("SELECT COUNT(*) FROM Users WHERE email = :email");
      $stmt->execute([':email' => $input['email']]);
      if ($stmt->fetchColumn() > 0) {
        $errors[] = "Email is already registered. Please use another email.";
      }

      if(empty($errors)) {
        $sqlQuery = "INSERT INTO Users (FirstName, Surname, Username, email, DateOfBirth, HashedPassword, joinDate) VALUES (:first_name, :surname, :username, :email, :dob, :HashedPassword, now())";
        $stmt = $dbConn->prepare($sqlQuery);
        $stmt->execute(array(
          ':first_name' => $input['first_name'],
          ':surname' => $input['surname'],
          ':username' => $input['username'],
          ':email' => $input['email'],
          ':dob' => $input['dob'],
          ':HashedPassword' => password_hash($input['password'], PASSWORD_DEFAULT)
        ));
      }
    } catch (Exception $e) {
      $errors[] = "There was a problem: " . $e->getMessage();
    }
  }

  return array($input, $errors);
}



?>