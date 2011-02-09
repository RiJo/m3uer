<?php

function get_file_info($path) {
    $real_path = realpath($path);
    $file_info = pathinfo($real_path);
    //~ $file_info['path'] = $path;
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

function make_relative_path($source, $destination) {
    $source_directories = explode(DIRECTORY_SEPARATOR, $source);
    $destination_directories = explode(DIRECTORY_SEPARATOR, $destination);

    $branch = 0;
    for ($i = 0; $i < count($source_directories) - 1 && $i < count($destination_directories) - 1; $i++) {
        if ($source_directories[$i] != $destination_directories[$i])
            break;
        $branch = $i + 1;
    }

    $temp = array();
    for ($i = $branch; $i < count($source_directories) - 1; $i++) {
        array_push($temp, '..');
    }
    for ($i = $branch; $i < count($destination_directories); $i++) {
        if ($destination_directories[$i] != '')
            array_push($temp, $destination_directories[$i]);
    }
    return implode(DIRECTORY_SEPARATOR, $temp);
}

// TODO: reuse when loading tree-structure..
function get_files($root, $relative_path, $extensions, $directories = false) {
    $full_path = $root.DIRECTORY_SEPARATOR.$relative_path;

    if (!is_dir($full_path))
        die("\"$full_path\" is not a directory");

    $directory = opendir($full_path);
    if (!$directory)
        die("Could not open directory \"$full_path\"");

    $files = array();
    while (false !== ($file = readdir($directory))) {
        $file_info = get_file_info($full_path.DIRECTORY_SEPARATOR.$file);

        if (is_dir($file_info['path']) && !in_array($file, array('.', '..'))) {
            // Directory
            if ($directories)
                array_push($files, $file_info['path']);
            $files = array_merge($files, get_files($root, $relative_path.DIRECTORY_SEPARATOR.$file, $extensions));
        }
        elseif (isset($file_info['extension']) && in_array($file_info['extension'], $extensions)) {
            // File
            array_push($files, simplify_path($file_info['path']));
        }
    }
    closedir($directory);

    return $files;
}

function load_filesystem($root_path, $extensions) {
    $result = array();
    foreach ($extensions as $key=>$value)
        $result[$key] = array();
    
    load_filesystem_recursive($root_path, '.', $extensions, $result);
    return $result;
}

function load_filesystem_recursive($root_path, $relative_path, $extensions, &$tree) {
    $full_path = $root_path.DIRECTORY_SEPARATOR.$relative_path;

    if (!is_dir($full_path))
        die("\"$full_path\" is not a directory");

    $directory = opendir($full_path);
    if (!$directory)
        die("Could not open directory \"$full_path\"");

    while (false !== ($file = readdir($directory))) {
        $file_info = get_file_info($full_path.DIRECTORY_SEPARATOR.$file);

        if (is_dir($file_info['path'])) {
            // Directory
            if (!in_array($file, array('.', '..'))) {
                foreach ($extensions as $key=>$value)
                    array_push($tree[$key], make_relative_path($root_path, $file_info['path']));
                load_filesystem_recursive($root_path, $relative_path.DIRECTORY_SEPARATOR.$file, $extensions, $tree);
            }
        }
        elseif (isset($file_info['extension'])) {
            // File
            foreach ($extensions as $key=>$value) {
                if (in_array($file_info['extension'], $value))
                    array_push($tree[$key], make_relative_path($root_path, $file_info['path']));
            }
        }
    }
    closedir($directory);
}

?>