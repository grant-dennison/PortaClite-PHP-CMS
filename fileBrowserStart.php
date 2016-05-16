<?php
    require_once "config.php";

    if(isset($_REQUEST["directory"])) {
        $browserRoot = $_REQUEST["directory"];
    }
    else if(isset($targetName) && isset($targets[$targetName])) {
        $browserRoot = $targets[$targetName]["relativePath"];
    }
?>
<script>
    var browserRoot_mirror = "<?php echo $browserRoot; ?>";
</script>

<ul class="fileBrowser">
    <script>
        var scriptTag = document.scripts[document.scripts.length - 1];
        var parentFileBrowser = scriptTag.parentElement;
        var targetForURI = encodeURIComponent("<?php echo $targetName; ?>");

        parentFileBrowser.addEventListener("click", function(e) {
            var linkElement = e.target;

            if(linkElement.tagName != "A") {
                return;
            }

            var linkText = linkElement.innerHTML;
            var link = linkElement.getAttribute("href");

            var lastChar = linkText.charAt(linkText.length - 1);

            var eventData = {
                "clientX": e.clientX,
                "clientY": e.clientY,
                "target": e.target,
                "path": link,
                "basename": linkText,
                "isDir": lastChar == "/",
            }
            var event = new CustomEvent("clicklink", {"detail": eventData});
            var cancelled = !parentFileBrowser.dispatchEvent(event);

            if(!cancelled) {

                if(lastChar == "/") {
                    var linkParent = linkElement.parentElement;
                    var childLists = linkParent.getElementsByTagName("ul");
                    if(childLists.length == 0) {
                        var xhttp = new XMLHttpRequest();
                        xhttp.onreadystatechange = function() {
                            if (xhttp.readyState == 4 && xhttp.status == 200) {
                                var newList = document.createElement("ul");
                                newList.innerHTML = xhttp.responseText;
                                linkParent.appendChild(newList);
                            }
                        };

                        var requestURL = "fileBrowser.php";
                        var params = "target=" + targetForURI + "&directory=" + encodeURIComponent(link);
                        xhttp.open("POST", requestURL, true);
                        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhttp.send(params);
                    }
                    else {
                        linkParent.removeChild(childLists[0]);
                    }
                }
                else {
                    //File
                }
            }

            e.preventDefault();
        });

        parentFileBrowser.addEventListener("dblclick", function(e) {
            var linkElement = e.target;

            if(linkElement.tagName != "A") {
                return;
            }

            var linkText = linkElement.innerHTML;
            var link = linkElement.getAttribute("href");

            var lastChar = linkText.charAt(linkText.length - 1);

            var eventData = {
                "clientX": e.clientX,
                "clientY": e.clientY,
                "target": e.target,
                "path": link,
                "basename": linkText,
                "isDir": lastChar == "/",
            }
            var event = new CustomEvent("dblclicklink", {"detail": eventData, bubbles: true, cancelable: true});
            var cancelled = !parentFileBrowser.dispatchEvent(event);

            if(!cancelled) {

                if(lastChar == "/") {
                    //Directory
                }
                else {
                    window.open(link,'_blank');
                }
            }

            e.preventDefault();
        });

        parentFileBrowser.addEventListener("contextmenu", function(e) {
            var linkElement = e.target;

            if(linkElement.tagName != "A") {
                return;
            }

            var linkText = linkElement.innerHTML;
            var link = linkElement.getAttribute("href");

            var lastChar = linkText.charAt(linkText.length - 1);

            var eventData = {
                "clientX": e.clientX,
                "clientY": e.clientY,
                "target": e.target,
                "path": link,
                "basename": linkText,
                "isDir": lastChar == "/",
            }
            var event = new CustomEvent("contextmenulink", {"detail": eventData});
            var cancelled = !parentFileBrowser.dispatchEvent(event);

            if(!cancelled) {

                //TODO: add "open in new tab" option?
            }

           e.preventDefault();
        });
    </script>
    <li>
        <a class="browserItem rootBrowserItem" href="<?php echo $browserRoot; ?>">root/</a>
    </li>
</ul>
