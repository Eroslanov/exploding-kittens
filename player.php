<?php
class Player {
    public $name;
    public $hand = [];
    public $lives = 1;

    public function __construct($name) {
        $this->name = $name;
    }

    public function drawCards(Deck $deck, $count) {
        for ($i = 0; $i < $count; $i++) {
            $card = $deck->drawCard();
            if ($card) {
                $this->hand[] = $card;
            }
        }
    }

    public function addCard(Card $card) {
        $this->hand[] = $card;
    }

    public function playTurn(Deck $deck) {
        // Бот вытягивает карту
        $card = $deck->drawCard();
        if ($card) {
            if ($card->type == 'ExplodingKitten') {
                if (in_array('Defuse', array_column($this->hand, 'type'))) {
                    echo "Бот " . htmlspecialchars($this->name) . " обезвредил взрывного котёнка!";
                    $deck->addToDiscardPile($card);
                    // Удаляем "Обезвреживание" из руки бота
                    foreach ($this->hand as $key => $handCard) {
                        if ($handCard->type == 'Defuse') {
                            unset($this->hand[$key]);
                            break;
                        }
                    }
                } else {
                    echo "Бот " . htmlspecialchars($this->name) . " вытянул взрывного котёнка и проиграл!";
                    $this->lives--;
                    if ($this->lives <= 0) {
                        echo "Бот " . htmlspecialchars($this->name) . " выбывает из игры!";
                    }
                }
            } else {
                $this->hand[] = $card;
            }
        }
    }
}