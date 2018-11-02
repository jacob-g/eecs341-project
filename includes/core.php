<?php
function display_error($error_msg) {
	?>
<!DOCTYPE html>
<html>
	<head>
		<title>&Eacute;amonBB Error</title>
	</head>
	<body>
		<h1>Error</h1>
		<p>An error was encountered. The message given was: <b><?php echo $error_msg; ?></b>.</p>
	</body>
</html>
	<?php
	die;
}

//a wrapper for the hashing function used here
function ebb_hash($password) {
	return sha1($password);
}

function redirect($url) {
	header('HTTP/1.1 301 Moved Temporarily');
	header('Location: ' . $url);
}