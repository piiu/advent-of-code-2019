<?php

class IntcodeComputer {
    private $defaultCode;
    private $code;
    private $relativeBase = 0;
    private $currentPointer = 0;
    private $inputs = [];
    private $isFinished = false;

    const OPCODE_ADD = 1;
    const OPCODE_MULTIPLY = 2;
    const OPCODE_INPUT = 3;
    const OPCODE_OUTPUT = 4;
    const OPCODE_JUMP_IF_TRUE = 5;
    const OPCODE_JUMP_IF_FALSE = 6;
    const OPCODE_LESS_THAN = 7;
    const OPCODE_EQUALS = 8;
    const OPCODE_ADJUST_BASE = 9;

    const MODE_POSITION = 0;
    const MODE_PARAMETER = 1;
    const MODE_RELATIVE = 2;

    const ASCII_NEWLINE = 10;

    public function __construct(array $code) {
        $this->defaultCode = array_map(function ($a) {
            return (int)$a;
        }, $code);
        $this->code = $this->defaultCode;
    }

    public function addInput(int $input) : self {
        $this->inputs[] = $input;
        return $this;
    }

    public function addAsciiInput(string $input) : self {
        $chars = str_split($input);
        foreach ($chars as $char) {
            $this->addInput(ord($char));
        }
        $this->addInput(self::ASCII_NEWLINE);
        return $this;
    }

    public function getOutput() : array {
        $output = [];

        while ($this->code[$this->currentPointer] !== 99) {

            $action = $this->code[$this->currentPointer];
            $opcode = (int)substr($action, -2);

            $param1 = $this->getIndex(1, $action);
            $param2 = $this->getIndex(2, $action);
            $param3 = $this->getIndex(3, $action);

            $param1Value = $this->code[$param1] ?? 0;
            $param2Value = $this->code[$param2] ?? 0;

            if ($opcode == self::OPCODE_ADD) {
                $this->code[$param3] = $param1Value + $param2Value;
                $this->currentPointer += 4;
                continue;
            }
            if ($opcode == self::OPCODE_MULTIPLY) {
                $this->code[$param3] = $param1Value * $param2Value;
                $this->currentPointer += 4;
                continue;
            }
            if ($opcode == self::OPCODE_INPUT) {
                if (empty($this->inputs)) {
                    return $output;
                }
                $this->code[$param1] = array_shift($this->inputs);
                $this->currentPointer += 2;
                continue;
            }
            if ($opcode == self::OPCODE_OUTPUT) {
                $this->currentPointer += 2;
                $output[] = $param1Value;
                continue;
            }
            if ($opcode == self::OPCODE_JUMP_IF_TRUE) {
                $this->currentPointer = $param1Value ? $param2Value : $this->currentPointer + 3;
                continue;
            }
            if ($opcode == self::OPCODE_JUMP_IF_FALSE) {
                $this->currentPointer = !$param1Value ? $param2Value : $this->currentPointer + 3;
                continue;
            }
            if ($opcode == self::OPCODE_LESS_THAN) {
                $this->code[$param3] = $param1Value < $param2Value ? 1 : 0;
                $this->currentPointer += 4;
                continue;
            }
            if ($opcode == self::OPCODE_EQUALS) {
                $this->code[$param3] = $param1Value == $param2Value ? 1 : 0;
                $this->currentPointer += 4;
                continue;
            }

            if ($opcode == self::OPCODE_ADJUST_BASE) {
                $this->relativeBase += $param1Value;
                $this->currentPointer += 2;
                continue;
            }
        }

        $this->isFinished = true;
        return $output;
    }

    public function getFirstOutput() : int {
        $output = $this->getOutput();
        return $output[0];
    }

    public function printAsciiOutput() {
        $output = $this->getOutput();
        foreach ($output as $item) {
            echo chr($item);
        }
        return $output;
    }

    public function reset() : self {
        $this->code = $this->defaultCode;
        $this->relativeBase = 0;
        $this->currentPointer = 0;
        $this->inputs = [];
        $this->isFinished = false;
        return $this;
    }

    public function isFinished(): bool {
        return $this->isFinished;
    }

    public function setPosition(int $index, int $value) {
        $this->code[$index] = $value;
    }

    private function getIndex(int $paramNumber, int $action) : int {
        $paramMode = strlen($action) > $paramNumber + 1 ? (int)substr($action, -1 * ($paramNumber + 2), 1) : 0;

        if ($paramMode == self::MODE_POSITION) {
            return $this->code[$this->currentPointer + $paramNumber] ?? 0;
        }
        if ($paramMode == self::MODE_PARAMETER) {
            return $this->currentPointer + $paramNumber;
        }
        if ($paramMode == self::MODE_RELATIVE) {
            return $this->code[$this->currentPointer + $paramNumber] + $this->relativeBase;
        }
    }
}
