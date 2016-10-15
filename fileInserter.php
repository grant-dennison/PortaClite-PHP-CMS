<?php
    require_once "config.php";
    require_once "helperFunctions.php";

    $request = getJSONParams();

    $target = $request["target"];
    $name = $request["name"];
    $insertPath = $request["location"];
    $action = $request["action"];

    $targetDir = "";

    if(is_dir($insertPath)) {
        $targetDir = $insertPath;
    }
    else {
        $targetDir = dirname($insertPath) . "/";
    }

    $targetFileName = $targetDir . $name;

    switch($action) {
        case "mkdir": {
            ensurePathWithinTarget($targetFileName, $target);
            mkdir($targetFileName, 0755, true);
            break;
        }
        case "touch": {
            if(file_exists($targetFileName)) {
                die("File already exists!");
            }

            ensurePathWithinTarget($targetFileName, $target);

            $ext = pathinfo($targetFileName, PATHINFO_EXTENSION);

            if(isset($newFileTemplate[$ext])) {
                copy($newFileTemplate[$ext], $targetFileName);
            }
            else {
                touch($targetFileName);
            }
        }
    }
?>
