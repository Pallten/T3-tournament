<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Lineup</title>
    <link rel="stylesheet" href="uppstallning.css">

</head>

<body>
    <a href="create.php">Go to create matches</a>

    <?php
    // Include database connection
    require 'connection.php';

    try {
        // Get the latest tournament
        $stmt = $pdo->query('SELECT * FROM tournament WHERE TournamentId = (SELECT MAX(TournamentId) FROM tournament)');
        $tournament = $stmt->fetch();

        if ($tournament) {
            echo "<h1>Lineup for tournament: " . htmlspecialchars($tournament['Turneringsnamn']) . "</h1>";

            // Get all matches for this tournament
            $stmtMatches = $pdo->prepare('SELECT * FROM matches WHERE TournamentId = :tournament_id ORDER BY Round, Position');
            $stmtMatches->execute(['tournament_id' => $tournament['TournamentId']]);
            $matches = $stmtMatches->fetchAll();


            // Function to calculate new position
            function calculateNewPosition($currentPosition, $tournamentSize)
            {
                // Check if current position is valid (should be less than or equal to tournament size)
                if ($currentPosition >= 1 && $currentPosition < $tournamentSize) {
                    // Calculate new position by dividing by 2 (to halve the position in the next round)
                    return intval($currentPosition / 2);
                }
                return null; // If there are no more positions to move to
            }



            // Function to get player name
            function getPlayerName($pdo, $playerId)
            {
                $stmt = $pdo->prepare('SELECT p.Name FROM people p JOIN players pl ON p.id = pl.PlayerId WHERE pl.PlayerId = :player_id');
                $stmt->execute(['player_id' => $playerId]);
                $player = $stmt->fetch(PDO::FETCH_ASSOC);

                return $player ? htmlspecialchars($player['Name']) : ''; // Return the name or a default text
            }



            foreach ($matches as $match) {
                if (array_key_exists('Winner', $match)) {
                    if (!is_null($match['Winner'])) {

                        // Get the latest tournament including size
                        $stmt = $pdo->query('SELECT * FROM tournament WHERE TournamentId = (SELECT MAX(TournamentId) FROM tournament)');
                        $tournament = $stmt->fetch();
                        $tournamentSize = $tournament['Size']; // Add tournament size
    
                        // Determine new position for the winner
                        $newPosition = calculateNewPosition($match['Position'], $tournamentSize);


                        if ($newPosition !== null) { // Check if new position is valid
                            if ($match['Position'] % 2 == 0) {
                                // If position is even, place winner in Player2
                                $stmtUpdate = $pdo->prepare('UPDATE matches 
                                    SET Player2 = :winner 
                                    WHERE TournamentId = :tournament_id AND Position = :new_position');
                                $stmtUpdate->execute([
                                    'winner' => $match['Winner'],
                                    'tournament_id' => $tournament['TournamentId'],
                                    'new_position' => $newPosition
                                ]);
                            } else {
                                // If position is odd, place winner in Player1
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
                            echo "<br>No valid new position for match " . htmlspecialchars($match['MatchId']);
                        }
                    } else {

                    }
                } else {
                    echo "<br>Winner field not found in match: " . htmlspecialchars($match['MatchId']);
                }
            }

            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    if ($match['Winner'] === NULL) {
                        // Get the players' IDs
                        $player1Id = $match['Player1']; // It is the ID for player 1
                        $player2Id = $match['Player2']; // It is the ID for player 2
    
                        // Get player names based on PlayerId
                        $player1Name = getPlayerName($pdo, $player1Id);
                        $player2Name = getPlayerName($pdo, $player2Id);

                        // Display match information and create link to matcher.php
    
                        echo "<p>Match " . htmlspecialchars($match['Position']) . " in round " . htmlspecialchars($match['Round']) . " <br>MatchID " . htmlspecialchars($match['MatchId']) . ":<br> " .
                            "<input type='hidden' name='match_id' value='" . htmlspecialchars($match['MatchId']) . "'>" . // Hidden field for match_id
                            "<a href='matcher.php?match_id=" . htmlspecialchars($match['MatchId']) . "&winner_id=" . htmlspecialchars($player1Id) . "'>" . $player1Name . "</a> " .
                            "vs " .
                            "<a href='matcher.php?match_id=" . htmlspecialchars($match['MatchId']) . "&winner_id=" . htmlspecialchars($player2Id) . "'>" . $player2Name . "</a></p>";

                    } else {
                        // Get winner name for completed matches
                        $winnerId = $match['Winner']; // Assume Winner is PlayerId
                        $winnerName = getPlayerName($pdo, $winnerId);
                        echo "<p>Match " . htmlspecialchars($match['Position']) . " in round " . htmlspecialchars($match['Round']) . " <br>MatchID " . htmlspecialchars($match['MatchId']) . ":<br> COMPLETED, winner " . $winnerName . "</p>";
                    }
                }
            } else {
                echo "<p>No matches found.</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</body>

</html>
