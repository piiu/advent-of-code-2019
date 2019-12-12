<?php

$input = file_get_contents(__DIR__ . '\input\day12');

/** @var Moon[] $moons */
$moons = array_map(function(string $row) : Moon {
    return new Moon(new Coordinates($row));
}, explode(PHP_EOL, $input));

$step = $totalEnergy = 0;
$isInitialPosition = false;
while (!$isInitialPosition) {
    $step ++;
    $influences = getAllAxisPositionsFromList($moons);
    $isInitialPosition = true;
    foreach ($moons as $moon) {
        $moon->applyGravity($influences);
        if ($step === 1000) {
            $totalEnergy += $moon->getEnergy();
        }
        if (!$moon->isInitialPosition()) {
            $isInitialPosition = false;
        }
    }
}

echo 'Part 1: '. $totalEnergy . PHP_EOL;
echo 'Part 2: '. $step . PHP_EOL;

class Moon {
    public $initialPosition;
    public $initialVelocity;

    public $position;
    public $velocity;

    public function __construct(Coordinates $position) {
        $this->position = $position;
        $this->velocity = new Coordinates();

        $this->initialPosition = clone($this->position);
        $this->initialVelocity = clone($this->velocity);
    }

    public function applyGravity(array $influences) {
        foreach ($influences as $axis => $influence) {
            $this->applyInfluenceForAxis($influence, $axis);
        }
        $this->position->add($this->velocity);
    }

    public function getEnergy() : int {
        return $this->position->getEnergy() * $this->velocity->getEnergy();
    }

    public function isInitialPosition() : bool {
        return $this->position->equals($this->initialPosition)
            && $this->velocity->equals($this->initialVelocity);
    }

    private function applyInfluenceForAxis(array $influence, string $axis) {
        foreach ($influence as $position) {
            if ($position === $this->position->{$axis}) {
                continue;
            }
            $modifier = $position > $this->position->{$axis} ? 1 : -1;
            $this->velocity->{$axis} += $modifier;
        }
    }
}

class Coordinates {
    public $x = 0;
    public $y = 0;
    public $z = 0;

    public function __construct(string $coordinatesString = null) {
        if ($coordinatesString) {
            $this->setCoordinatedFromString($coordinatesString);
        }
    }

    public function add(Coordinates $a) {
        $this->x += $a->x;
        $this->y += $a->y;
        $this->z += $a->z;
    }

    public function getEnergy() : int {
        return abs($this->x) + abs($this->y) + abs($this->z);
    }

    public function equals(Coordinates $coordinates) : bool {
        return $this->x === $coordinates->x
            && $this->y === $coordinates->y
            && $this->z === $coordinates->z;
    }

    private function setCoordinatedFromString(string $string) {
        preg_match_all('/[xyz]\=([0-9\-]*)[,>]/', $string, $matches);
        $this->x = (int)$matches[1][0];
        $this->y = (int)$matches[1][1];
        $this->z = (int)$matches[1][2];
    }
}

function getAllAxisPositionsFromList(array $moons) : array {
    $axises = ['x', 'y', 'z'];
    $influences = array_map(function(string $axis) use ($moons) : array {
        return array_map(function(Moon $moon) use ($axis) : int {
            return $moon->position->{$axis};
        }, $moons);
    }, $axises);
    return array_combine($axises, $influences);
}