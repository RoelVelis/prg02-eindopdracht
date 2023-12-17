<?php
session_start();


if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}
$is_admin = $_SESSION['is_admin'] ?? false;
/**@var array $films
 * @var mysqli $db
 */

require_once 'includes/database.php';

$sortColumn = isset($_GET['sortColumn']) ? htmlspecialchars($_GET['sortColumn']) : 'rating';
$sortOrder = isset($_GET['sortOrder']) ? htmlspecialchars($_GET['sortOrder']) : 'ASC';

$user_id = $_SESSION["user_id"];
if (isset($_GET["user_id"])) {
    $query = "SELECT *, studio.studio_name, GROUP_CONCAT(genre.genre_name SEPARATOR ', ') AS genre_names
        FROM film  
        JOIN studio ON film.studio_id = studio.id
        LEFT JOIN film_genre ON film.film_id = film_genre.movie_id
        LEFT JOIN genre ON film_genre.genre_id = genre.id
        LEFT JOIN film_user ON film.film_id = film_user.movie_id
        LEFT JOIN user ON film_user.user_id = user.user_id
        WHERE film_user.user_id = $user_id
        GROUP BY film.film_id
        ORDER BY $sortColumn $sortOrder";
    $buttonText = 'Main list';
    $securePageUrl = "secure.php?";
    $securePageUrlSort = "secure.php?user_id=$user_id";
} else {
    $query = "SELECT *, studio.studio_name, GROUP_CONCAT(genre.genre_name SEPARATOR ', ') AS genre_names
        FROM film  
        JOIN studio ON film.studio_id = studio.id
        LEFT JOIN film_genre ON film.film_id = film_genre.movie_id
        LEFT JOIN genre ON film_genre.genre_id = genre.id
        GROUP BY film.film_id
        ORDER BY $sortColumn $sortOrder";
    $buttonText = 'Own list';
    $securePageUrl = "secure.php?user_id=$user_id";
    $securePageUrlSort = "secure.php?";
}

if (isset($_GET['add_to_list']) && isset($_GET['movie_id']) && !isset($_GET['user_id']) && isset($_SESSION['user_id'])) {
    $movieIdToAdd = $_GET['movie_id'];

$userFilmQuery = "SELECT * FROM film_user WHERE user_id = $user_id AND movie_id = $movieIdToAdd";
$checkResult = mysqli_query($db, $userFilmQuery);

if (!$checkResult) {
    die("Error checking if the movie is already in the personal list: " . mysqli_error($db));
}

if (mysqli_num_rows($checkResult) === 0) {
    $insertQuery = "INSERT INTO film_user (user_id, movie_id) VALUES ($user_id, $movieIdToAdd)";
    $insertResult = mysqli_query($db, $insertQuery);

    if (!$insertResult) {
    die("Error adding the movie to the personal list: " . mysqli_error($db));
    }
    echo "Movie added to your list successfully!";
} else {
    echo "Movie is already in your list.";
}
header("Location: secure.php");
        exit();
}

$sqli = $query;

$filmTable = mysqli_query($db, $sqli)
or die('Error ' . mysqli_error($db) . ' with query ' . $sqli);

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

            window.location.href = `<?= $securePageUrlSort ?>sortColumn=${column}&sortOrder=${sortOrder}`;
        }
    </script>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="navbar">
            <div class="back-button">
                <a href="<?= $securePageUrl ?>"><?= $buttonText ?></a>
            </div>
        <div class="logout-button">
            <a href="logout.php">Logout</a>
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
                <th>Genre</th>
                <th class="sortable <?php echo $sortColumn === 'rating' ? 'sorted-' . strtolower($sortOrder) : ''; ?>"
                    onclick="sortTable('rating')">Ratings</th>
                <th class="sortable <?php echo $sortColumn === 'year' ? 'sorted-' . strtolower($sortOrder) : ''; ?>"
                    onclick="sortTable('year')">Year</th>
                <th>Director</th>
                <th><a href="studioDetails.php">Studio</a></th>
                <th>Details</th>
                <?php if (!isset($_GET['user_id']) && isset($_SESSION['user_id'])): ?>
                <th>Add to list</th>
                <?php endif; ?>
                <?php if ($is_admin): ?>
                    <th>Edit</th>
                    <th>Delete</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="11">&copy; My Collection | <a href="create.php?user_id=<?=$user_id?>">Add more</a></td>
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
                    <?php if (!isset($_GET['user_id']) && isset($_SESSION['user_id'])): ?>
                    <td> <a href="secure.php?add_to_list=true&movie_id=<?= $film['film_id'] ?>">Add to My List</a></td>
                    <?php endif; ?>
                    <?php if ($is_admin): ?>
                        <td><a href="edit.php?movie_id=<?= $film['film_id'] ?>">Edit</a></td>
                        <td><a href="delete.php?movie_id=<?= $film['film_id'] ?>" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</a></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>