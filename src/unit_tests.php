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

echo "<b>simplify_path()</b><br>";
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



echo "<br><br><b>".($passed_all ? "<font color=\"#009900\">All tests passed!</font>" : "<font color=\"#990000\">Did not pass all tests!</font>")."</b>";

?>