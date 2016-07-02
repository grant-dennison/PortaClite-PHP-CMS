<?php
    $targets = [
        "beta" => [
            "relativePath" => "../beta/",
            "absolutePath" => "http://beta.example.com/",
            "publishTarget" => "live"
        ],
        "live" => [
            "relativePath" => "../production/",
            "absolutePath" => "http://www.example.com/"
        ],
        "meta" => [
            "relativePath" => "./",
            "absolutePath" => "https://admin.example.com/"
        ]
    ];

    $newFileTemplate["php"] = $targets["beta"]["relativePath"] . "resources/page/default.php";

    $probablyBinaryDisplay = "[BINARY FILE]";
?>
