<?php
require_once('Utils.php');

$input = file_get_contents(__DIR__ . '/input/day18');
$vault = new Vault($input);

$vault->draw();

class Vault {
    private $map = [];
    public $currentX;
    public $currentY;

    const PASSAGE = '.';
    const WALL = '#';
    const MY_LOCATION = '@';

    public function __construct($input) {
        $rows = explode("\n", $input);
        foreach ($rows as $y => $row) {
            $chars = str_split($row);
            foreach ($chars as $x => $char) {
                if ($char === self::MY_LOCATION) {
                    $this->currentX = $x;
                    $this->currentY = $y;
                }
                $this->map[$y][$x] = $char;
            }
        }
    }

    public function useKey(string $key) {
        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $char) {
                if ($char === $key || $char === strtoupper($key)) {
                    $this->map[$y][$x] = self::PASSAGE;
                }
            }
        }
    }

    public function getNumberOfKeys() : int {
        $count = 0;
        foreach ($this->map as $row) {
            foreach ($row as $char) {
                if (preg_match('/^[a-z]$/', $char)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function draw() {
        Utils::drawBoard($this->map);
    }
}