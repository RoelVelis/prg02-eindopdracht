<?php
/** @var array $films
 * @var mysqli $db
 */

require_once 'includes/database.php';

$sortColumn = isset($_GET['sortColumn']) ? htmlspecialchars($_GET['sortColumn']) : 'rating';
$sortOrder = isset($_GET['sortOrder']) ? htmlspecialchars($_GET['sortOrder']) : 'ASC';

$query = "SELECT *, studio.studio_name, GROUP_CONCAT(genre.genre_name SEPARATOR ', ') AS genre_names
    FROM film  
    JOIN studio ON film.studio_id = studio.id
    LEFT JOIN film_genre ON film.film_id = film_genre.movie_id
    LEFT JOIN genre ON film_genre.genre_id = genre.id
    GROUP BY film.film_id
    ORDER BY $sortColumn $sortOrder";

$stmt = mysqli_prepare($db, $query);

$filmTable = mysqli_stmt_execute($stmt) ? mysqli_stmt_get_result($stmt) : die('Error ' . mysqli_error($db) . ' with query ' . $query);

$films = [];

while ($row = mysqli_fetch_assoc($filmTable)) {
    $films[] = $row;
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
    <title>Film lijst</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function sortTable(column) {
            var currentSortColumn = '<?php echo $sortColumn; ?>';
            var sortOrder = '<?php echo $sortOrder === "ASC" ? "DESC" : "ASC";  ?>';

            if (currentSortColumn !== column) {
                sortOrder = 'ASC';
            }

            window.location.href = `index.php?sortColumn=${column}&sortOrder=${sortOrder}`;
        }
    </script>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="login-button">
            <a href="login.php">Login</a>
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
                <th>Genre</th>
                <th class="sortable <?php echo $sortColumn === 'rating' ? 'sorted-' . strtolower($sortOrder) : ''; ?>"
                    onclick="sortTable('rating')">Ratings</th>
                <th class="sortable <?php echo $sortColumn === 'year' ? 'sorted-' . strtolower($sortOrder) : ''; ?>"
                    onclick="sortTable('year')">Year</th>
                <th>Director</th>
                <th><a href="studioDetails.php">Studio</a></th>
                <th>Details</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="8">&copy; My Collection</td>
            </tr>
            </tfoot>
            <tbody>
            <?php foreach ($films as $index => $film): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $film['name']?></td>
                    <td><?= $film['genre_names']?></td>
                    <td><?= $film['rating']?></td>
                    <td><?= $film['year']?></td>
                    <td><?= $film['director']?></td>
                    <td><?= $film['studio_name']?></td>
                    <td><a href="details.php?id=<?= $film['film_id'] ?>">Details</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>