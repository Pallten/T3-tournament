<?php
include 'koppling.php'; // Databasanslutning

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $playerId = $_POST['playerId'];
    $averageScore = $_POST['averageScore'];

    // Kontrollera att värdena finns och är giltiga
    if (!empty($playerId) && is_numeric($averageScore)) {
        $stmt = $pdo->prepare("UPDATE people SET average_score = :averageScore WHERE id = :playerId");
        $stmt->bindParam(':averageScore', $averageScore, PDO::PARAM_STR);
        $stmt->bindParam(':playerId', $playerId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Uppdatering lyckades!";
        } else {
            echo "Uppdatering misslyckades.";
        }
    } else {
        echo "Ogiltiga data.";
    }
} else {
    echo "Ogiltig begäran.";
}
?>
