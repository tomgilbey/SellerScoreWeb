<?php

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Displays a list of error messages.
 *
 * @param array $errors An array of error messages.
 * @return string HTML output of the error messages.
 */
function show_errors($errors) {
    echo "<h1 class='error-heading'>Errors</h1>\n";
    $output = "";
    foreach ($errors as $error) {
        $output .= "<p class='error-message'>$error</p>\n";
    }
    return $output;
}

/**
 * Establishes a connection to the database.
 *
 * @return PDO The database connection object.
 * @throws Exception If the connection fails.
 */
function getConnection() {
    try {
        $connection = new PDO("mysql:host=nuwebspace_db; dbname=w22002938", "w22002938", "exJbLChc");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    } catch (Exception $e) {
        throw new Exception("Connection error" . $e->getMessage(), 0, $e);
    }
}

/**
 * Generates the start of an HTML page, including the head and opening body tag.
 *
 * @param string $title The title of the page.
 * @return string HTML output for the page start.
 */
function makePageStart($title) {
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

        <!-- Custom styles for this template -->
        <link href="styles.css" rel="stylesheet">
        
    </head>
    <body>
HTML;
}

/**
 * Generates the navigation bar for the website.
 *
 * @return string HTML output for the navigation bar.
 */
function makeNavBar() {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $currentUser = $_SESSION['username'] ?? null; // Get the logged-in user's username
    $viewedUser = $_GET['user'] ?? null; // Get the 'user' parameter from the URL

    $output = <<<HTML
    <nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
        <a class="navbar-brand" href="#"><strong>SellerScore</strong></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" 
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
HTML;

    // Home link
    $output .= "\n    <li class='nav-item" . ($currentPage == 'index.php' ? ' active' : '') . "'>
                    <a class='nav-link' href='index.php'><strong>Home</strong></a>
                  </li>\n";

    // Links for logged-in users
    if (check_login()) {
        // My Reputation link
        $output .= "\n    <li class='nav-item" . ($currentPage == 'myReputation.php' ? ' active' : '') . "'>
                      <a class='nav-link' href='myReputation.php'><strong>My Reputation</strong></a>
                    </li>\n";

        // My Profile link (highlight only if viewing own profile)
        $isMyProfileActive = ($currentPage == 'profile.php' && $viewedUser === $currentUser) ? ' active' : '';
        $output .= "\n    <li class='nav-item$isMyProfileActive'>
                      <a class='nav-link' href='profile.php?user=" . urlencode($currentUser) . "'><strong>My Profile</strong></a>
                    </li>\n";
    }

    // Links for non-logged-in users
    if ($currentPage == 'index.php' && !check_login()) {
        $output .= <<<HTML
                <li class="nav-item">
                    <a class="nav-link" href="indexSellers.php"><strong>For Sellers!</strong></a>
                </li>
HTML;
    } elseif ($currentPage == 'indexSellers.php' && !check_login()) {
        $output .= <<<HTML
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><strong>For Buyers!</strong></a>
                </li>
HTML;
    }

    // Search bar
    $output .= <<<HTML
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item position-relative">
                  <form class="form-inline d-flex" action="searchResults.php" method="GET">
                      <input class="form-control mr-sm-2" type="text" name="account" id="searchInputNavbar" 
                            placeholder="Account Search" aria-label="Search" autocomplete="off">
                      <button class="btn btn-success my-2 my-sm-0" type="submit" style="height: 70px; color: white; background-color: #116400; border-color: #116400;">
                          <strong>Search</strong>
                      </button>
                  </form>
                  <div id="searchResultsNavbar" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                </li>
HTML;

    // Account management links
    if (check_login()) {
        $output .= "\n    <li class='nav-item" . ($currentPage == 'manageAccount.php' ? ' active' : '') . "'>
                        <a class='nav-link' href='manageAccount.php'><strong>Manage Account</strong></a>
                      </li>\n";
        $output .= "    <li class='nav-item'>
                        <a class='nav-link' href='logout.php'><strong>Logout</strong></a>
                      </li>\n";
    } else {
        $output .= "\n    <li class='nav-item" . ($currentPage == 'login.php' ? ' active' : '') . "'>
                        <a class='nav-link' href='login.php'><strong>Login</strong></a>
                      </li>\n";
        $output .= "\n    <li class='nav-item" . ($currentPage == 'register.php' ? ' active' : '') . "'>
                        <a class='nav-link' href='register.php'><strong>Register</strong></a>
                      </li>\n";
    }

    $output .= <<<HTML
            </ul>
        </div>
    </nav>
HTML;

    return $output;
}

