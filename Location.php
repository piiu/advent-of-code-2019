<?php


class Location {
    public $x;
    public $y;
    public $stepsTo;

    const NORTH = 1;
    const SOUTH = 2;
    const WEST = 3;
    const EAST = 4;

    const DIRECTIONS = [self::NORTH, self::EAST, self::SOUTH, self::WEST];

    public function __construct(int $x, int $y, int $direction = null, $stepsTo = null) {
        $this->x = $x;
        $this->y = $y;
        $this->stepsTo = $stepsTo;

        if ($direction) {
            $this->addDirection($direction);
        }
    }

    public function addDirection(int $direction) {
        if ($direction == self::NORTH) {
            $this->y += 1;
        }
        if ($direction == self::SOUTH) {
            $this->y -= 1;
        }
        if ($direction == self::WEST) {
            $this->x += 1;
        }
        if ($direction == self::EAST) {
            $this->x -= 1;
        }
        return $this;
    }
}