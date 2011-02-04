<?php

echo "<h1>Unit tests</h1>";

require_once('file_handling.php');

$passed_all = true;
$test_number = 1;

function test($condition) {
    global $passed_all;
    global $test_number;

    $passed_all &= $condition;

    if ($condition)
        echo "#$test_number: passed";
    else
        echo "#$test_number: failed";
    echo"<br>";
    $test_number++;
}

echo "<b>simplify_path()</b><br>";
test(simplify_path("/foo/bar/baz") === "/foo/bar/baz");
test(simplify_path("/foo/./bar/baz") === "/foo/bar/baz");
test(simplify_path("/foo/abc/../bar/baz") === "/foo/bar/baz");
test(simplify_path("/foo/abc/.././bar/baz") === "/foo/bar/baz");

echo "<br><br><b>".($passed_all ? "<font color=\"#009900\">All tests passed!</font>" : "<font color=\"#990000\">Did not pass all tests!</font>")."</b>";

?>