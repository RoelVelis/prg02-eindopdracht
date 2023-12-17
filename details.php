<?php
session_start();

$login = $_SESSION['loggedin'] ?? false;


/**@var array $films
 * @var mysqli $db
 */

require_once 'includes/database.php';

$query = "SELECT *, studio.studio_name, GROUP_CONCAT(genre.genre_name SEPARATOR ', ') AS genre_names
    FROM film  
    JOIN studio ON film.studio_id = studio.id
    LEFT JOIN film_genre ON film.film_id = film_genre.movie_id
    LEFT JOIN genre ON film_genre.genre_id = genre.id
    GROUP BY film.film_id";

$stmt = mysqli_prepare($db, $query);

mysqli_stmt_execute($stmt);

$filmTable = mysqli_stmt_get_result($stmt) or die('Error ' . mysqli_error($db) . ' with query ' . $query);

$films = [];

while ($row = mysqli_fetch_assoc($filmTable)) {
    $films[] = $row;
}

mysqli_close($db);

$index = isset($_GET['id']) ? htmlspecialchars($_GET['id'] - 1) : null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Details <?= htmlspecialchars($films[$index]['name']) ?> | Film Collection</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($films[$index]['name']) ?> details</h2>
    <section>
        <ul>
            <li>Genre: <?= htmlspecialchars($films[$index]['genre_names']) ?></li>
            <li>Release Year: <?= htmlspecialchars($films[$index]['year']) ?></li>
            <li>Director: <?= htmlspecialchars($films[$index]['director']) ?></li>
            <li>Studio: <?= htmlspecialchars($films[$index]['studio_name']) ?></li>
            <li>Rating: <?= htmlspecialchars($films[$index]['rating']) ?></li>
            <li>Length: <?= htmlspecialchars($films[$index]['length']) ?></li>
            <li>Description: <?= htmlspecialchars($films[$index]['description']) ?></li>

        </ul>
</section>
        <div class="back-button">
            <?php if($login):?>
            <a href="secure.php">Back to list</a>
            <?php else:?>
            <a href="index.php">Back to list</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


