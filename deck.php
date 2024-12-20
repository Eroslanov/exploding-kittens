<?php
class Card {
    public $type;

    public function __construct($type) {
        $this->type = $type;
    }
}

class Deck {
    private $cards = [];
    private $discardPile = [];

    public function __construct() {
        // Создаем стандартную колоду из 56 карт
        $this->cards = [
            new Card('Attack'),
            new Card('Skip'),
            new Card('Favor'),
            new Card('Shuffle'),
            new Card('SeeTheFuture'),
            new Card('Nope'),
            new Card('Defuse'),
            new Card('Defuse'),
            new Card('Defuse'),
            new Card('Defuse'),
            new Card('ExplodingKitten'),
            new Card('ExplodingKitten'),
            new Card('ExplodingKitten'),
            new Card('ExplodingKitten'),
            new Card('ExplodingKitten'),
            new Card('Attack'),
            new Card('Attack'),
            new Card('Skip'),
            new Card('Skip'),
            new Card('Favor'),
            new Card('Favor'),
            new Card('Favor'),
            new Card('Favor'),
            new Card('Shuffle'),
            new Card('Shuffle'),
            new Card('SeeTheFuture'),
            new Card('SeeTheFuture'),
            new Card('SeeTheFuture'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope'),
            new Card('Nope')
        ];
    }

    public function prepareDeck($numPlayers) {
        // Убираем "Взрывных котят" и "Обезвреживание"
        $explodingKittens = [];
        $defuses = [];
        foreach ($this->cards as $key => $card) {
            if ($card->type == 'ExplodingKitten') {
                $explodingKittens[] = $card;
                unset($this->cards[$key]);
            } elseif ($card->type == 'Defuse') {
                $defuses[] = $card;
                unset($this->cards[$key]);
            }
        }

        // Тасуем оставшиеся карты
        shuffle($this->cards);

        // Раздаем всем игрокам по 7 карт
        $hands = [];
        for ($i = 0; $i < $numPlayers; $i++) {
            $hand = [];
            for ($j = 0; $j < 7; $j++) {
                $hand[] = array_pop($this->cards);
            }
            $hands[] = $hand;
        }

        // Добавляем каждому игроку по одной карте "Обезвреживание"
        foreach ($hands as &$hand) {
            $hand[] = array_pop($defuses);
        }

        // Добавляем "Взрывных котят" обратно в колоду (на одного меньше, чем игроков)
        for ($i = 0; $i < $numPlayers - 1; $i++) {
            $this->cards[] = array_pop($explodingKittens);
        }

        // Тасуем колоду ещё раз
        shuffle($this->cards);

        return $hands;
    }

    public function shuffle() {
        shuffle($this->cards);
    }

    public function drawCard() {
        return array_pop($this->cards);
    }

    public function addCard(Card $card) {
        $this->cards[] = $card;
    }

    public function getCards() {
        return $this->cards;
    }

    public function getCardCount() {
        return count($this->cards);
    }

    public function addToDiscardPile(Card $card) {
        $this->discardPile[] = $card;
    }

    public function getDiscardPile() {
        return $this->discardPile;
    }
}