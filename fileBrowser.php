<?php
	require_once "helperFunctions.php";

	$request = getJSONParams();

	$searchDirectory = $request["directory"];
	$target = $request["target"];

	ensurePathWithinTarget($searchDirectory, $target);

	$entries = glob($searchDirectory . '{,.}*', GLOB_BRACE);

	$count = count($entries);
	for($i = 0; $i < $count; $i++) {
		$entry = $entries[$i];
		$entryDisplay = basename($entry);
		if(is_dir($entry)) {
			$entry = $entry . "/";
			$entryDisplay = $entryDisplay . "/";
		}

		//Don't display ./ and ../
		if($entryDisplay != "./" && $entryDisplay != "../") {
			echo "<li><a class=\"browserItem\" href=\"".$entry."\" data-target=\"$target\">$entryDisplay</a></li>";
		}
	}

	//2 counts ./ and ../
	if($count == 2) {
		echo "<li>(empty)</li>";
	}
