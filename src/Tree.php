<?php

class Node {
    public $value;
    private $childs;

    function __construct() {
        $this->value = null;
        $this->childs = array();
    }

    function exists($path) {
        assert(is_array($path));

        if (empty($path)) {
            // Found leaf
            return true;
        }
        else {
            // Recurse down in tree
            $child = array_shift($path);
            if (!isset($this->childs[$child])) {
                return false;
            }
            return $this->childs[$child]->exists($path);
        }
    }

    function insert($path, $key, $value) {
        assert(is_array($path));

        if (empty($path)) {
            // Found leaf
            $this->value[$key] = $value;
            return true;
        }
        else {
            // Recurse down in tree
            $child = array_shift($path);
            if (!isset($this->childs[$child])) {
                $this->childs[$child] = new Node();
                echo "<br>Node \"$child\" created";
            }
            return $this->childs[$child]->insert($path, $key, $value);
        }
    }

    function iterate($callback_before, $callback_after, $level) {
        $callback_before($this, $level);
        foreach ($this->childs as $child)
            $child->iterate($callback_before, $callback_after, $level + 1);
        $callback_after($this, $level);
    }

    function evaluate($key, $value) {
        if ($this->is_leaf()) {
            $result = (isset($this->value[$key]) && $this->value[$key] === $value);
        }
        else {
            $result = true;
            foreach ($this->childs as $child) {
                $result &= $child->evaluate($key, $value);
            }
        }
        return $result;
    }

    function is_leaf() {
        return empty($this->childs);
    }
}

?>