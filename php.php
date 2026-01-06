<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tournament</title>
    <link rel="stylesheet" href="php.css">
</head>
<body>
    <h1>Create Tournament</h1>

    <?php
    // Include database connection
    require 'connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get data from the form
        $tournamentTurneringsnamn = $_POST['tournament_name'];
        $selectedPlayers = $_POST['player_selection']; // Array with chosen players
        $typeOfGame = $_POST['TypeOfGame'];
        $maxThrow = $_POST['maxThrow'];


        try {
            // Create the tournament
            $tournamentSize = count($selectedPlayers); // Number of players based on selected
            $stmt = $pdo->prepare('INSERT INTO tournament (Turneringsnamn, Size, game, maxThrow) VALUES (:name, :size, :game, :maxThrow)');
            $stmt->execute(['name' => $tournamentTurneringsnamn, 'size' => $tournamentSize, 'game'=>$typeOfGame, 'maxThrow'=>$maxThrow]);
            $tournament_id = $pdo->lastInsertId(); // Get the ID of the newly created tournament

            // Total number of matches should be tournamentSize - 1
            $totalMatches = $tournamentSize - 1;

            // Shuffle the players
            shuffle($selectedPlayers);

            // Add selected players to the tournament and get PlayerId
            $stmtPlayer = $pdo->prepare('INSERT INTO Players (TournamentId, PlayerId, Name, Position) 
            SELECT :tournament_id, p.id, p.name, :position 
            FROM people p 
            WHERE p.name = :name
            ON DUPLICATE KEY UPDATE Position = VALUES(Position)');

            $playerIds = [];
            foreach ($selectedPlayers as $index => $player_name) {
                $stmtPlayer->execute([
                    'tournament_id' => $tournament_id,
                    'name' => $player_name,
                    'position' => $index // Use index as position
                ]);
                // Get PlayerId after the player has been added
                $playerIdStmt = $pdo->prepare('SELECT PlayerId FROM Players WHERE TournamentId = :tournament_id AND Name = :name');
                $playerIdStmt->execute(['tournament_id' => $tournament_id, 'name' => $player_name]);
                $playerIds[] = $playerIdStmt->fetchColumn(); // Save PlayerId
            }

            // Calculate how many matches per round
            $rounds = [];
            $matchesInRound = 1;
            $remainingMatches = $totalMatches;

            while ($remainingMatches > 0) {
                if ($remainingMatches >= $matchesInRound) {
                    $rounds[] = $matchesInRound;
                    $remainingMatches -= $matchesInRound;
                } else {
                    $rounds[] = $remainingMatches;
                    break;
                }
                $matchesInRound *= 2; // Doubles the number of matches for each previous round
            }

            // Create matches for each round
            $matchPosition = $totalMatches; // Match position in the tournament
            $roundNumber = count($rounds); // Start with the highest round

            foreach (array_reverse($rounds) as $matchesInThisRound) {
                for ($i = 0; $i < $matchesInThisRound; $i++) {
                    $stmt = $pdo->prepare("INSERT INTO matches (TournamentId, Round, Position) VALUES (:tournament_id, :round, :position)");
                    $stmt->execute([
                        'tournament_id' => $tournament_id,
                        'round' => $roundNumber,
                        'position' => $matchPosition
                    ]);
                    $matchPosition--; // Decrease for each new match
                }
                $roundNumber--; // Go to the next round down
            }

            // Assign players to matches based on Position
            $matchPosition = $totalMatches; // Reset match position
            $playerIndex = 0; // Index for players

            // We need to fetch the matches here to ensure they exist
            $stmtFetchMatches = $pdo->prepare("SELECT Position FROM matches WHERE TournamentId = :tournament_id ORDER BY Position DESC");
            $stmtFetchMatches->execute(['tournament_id' => $tournament_id]);
            $matches = $stmtFetchMatches->fetchAll(PDO::FETCH_ASSOC);

            foreach ($matches as $match) {
                // Fetch current position
                $position = $match['Position'];
                $stmtMatch = $pdo->prepare("UPDATE matches SET Player1 = :player1, Player2 = :player2 WHERE TournamentId = :tournament_id AND Position = :position");
                $stmtMatch->execute([
                    'tournament_id' => $tournament_id,
                    'position' => $position,
                    'player1' => isset($playerIds[$totalMatches - $playerIndex]) ? $playerIds[$totalMatches - $playerIndex] : null,
                    'player2' => isset($playerIds[$totalMatches - $playerIndex - 1]) ? $playerIds[$totalMatches - $playerIndex - 1] : null
                ]);
                $playerIndex += 2; // Increase by 2 for the next match
            }

            // Confirmation message with tournament players
            echo '<div class="confirmation">';
            echo '<h2>Tournament created with the following players</h2>';
            echo implode(', <br>', array_map('htmlspecialchars', $selectedPlayers));
            echo '<br><a href="uppstallning.php" class="button">Proceed to the tournament</a>'; // Link to proceed to the tournament
            echo '</div>';
        

        } catch (Exception $e) {
            // Log and handle errors
            error_log($e->getMessage());
            echo 'Ett fel uppstod vid skapandet av turneringen: ' . htmlspecialchars($e->getMessage());
        }
    }
    ?>

</body>
</html>


