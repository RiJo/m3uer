<?php

require_once('file_handling.php');

class File {
    public $text = "";
    public $id = "";
    public $iconCls = "";
    public $leaf = true;
    public $expanded = false;
    public $checked = 'undefined';
    //public $uiProvider = 'tristate';
    public $children = array();

    public function  __construct($id, $text) {
        $this->id = $id;
        $this->text = $text;
    }
}

class Filesystem {
    private $root_path = '';
    private $nodes = array();

    public function  __construct($root_path, $files, $checkboxes = false) {
        $this->root_path = $root_path;
        $this->checkboxes = $checkboxes;
        $this->add($files);
    }
    
    public function add($files) {
        if (!is_array($files))
            $files = array($files);

        foreach ($files as $file)
            $this->add_recursive($this->nodes, explode(DIRECTORY_SEPARATOR, $file));
    }

    private function add_recursive(&$nodes, $items, $relative_path = '.') {
        $key = array_shift($items);
        foreach ($nodes as $node) {
            if ($key == $node->text) {
                $node->expanded = true;
                return $this->add_recursive($node->children, $items, $relative_path.DIRECTORY_SEPARATOR.$key);
            }
        }
        $full_path = simplify_path($this->root_path.DIRECTORY_SEPARATOR.$relative_path.DIRECTORY_SEPARATOR.$key);
        $new_file = new File($full_path, $key);
        $new_file->leaf = !is_dir($full_path);
        $new_file->checked = $this->checkboxes ? false : 'undefined';
        array_push($nodes, $new_file);

        if (count($items) > 0)
            return $this->add_recursive($new_file->children, $items, $relative_path.DIRECTORY_SEPARATOR.$key);
    }

    public function check_paths($paths) {
        $invalid = array();
        foreach ($paths as $path)
            if (!$this->check_path($path))
                array_push($invalid, $path);
        return $invalid;
    }

    private function check_path($path) {
        if (strpos($path, $this->root_path) !== 0)
            return false;
        $stripped_path = substr($path, strlen($this->root_path) + strlen(DIRECTORY_SEPARATOR));
        return $this->valid_path(explode(DIRECTORY_SEPARATOR, $stripped_path));
    }

    private function valid_path($path) {
        $current_node = &$this->nodes;
        $current_file;
        $node_found;
        foreach ($path as $node_name) {
            $node_found = false;
            foreach ($current_node as $file) {
                if ($file->text == $node_name) {
                    $node_found = true;
                    $current_file = &$file;
                    $current_file->expanded = true; // TODO: only do this on valid paths
                    $current_node = &$file->children;
                    break;
                }
            }
            if (!$node_found)
                return false;
        }
        $current_file->checked = true;
        return true;
    }

    public function to_json() {
        return json_encode($this->nodes);
    }
}

?>