let currentPlayer = 1;
let player1Wins = 0;
let player2Wins = 0;
let player1Kast = 0;
let player2Kast = 0;
let scoreHistory = []; // Array för att hålla poänghistorik
let player1TotalScore = 0; // Håller totalsumman för spelare 1
let player2TotalScore = 0; // Håller totalsumman för spelare 2
let selectedPlayer; // Global variabel för att lagra valet av spelare

const matchId = document.querySelector('input[name="match_id"]').value;

// Fokus på scoreInput när sidan laddas
window.onload = function () {
    document.getElementById("scoreInput").focus();
};



// Funktion för att lägga till värde till input-fältet
function addToInput(value) {
    const inputField = document.getElementById('scoreInput');
    inputField.value += value;
}

// Funktion för att skifta spelare.
function togglePlayer() {
    currentPlayer = currentPlayer === 1 ? 2 : 1;
    document.getElementById('player1').classList.toggle('active', currentPlayer === 1);
    document.getElementById('player2').classList.toggle('active', currentPlayer === 2);
}

// Funktion för att skicka poäng
function submitScore() {
    const inputField = document.getElementById('scoreInput');
    const scoreDisplay = document.getElementById(`score${currentPlayer}`);
    const lastScoreDisplay = document.getElementById(`lastScore${currentPlayer}`);
    // const lostScoreDisplay = document.getElementById(`lostScore${currentPlayer}`);
    const recentScoreDisplay = document.getElementById(`recentScore${currentPlayer}`);
    const points = parseInt(inputField.value) || 0;

    const matchId = document.querySelector('input[name="match_id"]').value;

    // SKA SKIFTAS TILL 180 EFTER ALPHA!
    if (points > 501) {
        alert("ver 8.");
        return;
    }

    // DU ÄR TJOCK!
    if (points < 0 || (parseInt(scoreDisplay.innerText) - points < 0)) {
        alert("Ogiltig poäng! Gå ut med rätt!");
        return;
    }

    // Minska poängen
    const currentScore = parseInt(scoreDisplay.innerText) - points;
    // const lostScore = (typeOfGame - currentScore); // Använd din logik för att få currentScore
    const lostScore = typeOfGame + currentScore;
    // lostScoreDisplay.innerText = lostScore; // Visa borttagna poäng för den aktuella spelaren

    if (currentPlayer === 1) {
        player1Kast++;
        player1TotalScore += points; // Uppdatera totalscore för spelare 1
        console.log(`Player 1 TOT: ${player1TotalScore}`);
    } else {
        player2Kast++;
        player2TotalScore += points; // Uppdatera totalscore för spelare 2
        console.log(`Player 2 TOT: ${player2TotalScore}`);
    }


    // För att se hur många kast varje spelare har gjort
    //console.log(`Player 1 TOT: ${player1TotalScore}`); 
    //console.log(`Player 2 TOT: ${player2TotalScore}`);

    const averageScore = currentPlayer === 1
        ? (player1TotalScore / player1Kast).toFixed(2)
        : (player2TotalScore / player2Kast).toFixed(2);

    // Visa averageScore (lägg till ett element i din HTML om du vill visa det)
    const averageScoreDisplay = document.getElementById(`averageScore${currentPlayer}`);
    averageScoreDisplay.innerText = averageScore; // Visa averageScore för den aktuella spelaren

    // Spara den tidigare poängen innan den uppdateras
    scoreHistory.push({ playerId: currentPlayer, score: parseInt(scoreDisplay.innerText) });


    
    // Vid avslutad match när poängen är 0
    if (currentScore === 0) {
        const WINNER_ID = currentPlayer === 1 ? player1Id : player2Id;
        alert(`Spelare ${WINNER_ID} har vunnit! OCH DET HÄR ÄR ETT TEST`);

        // SKA SÄTTAS IGÅNG SENARE!  
     //   window.location.href = 'uppstallning.php';
        document.getElementById('score1').innerText = typeOfGame;
        document.getElementById('score2').innerText = typeOfGame;

        const player1AverageScore = (player1Kast > 0) ? (player1TotalScore / player1Kast).toFixed(2) : 0;
        const player2AverageScore = (player2Kast > 0) ? (player2TotalScore / player2Kast).toFixed(2) : 0;

        updatePlayersAverageScore(player1Id, player1AverageScore, player1Kast, player2Id, player2AverageScore, player2Kast);

        if (currentPlayer === 1) {
            player1Wins++;
            document.getElementById('player1Wins').innerText = player1Wins;
            updateWinnerInDatabase(player1Id, matchId);
        } else {
            player2Wins++;
            document.getElementById('player2Wins').innerText = player2Wins;
            updateWinnerInDatabase(player2Id, matchId);
        }
    } else {
        scoreDisplay.innerText = currentScore;
    }


                // LIMIT FÖR ANTAL KAST
                // Funktion för att visa dialogen
                function showPlayerSelectionDialog() {
                    document.getElementById("playerSelectionDialog").style.display = "block";
                }
                //SIFFRAN SKA SKIFTAS TILL ANTAL MAX KAST
                if (player1Kast + player2Kast === (maxThrow)*2) {
                    console.log("Player 1 har kastat 2 gånger");
                    showPlayerSelectionDialog(); // Visa den anpassade dialogen när gränsen för kast är nådd
                }



    lastScoreDisplay.innerText = points;
    recentScoreDisplay.innerText = points;
    inputField.value = '';

    togglePlayer();
    
}

