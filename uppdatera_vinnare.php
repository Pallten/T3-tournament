<?php
// Inkludera databasanslutningen
include('koppling.php');  // Säkerställ att denna pekar på rätt fil

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hämta data från AJAX
    $winnerId = $_POST['winner_id'];
    $matchId = $_POST['match_id'];

    // Använd prepared statements för att uppdatera databasen
    $sql = "UPDATE matches SET Winner = :winnerId WHERE MatchId = :matchId";
    $stmt = $pdo->prepare($sql);

    // Binda parametrarna för säkerhet och kör frågan
    if ($stmt->execute(['winnerId' => $winnerId, 'matchId' => $matchId])) {
        echo "Vinnaren för matchen med MatchId $matchId har uppdaterats!";
    } else {
        echo "Fel vid uppdatering.";
    }
}
?>
