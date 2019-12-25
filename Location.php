<?php


class Location {
    public $x;
    public $y;

    const NORTH = 1;
    const SOUTH = 2;
    const WEST = 3;
    const EAST = 4;

    const DIRECTIONS = [self::NORTH, self::EAST, self::SOUTH, self::WEST];

    public function __construct(int $x, int $y, int $direction = null) {
        $this->x = $x;
        $this->y = $y;

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

    public function isEqual(self $location) {
        return $this->x === $location->x && $this->y === $location->y;
    }

    public function getValueFromMap(array $map) {
        return $map[$this->y][$this->x] ?? null;
    }
}