// Funktion för att avgöra vinnaren
function determineWinner() {
        updateWinnerInDatabase(selectedPlayer, matchId);

    console.log(`selectedPlayer: ${selectedPlayer}`);
}

function undoLastScore() {
    if (scoreHistory.length > 0) {
        const lastEntry = scoreHistory.pop(); // Ta bort den senaste poängen från historiken
        const scoreDisplay = document.getElementById(`score${lastEntry.playerId}`);
        scoreDisplay.innerText = lastEntry.score; // Återställ poängen för den spelaren

        // Uppdatera lostScore
        const currentScore = parseInt(scoreDisplay.innerText);
        const lostScore = typeOfGame - currentScore;
        // const lostScoreDisplay = document.getElementById(`lostScore${lastEntry.playerId}`);
        // lostScoreDisplay.innerText = lostScore; // Visa uppdaterat lostScore för den aktuella spelaren

        // Minska kastningen för spelaren som ångrar sitt sista kast
        if (lastEntry.playerId === 1) {
            player1Kast--;
        } else {
            player2Kast--;
        }

        // Justera totalpoäng för den spelare som ångrar sitt senaste kast
        if (lastEntry.playerId === 1) {
            player1TotalScore = lostScore; // Minska totalpoängen för spelare 1
            console.log(`lostscore: ${lostScore}  Total Score: ${player1TotalScore} Nuvarande poäng: ${currentScore}`);
        } else {
            player2TotalScore = lostScore; // Minska totalpoängen för spelare 2
            console.log(`lostscore: ${lostScore}  Total Score: ${player2TotalScore} Nuvarande poäng: ${currentScore}`);
        }

        const averageScore = lastEntry.playerId === 1
            ? (player1TotalScore / player1Kast).toFixed(2)
            : (player2TotalScore / player2Kast).toFixed(2);

        // Visa averageScore (lägg till ett element i din HTML om du vill visa det)
        const averageScoreDisplay = document.getElementById(`averageScore${lastEntry.playerId}`);
        averageScoreDisplay.innerText = averageScore; // Visa averageScore för den aktuella spelaren

        // Uppdatera lastScoreDisplay med den återställda poängen
        const lastScoreDisplay = document.getElementById(`lastScore${lastEntry.playerId}`);
        lastScoreDisplay.innerText = ''; // Rensa den senaste poängen eftersom den har ångrats

        // Byt tillbaka till föregående spelare
        togglePlayer();
    } else {
        alert("Ingen poänghistorik att gå tillbaka till!");
    }
}


function updatePlayersAverageScore(player1Id, player1AverageScore, player1Kast, player2Id, player2AverageScore, player2Kast) {
    console.log("Skickar data till servern:");
    console.log(`Player 1 ID: ${player1Id}, Average Score: ${player1AverageScore}, KAST: ${player1Kast}`);
    console.log(`Player 2 ID: ${player2Id}, Average Score: ${player2AverageScore}, KAST: ${player2Kast}`);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "updateMatchDetails.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log("Uppdatering lyckades:", xhr.responseText);
        } else if (xhr.readyState === 4) {
            console.error("Uppdatering misslyckades:", xhr.responseText);
        }
    };

    xhr.send(`player1Id=${player1Id}&player1AverageScore=${player1AverageScore}&player1Kast=${player1Kast}&player2Id=${player2Id}&player2AverageScore=${player2AverageScore}&player2Kast=${player2Kast}`);
}

// Funktion för att deklarera vinnaren
function updateWinnerInDatabase(winnerId, matchId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "uppdatera_vinnare.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                console.log("Vinnaren har uppdaterats i databasen.");
            } else {
                console.error("Det gick inte att uppdatera vinnaren i databasen.");
            }
        }
    };

    xhr.send("winner_id=" + winnerId + "&match_id=" + matchId);
}
