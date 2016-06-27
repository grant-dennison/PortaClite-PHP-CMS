<?php
    require_once "config.php";
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

    $response = [];

    // return mime type ala mimetype extension
    $finfo = finfo_open(FILEINFO_MIME);

    switch($action) {
        case "publish": {
            if (!file_exists(dirname($nextTargetFilename))) {
                mkdir(dirname($nextTargetFilename), 0755, true);
            }

            ensurePathWithinTarget($nextTargetFilename, $nextTargetName);

            $targetFile = fopen($targetFilename, "r");
            $nextTargetFile = fopen($nextTargetFilename, "c");
            //I don't think order matters here since there shouldn't be any cycles
            flock($targetFile, LOCK_SH);
            flock($nextTargetFile, LOCK_EX);
            $sha1Target = sha1_file($targetFilename);
            $sha1Next = sha1_file($nextTargetFilename);
            if($_POST["hash"] == $sha1Target && $_POST["deployHash"] == $sha1Next) {
                copy($targetFilename, $nextTargetFilename);
                $sha1Next = $sha1Target;
                $response["success"] = true;
            }
            $response["hash"] = $sha1Target;
            $response["deployHash"] = $sha1Next;
            flock($nextTargetFile, LOCK_UN);
            flock($targetFile, LOCK_UN);
            fclose($nextTargetFile);
            fclose($targetFile);
            break;
        }
        case "save": {
            $file = fopen($targetFilename, "c") or die("Unable to open file |".$targetFilename."|!");
            flock($file, LOCK_EX);
            if(sha1_file($targetFilename) == $_POST["hash"]) {
                ftruncate($file, 0);
                fwrite($file, $content);
                $response["success"] = true;
            }
            $response["hash"] = sha1_file($targetFilename);
            $response["deployHash"] = sha1_file($nextTargetFilename);
            flock($file, LOCK_UN);
            fclose($file);
            break;
        }
        case "delete": {
            deleteFile($targetFilename);
            break;
        }
        case "diff": {
            if(filesize($nextTargetFilename) > 100000000) {
                $response["contentNextTarget"] = $probablyBinaryDisplay;
            }
            else {
                $contentsNextTarget = file_get_contents($nextTargetFilename);

                //check to see if the mime-type starts with 'text'
                if(substr(finfo_file($finfo, $nextTargetFilename), 0, 4) == 'text' || $contentsNextTarget == '' || ctype_space($contentsNextTarget)) {
                    if (!file_exists($nextTargetFilename)) {
                        touch($nextTargetFilename);
                    }
                    $response["contentNextTarget"] =  $contentsNextTarget;
                }
                else {
                    $response["contentNextTarget"] = $probablyBinaryDisplay;
                }
            }
        }
        case "fetch": {
            if(filesize($targetFilename) > 100000000) {
                $response["content"] = $probablyBinaryDisplay;
            }
            else {
                $contents = file_get_contents($targetFilename);

                //check to see if the mime-type starts with 'text'
                if(substr(finfo_file($finfo, $targetFilename), 0, 4) == 'text' || $contents == '' || ctype_space($contents)) {
                    if (!file_exists($targetFilename)) {
                        touch($targetFilename);
                    }
                    $response["content"] =  $contents;
                }
                else {
                    $response["content"] = $probablyBinaryDisplay;
                }
            }
            $response["success"] = true;
            $response["hash"] = sha1_file($targetFilename);
            $response["deployHash"] = sha1_file($nextTargetFilename);
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

    echo json_encode($response);

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
