<?php

echo "<h1>Unit tests</h1>";

require_once('file_handling.php');

$passed_all = true;
$test_number = 1;

function test($actual, $reference) {
    global $passed_all;
    global $test_number;

    $condition = ($actual === $reference);
    $passed_all &= $condition;

    if ($condition)
        echo "#$test_number: <font color=\"#006600\">passed</font>";
    else
        echo "#$test_number: <font color=\"#660000\">failed</font> ('$actual' should be '$reference')";
    echo"<br>";
    $test_number++;
}

echo "<br><b>simplify_path()</b><br>";
test(simplify_path("/foo/bar/baz"), "/foo/bar/baz");
test(simplify_path("./foo/bar/baz"), "foo/bar/baz");
test(simplify_path("../foo/bar/baz"), "../foo/bar/baz");
test(simplify_path("/foo/./bar/baz"), "/foo/bar/baz");
test(simplify_path("/foo/abc/../bar/baz"), "/foo/bar/baz");
test(simplify_path("/foo/abc/.././bar/baz"), "/foo/bar/baz");
test(simplify_path("/abc/def/ghi/../../jkl"), "/abc/jkl");
test(simplify_path("./././."), "");
test(simplify_path("././././."), "");
test(simplify_path("../../../.."), "../../../..");
test(simplify_path("../../../../.."), "../../../../..");

echo "<br><b>make_relative_path()</b><br>";
// Test relative to file
test(make_relative_path("foo.m3u", "bar.mp3", true), "bar.mp3");
test(make_relative_path("/foo/bar.m3u", "/foo/bar.mp3", true), "bar.mp3");
test(make_relative_path("/foo/bar.m3u", "/foo/bar.m3u", true), "bar.m3u");
test(make_relative_path("/foo/bar.m3u", "/", true), "..");
test(make_relative_path("/", "/foo/bar.mp3", true), "foo/bar.mp3");
test(make_relative_path("/foo/bar.m3u", "/foo/bar/baz.mp3", true), "bar/baz.mp3");
test(make_relative_path("/foo/bar/baz.m3u", "/foo/bar.mp3", true), "../bar.mp3");
test(make_relative_path("/foo/bar/baz.m3u", "/foo/baz/bar.mp3", true), "../baz/bar.mp3");
// Test relative to directory
test(make_relative_path("foo", "bar.mp3", false), "../bar.mp3");
test(make_relative_path("/foo", "/foo/bar.mp3", false), "bar.mp3");
test(make_relative_path("/foo/bar", "/foo/bar", false), "");
test(make_relative_path("/foo/bar", "/", false), "../..");
test(make_relative_path("/", "/foo/bar.mp3", false), "foo/bar.mp3");
test(make_relative_path("/foo/bar/baz", "/foo/bar.mp3", false), "../../bar.mp3");
test(make_relative_path("/foo/bar/baz", "/foo/baz/bar.mp3", false), "../../baz/bar.mp3");

$root = '/home/rijo/programming/github/m3uer/src';
$extensions = array(
    'playlists' => array('m3u'),
    'music' => array('wav', 'mp3')
);
$tree = load_filesystem($root, $extensions);
echo "<pre>".print_r($tree, true)."</pre>";

echo "<br><br><b>".($passed_all ? "<font color=\"#009900\">All tests passed!</font>" : "<font color=\"#990000\">Did not pass all tests!</font>")."</b>";

?>