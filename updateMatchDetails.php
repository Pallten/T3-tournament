<?php
include 'koppling.php'; // Databasanslutning

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $player1Id = $_POST['player1Id'];
    $player1AverageScore = $_POST['player1AverageScore'];
    $player1Kast = $_POST['player1Kast'];
    $player2Id = $_POST['player2Id'];
    $player2AverageScore = $_POST['player2AverageScore'];
    $player2Kast = $_POST['player2Kast'];

    // Kontrollera att värdena finns och är giltiga
    if (
        !empty($player1Id) && is_numeric($player1AverageScore) && is_numeric($player1Kast) &&
        !empty($player2Id) && is_numeric($player2AverageScore) && is_numeric($player2Kast)
    ) {
        // Hämta nuvarande tot_kast och average_score för player1
        $stmtCheck1 = $pdo->prepare("SELECT tot_kast, average_score FROM people WHERE id = :playerId");
        $stmtCheck1->bindParam(':playerId', $player1Id, PDO::PARAM_INT);
        $stmtCheck1->execute();
        $player1Data = $stmtCheck1->fetch(PDO::FETCH_ASSOC);
        
        // Beräkna nytt medelvärde för player1
        $newPlayer1AverageScore = (($player1Data['average_score'] * $player1Data['tot_kast']) + ($player1Kast * $player1AverageScore)) / ($player1Data['tot_kast'] + $player1Kast);
        
        // Uppdatera player1
        $stmt1 = $pdo->prepare("UPDATE people SET average_score = :averageScore, tot_kast = tot_kast + :kast WHERE id = :playerId");
        $stmt1->bindParam(':averageScore', $newPlayer1AverageScore, PDO::PARAM_STR);
        $stmt1->bindParam(':kast', $player1Kast, PDO::PARAM_INT);
        $stmt1->bindParam(':playerId', $player1Id, PDO::PARAM_INT);
        $stmt1->execute();

        // Hämta nuvarande tot_kast och average_score för player2
        $stmtCheck2 = $pdo->prepare("SELECT tot_kast, average_score FROM people WHERE id = :playerId");
        $stmtCheck2->bindParam(':playerId', $player2Id, PDO::PARAM_INT);
        $stmtCheck2->execute();
        $player2Data = $stmtCheck2->fetch(PDO::FETCH_ASSOC);
        
        // Beräkna nytt medelvärde för player2
        $newPlayer2AverageScore = (($player2Data['average_score'] * $player2Data['tot_kast']) + ($player2Kast * $player2AverageScore)) / ($player2Data['tot_kast'] + $player2Kast);
        
        // Uppdatera player2
        $stmt2 = $pdo->prepare("UPDATE people SET average_score = :averageScore, tot_kast = tot_kast + :kast WHERE id = :playerId");
        $stmt2->bindParam(':averageScore', $newPlayer2AverageScore, PDO::PARAM_STR);
        $stmt2->bindParam(':kast', $player2Kast, PDO::PARAM_INT);
        $stmt2->bindParam(':playerId', $player2Id, PDO::PARAM_INT);
        $stmt2->execute();

        echo "Uppdatering lyckades!";
    } else {
        echo "Ogiltiga data.";
    }
} else {
    echo "Ogiltig begäran.";
}
?>
