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
            "relativePath" => "../beta/",
            "absolutePath" => "https://admin.example.com/"
        ]
    ]

    $newFileTemplate["php"] = $rootRelative["beta"] . "resources/page/default.php";
?>
