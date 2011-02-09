<?php

require_once('file_handling.php');

class File {
    public $text = "";
    public $id = "";
    public $iconCls = "";
    public $leaf = true;
    public $expanded = false;
    public $checked = false;
    //public $uiProvider = 'tristate';
    public $children = array();

    function  __construct($id,$text,$iconCls,$leaf) {
        $this->id = $id;
        $this->text = $text;
        $this->iconCls = $iconCls;
        $this->leaf = $leaf;
    }
}

class Filesystem {
    private $root_path = '';
    private $nodes = array();

    public function load($root_path, $extensions) {
        $this->root_path = $root_path;
        return $this->load_from_path($this->nodes, '.', $extensions);
    }

    private function load_from_path(&$parent, $path, $extensions) {
        $full_path = $this->root_path.DIRECTORY_SEPARATOR.$path;

        if (!is_dir($full_path))
            die("\"$full_path\" is not a directory");

        $directory = opendir($full_path);
        if (!$directory)
            die("Could not open directory \"$full_path\"");

        while (false !== ($file = readdir($directory))) {
            $file_info = get_file_info($full_path.DIRECTORY_SEPARATOR.$file);

            if (is_dir($file_info['path']) && !in_array($file, array('.', '..'))) {
                // Directory
                $new_directory = new File($file_info['path'], $file, "folder.png", false);
                array_push($parent, $new_directory);
                $this->load_from_path($new_directory->children, $path.DIRECTORY_SEPARATOR.$file, $extensions);
            }
            elseif (isset($file_info['extension']) && in_array($file_info['extension'], $extensions)) {
                // File
                $new_file = new File($file_info['path'], $file, "file.png", true);
                array_push($parent, $new_file);
            }
        }
        closedir($directory);
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

    public function get_paths($extensions) {
        
    }

    public function to_json() {
        return json_encode($this->nodes);
    }
}

/*

in = {
    playlists   => array('m3u'),
    music       => array('mp3', 'wav')
}

out = {
    playlists   => array(
        foo => array (
            bar.m3u
            baz => array(
                hej.m3u,
                da.m3u
            )
        )
    )
    music       => array(
        foo => array (
            abc.mp3,
            def.mp3,
            ghi.mp3
        )
    )
}

*/

?>