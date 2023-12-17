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

$queryStudios = "SELECT * FROM studio";
$resultStudios = mysqli_query($db, $queryStudios);

$studios = [];
while ($row = mysqli_fetch_assoc($resultStudios)) {
    $studios[] = $row;
}

// Fetch genres
$genresQuery = "SELECT * FROM genre";
$genresResult = mysqli_query($db, $genresQuery);

// Check if genres query was successful
if (!$genresResult) {
    die("Error fetching genres: " . htmlspecialchars(mysqli_error($db), ENT_QUOTES, 'UTF-8'));
}

$errors = [];

if (isset($_POST['submit'])) {
    $studioID = $_POST['studio'];
    $name = $_POST['name'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $year = $_POST['year'] ?? '';
    $description = $_POST['description'] ?? '';
    $length = $_POST['length'] ?? '';
    $director = $_POST['director'] ?? '';

    $user_id = $_SESSION["user_id"];

    // Prepare and bind the SQL statement
    $sql = "INSERT INTO film (studio_id, name, rating, year, description, length, director, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, '')";
    $stmt = mysqli_prepare($db, $sql);

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "issssss", $studioID, $name, $rating, $year, $description, $length, $director);

    // Execute the statement
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $movie_id = mysqli_insert_id($db);

        // Insert movie-user relation into film_user table
        $sql_film_user = "INSERT INTO film_user (user_id, movie_id) VALUES (?, ?)";
        $stmt_film_user = mysqli_prepare($db, $sql_film_user);
        mysqli_stmt_bind_param($stmt_film_user, "ii", $user_id, $movie_id);
        mysqli_stmt_execute($stmt_film_user);

        // Insert movie-genre relations into film_genre table
        if (isset($_POST['genres']) && is_array($_POST['genres'])) {
            foreach ($_POST['genres'] as $genre_id) {
                $sql_film_genre = "INSERT INTO film_genre (movie_id, genre_id) VALUES (?, ?)";
                $stmt_film_genre = mysqli_prepare($db, $sql_film_genre);
                mysqli_stmt_bind_param($stmt_film_genre, "ii", $movie_id, $genre_id);
                mysqli_stmt_execute($stmt_film_genre);
            }
        }

        echo "Movie created successfully!";
    } else {
        $errors[] = "Error creating movie: " . htmlspecialchars(mysqli_error($db), ENT_QUOTES, 'UTF-8');
    }

    // Close the statement
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmt_film_user);
    mysqli_stmt_close($stmt_film_genre);
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Movie</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Add a movie to the list!</h2>
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
                <label class="label" for="name">Movie Name</label>
                <div class="field">
                    <input class="input" id="name" type="text" name="name" value="<?= $name ?? '' ?>" required/>
                </div>
            </div>
            <div>
                <label class="label">Genres</label>
                <div class="field">
                    <?php
                    $genresResult = mysqli_query($db, $genresQuery);

                    while ($genre = mysqli_fetch_assoc($genresResult)) {
                        echo '<input type="checkbox" name="genres[]" value="' . $genre['id'] . '">';
                        echo '<label>' . $genre['genre_name'] . '</label><br>';
                    }

                    mysqli_close($db);
                    ?>
                </div>
            </div>

            <div>
            <label for="studio">Studio:</label>
                <select name="studio" id="studio" required>
                 <?php foreach ($studios as $studio): ?>
                      <option value="<?= $studio['id'] ?>"><?= $studio['studio_name'] ?></option>
                 <?php endforeach; ?>
              </select>
            </div>
            <div>
                <label class="label" for="rating">Rating</label>
                <div class="field">
                    <input class="input" id="rating" type="text" name="rating" value="<?= $rating ?? '' ?>" required />
                </div>
            </div>
            <div>
                <label class="label" for="year">Year</label>
                <div class="field">
                    <input class="input" id="year" type="text" name="year" value="<?= $year ?? '' ?>" required />
                </div>
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="4" cols="50" required></textarea>
            </div>
            <div>
                <label for="length">Length</label>
                <input class="input" id="length" type="text" name="length" pattern="^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$" placeholder="hh:mm:ss" value="<?= $length ?? '' ?>" required>
            </div>
            <div>
                <label class="label" for="director">Director</label>
                <div class="field">
                    <input class="input" id="director" type="text" name="director" value="<?= $director ?? '' ?>" required/>
                </div>
            </div>
            <div class="bottom-submit">
                <div>
                    <button type="submit" name="submit">Add movie</button>
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
