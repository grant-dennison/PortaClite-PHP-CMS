(function() {
    //---------- INITIALIZATION ----------
    var currentFileTarget;
    var currentFilePath;
    var interactFileTarget;
    var interactFilePath;
    var interactFileIsDir;
    var interactElement; //HTML link interacted with
    var insertParentList;
    var targetFileContents;
    var targetFileHash;
    var publishTargetFileHash;
    // var fileBrowsers in index.php
    // var mainFileBrowser ind index.php

    //File navigation context menu setup
    function makeContextMenuOptions(target, interactFilePath, interactElement) {
        var targetConfig = targetConfigurations[target];
        var targetName = targetConfig["name"] || target;
        var root = targetConfig["relativePath"];

        var publishTarget = targetConfig["publishTarget"];
        var publishTargetConfig = targetConfigurations[publishTarget] || {};
        var publishTargetName = publishTargetConfig["name"] || publishTarget;

        var options = [
            {
                label: "Open Link on current target (" + targetName + ")",
                onclick: function() {
                    window.open(interactFilePath.replace(root, targetConfig["absolutePath"]), "_blank")
                }
            },
            {
                label: "Open Link on publish target (" + publishTargetName + ")",
                onclick: function() {
                    window.open(interactFilePath.replace(root, publishTargetConfig["absolutePath"]), "_blank")
                }
            },
            {
                label: "Insert",
                children: [
                    {
                        label: "New File",
                        onclick: function() {
                            var baseFileName = window.prompt("Filename:", "index.php");
                            if(baseFileName) {
                                insertFile(target, baseFileName, interactFilePath, insertParentList);
                            }
                        }
                    },
                    {
                        label: "New Directory",
                        onclick: function() {
                            var directoryName = window.prompt("Directory name:", "folder");
                            if(directoryName) {
                                mkDir(target, directoryName, interactFilePath, insertParentList);
                            }
                        }
                    },
                    {
                        label: "Upload",
                        onclick: function() {
                            document.getElementById("DropZoneBack").style.visibility = "visible";
                        }
                    }
                ]
            },
            {
                label: "Rename",
                onclick: function() {
                    if(interactFilePath === root) {
                        return;
                    }
                    var newFileName = window.prompt("New Filename:", "");
                    if(newFileName) {
                        var newPath = newFileName.replace(/\/$/, "");
                        rename(target, interactFilePath, newPath, interactElement);
                    }
                }
            },
            {
                label: "Delete",
                onclick: function() {
                    if(interactFilePath === root) {
                        alert("You cannot delete the website root!");
                        return;
                    }

                    if(window.confirm(getRootRelativePath(target, interactFilePath) + "\n\nAre you sure you want to delete this file?")) {
                        deleteFile(target, interactFilePath, interactElement);
                    }
                }
            }
        ];

        //If no publish target exists, splice out context menu option to open on publish target
        if(!publishTargetName) {
            options.splice(1, 1);
        }

        return options;
    }

    //CodeMirror setup
    var codeMirrorContainer = document.getElementById("codeEditor");
    var codeEditor = CodeMirror(codeMirrorContainer, {
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: false,
        lineWrapping: true
    });
    codeEditor.setOption("extraKeys", {
        Tab: function(cm) {
            var spaces = new Array(cm.getOption("indentUnit") + 1).join(" ");
            cm.replaceSelection(spaces);
        },
        "Ctrl-S": saveFile
    });
    //Soft wrap
    var charWidth = codeEditor.defaultCharWidth(), basePadding = 4;
    codeEditor.on("renderLine", function(cm, line, elt) {
        var off = CodeMirror.countColumn(line.text, null, cm.getOption("tabSize")) * charWidth;
        elt.style.textIndent = "-" + off + "px";
        elt.style.paddingLeft = (basePadding + off) + "px";
    });
    codeEditor.refresh();


    //---------- EVENTS ----------

    document.getElementById("saveButton").addEventListener("click", function(e) {
        saveFile();
        e.preventDefault();
    });
    document.getElementById("diffLocalButton").addEventListener("click", function(e) {
        diffLocal();
        toggleHighlightButton("diffLocalButton", false);
        e.preventDefault();
    });
    document.getElementById("publishButton").addEventListener("click", function(e) {
        publishFile();
        e.preventDefault();
    });
    document.getElementById("diffPublishButton").addEventListener("click", function(e) {
        diffPublish();
        toggleHighlightButton("diffPublishButton", false);
        e.preventDefault();
    });

    window.onbeforeunload = function (e) {
        if(isSaved()) {
            return;
        }

        var message = "There are unsaved changes in the open file.\nAre you sure you want to discard these changes?";
        var e2 = e || window.event;
        // For IE and Firefox
        if (e2) {
            e2.returnValue = message;
        }

        // For Safari
        return message;
    };

    //mainFileBrowser declared/initialized in global scope of index.php
    mainFileBrowser.addEventListener("clicklink", function(e) {
        if(!e.detail.isDir && (isSaved() || window.confirm("There are unsaved changes in the open file.\nAre you sure you want to discard these changes?"))) {
            fetchFile(e.detail.serverTarget, e.detail.path);
            toggleHighlightButton("diffLocalButton", false);
            toggleHighlightButton("diffPublishButton", false);
            e.preventDefault();
        }
    });
    mainFileBrowser.addEventListener("dblclicklink", function(e) {
        // if(!e.detail.isDir) {
        //     fetchFile(e.detail.path);
        //     e.preventDefault();
        // }
        e.preventDefault();
    });
    mainFileBrowser.addEventListener("contextmenulink", function(e) {
        interactFileTarget = e.detail.serverTarget;
        interactFilePath = e.detail.path;
        interactFileIsDir = e.detail.isDir;
        interactElement = e.detail.target;
        if(interactFileIsDir) {
            var dirListItem = interactElement.parentElement;
            insertParentList = dirListItem.getElementsByTagName("UL")[0];
        }
        else {
            insertParentList = interactElement.parentElement.parentElement;
        }
        var menuOptions = makeContextMenuOptions(interactFileTarget, interactFilePath, interactElement);
        var fileLinkContextMenu = contextmenu(menuOptions);
        contextmenu.show(fileLinkContextMenu, e.detail.clientX, e.detail.clientY);
    });

    Dropzone.autoDiscover = false;
    var dropZoneBack = document.getElementById("DropZoneBack");
    var dropZoneUploader = new Dropzone("#DropZoneContainer", { url: "uploadFile.php"});
    dropZoneUploader.on("sending", function(file, xhr, data) {
        data.append("location", interactFilePath);
        data.append("target", interactFileTarget);
    });

    //Hide dropzone on click backdrop
    dropZoneBack.addEventListener("click", function(e) {
        if(e.target === dropZoneBack) {
            dropZoneBack.style.visibility = "hidden";
            var zone = dropZoneUploader;
            for(var i = 0; i < zone.files.length; i++) {
                insertToFileBrowser(interactFileTarget, zone.files[i].name, interactFilePath, insertParentList);
            }
            zone.removeAllFiles();
        }
    });

    //Hide diff on click backdrop
    var diffBack = document.getElementById("DiffBack");
    diffBack.addEventListener("click", function(e) {
        if(e.target === diffBack) {
            diffBack.style.visibility = "hidden";
        }
    });

    //Periodically check if work is unsaved
    setInterval(function() {
        var saveButton = document.getElementById("saveButton");
        if(isSaved()) {
            saveButton.className = "";
        }
        else {
            saveButton.className = "unsaved";
        }
    }, 1000);


    //---------- METHODS ----------
    function getPublishTarget(target) {
        return (targetConfigurations[target] || {})["publishTarget"];
    }
    function getRootRelativePath(target, relativePath) {
        var targetConfig = targetConfigurations[target];
        var relativeRoot = targetConfig["relativePath"];
        var targetName = targetConfig["name"] || target;
        return relativePath.replace(relativeRoot, targetName + " root/");
    }
    function saveFile() {
        if(!currentFilePath || codeEditor.getOption("readOnly")) {
            return;
        }

        if(!getPublishTarget(currentFileTarget)) {
            if(!confirm("You are about to make a change directly to an end deployment target.\nAre you sure you want to continue?")) {
                return;
            }
        }

        var submittingCode = codeEditor.getValue();

        var xhttp = new POSTRequest("fileMan.php", currentFileTarget, "save");
        xhttp.addData("file", currentFilePath);
        xhttp.addData("content", submittingCode);
        //Pass the hash of the unmodified file back to the server
        xhttp.addData("hash", targetFileHash);
        xhttp.onresponse = function() {
            if(!this.response.success) {
                alert("The file on the server has been modified by another user since you opened it. Please compare your working copy with the server copy before overwriting it.");
                toggleHighlightButton("diffLocalButton", true);
            }
            else { //on success
                targetFileHash = this.response.hash;
                targetFileContents = submittingCode;
            }

            applyPublished(this.response.hash === this.response.deployHash);
            publishTargetFileHash = this.response.deployHash;
        };
        xhttp.send();
    }
    function isSaved() {
        if(!currentFilePath) {
            return true;
        }
        return codeEditor.getValue() === targetFileContents;
    }

    function publishFile() {
        if(!currentFilePath || !getPublishTarget(currentFileTarget)) {
            return;
        }
        var xhttp = new POSTRequest("fileMan.php", currentFileTarget, "publish");
        xhttp.addData("file", currentFilePath);
        xhttp.addData("hash", targetFileHash);
        xhttp.addData("deployHash", publishTargetFileHash);
        xhttp.onresponse = function() {
            if(this.response.hash !== targetFileHash) {
                alert("The file you are trying to deploy has been modified since you last viewed it. \n\rFile not deployed.");
                toggleHighlightButton("diffLocalButton", true);
            }
            else if(this.response.deployHash !== targetFileHash) {
                if(this.response.deployHash !== publishTargetFileHash) {
                    alert("The deployed file has been modified. Please compare files before overwriting the deployed file.");
                    toggleHighlightButton("diffPublishButton", true);
                }
                else {
                    alert("File has not been deployed for unknown reason.");
                }
            }
            else {
                publishTargetFileHash = this.response.deployHash;
            }
            applyPublished(this.response.hash === this.response.deployHash);
        };
        xhttp.send();
    }

    function applyPublished(isPublished) {
        if(isPublished) {
            document.getElementById("publishButton").className = "";
        }
        else {
            document.getElementById("publishButton").className = "unsaved";
        }
    }

    function toggleHighlightButton(buttonId, isHighlighted) {
        var button = document.getElementById(buttonId);
        if(!button) {
            return;
        }
        if(isHighlighted) {
            button.className = "unsaved";
        }
        else {
            button.className = "";
        }
    }

    function togglePublishButtons(canPublish) {
        var display = canPublish ? "" : "none";
        document.getElementById("diffPublishButton").style.display = display;
        document.getElementById("publishButton").style.display = display;
    }

    function deleteFile(target, filename, element) {
        var xhttp = new POSTRequest("fileMan.php", target, "delete");
        xhttp.addData("file", filename);
        xhttp.onresponse = function() {
            if(filename === currentFilePath) {
                loadFileToEditor("", "", "");
            }
            if(element) {
                var listItemElement = element.parentElement;
                var parentList = listItemElement.parentElement;
                parentList.removeChild(listItemElement);
            }
        };
        xhttp.send();
    }

    function diffFiles(content1, content2) {
        var diff = JsDiff['diffChars'](content1, content2);
        var fragment = document.createDocumentFragment();
        for (var i=0; i < diff.length; i++) {

            if (diff[i].added && diff[i + 1] && diff[i + 1].removed) {
                var swap = diff[i];
                diff[i] = diff[i + 1];
                diff[i + 1] = swap;
            }

            //Add carriage return symbols
            diff[i].value = diff[i].value.replace(/\n/g, "\u21B5\n");

            var node;
            if (diff[i].removed) {
                node = document.createElement('del');
                node.appendChild(document.createTextNode(diff[i].value));
            } else if (diff[i].added) {
                node = document.createElement('ins');
                node.appendChild(document.createTextNode(diff[i].value));
            } else {
                node = document.createTextNode(diff[i].value);
            }
            fragment.appendChild(node);
        }
        var result = document.getElementById("DiffView");

        result.textContent = '';
        result.appendChild(fragment);

        document.getElementById("DiffBack").style.visibility = "visible";
    }

    function diffLocal() {
        if(!currentFilePath) {
            return;
        }

        var xhttp = new POSTRequest("fileMan.php", currentFileTarget, "fetch");
        xhttp.addData("file", currentFilePath);
        xhttp.onresponse = function() {
            if(!this.response.success) {
                alert("Something went wrong with preparing the diff for the requested file.");
                return;
            }

            var local = codeEditor.getValue();
            var remote = this.response.content;
            if(local === probablyBinaryDisplay && remote === probablyBinaryDisplay) {
                if(targetFileHash !== this.response.hash) {
                    remote += " (modified)";
                }
            }
            diffFiles(remote, local);

            targetFileHash = this.response.hash;
            publishTargetFileHash = this.response.deployHash;
            applyPublished(targetFileHash === publishTargetFileHash);
        };
        xhttp.send();
    }

    function diffPublish() {
        if(!currentFilePath) {
            return;
        }

        var xhttp = new POSTRequest("fileMan.php", currentFileTarget, "diff");
        xhttp.addData("file", currentFilePath);
        xhttp.onresponse = function() {
            if(!this.response.success) {
                alert("Something went wrong with preparing the diff for the requested file.");
                return;
            }

            var local = this.response.content;
            var remote = this.response.contentNextTarget;
            if(local === probablyBinaryDisplay && remote === probablyBinaryDisplay) {
                if(targetFileHash !== this.response.hash) {
                    remote += " (modified)";
                }
            }
            diffFiles(remote, local);

            targetFileHash = this.response.hash;
            publishTargetFileHash = this.response.deployHash;
            applyPublished(targetFileHash === publishTargetFileHash);
        };
        xhttp.send();
    }

    function loadFileToEditor(target, filename, contents) {
        targetFileContents = contents;
        codeEditor.setValue(contents || "");
        codeEditor.setOption("readOnly", contents === probablyBinaryDisplay);
        //Get extension
        var extResult = /[^\/]+\.([^\/\.]+)/.exec(filename);
        var ext = "default";
        if(extResult) {
            ext = extResult[1];
        }
        codeEditor.setOption("mode", extToCMMode(ext));
        currentFileTarget = target;
        currentFilePath = filename;
        togglePublishButtons(!!getPublishTarget(target));
        document.getElementById("fileInfo").innerHTML = getRootRelativePath(target, currentFilePath);
    }

    function fetchFile(target, filename) {
        var xhttp = new POSTRequest("fileMan.php", target, "fetch");
        xhttp.addData("file", filename);
        xhttp.onresponse = function() {
            if(!this.response.success) {
                alert("Something went wrong with fetching the requested file.");
                return;
            }
            targetFileHash = this.response.hash;
            publishTargetFileHash = this.response.deployHash;
            loadFileToEditor(target, filename, this.response.content);
            applyPublished(targetFileHash === publishTargetFileHash);
        };
        xhttp.send();
    }

    function insertFile(target, filename, location, containingListElement) {
        var xhttp = new POSTRequest("fileInserter.php", target, "touch");
        xhttp.addData("name", filename);
        xhttp.addData("location", location);
        xhttp.onresponse = function() {
            //Update file browser tree
            insertToFileBrowser(target, filename, location, containingListElement);
            fetchFile(target, targetDirectory(location) + filename);
        };
        xhttp.send();
    }

    function insertToFileBrowser(target, filename, location, containingListElement) {
        if(containingListElement) {
            var newListItem = document.createElement("LI");
            var newLink = document.createElement("A");
            newLink.setAttribute("class", "browserItem");
            newLink.setAttribute("href", targetDirectory(location) + filename);
            newLink.setAttribute("data-target", target);
            newLink.textContent = filename;
            newListItem.appendChild(newLink);
            containingListElement.insertBefore(newListItem, containingListElement.firstChild);
        }
    }

    function mkDir(target, dirname, location, containingListElement) {
        var xhttp = new POSTRequest("fileInserter.php", target, "mkdir");
        xhttp.addData("name", dirname);
        xhttp.addData("location", location);
        xhttp.onresponse = function() {
            //Update file browser tree
            insertToFileBrowser(target, dirname + "/", location, containingListElement);
        };
        xhttp.send();
    }

    function move(target, oldFilePath, newFilePath) {
        var xhttp = new POSTRequest("fileMan.php", target, "move");
        xhttp.addData("file", oldFilePath);
        xhttp.addData("content", newFilePath);
        xhttp.send();
    }
    function rename(target, interactFilePath, newName, linkElement) {
        var oldFilePath = interactFilePath.replace(/\/$/, "");
        var newFilePath = oldFilePath.replace(/\/[^\/]+\/?$/, "/" + newName);
        move(target, oldFilePath, newFilePath);
        var ending = (interactFileIsDir ? "/" : "");
        linkElement.innerHTML = newName + ending;
        linkElement.setAttribute("href", newFilePath + ending);
    }

    function targetDirectory(targetPath) {
        return /.+\//.exec(targetPath);
    }


    //---------- CLASSES ----------

    function POSTRequest(url, serverTarget, action) {
        this.xhttp = new XMLHttpRequest();
        this.xhttp.open("POST", url, true);
        this.xhttp.setRequestHeader("Content-type", "application/json");
        this.onresponse = function() { };
        this.data = {};
        this.addData("target", serverTarget);
        this.addData("action", action);
    }
    POSTRequest.prototype.addData = function(key, value) {
        this.data[key] = value;
    };
    POSTRequest.prototype.send = function() {
        var pr = this;
        this.xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                pr.responseText = this.responseText;
                pr.responseXML = this.responseXML;
                pr.response = this.responseText ? JSON.parse(this.responseText) : {};
                pr.onresponse();
            }
        };

        var dataString = JSON.stringify(this.data);
        this.xhttp.send(dataString);
    };
} ());
