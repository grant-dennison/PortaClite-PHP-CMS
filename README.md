## Configuration
CMS expects a file called config.php in web root that looks something like the following:

```php
<?php
$targetGroups = [
    "beta" => [
        "beta"
    ],
    "blogger" => [
        "beta blog",
        "beta other"
    ],
    "live" => [
        "live"
    ]
];

$targets = [
    "beta" => [
        "name" => "beta",
        "relativePath" => "../beta/",
        "absolutePath" => "http://beta.example.com/",
        "publishTarget" => "live"
    ],
    "beta blog" => [
        "name" => "beta blog",
        "relativePath" => "../beta/blog/",
        "absolutePath" => "http://beta.example.com/blog/",
        "publishTarget" => "live blog"
    ],
    "beta other" => [
        "name" => "beta other",
        "relativePath" => "../beta/other/",
        "absolutePath" => "http://beta.example.com/other/",
        "publishTarget" => "live other"
    ],
    "live" => [
        "name" => "pkg.com live snap",
        "relativePath" => "../live/",
        "absolutePath" => "http://www.example.com/"
    ],
    "live blog" => [
        "name" => "live blog",
        "relativePath" => "../live/blog/",
        "absolutePath" => "http://www.example.com/blog/"
    ],
    "live other" => [
        "name" => "live other",
        "relativePath" => "../live/other/",
        "absolutePath" => "http://www.example.com/other/"
    ],
    "meta" => [
        "relativePath" => "./",
        "absolutePath" => "https://admin.example.com/"
    ]
];

$newFileTemplate["php"] = $targets["beta"]["relativePath"] . "resources/page/default.php";

$probablyBinaryDisplay = "[BINARY FILE]";
```