<?php

$input = file_get_contents(__DIR__ . '/input/day22');
$instructions = array_map(function (string $instruction) : array {
    preg_match('/([\w\s]*) ([-\d]*)?/', $instruction, $matches);
    return [
        'operation' => $matches[1],
        'parameter' => (int)$matches[2]
    ];
}, explode("\n", $input));

$deck = new Deck(10007);
$deck->shuffle($instructions);
echo 'Part 1: '. $deck->findCard(2019) . PHP_EOL;

$deck = new Deck(119315717514047);
for ($count = 0; $count < 101741582076661; $count++) {
    $deck->shuffle($instructions);
}
echo 'Part 2: '. $deck->findCard(2020) . PHP_EOL;

class Deck {
    private $cards = [];
    private $deckSize;

    public function __construct(int $size) {
        $this->deckSize = $size;
        $this->cards = range(0, $size - 1);
    }

    public function shuffle($instructions) {
        foreach ($instructions as $instruction) {
            switch ($instruction['operation']) {
                case 'deal with increment':
                    $this->dealWithIncrement($instruction['parameter']);
                    break;
                case 'cut':
                    $this->cut($instruction['parameter']);
                    break;
                case 'deal into new':
                    $this->dealIntoNewStack();
            }
        }
    }

    private function dealIntoNewStack() {
        $this->cards = array_reverse($this->cards);
    }

    private function cut(int $n) {
        $firstSlice = array_slice($this->cards, $n);
        $secondSlice = array_slice($this->cards, 0, $n);
        $this->cards = array_merge($firstSlice, $secondSlice);
    }

    private function dealWithIncrement(int $n) {
        $newDeck = [];
        $position = 0;
        foreach ($this->cards as $card) {
            $newDeck[$position] = $card;
            $position += $n;
            if ($position > $this->deckSize) {
                $position -= $this->deckSize;
            }
        }
        ksort($newDeck);
        $this->cards = $newDeck;
    }

    public function findCard(int $card) {
        return array_search($card, $this->cards);
    }
}