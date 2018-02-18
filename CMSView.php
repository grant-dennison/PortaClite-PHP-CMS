<!DOCTYPE html>
<html>
    <head>
        <?php
        require_once "config.php";
        require_once "helperFunctions.php";

        reset($targetGroups);
        $targetGroupName = key($targetGroups);
        if(array_key_exists("group", $_REQUEST)) {
            $targetGroupName = $_REQUEST["group"];
        }

        //Check that target is valid
        $targetGroupIsReal = false;
        foreach($targetGroups as $groupOpt => $group) {
            if($groupOpt === $targetGroupName) {
                $targetGroupIsReal = true;
                break;
            }
        }
        if(!$targetGroupIsReal) {
            echo "Invalid group. Please choose from the following\n";
            echo "<pre>";
            print_r(array_keys($targetGroups));
            echo "</pre>";
            return;
        }
        ?>

        <title><?= $targetGroupName ?> CMS</title>
        <link rel="icon"
        type="image/png"
        href="CMSIcon.png">

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
        var probablyBinaryDisplay = "<?= $probablyBinaryDisplay ?>";

        var targetConfigurations = <?= json_encode($targets) ?>;
        </script>

        <link rel="stylesheet" type="text/css" href="cms.css">
    </head>
    <body>
        <div id="SiteNavigationBar" class="clearfix">
            <a id="diffLocalButton" href="#">Preview Save</a>
            <a id="saveButton" href="#">Save</a>
            <a id="diffPublishButton" href="#">Preview Publish</a>
            <a id="publishButton" href="#">Publish</a>
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

        <div id="DropZoneBack" class="backdrop">
            <form id="DropZoneContainer" class="dropzone"
            style="position:relative; top: 50%; left: 50%; width: 400px; height: 300px; margin-left: -200px; margin-top: -150px;">
            </form>
        </div>

        <div id="DiffBack" class="backdrop">
            <pre id="DiffView"></pre>
        </div>

        <script src="codemirror-ext.js"></script>
        <script src="CMSController.js"></script>
    </body>
</html>
