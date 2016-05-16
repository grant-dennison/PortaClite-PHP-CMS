<!DOCTYPE html>
<html>
<head>
    <?php
        if(isset($_REQUEST["target"])) {
            $target = $_REQUEST["target"];
            if($target == "live") {
                $target = "prod";
            }
        }
        if(!isset($target)) {
            $target = "beta";
        }

        require "config.php";
        require "helperFunctions.php";

        //Check that target is valid
        $targetIsReal = false;
        foreach($targets as $targetOpt) {
            if($targetOpt == $target) {
                $targetIsReal = true;
            }
        }
        if(!$targetIsReal) {
            echo "Invalid target. Please choose from the following\n";
            echo "<pre>";
            print_r($targets);
            echo "</pre>";
            return;
        }
    ?>

    <title>PortaClite - <?php echo $target; ?></title>
    <link rel="icon"
        type="image/png"
        href="/CMSIcon.png">

    <!-- jQuery -->
    <!-- <script src="jquery-1.12.0.min.js"></script> -->

    <!-- Dropzone -->
    <script src="bower_components/dropzone/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="bower_components/dropzone/dist/min/dropzone.min.css">

    <!-- ContextMenu -->
    <link rel="stylesheet" href="bower_components/contextmenu/contextmenu.css">
    <script src="bower_components/contextmenu/contextmenu.js"></script>

    <!-- CodeMirror -->
    <link rel="stylesheet" href="bower_components/codemirror/lib/codemirror.css">
    <script src="bower_components/codemirror/lib/codemirror.js"></script>
    <script src="bower_components/codemirror/addon/edit/matchbrackets.js"></script>
    <script src="bower_components/codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="bower_components/codemirror/mode/sql/sql.js"></script>
    <script src="bower_components/codemirror/mode/xml/xml.js"></script>
    <script src="bower_components/codemirror/mode/javascript/javascript.js"></script>
    <script src="bower_components/codemirror/mode/css/css.js"></script>
    <script src="bower_components/codemirror/mode/clike/clike.js"></script>
    <script src="bower_components/codemirror/mode/php/php.js"></script>

    <script>
        var serveTarget = encodeURIComponent("<?php echo $target; ?>");
        var root_mirror = "<?php echo $rootRelative[$target]; ?>";
        var fullRoot_mirror = "<?php echo $rootAbsolute[$target]; ?>";
        <?php if(nextTarget($target)) : ?>
            var publishRoot_mirror = "<?php echo $rootRelative[nextTarget($target)]; ?>";
            var fullPublishRoot_mirror = "<?php echo $rootAbsolute[nextTarget($target)]; ?>";
        <?php else : ?>
            var publishRoot_mirror = null;
            var fullPublishRoot_mirror = null;
        <?php endif; ?>
        var fullProductionPath_mirror = "<?php echo $rootAbsolute["prod"]; ?>";
        var fullBetaPath_mirror = "<?php echo $rootAbsolute["beta"]; ?>";
        console.log("fullBetaPath: " + fullBetaPath_mirror);
    </script>

    <link rel="stylesheet" type="text/css" href="cms.css">
</head>
<body>
    <div id="SiteNavigationBar" class="clearfix">
        <a id="saveButton" href="#">Save</a>
        <?php if(nextTarget($target)) : ?>
            <a id="publishButton" href="#">Publish</a>
        <?php endif; ?>
        <!-- <a href="#">Engrave</a>
        <a href="#">Toggle Editor</a> -->
    </div>

    <table id="mainPane"><tr id="mainRow">
        <!-- File Browser -->
        <td style="width: 30%;padding: 0;top: 0px;bottom: 100px;height: auto;">
            <!-- <div style="resize: horizontal; width:250px; overflow: scroll; white-space:nowrap; height:auto; border: 1px solid black;"> -->
                <?php
                    require "fileBrowserStart.php";
                ?>
            <!-- </div> -->
            <script>
                var fileBrowsers = document.getElementsByClassName("fileBrowser");
                var mainFileBrowser = fileBrowsers[fileBrowsers.length - 1];
            </script>
        </td>
        <td style="width: 70%;left: 30%;padding: 0;top: 0px;bottom: 100px;height: auto;">
            <!-- <iframe id="contentIFrame" src="" style="display: none; width: 100%; border: none;"></iframe>
            <script>
                var contentIFrame = document.getElementById("contentIFrame");
            </script> -->
            <div id="codeEditor" style="width: 100%; height: 100%;">
            </div>
        </td>
    </tr></table>

    <div style="position: absolute; bottom: 0;">
        <p id="fileInfo">No file opened</p>
    </div>

    <div id="DropZoneBack" style="visibility: hidden; position: absolute; z-index: 10; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(50, 50, 50, .5)">
        <form id="DropZoneContainer" class="dropzone"
            style="position:relative; top: 50%; left: 50%; width: 400px; height: 300px; margin-left: -200px; margin-top: -150px;">
        </form>
    </div>

    <script src="codemirror-ext.js"></script>
    <script src="CMSController.js"></script>
</body>
</html>
