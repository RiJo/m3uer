<?php

class Tree {
    private $head;

    function __construct() {
        $this->head = new Node();
    }

    function insert($keys, $value = null) {
        $this->head->insert($keys, $value);
    }

    function iterate($callback_before, $callback_after) {
        $this->head->iterate($callback_before, $callback_after, 1);
    }
}

class Node {
    public $value;
    private $childs;

    function __construct() {
        $this->value = null;
        $this->childs = array();
    }

    function insert($keys, $value = null) {
        assert(is_array($keys));

        if (empty($keys)) {
            // Found leaf
            $this->value = $value;
            return true;
        }
        else {
            // Recurse down in tree
            $key = array_shift($keys);
            if (!isset($this->childs[$key]))
                $this->childs[$key] = new Node();
            return $this->childs[$key]->insert($keys, $value);
        }
    }

    function iterate($callback_before, $callback_after, $level) {
        $callback_before($this, $level);
        foreach ($this->childs as $child) {
            $child->iterate($callback_before, $callback_after, $level + 1);
        }
        $callback_after($this, $level);
    }

    function is_leaf() {
        return empty($this->childs);
    }
}

?>