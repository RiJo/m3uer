<?php

class Node {
    public $value;
    private $childs;

    function __construct() {
        $this->value = null;
        $this->childs = array();
    }

    function insert($path, $key, $value) {
        assert(is_array($path));

        if (empty($path)) {
            // Found leaf
            //~ echo "\ninsert: \"$key\" = \"$value\"<br>";
            $this->value[$key] = $value;
            return true;
        }
        else {
            // Recurse down in tree
            $child = array_shift($path);
            if (!isset($this->childs[$child])) {
                //~ echo "\n (creating node \"$child\", current: ".print_r(array_keys($this->childs),true).") ";
                $this->childs[$child] = new Node();
            }
            //~ echo "\n--> \"$child\" ";
            return $this->childs[$child]->insert($path, $key, $value);
        }
    }

    function iterate($callback_before, $callback_after, $level) {
        $callback_before($this, $level);
        foreach ($this->childs as $child) {
            $child->iterate($callback_before, $callback_after, $level + 1);
        }
        $callback_after($this, $level);
    }

    function evaluate($key, $value) {
        $result = (isset($this->value[$key]) && $this->value[$key] == $value);
        foreach ($this->childs as $child)
            $result &= $child->evaluate($key, $value);
        return $result;
    }

    function is_leaf() {
        return empty($this->childs);
    }
}

?>