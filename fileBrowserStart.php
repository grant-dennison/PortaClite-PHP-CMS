<ul class="fileBrowser">
    <script>
        var scriptTag = document.scripts[document.scripts.length - 1];
        var parentFileBrowser = scriptTag.parentElement;

        function clickEventToCustomEvent(e, customType) {
            var linkElement = e.target;

            var linkText = linkElement.innerHTML;
            var link = linkElement.getAttribute("href");
            var serverTarget = linkElement.getAttribute("data-target");

            var lastChar = linkText.charAt(linkText.length - 1);

            var eventData = {
                "clientX": e.clientX,
                "clientY": e.clientY,
                "target": e.target,
                "serverTarget": serverTarget,
                "path": link,
                "basename": linkText,
                "isDir": lastChar === "/",
            };
            return new CustomEvent(customType, {"detail": eventData});
        }

        parentFileBrowser.addEventListener("click", function(e) {
            if(e.target.tagName !== "A") {
                return;
            }

            var event = clickEventToCustomEvent(e, "clicklink");
            var cancelled = !parentFileBrowser.dispatchEvent(event);

            if(!cancelled) {
                if(event.detail.isDir) {
                    var linkElement = event.detail.target;
                    var linkParent = linkElement.parentElement;
                    var childLists = linkParent.getElementsByTagName("ul");
                    if(childLists.length === 0) {
                        var xhttp = new XMLHttpRequest();
                        xhttp.onreadystatechange = function() {
                            if (xhttp.readyState === 4 && xhttp.status === 200) {
                                var newList = document.createElement("ul");
                                newList.innerHTML = xhttp.responseText;
                                linkParent.appendChild(newList);
                            }
                        };

                        var requestURL = "fileBrowser.php";
                        var params = {
                            target: event.detail.serverTarget,
                            directory: event.detail.path
                        };
                        xhttp.open("POST", requestURL, true);
                        xhttp.setRequestHeader("Content-type", "application/json");
                        xhttp.send(JSON.stringify(params));
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
            if(e.target.tagName !== "A") {
                return;
            }

            var event = clickEventToCustomEvent(e, "dblclicklink");
            var cancelled = !parentFileBrowser.dispatchEvent(event);

            if(!cancelled) {

                if(event.detail.isDir) {
                    //Directory
                }
                else {
                    window.open(event.detail.path,'_blank');
                }
            }

            e.preventDefault();
        });

        parentFileBrowser.addEventListener("contextmenu", function(e) {
            if(e.target.tagName !== "A") {
                return;
            }

            var event = clickEventToCustomEvent(e, "contextmenulink");
            var cancelled = !parentFileBrowser.dispatchEvent(event);

            if(!cancelled) {

                //TODO: add "open in new tab" option?
            }

           e.preventDefault();
        });
    </script>
    <?php

    require_once "config.php";

    if(isset($targetGroupName) && isset($targetGroups[$targetGroupName])) {
        foreach($targetGroups[$targetGroupName] as $target) {
            $targetInfo = $targets[$target];
            $browserRoot = $targetInfo["relativePath"];
            $rootName = ($targetInfo["name"] ?: $target) . " root";
            echo <<<HEREDOC
    <li>
        <a class="browserItem rootBrowserItem" href="$browserRoot" data-target="$target">$rootName/</a>
    </li>

HEREDOC;
        }
    }

    ?>
</ul>
