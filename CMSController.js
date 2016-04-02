(function() {
    //---------- INITIALIZATION ----------
    var currentFilePath;
    var interactFilePath;
    var interactFileIsDir;
    var interactElement; //HTML link interacted with
    var insertParentList;
    var serverFileContents;
    // var fileBrowsers in index.php
    // var mainFileBrowser ind index.php

    //File navigation context menu setup
    var fileLinkContextMenu = contextmenu([
        {
            label: "Open Link on Beta",
            onclick: function(e) {
                window.open(interactFilePath.replace(root_mirror, fullBetaPath_mirror), "_blank")
            }
        },
        {
            label: "Open Link on Live",
            onclick: function(e) {
                window.open(interactFilePath.replace(root_mirror, fullProductionPath_mirror), "_blank")
            }
        },
        {
            label: "Insert",
            children: [
                {
                    label: "New File",
                    onclick: function(e) {
                        var baseFileName = window.prompt("Filename:", "index.php");
                        if(baseFileName) {
                            insertFile(baseFileName, interactFilePath, insertParentList);
                        }
                    }
                },
                {
                    label: "New Directory",
                    onclick: function(e) {
                        var directoryName = window.prompt("Directory name:", "folder");
                        if(directoryName) {
                            mkDir(directoryName, interactFilePath, insertParentList);
                        }
                    }
                },
                {
                    label: "Upload",
                    onclick: function(e) {
                        document.getElementById("DropZoneBack").style.visibility = "visible";
                    }
                }
            ]
        },
        {
            label: "Rename",
            onclick: function(e) {
                if(interactFilePath == root_mirror) {
                    return;
                }
                var newFileName = window.prompt("New Filename:", "");
                if(newFileName) {
                    var newPath = newFileName.replace(/\/$/, "");
                    rename(interactFilePath, newPath, interactElement);
                }
            }
        },
        {
            label: "Delete",
            onclick: function(e) {
                if(interactFilePath == root_mirror) {
                    alert("You cannot delete the website root!");
                    return;
                }

                if(window.confirm(interactFilePath.replace(root_mirror, "root/") + "\n\nAre you sure you want to delete this file?")) {
                    // console.log("about to delete file");
                    deleteFile(interactFilePath, interactElement);
                }
                else {
                    // console.log("cancelled delete file");
                }
            }
        }
    ]);

    //CodeMirror setup
    //var scriptTag = document.scripts[document.scripts.length - 1];
    var codeMirrorContainer = document.getElementById("codeEditor");//scriptTag.parentElement;
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
            var spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
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
    if(publishRoot_mirror) {
        document.getElementById("publishButton").addEventListener("click", function(e) {
            publishFile();
            e.preventDefault();
        });
    }

    //mainFileBrowser declared/initialized in global scope of index.php
    mainFileBrowser.addEventListener("clicklink", function(e) {
        if(!e.detail.isDir && (isSaved() || window.confirm("There are unsaved changes in the open file.\nAre you sure you want to discard these changes?"))) {
            fetchFile(e.detail.path);
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
        contextmenu.show(fileLinkContextMenu, e.detail.clientX, e.detail.clientY);
    });

    Dropzone.autoDiscover = false;
    var dropZoneBack = document.getElementById("DropZoneBack");
    var dropZoneUploader = new Dropzone("#DropZoneContainer", { url: "uploadFile.php"});
    dropZoneUploader.on("sending", function(file, xhr, data) {
        data.append("location", interactFilePath);
        data.append("target", serveTarget);
    });

    dropZoneBack.addEventListener("click", function(e) {
        if(e.target == dropZoneBack) {
            dropZoneBack.style.visibility = "hidden";
            var zone = dropZoneUploader;
            for(var i = 0; i < zone.files.length; i++) {
                insertToFileBrowser(zone.files[i].name, interactFilePath, insertParentList);
            }
            zone.removeAllFiles();
        }
    });

    //Periodically check if work is unsaved
    setInterval(function() {
        if(!currentFilePath) {
            return;
        }
        var saveButton = document.getElementById("saveButton");
        if(codeEditor.getValue() === serverFileContents) {
            saveButton.className = "";
        }
        else {
            saveButton.className = "unsaved";
        }
    }, 1000);


    //---------- METHODS ----------

    function targetURL(relativePath) {
        return relativePath.replace(root_mirror, fullRoot_mirror);
    }
    function publishURL(relativePath) {
        return relativePath.replace(root_mirror, fullPublishRoot_mirror);
    }

    function saveFile() {
        if(!currentFilePath || codeEditor.getOption("readOnly")) {
            return;
        }

        if(!publishRoot_mirror) {
            if(!confirm("You are about to make a change directly to the live site.\nAre you sure you want to continue?")) {
                return;
            }
        }

        var submittingCode = codeEditor.getValue()

        var xhttp = new POSTRequest("fileMan.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "save");
        xhttp.addData("file", currentFilePath);
        xhttp.addData("content", submittingCode);
        xhttp.onresponse = function() {
            serverFileContents = submittingCode;
            checkIsFilePublished();
            //Run file after saving
            // var run = new POSTRequest(targetURL(currentFilePath));
            // run.send();
        }
        xhttp.send();
    }
    function isSaved() {
        if(!currentFilePath) {
            return true;
        }
        return codeEditor.getValue() === serverFileContents;
    }

    function publishFile() {
        if(!currentFilePath || !publishRoot_mirror) {
            return;
        }
        var xhttp = new POSTRequest("fileMan.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "publish");
        xhttp.addData("file", currentFilePath);
        xhttp.onresponse = function() {
            checkIsFilePublished();
            //Run file after publishing
            // var run = new POSTRequest(publishURL(currentFilePath));
            // run.send();
        }
        xhttp.send();
    }
    function checkIsFilePublished() {
        if(!currentFilePath || !publishRoot_mirror) {
            return;
        }
        var xhttp = new POSTRequest("fileMan.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "isPublished");
        xhttp.addData("file", currentFilePath);
        xhttp.onresponse = function() {
            if(this.responseText == "true") {
                document.getElementById("publishButton").className = "";
            }
            else {
                document.getElementById("publishButton").className = "unsaved";
            }
        }
        xhttp.send();
    }

    function deleteFile(filename, element) {
        var xhttp = new POSTRequest("fileMan.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "delete");
        xhttp.addData("file", filename);
        xhttp.onresponse = function() {
            if(filename == currentFilePath) {
                loadFileToEditor("", "");
            }
            if(element) {
                listItemElement = element.parentElement;
                parentList = listItemElement.parentElement;
                parentList.removeChild(listItemElement);
            }
        }
        xhttp.send();
    }

    function loadFileToEditor(filename, contents) {
        serverFileContents = contents;
        codeEditor.setValue(contents);
        codeEditor.setOption("readOnly", contents == "[BINARY FILE]");
        //Get extension
        var extResult = /[^\/]+\.([^\/\.]+)/.exec(filename);
        var ext = "default";
        if(extResult) {
            ext = extResult[1];
        }
        codeEditor.setOption("mode", extToCMMode(ext));
        currentFilePath = filename;
        document.getElementById("fileInfo").innerHTML = currentFilePath.replace(root_mirror, "root/");
        checkIsFilePublished();
    }

    function fetchFile(filename) {
        var xhttp = new POSTRequest("fileMan.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "fetch");
        xhttp.addData("file", filename);
        xhttp.onresponse = function() {
            loadFileToEditor(filename, this.responseText);
        }
        xhttp.send();
    }

    function insertFile(filename, location, containingListElement) {
        var xhttp = new POSTRequest("fileInserter.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "touch");
        xhttp.addData("name", filename);
        xhttp.addData("location", location);
        xhttp.onresponse = function() {
            //Update file browser tree
            insertToFileBrowser(filename, location, containingListElement);
            fetchFile(targetDirectory(location) + filename);
        }
        xhttp.send();
    }

    function insertToFileBrowser(filename, location, containingListElement) {
        if(containingListElement) {
            var newListItem = document.createElement("LI");
            var newLink = document.createElement("A");
            newLink.setAttribute("class", "browserItem");
            newLink.setAttribute("href", targetDirectory(location) + filename);
            newLink.textContent = filename;
            newListItem.appendChild(newLink);
            containingListElement.insertBefore(newListItem, containingListElement.firstChild);
        }
    }

    function mkDir(dirname, location, containingListElement) {
        var xhttp = new POSTRequest("fileInserter.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "mkdir");
        xhttp.addData("name", dirname);
        xhttp.addData("location", location);
        xhttp.onresponse = function() {
            //Update file browser tree
            insertToFileBrowser(dirname + "/", location, containingListElement);
        }
        xhttp.send();
    }

    function move(oldFilePath, newFilePath) {
        var xhttp = new POSTRequest("fileMan.php");
        xhttp.addData("target", serveTarget);
        xhttp.addData("action", "move");
        xhttp.addData("file", oldFilePath);
        xhttp.addData("content", newFilePath);
        xhttp.onresponse = function() {

        }
        xhttp.send();
    }
    function rename(interactFilePath, newName, linkElement) {
        var oldFilePath = interactFilePath.replace(/\/$/, "");
        var newFilePath = oldFilePath.replace(/\/[^\/]+\/?$/, "/" + newName);
        move(oldFilePath, newFilePath);
        var ending = (interactFileIsDir ? "/" : "");
        linkElement.innerHTML = newName + ending;
        linkElement.setAttribute("href", newFilePath + ending);
    }

    function targetDirectory(targetPath, isDir) {
        return /.+\//.exec(targetPath);
    }


    //---------- CLASSES ----------

    function POSTRequest(url) {
        this.xhttp = new XMLHttpRequest();
        this.xhttp.open("POST", url, true);
        this.xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        this.onresponse = function() { };
        this.data = {};
    }
    POSTRequest.prototype.addData = function(key, value) {
        this.data[encodeURIComponent(key)] = encodeURIComponent(value);
    };
    POSTRequest.prototype.send = function() {
        var pr = this;
        this.xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                pr.responseText = this.responseText;
                pr.responseXML = this.responseXML;
                pr.onresponse();
            }
        };

        var dataString = "";
        for(var key in this.data) {
            dataString += key;
            dataString += "=";
            dataString += this.data[key];
            dataString += "&";
        }
        dataString = dataString.substr(0, dataString.length - 1);
        this.xhttp.send(dataString);
        //this.send(dataString);
    };
} ());
