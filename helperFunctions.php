<?php
    require_once "config.php";

    function nextTarget($currentTarget) {
        global $targets;
        $i = 0;
        $targetCount = count($targets);
        while($i < $targets && $targets[$i] != $currentTarget) {
            $i++;
        }
        if($i == $targetCount) {
            error_log("target $currentTarget not found");
            return false;
        }
        else if($i == $targetCount - 1) {
            return false;
        }
        else {
            return $targets[$i + 1];
        }
    }

    function ensurePathWithinTarget($path, $target) {
        if(!isPathWithinTarget($path, $target)) {
            error_log("Attempted security breach: accessing $path supposedly under target $target");
            die("Access denied. Path ($path) not within specified target ($target) directory.");
        }
    }
    function isPathWithinTarget($path, $target) {
        global $rootRelative;
        if(!isset($rootRelative[$target])) {
            return false;
        }

        return isPathWithinPath($path, $rootRelative[$target]);
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
