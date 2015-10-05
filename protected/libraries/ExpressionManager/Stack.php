<?php
namespace ls\expressionmanager;


class Stack {

    protected $items = [];

    protected $transactions;

    public function __construct($transactionSupport = true) {
        if ($transactionSupport) {
            $this->transactions = new self(false);
        }
    }
    public function push($item) {
        array_push($this->items, $item);
    }

    public function pop() {
        if (count($this->items) == 0) {
            throw new \Exception("Popping from empty stack.");
        }
        return array_pop($this->items);
    }

    public function begin() {
        $this->transactions->push($this->items);
        return true;
    }

    public function rollback() {
        $this->items = $this->transactions->pop();
        return false;
    }

    public function commit() {
        $this->transactions->pop();
        return true;
    }

    public function count() {
        return count($this->items);
    }
}