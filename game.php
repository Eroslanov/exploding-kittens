<?php
session_start();

// Подключаем необходимые файлы
require 'deck.php';
require 'player.php';

// Функция для проверки победителя
function checkWinner($player, $bots) {
    $aliveBots = array_filter($bots, function ($bot) {
        return $bot->lives > 0;
    });

    if (empty($aliveBots)) {
        echo "Поздравляем! Вы победили!";
        session_destroy();
        exit;
    }
}

// Инициализация игры
if (!isset($_SESSION['game'])) {
    // Проверяем, что пользователь ввел имя
    if (empty($_POST['player_name'])) {
        header('Location: index.php');
        exit;
    }

    // Создаем колоду и готовим её
    $deck = new Deck();
    $hands = $deck->prepareDeck(4); // 4 игрока: пользователь + 3 бота

    // Создаем игрока (пользователя)
    $player = new Player($_POST['player_name']);
    $player->hand = $hands[0];

    // Создаем ботов
    $bots = [];
    for ($i = 1; $i <= 3; $i++) {
        $bot = new Player("Бот $i");
        $bot->hand = $hands[$i];
        $bots[] = $bot;
    }

    // Сохраняем состояние игры в сессию
    $_SESSION['game'] = [
        'deck' => serialize($deck),
        'player' => serialize($player),
        'bots' => array_map('serialize', $bots),
        'current_player' => 0, // Начинаем с пользователя
        'discard_pile' => serialize([]) // Стопка сброса
    ];
}

// Загружаем состояние игры из сессии
$game = $_SESSION['game'];
$deck = unserialize($game['deck']);
$player = unserialize($game['player']);
$bots = array_map('unserialize', $game['bots']);
$currentPlayerIndex = $game['current_player'];
$discardPile = unserialize($game['discard_pile']);

// Обработка действий игрока
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'вытянуть') {
        // Игрок вытягивает карту
        $card = $deck->drawCard();
        if ($card) {
            if ($card->type == 'ExplodingKitten') {
                if (in_array('Defuse', array_column($player->hand, 'type'))) {
                    echo "Вы обезвредили взрывного котёнка!";
                    $deck->addToDiscardPile($card);
                    // Удаляем "Обезвреживание" из руки игрока
                    foreach ($player->hand as $key => $handCard) {
                        if ($handCard->type == 'Defuse') {
                            unset($player->hand[$key]);
                            break;
                        }
                    }
                } else {
                    echo "Вы выиграли взрывного котёнка! Вы проиграли!";
                    session_destroy();
                    exit;
                }
            } else {
                $player->addCard($card);
            }
        } else {
            echo "Колода пуста! Ничья!";
            session_destroy();
            exit;
        }

        // Передаём ход следующему игроку (боту)
        $currentPlayerIndex = ($currentPlayerIndex + 1) % 4;
        $_SESSION['game']['current_player'] = $currentPlayerIndex;

        // Боты делают свои ходы
        while ($currentPlayerIndex != 0) {
            $bot = $bots[$currentPlayerIndex - 1];
            $bot->playTurn($deck, $discardPile);
            $currentPlayerIndex = ($currentPlayerIndex + 1) % 4;
        }

        // Проверяем, есть ли победитель
        checkWinner($player, $bots);

        // Сохраняем обновленное состояние игры
        $_SESSION['game'] = [
            'deck' => serialize($deck),
            'player' => serialize($player),
            'bots' => array_map('serialize', $bots),
            'current_player' => $currentPlayerIndex,
            'discard_pile' => serialize($discardPile)
        ];
    } elseif (isset($_POST['action']) && $_POST['action'] == 'играть_карту') {
        // Игрок играет карту
        $cardType = $_POST['card_type'];
        foreach ($player->hand as $key => $card) {
            if ($card->type == $cardType) {
                // Удаляем карту из руки игрока
                unset($player->hand[$key]);
                // Добавляем карту в стопку сброса
                $discardPile[] = $card;
                break;
            }
        }

        // Передаём ход следующему игроку (боту)
        $currentPlayerIndex = ($currentPlayerIndex + 1) % 4;
        $_SESSION['game']['current_player'] = $currentPlayerIndex;

        // Боты делают свои ходы
        while ($currentPlayerIndex != 0) {
            $bot = $bots[$currentPlayerIndex - 1];
            $bot->playTurn($deck, $discardPile);
            $currentPlayerIndex = ($currentPlayerIndex + 1) % 4;
        }

        // Проверяем, есть ли победитель
        checkWinner($player, $bots);

        // Сохраняем обновленное состояние игры
        $_SESSION['game'] = [
            'deck' => serialize($deck),
            'player' => serialize($player),
            'bots' => array_map('serialize', $bots),
            'current_player' => $currentPlayerIndex,
            'discard_pile' => serialize($discardPile)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Взрывные котята</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
    <link rel="icon" href="data:,"> <!-- Отключаем загрузку favicon.ico -->
</head>
<body>
    <div class="container">
        <h1>Игра Взрывные котята</h1>
        <div class="game-board">
            <div class="center">
                <div class="deck">
                    <h2>Колода</h2>
                    <p><?php echo $deck->getCardCount(); ?> карт осталось</p>
                    <form action="game.php" method="POST">
                        <button type="submit" name="action" value="вытянуть">Вытянуть карту</button>
                    </form>
                </div>
                <div class="discard-pile">
                    <h2>Стопка сброса</h2>
                    <ul>
                        <?php foreach ($discardPile as $card): ?>
                            <li><?php echo htmlspecialchars($card->type); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
              <div class="players">
                <?php
                // Расположение игроков по кругу
                $players = array_merge([$player], $bots);
                $positions = [0, 90, 180, 270];
                foreach ($players as $index => $playerOrBot): ?>
                    <div class="player" style="transform: rotate(<?php echo $positions[$index] . 'deg'; ?>) translate(200px) rotate(<?php echo -$positions[$index] . 'deg'; ?>);">
                        <h2><?php echo htmlspecialchars($playerOrBot->name); ?></h2>
                        <ul>
                            <?php if ($playerOrBot === $player): ?>
                                <?php foreach ($player->hand as $card): ?>
                                    <li>
                                        <form action="game.php" method="POST" onsubmit="return confirm('Играть карту: <?php echo htmlspecialchars($card->type); ?>?');">
                                            <input type="hidden" name="action" value="играть_карту">
                                            <input type="hidden" name="card_type" value="<?php echo htmlspecialchars($card->type); ?>">
                                            <button type="submit" style="background-image: url(images/<?php echo strtolower(str_replace(' ', '-', $card->type)); ?>.png); background-size: cover; width: 50px; height: 70px;"></button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p><?php echo count($playerOrBot->hand); ?> карт</p>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>