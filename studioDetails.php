<?php
session_start();

$login = $_SESSION['loggedin'] ?? false;

if ($login){
    $securePageUrl = "secure.php";
} else {
    $securePageUrl = "index.php";
}

/**@var array $films
 * @var mysqli $db
 */

require_once 'includes/database.php';

$query = "SELECT studio.id, studio.studio_name, GROUP_CONCAT(film.name SEPARATOR ', ') AS movie_names
FROM studio
LEFT JOIN film ON studio.id = film.studio_id
GROUP BY studio.studio_name";

$sqli = $query;

$studioTable = mysqli_query($db, $sqli)
or die('Error ' . mysqli_error($db) . ' with query ' . $sqli);

$studios = [];

while ($row = mysqli_fetch_assoc($studioTable)) {
    $studios[] = $row;
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
    <title>Studio List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="header">
     <div class="navbar">
          <div class="back-button">
            <a href="<?= $securePageUrl ?>">Back to main list</a>
          </div>
      </div>
    </div>
    <h1> Film Collection </h1>
    <hr>
    <div>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Films</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="11">&copy; My Collection <?php if ($login):?>| <a href="studioCreate.php">Add more</a> <?php endif;?></td>
            </tr>
            </tfoot>
            <tbody>
            <?php foreach($studios as $index => $studio): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?=$studio['studio_name']?></td>
                <td><?=$studio['movie_names']?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>
</body>
</html>
