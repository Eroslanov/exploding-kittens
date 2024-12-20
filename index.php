<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Взрывные котята</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Добро пожаловать в Взрывные котята!</h1>
        <form action="game.php" method="POST">
            <label for="player-name">Введите ваше имя:</label>
            <input type="text" id="player-name" name="player_name" required>
            <button type="submit">Начать игру</button>
        </form>
    </div>
</body>
</html>