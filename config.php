<?php
    $targets = ["beta", "prod"];

    $rootRelative = [];
    $rootAbsolute = [];

    $rootRelative["beta"] = "../beta/";
    $rootAbsolute["beta"] = "http://beta.example.com/";

    $rootRelative["prod"] = "../production/";
    $rootAbsolute["prod"] = "http://example.com/";

    $newPHPTemplate = $rootRelative["beta"] . "resources/page/default.php";
?>
