<HTML>
	<head>
		<title>JSONRPC2-php examples</title>
	</head>
	<body>
	
<?php
	require_once('spec_examples.php');
	
	$examples = get_examples();
	
	// process all of the examples
	foreach( $examples as $example){
		echo "<div>{$example['description']}</div>";
		echo '<form id="'.$example['description'].'" action="server.php" method="post">
			<textarea name="jsonrpc" cols="60" rows="3">'.htmlspecialchars($example['request'], ENT_QUOTES).'</textarea>
			<BR/>
			<input type="submit">
		</form>';
		echo "<HR/>";
	}

?>
		
	</body>
</HTML>