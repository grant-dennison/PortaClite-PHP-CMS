<!DOCTYPE html>
<html>
    <head>
        <?php
        require_once "config.php";
        require_once "helperFunctions.php";

        $targetName = $_REQUEST["target"];
        if(!$targetName) {
            reset($targets);
            $targetName = key($targets);
        }

        //Check that target is valid
        $targetIsReal = false;
        foreach($targets as $targetOpt => $targetInfo) {
            if($targetOpt == $targetName) {
                $targetIsReal = true;
                break;
            }
        }
        if(!$targetIsReal) {
            echo "Invalid target. Please choose from the following\n";
            echo "<pre>";
            print_r(array_keys($targets));
            echo "</pre>";
            return;
        }
        ?>

        <title><?php echo $targetName; ?> CMS</title>
        <link rel="icon"
        type="image/png"
        href="/CMSIcon.png">

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

        <!-- JSDiff -->
        <script src="bower_components/jsdiff/diff.min.js"></script>

        <script>
        var probablyBinaryDisplay = "<?php echo $probablyBinaryDisplay; ?>";

        var serveTarget = encodeURIComponent("<?php echo $targetName; ?>");
        var targetName = "<?php echo $targetName; ?>";
        var root_mirror = "<?php echo $targetInfo["relativePath"]; ?>";
        var fullRoot_mirror = "<?php echo $targetInfo["absolutePath"]; ?>";
        <?php
        $publishTargetName = $targetInfo["publishTarget"];
        if($publishTargetName) :
            $publishTargetInfo = $targets[$publishTargetName];
        ?>
        var publishTargetName = "<?php echo $publishTargetName; ?>";
        var publishRoot_mirror = "<?php echo $publishTargetInfo["relativePath"]; ?>";
        var fullPublishRoot_mirror = "<?php echo $publishTargetInfo["absolutePath"]; ?>";
        <?php
        else :
        ?>
        var publishTargetName = null;
        var publishRoot_mirror = null;
        var fullPublishRoot_mirror = null;
        <?php
        endif;
        ?>
        </script>

        <link rel="stylesheet" type="text/css" href="cms.css">
    </head>
    <body>
        <div id="SiteNavigationBar" class="clearfix">
            <a id="saveButton" href="#">Save</a>
            <a id="diffLocalButton" href="#">Diff Local</a>
            <?php if($targetInfo["publishTarget"]) : ?>
                <a id="publishButton" href="#">Publish</a>
                <a id="diffPublishButton" href="#">Diff Publish</a>
            <?php endif; ?>
        </div>

        <table id="mainPane"><tr id="mainRow">
            <!-- File Browser -->
            <td style="width: 30%;padding: 0;top: 0px;bottom: 100px;height: auto;">
                <?php
                require "fileBrowserStart.php";
                ?>
                <script>
                var fileBrowsers = document.getElementsByClassName("fileBrowser");
                var mainFileBrowser = fileBrowsers[fileBrowsers.length - 1];
                </script>
            </td>
            <td style="width: 70%;left: 30%;padding: 0;top: 0px;bottom: 100px;height: auto;">
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

        <div id="DiffBack" style="visibility: hidden; position: absolute; z-index: 10; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(50, 50, 50, .5)">
            <div id="DiffView" style="position:relative; top: 50%; left: 50%; width: 400px; height: 300px; margin-left: -200px; margin-top: -150px; background-color: white; font-family: 'Lucida Console', Monaco, monospace"></div>
        </div>

        <script src="codemirror-ext.js"></script>
        <script src="CMSController.js"></script>
    </body>
</html>
