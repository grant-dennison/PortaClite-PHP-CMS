<!DOCTYPE HTML>
<html>
<body>

<!-- <title>CodeMirror: PHP mode</title> -->
<!-- <meta charset="utf-8"/> -->
<!-- <link rel=stylesheet href="codemirror-5.8/doc/docs.css"> -->

<link rel="stylesheet" href="codemirror-5.8/lib/codemirror.css">
<script src="codemirror-5.8/lib/codemirror.js"></script>
<script src="codemirror-5.8/addon/edit/matchbrackets.js"></script>
<script src="codemirror-5.8/mode/htmlmixed/htmlmixed.js"></script>
<script src="codemirror-5.8/mode/xml/xml.js"></script>
<script src="codemirror-5.8/mode/javascript/javascript.js"></script>
<script src="codemirror-5.8/mode/css/css.js"></script>
<script src="codemirror-5.8/mode/clike/clike.js"></script>
<script src="codemirror-5.8/mode/php/php.js"></script>


<!-- <script>
	var scriptTag = document.scripts[document.scripts.length - 1];
	var parentElement = scriptTag.parentElement;

	var editor = CodeMirror(parentElement);
	var myCodeMirror = CodeMirror(document.body);

	//parentElement.appendChild(editor);
</script> -->

<script>
  var editor = CodeMirror(document.body, {
	lineNumbers: true,
	matchBrackets: true,
	mode: "application/x-httpd-php",
	indentUnit: 4,
	indentWithTabs: false
  });

  editor.setOption("extraKeys", {
  Tab: function(cm) {
    var spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
    cm.replaceSelection(spaces);
  }
});
</script>

</body>
</html>
