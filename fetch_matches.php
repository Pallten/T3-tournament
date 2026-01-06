<?php
require 'connection.php';

try {
    $stmt = $pdo->prepare('
        SELECT m.*, 
               p1.Name AS Player1Name, 
               p2.Name AS Player2Name 
        FROM matches m 
        LEFT JOIN players pl1 ON m.Player1 = pl1.PlayerId
        LEFT JOIN people p1 ON pl1.PlayerId = p1.id
        LEFT JOIN players pl2 ON m.Player2 = pl2.PlayerId
        LEFT JOIN people p2 ON pl2.PlayerId = p2.id
        WHERE m.TournamentId = (SELECT MAX(TournamentId) FROM tournament) 
        ORDER BY m.Round, m.Position
    ');
    $stmt->execute();
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($matches);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>



//FUNGERAR INTE FÖR NU!
