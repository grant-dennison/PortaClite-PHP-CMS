<?php
	require_once "helperFunctions.php";

	$searchDirectory = $_REQUEST["directory"];

	ensurePathWithinTarget($searchDirectory, $_REQUEST["target"]);

//	echo "$searchDirectory <br /><br />";

//	$entries = glob("$searchDirectory*");
	$entries = glob($searchDirectory . '{,.}*', GLOB_BRACE);

//	echo "<ul>";

	$count = count($entries);
	for($i = 0; $i < $count; $i++) {
		$entry = $entries[$i];
		$entryDisplay = basename($entry);
		if(is_dir($entry)) {
			$entry = $entry . "/";
			$entryDisplay = $entryDisplay . "/";
		}
		if($entryDisplay != "./" && $entryDisplay != "../") {
			echo "<li><a class=\"browserItem\" href=\"".$entry."\">".$entryDisplay."</a></li>";
		}
	}

	//2 counts ./ and ../
	if($count == 2) {
		echo "<li>(empty)</li>";
	}

//	echo "</ul>";
?>
