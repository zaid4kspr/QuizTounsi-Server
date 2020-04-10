<?php
session_start();
if(isset($_POST['username']) &&  isset($_POST['password']) )
{
    include 'library/crud.php';
	$db = new Database();
	$db->connect();
	
	$username = stripslashes($_POST['username']);
	$username = $db->escapeString($username);;
	// $username=mysqli_real_escape_string($conn, $username);
	
	$password = stripslashes($_POST['password']);
    $password=$db->escapeString($password);
    // $password=mysqli_real_escape_string($conn, $password);
    $pwordhash=md5($password);
            
    if (!empty($username) && !empty($password) ){
		$sql = "SELECT * FROM authenticate WHERE auth_username='$username' AND auth_pass='$pwordhash' ";
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			foreach($result as $row){
				$_SESSION['username'] = $row["auth_username"];
				$_SESSION['company_name'] = 'Quiz';
			}
			echo "1";
			// header("location:welcome.php");
		}else{
			echo "<p class='alert alert-danger'>Id or password does not match</p>" . '<br>';   
		}
	}else{
		echo " <p class='alert alert-danger'>!!every field is mandetary</p></p>";
	}
}?>