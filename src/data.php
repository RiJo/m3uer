<?php 

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

define('KEY_CONTENTS',              'contents');
define('KEY_VALID',                 'valid');
define('KEY_INVALID',               'invalid');
define('KEY_COMMENT',               'comment');

if (!isset($_GET['q']))
    die("No valid query given");

switch ($_GET['q']) {
    case 'playlist-tree':
        assure_keys($_GET, array('root', 'path'));
        assure_keys($_SESSION, SESSION_MEDIA);
        echo playlist_valid_tree($_GET['root'], $_GET['path'], unserialize($_SESSION[SESSION_MEDIA]));
        break;
    case 'playlist-contents':
        assure_keys($_GET, array('root', 'path'));
        echo playlist_contents($_GET['root'], $_GET['path']);
        break;
    case 'playlist-invalid-count':
        assure_keys($_GET, array('root', 'path'), '-1');
        echo playlist_invalid_count($_GET['root'], $_GET['path']);
        break;
    case 'playlists':
        assure_keys($_GET, array('root'));
        assure_keys($_SESSION, SESSION_PLAYLISTS);
        echo playlists($_GET['root'], unserialize($_SESSION[SESSION_PLAYLISTS]));
        break;
    default:
        die("Unrecognized query $_GET[q]");
}

function assure_keys($array, $keys, $error_message = '') {
    if (!is_array($keys))
        $keys = array($keys);

    foreach ($keys as $key)
        if (!isset($array[$key]))
            die($error_message);
}

////////////////////////////////////////////////////////////////////////////////
//   QUERIES   /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function playlist_valid_tree($root, $playlist, $filesystem) {
    if (!file_exists($playlist))
        die("Could not locate playlist \"$playlist\"");

    $result = load_playlist($root, $playlist, true);

    $filesystem->check($result[KEY_VALID]);
    $filesystem->expand($result[KEY_VALID]);

    return $filesystem->to_json();
}

function playlist_contents($root, $playlist) {

    if (!file_exists($playlist))
        die("Could not locate playlist \"$playlist\"");

    $result = load_playlist($root, $playlist, true);

    return json_encode($result[KEY_CONTENTS]);
}

function playlist_invalid_count($root, $playlist) {
    if (!file_exists($playlist))
        die("Could not locate playlist \"$playlist\"");

    $result = load_playlist($root, $playlist, true);

    return count($result[KEY_INVALID]);
}

function playlists($root, $playlists) {
    return $playlists->to_json();
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
        $handle = @fopen($playlist_file_info['path'], 'r')
            or die("Error: could not open file '$playlist_file_info[path]' for reading");
        $contents = fread($handle, filesize($playlist_file_info['path']));
        fclose($handle);
    }

    $result = array(
        KEY_CONTENTS => array(),
        KEY_VALID => array(),
        KEY_INVALID => array(),
        KEY_COMMENT => array()
    );
    $comments = array();
    foreach (explode(LINE_BREAK, $contents) as $line) {
        $line = trim($line);
        if (strlen($line) > 0) {
            if ($line[0] == COMMENT_SYMBOL) {
                array_push($result[KEY_CONTENTS], array('type'=>KEY_COMMENT, 'content'=>trim(substr($line, 1))));
                array_push($result[KEY_COMMENT], trim(substr($line, 1)));
            }
            else {
                $file_path = realpath($line);
                if (strlen($file_path) == 0) // Check if path is relative
                    $file_path = simplify_path($playlist_file_info['dirname'].DIRECTORY_SEPARATOR.$line);

                if (file_exists($file_path)) {
                    array_push($result[KEY_CONTENTS], array('type'=>KEY_VALID, 'content'=>$line));
                    array_push($result[KEY_VALID], $file_path);
                }
                else {
                    array_push($result[KEY_CONTENTS], array('type'=>KEY_INVALID, 'content'=>$line));
                    array_push($result[KEY_INVALID], $file_path);
                }
            }
        }
    }

    return $result;
}

?>