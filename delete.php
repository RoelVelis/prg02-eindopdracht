<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || !$_SESSION["is_admin"]) {
    // Redirect to the login page or handle accordingly
    header("Location: login.php");
    exit();
}
/**
 * @var mysqli $db
 */

require_once 'includes/database.php';

if (isset($_GET['movie_id'])) {
    $movie_id = $_GET['movie_id'];

    // Perform the deletion
    $deleteFilmQuery = "DELETE FROM film WHERE film_id = $movie_id";
    $deleteFilmResult = mysqli_query($db, $deleteFilmQuery);

    // Check if the deletion was successful
    if ($deleteFilmResult) {
        // Redirect back to the movie list
        header("Location: secure.php");
        exit();
    } else {
        // Handle the error, e.g., display an error message
        echo "Error deleting movie: " . mysqli_error($db);
    }
} else {
    // Handle the case where 'movie_id' is not set in the URL
    echo "Invalid request. Movie ID not provided.";
}
