<?php
    require "config.php";
    require_once "helperFunctions.php";

    $content = $_POST["content"];

    $targetName = $_POST["target"];
    $targetInfo = $targets[$targetName];
    if(!$targetInfo) {
        return;
    }
    $nextTargetName = $targetInfo["publishTarget"];
    $nextTargetInfo = $targets[$nextTargetName];

    $targetRoot = $targetInfo["relativePath"];
    $targetFilename = $_POST["file"];
    ensurePathWithinTarget($targetFilename, $targetName);

    if($nextTargetName) {
        $nextTargetRoot = $nextTargetInfo["relativePath"];
        $nextTargetFilename = substr_replace($targetFilename, $nextTargetRoot, 0, strlen($targetRoot));
    }

    $action = $_POST["action"];

    switch($action) {
        case "publish": {
            if (!file_exists(dirname($nextTargetFilename))) {
                mkdir(dirname($nextTargetFilename), 0755, true);
            }

            ensurePathWithinTarget($nextTargetFilename, $nextTargetName);

            copy($targetFilename, $nextTargetFilename);
            break;
        }
        case "save": {
            $file = fopen($targetFilename, "w") or die("Unable to open file |".$targetFilename."|!");
            fwrite($file, $content);
            fclose($file);
            break;
        }
        case "delete": {
            deleteFile($targetFilename);
            break;
        }
        case "isPublished": {
            if(file_exists($nextTargetFilename) && sha1_file($targetFilename) == sha1_file($nextTargetFilename)) {
                echo "true";
            }
            else {
                echo "false";
            }
            break;
        }
        case "fetch": {
            // return mime type ala mimetype extension
            $finfo = finfo_open(FILEINFO_MIME);
            $probablyBinaryDisplay = "[BINARY FILE]";

            if(filesize($targetFilename) > 100000000) {
                echo $probablyBinaryDisplay;
            }
            else {
                $contents = file_get_contents($targetFilename);

                //check to see if the mime-type starts with 'text'
                if(substr(finfo_file($finfo, $targetFilename), 0, 4) == 'text' || $contents == '' || ctype_space($contents)) {
                    if (!file_exists($targetFilename)) {
                        $newFile = fopen($targetFilename, "w");
                        fclose($newFile);
                    }
                    echo $contents;
                }
                else {
                    echo $probablyBinaryDisplay;
                }
            }
            break;
        }
        case "move": {
            $newPath = $content;
            if(file_exists($newPath)) {
                die("file already exists");
            }

            //Prevent security breach
            ensurePathWithinTarget($newPath, $targetName);

            rename($targetFilename, $newPath);
            break;
        }
    }

    function deleteFile($filename) {
        if(!file_exists($filename)) {
            return false;
        }
        else if(is_dir($filename)) { //Handle directories differently. See http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
            $dir = $filename;
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                    RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
            echo "successfully deleted $dir\n";
        }
        else {
            if(unlink($filename)) {
                echo "successfully deleted $filename\n";
            }
        }
    }
?>
