<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title>Create Tournament</title>
    <link rel="stylesheet" href="create.css">
    <script>
        //Function for updating number of players
        function updatePlayerCount() {
            // Fetch all boxes
            const checkboxes = document.querySelectorAll('input[name="player_selection[]"]');
            let count = 0;

            // Count the number of checked boxes
            checkboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    count++;
                }
            });

            // Update the hidden input with the selected number
            document.getElementById('tournament_size').value = count;
        }
    </script>

</head>

<body>
    <h1>Create new tournament</h1>
    <form action="php.php" method="POST">
        <label for="tournament_name">Tournament name:</label>
        <input type="text" id="tournament_name" name="tournament_name" required>
        <br><br>

        
            <label for="numberInput">Throws per game:</label>
            <input type="number" id="maxThrow" name="maxThrow">


            <br><br>


        <label for="TypeOfGame">Game lenght:</label><br>
        <input type="radio" id="101" name="TypeOfGame" value="101">
        <label for="101">101</label><br>
        <input type="radio" id="301" name="TypeOfGame" value="301">
        <label for="301">301</label><br>
        <input type="radio" id="501" name="TypeOfGame" value="501">
        <label for="501">501</label>
        <br><br>

        <input type="hidden" id="tournament_size" name="tournament_size" value="0">

        <label>Chose players:</label><br>
        <?php
        // Include the database connection
        require 'connection.php';

        // Manage the addition of new players
        if (isset($_POST['add'])) {
            $name = $_POST['name']; 
            $score = $_POST['score'];
            $stmt = $pdo->prepare("INSERT INTO people (name, score) VALUES (:name, :score)");
            $stmt->execute(['name' => $name, 'score' => $score]);
        }

        try {
          // Use parameters for security
            $stmt = $pdo->query('SELECT name FROM people');
            $people = $stmt->fetchAll();

            // Check if there are any results
            if (count($people) > 0) {
                foreach ($people as $person) {
                    // Add onchange to update the number of players
                    echo '<input type="checkbox" name="player_selection[]" value="' . htmlspecialchars($person['name']) . '" onchange="updatePlayerCount()">' . htmlspecialchars($person['name']) . '<br>';
                }
            } else {
                echo 'No names found in the table.';
            }
        } catch (Exception $e) {
           // Log and handle errors
            error_log($e->getMessage());
            echo 'An error occurred while retrieving the names..';
        }
        ?>
        <br>
        <button type="submit">Create tournament</button>
    </form>

    <h2>New Player</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Namn" required>
        <input type="number" name="score" value="0" required>
        <button type="submit" name="add">Add player</button>
    </form>
</body>


</html>