/**
 * Generates the footer for the website.
 *
 * @return string HTML output for the footer.
 */
function makeFooter() {
    return <<<HTML
    <footer class="footer bg-dark text-white mt-5">
      <div class="container text-center py-3">
        <p>&copy; 2025 SellerScore. All rights reserved.</p>
      </div>
      <script>
      document.addEventListener("DOMContentLoaded", function() {
        const searchInputs = document.querySelectorAll("#searchInput, #searchInputNavbar");
        const searchResults = document.querySelectorAll("#searchResults, #searchResultsNavbar");

        searchInputs.forEach((searchInput, index) => {
          const resultsContainer = searchResults[index];

          searchInput.addEventListener("keyup", function() {
            let query = searchInput.value.trim();

            if (query.length < 3) {
              resultsContainer.innerHTML = "";
              return;
            }

            fetch("searchUsers.php?q=" + encodeURIComponent(query))
              .then(response => response.text())
              .then(data => {
                resultsContainer.innerHTML = data;
              })
              .catch(error => console.error("Error:", error));
          });

          document.addEventListener("click", function(event) {
            if (!resultsContainer.contains(event.target) && event.target !== searchInput) {
              resultsContainer.innerHTML = "";
            }
          });
        });
      });
      </script>
    </footer>
HTML;
}

/**
 * Generates the closing tags for an HTML page.
 *
 * @return string HTML output for the page end.
 */
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

/**
 * Sets a session variable.
 *
 * @param string $key The session key.
 * @param mixed $value The value to store in the session.
 * @return bool True if the session variable was set.
 */
function set_session($key, $value) {
    $_SESSION[$key] = $value;
    return true;
}

/**
 * Retrieves a session variable.
 *
 * @param string $key The session key.
 * @return mixed|null The session value, or null if not set.
 */
function get_session($key) {
    if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
    }
    return null;
}

/**
 * Checks if the user is logged in.
 *
 * @return bool True if the user is logged in, false otherwise.
 */
function check_login() {
    if (get_session('logged-in')) {
        return true;
    }
    return false;
}

/**
 * Redirects the user to the login page if they are not logged in.
 */
function loggedIn() {
    if (!check_login()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Logs the user out by clearing the session and redirecting to the homepage.
 */
function logOut() {
    $_SESSION = array();
    session_destroy();
    header('Location: index.php');
    exit();
}

/**
 * Validates the login form input and checks the credentials against the database.
 *
 * @return array An array containing the sanitized input and any validation errors.
 */
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
                $_SESSION['userID'] = $user->userID;
                $_SESSION['logged-in'] = true;
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

/**
 * Validates the registration form input and creates a new user in the database.
 *
 * @return array An array containing the sanitized input and any validation errors.
 */
function validate_registration() {
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
    } elseif (strlen($input['username']) < 5) {
        $errors[] = "Username must be at least 5 characters.";
    } elseif (!preg_match('/^\S+$/', $input['username'])) {
        $errors[] = "Username cannot contain spaces.";
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

            if (empty($errors)) {
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

                $userID = $dbConn->lastInsertId();

                $sqlReputationQuery = "INSERT INTO userReputation (UserID, AverageRating, TotalReviews, updated) VALUES (:userID, 0, 0, now())";
                $stmt = $dbConn->prepare($sqlReputationQuery);
                $stmt->execute([
                    ':userID' => $userID
                ]);
            }
        } catch (Exception $e) {
            $errors[] = "There was a problem: " . $e->getMessage();
        }
    }

    return array($input, $errors);
}
?>