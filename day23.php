<?php
require_once('IntcodeComputer.php');

$input = file_get_contents(__DIR__ . '/input/day23');
$code = explode(',', $input);

/** @var NIC[] $computers */
$computers = [];
for ($i = 0; $i < 50; $i++) {
    $computers[] = new NIC($code, $i);
}
$nat = new NAT();

$part1Solved = false;
$packets = [];
$previousY = null;

while (true) {
    do {
        $newPackets = [];
        foreach ($computers as $computer) {
            $isSent = false;
            foreach ($packets as $index => $packet) {
                if ($packet->address === $computer->address) {
                    $output = $computer->receiveAndSend($packet);
                    $isSent = true;
                    unset($packets[$index]);
                    break;
                }
            }
            if (!$isSent) {
                $output = $computer->receiveAndSend(null);
            }
            if (!$output) {
                continue;
            }
            $newPackets = array_merge($newPackets, $output);
        }
        foreach ($packets as $index => $packet) {
            if ($packet->address !== 255) {
                continue;
            }
            if (!$part1Solved) {
                echo 'Part 1: ' . $packet->y . PHP_EOL;
                $part1Solved = true;
            }
            $nat->receive($packet);
            unset($packets[$index]);
        }
        $packets = array_merge($packets, $newPackets);
    } while (!empty($packets));
    $packets[] = $nat->memory;
    if ($nat->memory->y === $previousY) {
        echo 'Part 1: ' . $previousY . PHP_EOL;
        break;
    }
    $previousY = $nat->memory->y;
}



class NIC {
    public $address;
    private $computer;

    public function __construct(array $code, int $address) {
        $this->address = $address;
        $this->computer = new IntcodeComputer($code);
        $this->computer->addInput($address);
    }

    public function receiveAndSend(Packet $packet = null) {
        if ($packet) {
            $this->computer->addInput($packet->x)->addInput($packet->y);
        } else {
            $this->computer->addInput(-1);
        }
        $output = $this->computer->getOutput();
        if (!$output) {
            return null;
        }
        $packets = [];
        for ($i=0; $i < count($output); $i+=3) {
            $packets[] = new Packet(array_slice($output, $i, 3));
        }
        return $packets;
    }
}

class NAT {
    public $memory;

    public function receive(Packet $packet) {
        $packet->address = 0;
        $this->memory = $packet;
    }
}

class Packet {
    public $address;
    public $x;
    public $y;

    public function __construct(array $nicOutput) {
        $this->address = $nicOutput[0];
        $this->x = $nicOutput[1];
        $this->y = $nicOutput[2];
    }
}