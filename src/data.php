<?php 

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

define('KEY_CONTENTS',              'contents');
define('KEY_VALID',                 'valid');
define('KEY_INVALID',               'invalid');
define('KEY_COMMENTS',              'comments');

define('TYPE_COMMENT',              'comment');
define('TYPE_VALID',                'valid');
define('TYPE_INVALID',              'invalid');

if (!isset($_GET['q']))
    die("No valid query given");

switch ($_GET['q']) {
    case 'playlist-tree':
        playlist_valid_tree();
        break;
    case 'playlist-contents':
        playlist_contents();
        break;
    case 'playlist-invalid-count':
        playlist_invalid_count();
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

function playlist_valid_tree() {
    if (isset($_GET['root']) && isset($_GET['path']) && isset($_SESSION[SESSION_MEDIA])) {
        $root = $_GET['root'];
        $playlist = $_GET['path'];
        $filesystem = unserialize($_SESSION[SESSION_MEDIA]);

        if (!file_exists($playlist))
            die("Could not locate playlist \"$playlist\"");

        $result = load_playlist($root, $playlist, true);

        $filesystem->check($result[KEY_VALID]);
        $filesystem->expand($result[KEY_VALID]);

        echo $filesystem->to_json();
    }
}

function playlist_contents() {
    if (isset($_GET['root']) && isset($_GET['path'])) {
        $root = $_GET['root'];
        $playlist = $_GET['path'];

        if (!file_exists($playlist))
            die("Could not locate playlist \"$playlist\"");

        $result = load_playlist($root, $playlist, true);

        echo json_encode($result[KEY_CONTENTS]);
    }
}

function playlist_invalid_count() {
    if (isset($_GET['root']) && isset($_GET['path'])) {
        $root = $_GET['root'];
        $playlist = $_GET['path'];

        if (!file_exists($playlist))
            die("Could not locate playlist \"$playlist\"");

        $result = load_playlist($root, $playlist, true);

        echo count($result[KEY_INVALID]);
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
        $handle = @fopen($playlist_file_info['path'], 'r')
            or die("Error: could not open file '$playlist_file_info[path]' for reading");
        $contents = fread($handle, filesize($playlist_file_info['path']));
        fclose($handle);
    }

    $result = array(
        KEY_CONTENTS => array(),
        KEY_VALID => array(),
        KEY_INVALID => array(),
        KEY_COMMENTS => array()
    );
    $comments = array();
    foreach (explode(LINE_BREAK, $contents) as $line) {
        $line = trim($line);
        if (strlen($line) > 0) {
            if ($line[0] == COMMENT_SYMBOL) {
                array_push($result[KEY_CONTENTS], array('type'=>TYPE_COMMENT, 'content'=>trim(substr($line, 1))));
                array_push($result[KEY_COMMENTS], trim(substr($line, 1)));
            }
            else {
                $file_path = realpath($line);
                if (strlen($file_path) == 0) // Check if path is relative
                    $file_path = simplify_path($playlist_file_info['dirname'].DIRECTORY_SEPARATOR.$line);

                if (file_exists($file_path)) {
                    array_push($result[KEY_CONTENTS], array('type'=>TYPE_VALID, 'content'=>$line));
                    array_push($result[KEY_VALID], $file_path);
                }
                else {
                    array_push($result[KEY_CONTENTS], array('type'=>TYPE_INVALID, 'content'=>$line));
                    array_push($result[KEY_INVALID], $file_path);
                }
            }
        }
    }

    return $result;
}

?>