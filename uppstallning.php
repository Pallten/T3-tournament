<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title>Uppställning</title>
    <link rel="stylesheet" href="uppstallning.css">

</head>

<body>
    <a href="create.php">Gå till skapa matcher</a>

    <?php
    // Inkludera databasanslutningen
    require 'koppling.php';

    try {
        // Hämta den senaste turneringen
        $stmt = $pdo->query('SELECT * FROM tournament WHERE TournamentId = (SELECT MAX(TournamentId) FROM tournament)');
        $tournament = $stmt->fetch();

        if ($tournament) {
            echo "<h1>Uppställning för turnering: " . htmlspecialchars($tournament['Turneringsnamn']) . "</h1>";

            // Hämta alla matcher för denna turnering
            $stmtMatches = $pdo->prepare('SELECT * FROM matches WHERE TournamentId = :tournament_id ORDER BY Round, Position');
            $stmtMatches->execute(['tournament_id' => $tournament['TournamentId']]);
            $matches = $stmtMatches->fetchAll();


            // Funktion för att beräkna ny position
            function calculateNewPosition($currentPosition, $tournamentSize)
            {
                // Kolla om nuvarande position är giltig (bör vara mindre än eller lika med turneringsstorlek)
                if ($currentPosition >= 1 && $currentPosition < $tournamentSize) {
                    // Beräkna ny position genom att dela med 2 (för att halvera positionen i nästa runda)
                    return intval($currentPosition / 2);
                }
                return null; // Om det inte finns fler positioner att flytta till
            }



            // Funktion för att hämta spelarnamn
            function getPlayerName($pdo, $playerId)
            {
                $stmt = $pdo->prepare('SELECT p.Name FROM people p JOIN players pl ON p.id = pl.PlayerId WHERE pl.PlayerId = :player_id');
                $stmt->execute(['player_id' => $playerId]);
                $player = $stmt->fetch(PDO::FETCH_ASSOC);

                return $player ? htmlspecialchars($player['Name']) : ''; // Returnera namnet eller en standardtext
            }



            foreach ($matches as $match) {
                if (array_key_exists('Winner', $match)) {
                    if (!is_null($match['Winner'])) {

                        // Hämta den senaste turneringen inklusive storlek
                        $stmt = $pdo->query('SELECT * FROM tournament WHERE TournamentId = (SELECT MAX(TournamentId) FROM tournament)');
                        $tournament = $stmt->fetch();
                        $tournamentSize = $tournament['Size']; // Lägg till turneringsstorlek
    
                        // Bestäm ny position för vinnaren
                        $newPosition = calculateNewPosition($match['Position'], $tournamentSize);


                        if ($newPosition !== null) { // Kontrollera om ny position är giltig
                            if ($match['Position'] % 2 == 0) {
                                // Om positionen är jämn, placera vinnaren i Player2
                                $stmtUpdate = $pdo->prepare('UPDATE matches 
                                    SET Player2 = :winner 
                                    WHERE TournamentId = :tournament_id AND Position = :new_position');
                                $stmtUpdate->execute([
                                    'winner' => $match['Winner'],
                                    'tournament_id' => $tournament['TournamentId'],
                                    'new_position' => $newPosition
                                ]);
                            } else {
                                // Om positionen är udda, placera vinnaren i Player1
                                $stmtUpdate = $pdo->prepare('UPDATE matches 
                                    SET Player1 = :winner 
                                    WHERE TournamentId = :tournament_id AND Position = :new_position');
                                $stmtUpdate->execute([
                                    'winner' => $match['Winner'],
                                    'tournament_id' => $tournament['TournamentId'],
                                    'new_position' => $newPosition
                                ]);
                            }
                        } else {
                            echo "<br>Ingen giltig ny position för match " . htmlspecialchars($match['MatchId']);
                        }
                    } else {

                    }
                } else {
                    echo "<br>Winner-fältet hittades inte i match: " . htmlspecialchars($match['MatchId']);
                }
            }

            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    if ($match['Winner'] === NULL) {
                        // Hämta spelarnas ID:n
                        $player1Id = $match['Player1']; // Det är ID:t för spelare 1
                        $player2Id = $match['Player2']; // Det är ID:t för spelare 2
    
                        // Hämta spelarnamn baserat på PlayerId
                        $player1Name = getPlayerName($pdo, $player1Id);
                        $player2Name = getPlayerName($pdo, $player2Id);

                        // Visa information om matchen och skapa länk till matcher.php
    
                        echo "<p>Match " . htmlspecialchars($match['Position']) . " i runda " . htmlspecialchars($match['Round']) . " <br>MatchID " . htmlspecialchars($match['MatchId']) . ":<br> " .
                            "<input type='hidden' name='match_id' value='" . htmlspecialchars($match['MatchId']) . "'>" . // Dold fält för match_id
                            "<a href='matcher.php?match_id=" . htmlspecialchars($match['MatchId']) . "&winner_id=" . htmlspecialchars($player1Id) . "'>" . $player1Name . "</a> " .
                            "vs " .
                            "<a href='matcher.php?match_id=" . htmlspecialchars($match['MatchId']) . "&winner_id=" . htmlspecialchars($player2Id) . "'>" . $player2Name . "</a></p>";

                    } else {
                        // Hämta vinnarnamn för avslutade matcher
                        $winnerId = $match['Winner']; // Anta att Winner är PlayerId
                        $winnerName = getPlayerName($pdo, $winnerId);
                        echo "<p>Match " . htmlspecialchars($match['Position']) . " i runda " . htmlspecialchars($match['Round']) . " <br>MatchID " . htmlspecialchars($match['MatchId']) . ":<br> AVKLARAD, vinnare " . $winnerName . "</p>";
                    }
                }
            } else {
                echo "<p>Inga matcher hittades.</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p>Ett fel uppstod: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</body>

</html>