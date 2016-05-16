<?php
    require_once "config.php";

    function nextTarget($currentTarget) {
        global $targetChain;

        if(isset($targetChain[$currentTarget])) {
            return $targetChain[$currentTarget];
        }
        return false;
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
