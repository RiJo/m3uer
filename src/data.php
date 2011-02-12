<?php 

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

define('KEY_VALID',                 'valid');
define('KEY_INVALID',               'invalid');
define('KEY_COMMENTS',              'commants');

if (!isset($_GET['q']))
    die("No valid query given");

switch ($_GET['q']) {
    case 'playlist':
        playlist();
        break;
    case 'playlist-invalid':
        playlist_type_list(KEY_INVALID);
        break;
    case 'playlist-comments':
        playlist_type_list(KEY_COMMENTS);
        break;
    case 'playlists':
        playlists();
        break;
    default:
        die("Unrecognized query $_GET[q]");
}

////////////////////////////////////////////////////////////////////////////////
//   QUERIES   /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function playlist() {
    if (isset($_GET['root']) && isset($_GET['path']) && isset($_SESSION[SESSION_MEDIA])) {
        $root = $_GET['root'];
        $playlist = $_GET['path'];
        $filesystem = unserialize($_SESSION[SESSION_MEDIA]);

        if (!file_exists($playlist))
            die("Could not locate playlist \"$playlist\"");

        $result = parse_playlist($root, $playlist);

        $filesystem->check($result[KEY_VALID]);
        $filesystem->expand($result[KEY_VALID]);

        //~ die("<pre>".print_r($result, true)."</pre>");

        echo $filesystem->to_json();
    }
}

function playlist_type_list($key) {
    if (isset($_GET['root']) && isset($_GET['path'])) {
        $root = $_GET['root'];
        $playlist = $_GET['path'];

        if (!file_exists($playlist))
            die("Could not locate playlist \"$playlist\"");

        $result = parse_playlist($root, $playlist);

        return json_encode($result[$key]);
    }
}

function playlists() {
    if (isset($_GET['root']) && isset($_SESSION[SESSION_PLAYLISTS])) {
        $root = $_GET['root'];
        $playlists = unserialize($_SESSION[SESSION_PLAYLISTS]);

        echo $playlists->to_json();
    }
}

////////////////////////////////////////////////////////////////////////////////
//   HELPERS   /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function load_playlist($root, $playlist, $force_reload = false) {
    if (!isset($_SESSION[SESSION_PLAYLIST]))
        $_SESSION[SESSION_PLAYLIST] = array();

    if (!isset($_SESSION[SESSION_PLAYLIST][$playlist]) || $force_reload) {
        $_SESSION[SESSION_PLAYLIST][$playlist] = parse_playlist($root, $playlist);
    }
    return $_SESSION[SESSION_PLAYLIST][$playlist];
}

function parse_playlist($root, $playlist) {
    $playlist_file_info = get_file_info($playlist);

    $contents = "";
    if (filesize($playlist_file_info['path']) > 0) {
        $handle = fopen($playlist_file_info['path'], 'r')
            or die("Error: could not open file '$playlist_file_info[path]' for reading");
        $contents = fread($handle, filesize($playlist_file_info['path']));
        fclose($handle);
    }

    $result = array(
        KEY_VALID => array(),
        KEY_INVALID => array(),
        KEY_COMMENTS => array()
    );
    $comments = array();
    foreach (explode(LINE_BREAK, $contents) as $line) {
        $line = trim($line);
        if (strlen($line) > 0) {
            if ($line[0] == COMMENT_SYMBOL) {
                array_push($result[KEY_COMMENTS], trim(substr($line, 1)));
            }
            else {
                $file_path = realpath($line);
                if (strlen($file_path) == 0) // Check if path is relative
                    $file_path = simplify_path($playlist_file_info['dirname'].DIRECTORY_SEPARATOR.$line);

                if (file_exists($file_path))
                    array_push($result[KEY_VALID], $file_path);
                else
                    array_push($result[KEY_INVALID], $file_path);
            }
        }
    }

    return $result;
}

?>