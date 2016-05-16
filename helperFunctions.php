<?php
    require_once "config.php";

    function ensurePathWithinTarget($path, $targetName) {
        if(!isPathWithinTarget($path, $targetName)) {
            error_log("Attempted security breach: accessing $path supposedly under target $targetName");
            die("Access denied. Path ($path) not within specified target ($targetName) directory.");
        }
    }
    function isPathWithinTarget($path, $targetName) {
        global $targets;
        if(!isset($targets[$targetName])) {
            return false;
        }

        return isPathWithinPath($path, $targets[$targetName]["relativePath"]);
    }
    function isPathWithinPath($innerPath, $outerPath) {
        $fileCreated = false;
        if(!file_exists($innerPath)) {
            touch($innerPath);
            $fileCreated = true;
        }
        $fullInnerPath = realpath($innerPath);
        $fullOuterPath = realpath($outerPath);
        if($fileCreated) {
            unlink($innerPath);
        }

        return $fullOuterPath == substr($fullInnerPath, 0, strlen($fullOuterPath));
    }
?>
