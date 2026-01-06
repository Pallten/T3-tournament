<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title>Skapa Turnering</title>
    <link rel="stylesheet" href="create.css">
    <script>
        // Funktion för att uppdatera antalet valda spelare
        function updatePlayerCount() {
            // Hämta alla checkboxar
            const checkboxes = document.querySelectorAll('input[name="player_selection[]"]');
            let count = 0;

            // Räkna antalet markerade checkboxar
            checkboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    count++;
                }
            });

            // Uppdatera den dolda inputen med det valda antalet
            document.getElementById('tournament_size').value = count;
        }
    </script>

</head>

<body>
    <h1>Skapa Ny Turnering</h1>
    <form action="php.php" method="POST">
        <label for="tournament_name">Turneringsnamn:</label>
        <input type="text" id="tournament_name" name="tournament_name" required>
        <br><br>

        
            <label for="numberInput">Kast per match:</label>
            <input type="number" id="maxThrow" name="maxThrow">


            <br><br>


        <label for="TypeOfGame">Längd på spel:</label><br>
        <input type="radio" id="101" name="TypeOfGame" value="101">
        <label for="101">101</label><br>
        <input type="radio" id="301" name="TypeOfGame" value="301">
        <label for="301">301</label><br>
        <input type="radio" id="501" name="TypeOfGame" value="501">
        <label for="501">501</label>
        <br><br>

        <input type="hidden" id="tournament_size" name="tournament_size" value="0">

        <label>Välj Spelare:</label><br>
        <?php
        // Inkludera databasanslutningen
        require 'koppling.php';

        // Hantera tillägg av nya spelare
        if (isset($_POST['add'])) {
            $name = $_POST['name']; // Ändrat från 'Turneringsnamn' till 'name'
            $score = $_POST['score'];
            $stmt = $pdo->prepare("INSERT INTO people (name, score) VALUES (:name, :score)"); // Använd parametrar för säkerhet
            $stmt->execute(['name' => $name, 'score' => $score]);
        }

        try {
            // Hämta alla namn från "people" tabellen
            $stmt = $pdo->query('SELECT name FROM people');
            $people = $stmt->fetchAll();

            // Kontrollera om det finns några resultat
            if (count($people) > 0) {
                foreach ($people as $person) {
                    // Lägg till onchange för att uppdatera antalet spelare
                    echo '<input type="checkbox" name="player_selection[]" value="' . htmlspecialchars($person['name']) . '" onchange="updatePlayerCount()">' . htmlspecialchars($person['name']) . '<br>';
                }
            } else {
                echo 'Inga namn funna i tabellen.';
            }
        } catch (Exception $e) {
            // Logga och hantera fel
            error_log($e->getMessage());
            echo 'Ett fel uppstod när namnen skulle hämtas.';
        }
        ?>
        <br>
        <button type="submit">Skapa Turnering</button>
    </form>

    <h2>Ny spelare</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Namn" required>
        <input type="number" name="score" value="0" required>
        <button type="submit" name="add">Lägg till Spelare</button>
    </form>
</body>

</html>