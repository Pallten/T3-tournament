<?php
include 'connection.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $playerId = $_POST['playerId'];
    $averageScore = $_POST['averageScore'];

    // Check that the values ​​exist and are valid
    if (!empty($playerId) && is_numeric($averageScore)) {
        $stmt = $pdo->prepare("UPDATE people SET average_score = :averageScore WHERE id = :playerId");
        $stmt->bindParam(':averageScore', $averageScore, PDO::PARAM_STR);
        $stmt->bindParam(':playerId', $playerId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Update successful!";
        } else {
            echo "Update failed.";
        }
    } else {
        echo "Invalid data.";
    }
} else {
    echo "Invalid request.";
}
?>

