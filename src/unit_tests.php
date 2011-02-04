<?php

echo "<h1>Unit tests</h1>";

require_once('data.php');

$test_number = 1;
function test($condition) {
    global $test_number;

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

?>