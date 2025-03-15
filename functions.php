<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function show_errors($errors) {
    echo "<h1 class='error-heading'>Errors</h1>\n";
    $output = "";
    foreach ($errors as $error) {
        $output .= "<p class='error-message'>$error</p>\n";
    }
    return $output;
}

function getConnection() {
    try {
        $connection = new PDO("mysql:host=nuwebspace_db; dbname=w22002938", "w22002938", "exJbLChc");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    } catch (Exception $e) {
        throw new Exception("Connection error" . $e->getMessage(), 0, $e);
    }
}

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
        <link href="navbar-top.css" rel="stylesheet">
        <link href="sticky-footer.css" rel="stylesheet">
        <link href="signin.css" rel="stylesheet">
        
    </head>
    <body>
HTML;
}

function makeNavBar() {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $output = <<<HTML
    <nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
        <a class="navbar-brand" href="#">SellerScore</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" 
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
HTML;

    $output .= "\n    <li class='nav-item" . ($currentPage == 'index.php' ? ' active' : '') . "'>
                    <a class='nav-link' href='index.php'>Home</a>
                  </li>\n";
    $output .= "\n<li class='nav-item" . ($currentPage == 'myReputation.php' ? ' active' : '') . "'>
                    <a class='nav-link' href='myReputation.php'>My Reputation</a>\n
                  </li>\n";
    if (check_login()) {
        $output .= "\n    <li class='nav-item" . ($currentPage == 'profile.php' ? ' active' : '') . "'>
                      <a class='nav-link' href='profile.php?user=" . urlencode($_SESSION['username']) . "'>My Profile</a>
                    </li>\n";
    } else {
        $output .= "\n    <li class='nav-item'>
                      <a class='nav-link' href='login.php'>My Profile</a>
                    </li>\n";
    }

    $output .= <<<HTML
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item position-relative">
                  <form class="form-inline d-flex" action="searchResults.php" method="GET">
                      <input class="form-control mr-sm-2" type="text" name="account" id="searchInputNavbar" 
                            placeholder="Account Search" aria-label="Search" autocomplete="off">
                      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                  </form>
                  <div id="searchResultsNavbar" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                </li>
HTML;

    if (check_login()) {
        $output .= "\n    <li class='nav-item" . ($currentPage == 'manage.php' ? ' active' : '') . "'>
                        <a class='nav-link' href='manageAccount.php'>Manage Account</a>
                      </li>\n";
        $output .= "    <li class='nav-item'>
                        <a class='nav-link' href='logout.php'>Logout</a>
                      </li>\n";
    } else {
        $output .= "\n    <li class='nav-item" . ($currentPage == 'login.php' ? ' active' : '') . "'>
                        <a class='nav-link' href='login.php'>Login</a>
                      </li>\n";
        $output .= "\n    <li class='nav-item" . ($currentPage == 'register.php' ? ' active' : '') . "'>
                        <a class='nav-link' href='register.php'>Register</a>
                      </li>\n";
    }

    $output .= <<<HTML
            </ul>
        </div>
    </nav>
HTML;

    return $output;
}

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

function set_session($key, $value) {
    $_SESSION[$key] = $value;
    return true;
}

function get_session($key) {
    if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
    }
    return null;
}

function check_login() {
    if (get_session('logged-in')) {
        return true;
    }
    return false;
}

function loggedIn() {
    if (!check_login()) {
        header('Location: login.php');
        exit();
    }
}

function logOut() {
    $_SESSION = array();
    session_destroy();
    header('Location: index.php');
    exit();
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
            }
        } catch (Exception $e) {
            $errors[] = "There was a problem: " . $e->getMessage();
        }
    }

    return array($input, $errors);
}
?>