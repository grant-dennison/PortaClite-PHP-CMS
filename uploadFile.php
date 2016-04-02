<?php
    require_once "config.php";
    require_once "helperFunctions.php";
    $targetDir = "";
    $insertPath = $_POST["location"];
    $target = $_POST["target"];

    if(is_dir($insertPath)) {
        $targetDir = $insertPath;
    }
    else {
        $targetDir = dirname($insertPath) . "/";
    }
    $target_file = $targetDir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;

    // Check if file already exists
    if (file_exists($target_file)) {
        error_log("Sorry, file already exists.");
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["file"]["size"] > 100000000) {
        error_log("Sorry, your file is too large.");
        $uploadOk = 0;
    }
    // Allow certain file formats
    /*if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }*/
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        die("Sorry, your file was not uploaded.");
    // if everything is ok, try to upload file
    } else {
        ensurePathWithinTarget($target_file, $target);

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
//            error_log("The file ". basename( $_FILES["file"]["name"]). " has been uploaded to " . $target_dir);
        } else {
            error_log("Sorry, there was an error uploading your file.");
        }
    }
?>
