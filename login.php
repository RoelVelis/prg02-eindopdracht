<?php
session_start();

$login = false;
$errors = [];

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $login = true;
}

/** @var mysqli $db */
require_once "includes/database.php";

if (isset($_POST['submit'])) {
    // Get form data with htmlspecialchars for added security
    $email = mysqli_real_escape_string($db, htmlspecialchars($_POST['email']));
    $password = mysqli_real_escape_string($db, htmlspecialchars($_POST['password']));

    if (!$db) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Use prepared statement to prevent SQL injection
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $user['user_id'];
                $_SESSION["email"] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $login = true;

                header("location: secure.php?user_id=" . $user['user_id']);
                exit();
            } else {
                //wrong password
                $errors['loginFailed'] = "Wrong login information";
            }
        } else {
            //user isn't stored in the database
            $errors['email'] = "User not found";
        }
    } else {
        //database problem
        $errors['database'] = "Error: " . mysqli_error($db);
    }

    mysqli_close($db);
}
?>
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<section>

    <div class="container">
    <?php if ($login):?>
<p> You are logged in!</p>
        <p><a href="logout.php">Log out</a> </p>
    <?php else: ?>
        <h2>Log in</h2>
        <section>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
  <form action="" method="post">
    <div>
        <label class="label" for="email">Email</label>
        <div class="field">
            <input class="input" id="email" type="text" name="email" value="<?= $email ?? '' ?>" required/>
        </div>
    </div>
    <p>
        <?= $errors['email'] ?? '' ?>
    </p>
    <div>
        <label class="label" for="password">Password</label>
        <div class="field">
            <input class="input" id="password" type="password" name="password" required/>
        </div>
    </div>
    <p>
        <?= $errors['password'] ?? '' ?>
    </p>

      <div class="field">
          <button type="submit" name="submit">Log in With Email</button>
      </div>
      <a href="register.php">Register</a>

      <div class="back-button">
          <a href="index.php">Back to list</a>
      </div>
    </div>


</section>
    <?php endif; ?>
</body>
</html>
