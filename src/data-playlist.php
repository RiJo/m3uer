<?php 

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

function load_playlist($root, $playlist) {
    $playlist_file_info = get_file_info($root.DIRECTORY_SEPARATOR.$playlist);

    $contents = "";
    if (filesize($playlist_file_info['path']) > 0) {
        $handle = fopen($playlist_file_info['path'], 'r')
            or die("Error: could not open file '$playlist_file_info[path]' for reading");
        $contents = fread($handle, filesize($playlist_file_info['path']));
        fclose($handle);
    }

    $result = array(
        'valid' => array(),
        'invalid' => array(),
        'comments' => array()
    );
    $comments = array();
    foreach (explode(LINE_BREAK, $contents) as $line) {
        $line = trim($line);
        if (strlen($line) > 0) {
            if ($line[0] == COMMENT_SYMBOL) {
                array_push($result['comments'], trim(substr($line, 1)));
            }
            else {
                $file_path = realpath($line);
                if (strlen($file_path) == 0) // Check if path is relative
                    $file_path = simplify_path($playlist_file_info['dirname'].DIRECTORY_SEPARATOR.$line);

                if (file_exists($file_path))
                    array_push($result['valid'], $file_path);
                else
                    array_push($result['invalid'], $file_path);
            }
        }
    }

    return $result;
}

if (isset($_GET['root']) && isset($_GET['path']) && isset($_SESSION[SESSION_MEDIA])) {
    $root = $_GET['root'];
    $playlist = $_GET['path'];
    $filesystem = unserialize($_SESSION[SESSION_MEDIA]);

    if (!file_exists($playlist))
        die("Could not locate playlist \"$playlist\"");

    $result = load_playlist($root, $playlist);

    $filesystem->check_paths($result['valid']);

    //~ die("<pre>".print_r($result, true)."</pre>");

    echo $filesystem->to_json();
}

?>