<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}
/**
 * @var mysqli $db
 */

require_once 'includes/database.php';

$errors = [];

if (isset($_POST['submit'])) {
    $studioName = mysqli_real_escape_string($db, htmlspecialchars($_POST['studio_name'] ?? ''));

    if (empty($studioName)) {
        //not necessary anymore since 'required' tag is now in form but lazy to remove it
        $errors[] = "Empty studio name field";
    } else {
        $insertQuery = "INSERT INTO studio (studio_name) VALUES (?)";
        $stmt = mysqli_prepare($db, $insertQuery);
        mysqli_stmt_bind_param($stmt, "s", $studioName);

        $insertResult = mysqli_stmt_execute($stmt);

        if ($insertResult) {
            echo "Studio created!";
        } else {
            $errors[] = "Error creating studio: " . mysqli_error($db);
        }

        mysqli_stmt_close($stmt);
    }
}

mysqli_close($db);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Studio Create</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Create a Studio</h2>
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
                <label class="label" for="studio_name">Studio Name</label>
                <div class="field">
                    <input class="input" id="studio_name" type="text" name="studio_name" value="<?= $studioName ?? '' ?>" required/>
                </div>
            </div>
            <div class="bottom-submit">
                <div>
                    <button type="submit" name="submit">Add Studio</button>
                </div>
                <div class="back-button">
                    <a href="studioDetails.php">Back to Studio List</a>
                </div>
            </div>
        </form>
    </section>
</div>
</body>
</html>
