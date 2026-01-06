<?php
// Include the database connection
include('connection.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from AJAX
    $winnerId = $_POST['winner_id'];
    $matchId = $_POST['match_id'];

    // Use prepared statements to update the database
    $sql = "UPDATE matches SET Winner = :winnerId WHERE MatchId = :matchId";
    $stmt = $pdo->prepare($sql);

    // Bind the security parameters and run the query
    if ($stmt->execute(['winnerId' => $winnerId, 'matchId' => $matchId])) {
        echo "The winner for the match with MatchId $matchId has been updated!";
    } else {
        echo "Error during update.";
    }
}
?>

