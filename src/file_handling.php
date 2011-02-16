<?php

function get_file_info($path) {
    if (!file_exists($path))
        die("Could not locate file $path");
    $real_path = realpath($path);
    $file_info = pathinfo($real_path);
    $file_info['path'] = $real_path;
    return $file_info;
}

/*
 * Makes the same thing as realpath() but doesn't return empty sting on relative
 * paths.
 */
function simplify_path($path) {
    $temp = array();

    $skip_previous = 0;
    $items = explode(DIRECTORY_SEPARATOR, $path);
    for ($i = count($items) - 1; $i >= 0 ; $i--) {
        if ($items[$i] === ".")
            continue;
        if ($items[$i] === "..") {
            $skip_previous++;
            continue;
        }
        if ($skip_previous > 0) {
            $skip_previous--;
            continue;
        }
        array_unshift($temp, $items[$i]);
    }

    for ($i = 0; $i < $skip_previous; $i++)
        array_unshift($temp, '..');

    return implode(DIRECTORY_SEPARATOR, $temp);
}

function make_relative_path($source, $destination, $is_file = true) {
    $source_directories = explode(DIRECTORY_SEPARATOR, $source);
    $destination_directories = explode(DIRECTORY_SEPARATOR, $destination);

    $branch = 0;
    for ($i = 0; $i < count($source_directories) - $is_file && $i < count($destination_directories) - $is_file; $i++) {
        if ($source_directories[$i] != $destination_directories[$i])
            break;
        $branch = $i + 1;
    }

    $temp = array();
    for ($i = $branch; $i < count($source_directories) - $is_file; $i++) {
        if ($source_directories[$i] != '')
            array_push($temp, '..');
    }
    for ($i = $branch; $i < count($destination_directories); $i++) {
        if ($destination_directories[$i] != '')
            array_push($temp, $destination_directories[$i]);
    }
    return implode(DIRECTORY_SEPARATOR, $temp);
}

function load_filesystem($root_path, $extensions, $skip_patterns = array()) {
    $result = array('directories' => array());
    foreach ($extensions as $key=>$value)
        $result[$key] = array();
    
    load_filesystem_recursive($root_path, '.', $extensions, $skip_patterns, $result);
    return $result;
}

function load_filesystem_recursive($root_path, $relative_path, $extensions, $skip_patterns, &$tree) {
    $full_path = $root_path.DIRECTORY_SEPARATOR.$relative_path;

    if (!is_dir($full_path))
        die("\"$full_path\" is not a directory");

    $directory = opendir($full_path);
    if (!$directory)
        die("Could not open directory \"$full_path\"");

    while (false !== ($file = readdir($directory))) {
        $skip = false;
        foreach ($skip_patterns as $pattern) {
            if (preg_match($pattern, $file))
                $skip = true;;
        }
        if ($skip)
            continue;

        $file_info = get_file_info($full_path.DIRECTORY_SEPARATOR.$file);

        if (is_dir($file_info['path'])) {
            // Directory
            if (!in_array($file, array('.', '..'))) {
                array_push($tree['directories'], make_relative_path($root_path, $file_info['path'], false));
                load_filesystem_recursive($root_path, $relative_path.DIRECTORY_SEPARATOR.$file, $extensions, $skip_patterns, $tree);
            }
        }
        elseif (isset($file_info['extension'])) {
            // File
            foreach ($extensions as $key=>$value) {
                if (in_array($file_info['extension'], $value))
                    array_push($tree[$key], make_relative_path($root_path, $file_info['path'], false));
            }
        }
    }
    closedir($directory);
}

?>