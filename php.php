<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skapa Turnering</title>
    <link rel="stylesheet" href="php.css">
</head>
<body>
    <h1>Skapa Turnering</h1>

    <?php
    // Inkludera databasanslutningen
    require 'koppling.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Hämta data från formuläret
        $tournamentTurneringsnamn = $_POST['tournament_name'];
        $selectedPlayers = $_POST['player_selection']; // Array med valda spelare
        $typeOfGame = $_POST['TypeOfGame'];
        $maxThrow = $_POST['maxThrow'];


        try {
            // Skapa turneringen
            $tournamentSize = count($selectedPlayers); // Antal spelare baserat på valda
            $stmt = $pdo->prepare('INSERT INTO tournament (Turneringsnamn, Size, game, maxThrow) VALUES (:name, :size, :game, :maxThrow)');
            $stmt->execute(['name' => $tournamentTurneringsnamn, 'size' => $tournamentSize, 'game'=>$typeOfGame, 'maxThrow'=>$maxThrow]);
            $tournament_id = $pdo->lastInsertId(); // Hämta ID för den nyss skapade turneringen

            // Totalt antal matcher ska vara tournamentSize - 1
            $totalMatches = $tournamentSize - 1;

            // Slumpla spelarna
            shuffle($selectedPlayers); // Slumpar ordningen på spelarna

            // Lägg till valda spelare i turneringen och hämta PlayerId
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
                    'position' => $index // Använd index som position
                ]);
                // Hämta PlayerId efter att spelaren har lagts till
                $playerIdStmt = $pdo->prepare('SELECT PlayerId FROM Players WHERE TournamentId = :tournament_id AND Name = :name');
                $playerIdStmt->execute(['tournament_id' => $tournament_id, 'name' => $player_name]);
                $playerIds[] = $playerIdStmt->fetchColumn(); // Spara PlayerId
            }

            // Beräkna hur många matcher per runda
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
                $matchesInRound *= 2; // Dubblar antalet matcher för varje föregående runda
            }

            // Skapa matcher för varje runda
            $matchPosition = $totalMatches; // Matchposition i turneringen
            $roundNumber = count($rounds); // Starta med högsta rundan

            foreach (array_reverse($rounds) as $matchesInThisRound) {
                for ($i = 0; $i < $matchesInThisRound; $i++) {
                    $stmt = $pdo->prepare("INSERT INTO matches (TournamentId, Round, Position) VALUES (:tournament_id, :round, :position)");
                    $stmt->execute([
                        'tournament_id' => $tournament_id,
                        'round' => $roundNumber,
                        'position' => $matchPosition
                    ]);
                    $matchPosition--; // Minska för varje ny match
                }
                $roundNumber--; // Gå till nästa runda nedåt
            }

            // Tilldela spelare till matcherna baserat på Position
            $matchPosition = $totalMatches; // Återställ matchposition
            $playerIndex = 0; // Index för spelare

            // Vi behöver hämta matcherna här för att säkerställa att de finns
            $stmtFetchMatches = $pdo->prepare("SELECT Position FROM matches WHERE TournamentId = :tournament_id ORDER BY Position DESC");
            $stmtFetchMatches->execute(['tournament_id' => $tournament_id]);
            $matches = $stmtFetchMatches->fetchAll(PDO::FETCH_ASSOC);

            foreach ($matches as $match) {
                // Hämta den nuvarande positionen
                $position = $match['Position'];
                $stmtMatch = $pdo->prepare("UPDATE matches SET Player1 = :player1, Player2 = :player2 WHERE TournamentId = :tournament_id AND Position = :position");
                $stmtMatch->execute([
                    'tournament_id' => $tournament_id,
                    'position' => $position,
                    'player1' => isset($playerIds[$totalMatches - $playerIndex]) ? $playerIds[$totalMatches - $playerIndex] : null,
                    'player2' => isset($playerIds[$totalMatches - $playerIndex - 1]) ? $playerIds[$totalMatches - $playerIndex - 1] : null
                ]);
                $playerIndex += 2; // Öka med 2 för nästa match
            }

            // Bekräftelsemeddelande med turneringens spelare
            echo '<div class="confirmation">';
            echo '<h2>Turnering skapad med följande spelare</h2>';
            echo implode(', <br>', array_map('htmlspecialchars', $selectedPlayers));
            echo '<br><a href="uppstallning.php" class="button">Gå vidare till turneringen</a>'; // Länk för att gå vidare till turneringen
            echo '</div>';
        

        } catch (Exception $e) {
            // Logga och hantera fel
            error_log($e->getMessage());
            echo 'Ett fel uppstod vid skapandet av turneringen: ' . htmlspecialchars($e->getMessage());
        }
    }
    ?>

</body>
</html>
