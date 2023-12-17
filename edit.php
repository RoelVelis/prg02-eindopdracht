<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

/** @var mysqli $db */
require_once 'includes/database.php';

if (isset($_GET["movie_id"])) {
    $movie_id = $_GET["movie_id"];

    $query = "SELECT * FROM film WHERE film_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $movie_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $movie = mysqli_fetch_assoc($result);
    } else {
        echo "Movie not found.";
        exit();
    }
} else {
    echo "Movie ID not provided.";
    exit();
}

$editQuery = "SELECT * FROM film WHERE film_id = ?";
$editStmt = mysqli_prepare($db, $editQuery);
mysqli_stmt_bind_param($editStmt, "i", $movie_id);
mysqli_stmt_execute($editStmt);
$editResult = mysqli_stmt_get_result($editStmt);

if (!$editResult) {
    die("Error fetching movie details: " . mysqli_error($db));
}

$editMovie = mysqli_fetch_assoc($editResult);

if (!$editMovie) {
    die("Movie not found");
}

$movieGenresQuery = "SELECT genre_id FROM film_genre WHERE movie_id = ?";
$movieGenresStmt = mysqli_prepare($db, $movieGenresQuery);
mysqli_stmt_bind_param($movieGenresStmt, "i", $movie_id);
mysqli_stmt_execute($movieGenresStmt);
$movieGenresResult = mysqli_stmt_get_result($movieGenresStmt);

if (!$movieGenresResult) {
    die("Error fetching movie genres: " . mysqli_error($db));
}

$movieGenres = [];
while ($row = mysqli_fetch_assoc($movieGenresResult)) {
    $movieGenres[] = $row['genre_id'];
}

$queryStudios = "SELECT * FROM studio";
$resultStudios = mysqli_query($db, $queryStudios);

$studios = [];
while ($row = mysqli_fetch_assoc($resultStudios)) {
    $studios[] = $row;
}

$genresQuery = "SELECT * FROM genre";
$genresResult = mysqli_query($db, $genresQuery);

if (!$genresResult) {
    die("Error fetching genres: " . mysqli_error($db));
}

$errors = [];

if (isset($_POST['submit'])) {
    $newStudioID = mysqli_real_escape_string($db, $_POST['newStudio']);
    $newName = htmlspecialchars(mysqli_real_escape_string($db, $_POST['newName'] ?? ''));
    $newRating = htmlspecialchars(mysqli_real_escape_string($db, $_POST['newRating'] ?? ''));
    $newYear = htmlspecialchars(mysqli_real_escape_string($db, $_POST['newYear'] ?? ''));
    $newDescription = htmlspecialchars(mysqli_real_escape_string($db, $_POST['newDescription'] ?? ''));
    $newLength = htmlspecialchars(mysqli_real_escape_string($db, $_POST['newLength'] ?? ''));
    $newDirector = htmlspecialchars(mysqli_real_escape_string($db, $_POST['newDirector'] ?? ''));

    $user_id = $_SESSION["user_id"];

    if (empty($newStudioID) || empty($newName) || empty($newRating) || empty($newYear) || empty($newDescription) || empty($newLength) || empty($newDirector)) {
        $errors[] = "All fields are required. Please fill in the form.";
    }

    if (!$db) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $queryUpdate = "UPDATE film SET studio_id = ?, name = ?, rating = ?, year = ?, description = ?, length = ?, director = ? WHERE film_id = ?";
    $stmtUpdate = mysqli_prepare($db, $queryUpdate);

    mysqli_stmt_bind_param($stmtUpdate, "issssssi", $newStudioID, $newName, $newRating, $newYear, $newDescription, $newLength, $newDirector, $movie_id);
    mysqli_stmt_execute($stmtUpdate);

    if ($stmtUpdate) {
        $deleteGenresQuery = "DELETE FROM film_genre WHERE movie_id = ?";
        $stmtDeleteGenres = mysqli_prepare($db, $deleteGenresQuery);
        mysqli_stmt_bind_param($stmtDeleteGenres, "i", $movie_id);
        mysqli_stmt_execute($stmtDeleteGenres);

        if (isset($_POST['genres']) && is_array($_POST['genres'])) {
            foreach ($_POST['genres'] as $genre_id) {
                $sql_film_genre = "INSERT INTO film_genre (movie_id, genre_id) VALUES (?, ?)";
                $stmtFilmGenre = mysqli_prepare($db, $sql_film_genre);
                mysqli_stmt_bind_param($stmtFilmGenre, "ii", $movie_id, $genre_id);
                mysqli_stmt_execute($stmtFilmGenre);
            }
        }

        echo "Movie updated successfully!";
    } else {
        echo "Error updating movie details: " . mysqli_error($db);
    }
}


?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edit details</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Edit Details</h2>
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
                <label class="label" for="newName">Movie Name</label>
                <div class="field">
                    <input class="input" id="newName" type="text" name="newName" value="<?= $newName ?? '' ?>" required/>
                </div>
            </div>
            <div>
                <label class="label">Genres</label>
                <div class="field">
                    <?php
                    $genresResult = mysqli_query($db, $genresQuery);

                    while ($genre = mysqli_fetch_assoc($genresResult)) {
                        $genreId = $genre['id'];
                        $isChecked = in_array($genreId, $movieGenres); // Check if the genre is associated with the movie
                        echo '<input type="checkbox" name="genres[]" value="' . $genreId . '" ' . ($isChecked ? 'checked' : '') . '>';
                        echo '<label>' . $genre['genre_name'] . '</label><br>';
                    }

                    mysqli_close($db);
                    ?>
                </div>
            </div>

            <div>
                <label for="newStudio">Studio:</label>
                <select name="newStudio" id="newStudio" required>
                    <?php foreach ($studios as $studio): ?>
                        <option value="<?= $studio['id'] ?>"><?= $studio['studio_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label" for="newRating">Rating</label>
                <div class="field">
                    <input class="input" id="newRating" type="text" name="newRating" value="<?= $newRating ?? '' ?>" required />
                </div>
            </div>
            <div>
                <label class="label" for="newYear">Year</label>
                <div class="field">
                    <input class="input" id="newYear" type="text" name="newYear" value="<?= $newYear ?? '' ?>" required />
                </div>
            </div>
            <div>
                <label for="newDescription">Description:</label>
                <textarea name="newDescription" id="newDescription" rows="4" cols="50" required></textarea>
            </div>
            <div>
                <label for="newLength">Length</label>
                <input class="input" id="newLength" type="text" name="newLength" pattern="^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$" placeholder="hh:mm:ss" value="<?= $newLength ?? '' ?>" required>
            </div>
            <div>
                <label class="label" for="newDirector">Director</label>
                <div class="field">
                    <input class="input" id="newDirector" type="text" name="newDirector" value="<?= $newDirector ?? '' ?>" required/>
                </div>
            </div>
            <div class="bottom-submit">
                <div>
                    <button type="submit" name="submit">Update movie</button>
                </div>
                <div class="back-button">
                    <?php if(empty($user_id)): ?>
                        <a href="secure.php">Back to list</a>
                    <?php else: ?>
                        <a href="secure.php?user_id=<?=$user_id?>">Back to list</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </section>

</div>
</body>
</html>
