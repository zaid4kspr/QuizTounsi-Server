<?php include('library/crud.php');
	$db = new Database();
	$db->connect();
	$sql = "SELECT * FROM `settings` where `type` = 'privacy_policy'";
	$db->sql($sql);
	$res = $db->getResult();
	// print_r($res);
	/* Array ( [0] => Array ( [id] => 1 [type] => privacy_policy [message] => 'privacy_policy goes here') ) */
	
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width'>
	<title>Privacy Policy</title>
	<style> body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding:1em; } </style>
</head>
<body>
	<?php echo $res[0]['message'];?>
</body>
</html>