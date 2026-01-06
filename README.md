# Dart Tournament Web App

A PHP based web application for creating and managing dart tournaments.
The system automatically generates matches, tracks results across multiple rounds, and determines a final winner based on match outcomes.

The project was built as a learning focused implementation of tournament logic, database driven state, and dynamic server side rendering.

---

## Features

* Create tournaments with any number of players
* Automatic match and round generation
* Dynamic bracket progression based on winners
* Support for uneven player counts
* Database driven tournament state
* Clear separation between tournament, player, and match data

---

## Installation

### Requirements

* PHP 8 or newer
* MySQL or MariaDB
* Local web server such as XAMPP or MAMP

### Setup

1. Clone the repository

```bash
git clone https://github.com/yourusername/dart_tournament_web
```

2. Move the project to your web server root directory
   For example `htdocs` if using XAMPP

3. Create a new database
   Example name: `dart_tournament`

4. Import the database schema using phpMyAdmin or another MySQL client

5. Configure database credentials in `config.php`

```php
$host = "localhost";
$db   = "dart_tournament";
$user = "root";
$pass = "";
```

6. Open the project in your browser

```
http://localhost/dart_tournament_web
```

---

## Usage

1. Create a new tournament
2. Select and assign players
3. Matches are generated automatically
4. Enter match results
5. Winners progress to the next round
6. The final round determines the tournament winner

---

## Database Structure

| Table      | Description                                    |
| ---------- | ---------------------------------------------- |
| tournament | Stores tournament name and size                |
| players    | Stores player id name position and tournament  |
| matches    | Stores match round position players and winner |

---

## Project Structure

* `create.php`
  Handles tournament creation and player selection

* `matches.php`
  Displays matches and handles winner updates

* `uppstallning.php`
  Shows player lineup for a specific match

* `config.php`
  Database connection settings

* `functions.php`
  Shared tournament and match logic

---

## Tournament Logic Overview

* Total rounds are calculated dynamically based on player count
* First round assigns players based on position
* Each subsequent round uses winners from the previous round
* Final round determines the overall tournament winner

---

## Future Improvements

* Translating comments to english
* Improved visual bracket layout
* Player statistics and history
* Tournament editing after creation
* Authentication for multiple users

---
