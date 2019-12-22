<?php

$input = file_get_contents(__DIR__ . '/input/day22');
$instructions = explode("\n", $input);

$deck = new Deck();

foreach ($instructions as $instruction) {
    preg_match('/([\w\s]*) ([-\d]*)?/', $instruction, $matches);
    $operation = $matches[1];
    $parameter = (int)$matches[2];
    switch ($operation) {
        case 'deal with increment':
            $deck->dealWithIncrement($parameter);
            break;
        case 'cut':
            $deck->cut($parameter);
            break;
        case 'deal into new':
            $deck->dealIntoNewStack();
    }
}

echo 'Part 1: '. $deck->findCard(2019) . PHP_EOL;

class Deck {
    private $cards = [];

    const DECK_SIZE = 10007;

    public function __construct() {
        $this->cards = range(0, self::DECK_SIZE - 1);
    }

    public function dealIntoNewStack() {
        $this->cards = array_reverse($this->cards);
    }

    public function cut(int $n) {
        $firstSlice = array_slice($this->cards, $n);
        $secondSlice = array_slice($this->cards, 0, $n);
        $this->cards = array_merge($firstSlice, $secondSlice);
    }

    public function dealWithIncrement(int $n) {
        $newDeck = [];
        $position = 0;
        foreach ($this->cards as $card) {
            $newDeck[$position] = $card;
            $position += $n;
            if ($position > self::DECK_SIZE) {
                $position -= self::DECK_SIZE;
            }
        }
        ksort($newDeck);
        $this->cards = $newDeck;
    }

    public function findCard(int $card) {
        return array_search($card, $this->cards);
    }
}