<?php
    $targets = ["meta", "beta", "prod"];

    $targetChain = [
        "beta" => "prod"
    ];

    $rootRelative = [];
    $rootAbsolute = [];

    $rootRelative["meta"] = "../";
    $rootAbsolute["meta"] = "https://admin.example.com/";

    $rootRelative["beta"] = "../beta/";
    $rootAbsolute["beta"] = "http://beta.example.com/";

    $rootRelative["prod"] = "../production/";
    $rootAbsolute["prod"] = "http://www.example.com/";

    $newPHPTemplate = $rootRelative["beta"] . "resources/page/default.php";
?>
