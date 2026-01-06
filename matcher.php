<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title>Välj Vinnare</title>
    <link rel="stylesheet" href="501/501_stil.css">


    <style>
        #playerSelectionDialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 2px solid #000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-align: center;
        }

        #playerSelectionDialog p {
            margin-bottom: 10px;
            font-size: 16px;
        }

        #playerSelectionDialog button {
            margin: 5px;
            padding: 8px 16px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <?php
    require 'koppling.php';

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['match_id'])) {
        // Hämta matchinformation baserat på match_id
        $match_id = $_GET['match_id'];
        $stmt = $pdo->prepare('SELECT matches.*, tournament.game, tournament.maxThrow
        FROM matches
        JOIN tournament ON matches.TournamentId = tournament.TournamentId 
        WHERE matches.MatchId = :match_id');
        $stmt->execute(['match_id' => $match_id]);
        $match = $stmt->fetch();

        if ($match) {

            echo "<h1>Välj vinnare för Match " . htmlspecialchars($match['Position']) . " i Runda " . htmlspecialchars($match['Round']) . "</h1>";

            function getPlayerName($pdo, $playerId)
            {
                $stmt = $pdo->prepare('SELECT p.Name FROM people p JOIN players pl ON p.id = pl.PlayerId WHERE pl.PlayerId = :player_id');
                $stmt->execute(['player_id' => $playerId]);
                $player = $stmt->fetch(PDO::FETCH_ASSOC);
                return $player ? htmlspecialchars($player['Name']) : 'Okänt namn';
            }

            // Hämta spelare 1 och 2
            $player1Id = $match['Player1'];
            $player2Id = $match['Player2'];
            $typeOfGame = $match['game'];
            $maxThrow = $match['maxThrow'];
            $player1Name = getPlayerName($pdo, $player1Id);
            $player2Name = getPlayerName($pdo, $player2Id);



            echo "<form action='uppdatera_vinnare.php' method='POST'>";
            echo "<input type='hidden' name='match_id' value='" . htmlspecialchars($match['MatchId']) . "'>";
            echo "<input type='hidden' name='tournament_id' value='" . htmlspecialchars($match['TournamentId']) . "'>";

            echo "</form>";
        } else {
            echo "<p>Ingen match hittades.</p>";
        }
    }

    ?>

    <h1><?php echo $match['game'] ?> Dart Game</h1>
    <div class="player-container">
        <div id="player1" class="player active">
            <h2><?php echo $player1Name; ?></h2>
            <p>Poäng: <span id="score1"><?php echo $match['game'] ?></span></p>
            <p>Senaste poäng: <span id="lastScore1">0</span></p>
            <!--  <p>Antal poäng: <span id="lostScore1">0</span></p>  för att testa AVG-->
            <p>AVG: <span id="averageScore1">0</span></p>


        </div>
        <div id="player2" class="player">
            <h2><?php echo $player2Name; ?></h2>
            <p>Poäng: <span id="score2"><?php echo $match['game'] ?></span></p>
            <p>Senaste poäng: <span id="lastScore2">0</span></p>
            <!--  <p>Antal poäng: <span id="lostScore2">0</span></p>     för att testa AVG-->
            <p>AVG: <span id="averageScore2">0</span></p>
        </div>
    </div>

    <input type="text" id="scoreInput" placeholder="Ange poäng" />
    <div class="numpad">
        <button onclick="addToInput('1')">1</button>
        <button onclick="addToInput('2')">2</button>
        <button onclick="addToInput('3')">3</button>
        <button onclick="addToInput('4')">4</button>
        <button onclick="addToInput('5')">5</button>
        <button onclick="addToInput('6')">6</button>
        <button onclick="addToInput('7')">7</button>
        <button onclick="addToInput('8')">8</button>
        <button onclick="addToInput('9')">9</button>
        <button onclick="addToInput('0')">0</button>
        <button onclick="undoLastScore()">Ångra</button>
        <button onclick="submitScore(<?php echo htmlspecialchars($match['MatchId']); ?>)">Skicka</button>
    </div>

    <h3>Senaste poäng</h3>
    <div id="latest-scores"></div>

    <div class="last-scores">
        <h3>Senaste uppfyllda poäng:</h3>
        <p>Spelare 1: <span id="recentScore1">0</span></p>
        <p>Spelare 2: <span id="recentScore2">0</span></p>
    </div>
    <div class="wins">
        <h3>Vinster:</h3>
        <p>Spelare 1: <span id="player1Wins">0</span></p>
        <p>Spelare 2: <span id="player2Wins">0</span></p>
    </div>


    <div id="playerSelectionDialog" style="display: none;">
    <p>Max antal kast är uppnått för matchen.<br>
    Kasta 3 pilar och spelaren med högst poäng vinner<br>
    Vinnare:</p>
    <button onclick="selectPlayer(<?php echo $player1Id; ?>)"><?php echo $player1Name; ?></button>
    <button onclick="selectPlayer(<?php echo $player2Id; ?>)"><?php echo $player2Name; ?></button>
</div>

    <script>
// Funktion som hanterar valet av spelare
function selectPlayer(playerId) {
    alert(`Spelare med ID ${playerId} har valts`);
    console.log(`Spelare med ID ${playerId} har valts`);
    selectedPlayer = playerId; // Spara det valda spelarnumret
    document.getElementById("playerSelectionDialog").style.display = "none"; // Dölj dialogen efter valet

    // Kalla på vinnarfunktionen (om det behövs direkt här)
    determineWinner();
}


        // Skicka spelare ID:n till JavaScript
        const player1Id = <?php echo json_encode($player1Id); ?>;
        const player2Id = <?php echo json_encode($player2Id); ?>;
        const typeOfGame = <?php echo json_encode($typeOfGame); ?>;
        const maxThrow = <?php echo json_encode($maxThrow); ?>;
        

        // Lägg till lyssnare för Enter-knappen
        document.getElementById("scoreInput").addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                // Förhindra standardbeteendet så att formuläret inte skickas om du använder formulär
                event.preventDefault();
                // Anropar submitScore-funktionen med match_id
                submitScore(<?php echo json_encode($match['MatchId']); ?>);
            }
        });

            // Lyssna på klick utanför dialogrutan
    window.onclick = function(event) {
        const dialog = document.getElementById("playerSelectionDialog");
        if (event.target !== dialog && !dialog.contains(event.target)) {
            dialog.style.display = "none"; // Döljer dialogrutan om klickat utanför
        }
    }

    </script>

    <script src="501/kalkyl.js"></script>

</body>

</html>