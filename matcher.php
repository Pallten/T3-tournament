<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title>Chose winner</title>
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
    require 'connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['match_id'])) {
        // Get match information based on match_id
        $match_id = $_GET['match_id'];
        $stmt = $pdo->prepare('SELECT matches.*, tournament.game, tournament.maxThrow
        FROM matches
        JOIN tournament ON matches.TournamentId = tournament.TournamentId 
        WHERE matches.MatchId = :match_id');
        $stmt->execute(['match_id' => $match_id]);
        $match = $stmt->fetch();

        if ($match) {

            echo "<h1>Choose the winner for Match " . htmlspecialchars($match['Position']) . " in Round " . htmlspecialchars($match['Round']) . "</h1>";

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
            echo "<p>No match found.</p>";
        }
    }

    ?>

    <h1><?php echo $match['game'] ?> Dart Game</h1>
    <div class="player-container">
        <div id="player1" class="player active">
            <h2><?php echo $player1Name; ?></h2>
            <p>Score: <span id="score1"><?php echo $match['game'] ?></span></p>
            <p>Last score: <span id="lastScore1">0</span></p>
            <!-- <p>Number of points: <span id="lostScore1">0</span></p> to test AVG-->
            <p>AVG: <span id="averageScore1">0</span></p>


        </div>
        <div id="player2" class="player">
            <h2><?php echo $player2Name; ?></h2>
            <p>Score: <span id="score2"><?php echo $match['game'] ?></span></p>
            <p>Last score: <span id="lastScore2">0</span></p>
            <!-- <p>Number of points: <span id="lostScore1">0</span></p> to test AVG-->
            <p>AVG: <span id="averageScore2">0</span></p>
        </div>
    </div>

    <input type="text" id="scoreInput" placeholder="Submit score" />
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

    <h3>Last score</h3>
    <div id="latest-scores"></div>

    <div class="last-scores">
        <h3>Latest points fulfilled:</h3>
        <p>Player 1: <span id="recentScore1">0</span></p>
        <p>Player 2: <span id="recentScore2">0</span></p>
    </div>
    <div class="wins">
        <h3>Wins:</h3>
        <p>Player 1: <span id="player1Wins">0</span></p>
        <p>Player 2: <span id="player2Wins">0</span></p>
    </div>


    <div id="playerSelectionDialog" style="display: none;">
    <p>Maximum number of throws has been reached for the match.<br>
    Throw 3 darts and the player with the highest score wins<br>
    Winner:</p>
    <button onclick="selectPlayer(<?php echo $player1Id; ?>)"><?php echo $player1Name; ?></button>
    <button onclick="selectPlayer(<?php echo $player2Id; ?>)"><?php echo $player2Name; ?></button>
</div>

    <script>
// Function that handles player selection
function selectPlayer(playerId) {
    alert(`Spelare med ID ${playerId} har valts`);
    console.log(`Player with ID ${playerId} have been chosen`);
    selectedPlayer = playerId; // Save the selected player number
    document.getElementById("playerSelectionDialog").style.display = "none"; // Hide the dialog after selection

   // Call the winner function (if needed directly here)
    determineWinner();
}


        // Send the player ID to JavaScript
        const player1Id = <?php echo json_encode($player1Id); ?>;
        const player2Id = <?php echo json_encode($player2Id); ?>;
        const typeOfGame = <?php echo json_encode($typeOfGame); ?>;
        const maxThrow = <?php echo json_encode($maxThrow); ?>;
        

        // Add listener for the Enter button
        document.getElementById("scoreInput").addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                // Prevent the default behavior so that the form is not submitted if you use forms
                event.preventDefault();
                // Calls the submitScore function with match_id
                submitScore(<?php echo json_encode($match['MatchId']); ?>);
            }
        });

            // Listen for clicks outside the dialog box
    window.onclick = function(event) {
        const dialog = document.getElementById("playerSelectionDialog");
        if (event.target !== dialog && !dialog.contains(event.target)) {
            dialog.style.display = "none"; // Hides the dialog if clicked outside
        }
    }

    </script>

    <script src="501/kalkyl.js"></script>

</body>


</html>
