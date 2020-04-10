<?php
/*
	API v5.4
	Quiz Online - WRTeam.in 
	WRTeam Developers
*/
session_start();
if(!isset($_SESSION['id']) && !isset($_SESSION['username'])){
	header("location:index.php");
	return false;
	exit();
}
include('library/crud.php');
include('library/functions.php');

$db = new Database();
$db->connect();

$fn = new Functions();
$config = $fn->get_configurations();
// print_r($config);
if(isset($config['system_timezone']) && !empty($config['system_timezone'])){
	date_default_timezone_set($config['system_timezone']);
}else{
	date_default_timezone_set('Asia/Kolkata');
}
if(isset($config['system_timezone_gmt']) && !empty($config['system_timezone_gmt'])){
	$db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
}else{
	$db->sql("SET `time_zone` = '+05:30'");
}

$db->sql("SET NAMES 'utf8'");
$auth_username = $db->escapeString($_SESSION["username"]);

/*
	1. add_category()
	2. update_category()
	3. delete_category()
	4. add_subcategory()
	5. update_subcategory()
	6. delete_subcategory()
	7. get_subcategories_of_category()
	8. add_question()
	9. update_question()
	10. delete_question()
	11. send_notifications()
	12. delete_notification()
	13. update_fcm_server_key()
	14. delete_question_report()
	15. import_questions()
	16. update_category_order()
	17. update_subcategory_order()
	18. update_policy()
	19. update_terms()
	20. update_user()
	21. add_admin_form()
	22. update_admin()
	23. delete_admin()
	24. system_configurations()
	25. delete_multiple()
	26. add_language()
	27. update_language()
	28. delete_language()
	29. get_categories_of_language()
	30. update_about_us()
	
	functions 
	----------------
	1. checkadmin($auth_username)
*/

function checkadmin($auth_username){
	$db = new Database();
	$db->connect();
	$db->sql("SELECT `auth_username`,`role` FROM `authenticate` WHERE `auth_username`='$auth_username' LIMIT 1");
	$res = $db->getResult();
	if(!empty($res)){
		if($res[0]["role"]=="admin"){
			return true;
		}else{
			return false;
		}
	}
}

//1. add_category 
if(isset($_POST['name']) and isset($_POST['add_category'])){
	$name = $db->escapeString($_POST['name']);
	$language_id = ($fn->is_language_mode_enabled())?$db->escapeString($_POST['language_id']):0;
	$filename = '';
	// common image file extensions
	if($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0){
		if (!is_dir('images/category')) {
			mkdir('images/category', 0777, true);
		}
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		// $extension = explode(".", $_FILES["image"]["name"]);
		// $extension = end($extension);
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
		$target_path = 'images/category/';
		$filename = microtime(true).'.'. strtolower($extension);
		$full_path = $target_path."".$filename;
		if(!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
	}
	
	$sql = "INSERT INTO `category` (`language_id`,`category_name`, `image`,`row_order`) VALUES ('".$language_id."','".$name."','".$filename."','0')";
	// echo $sql;
	// return false;
	$db->sql($sql);
	echo '<label class="alert alert-success">Category created successfully!</label>';
}

//2. update_category
if(isset($_POST['category_id']) && isset($_POST['update_category'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$id = $_POST['category_id'];
	$name = $db->escapeString($_POST['name']);
	$language_id = ($fn->is_language_mode_enabled())?$db->escapeString($_POST['language_id']):0;
	if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0)
	{
		if (!is_dir('images/category')) {
			mkdir('images/category', 0777, true);
		}
		//image isn't empty and update the image
		$image_url = $db->escapeString($_POST['image_url']);
		// common image file extensions
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		// $extension = end(explode(".", $_FILES["image"]["name"]));
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			echo '<p class="alert alert-danger">Image type is invalid</p>';
			return false;
		}
		$target_path = 'images/category/';
		$filename = microtime(true).'.'. strtolower($extension);
		$full_path = $target_path."".$filename;
		if(!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)){
			echo '<p class="alert alert-danger">Image type is invalid</p>';
			return false;
		}
		if($image_url != "images/logo-half.png"){
			// if its not half logo image
			unlink($image_url);
		}
		
		$sql = "Update category set `image`='".$filename."' where `id`=".$id;
		$db->sql($sql);
    } 
	
	$sql = "Update `category` set `category_name`='".$name."'"; 
	$sql .= ($fn->is_language_mode_enabled())?", `language_id` = ".$language_id." ":"";
	$sql .= " where `id`=".$id;
	
	// echo $sql;
	// return false;
	$db->sql($sql);
	echo "<p class='alert alert-success'>Category updated successfully!</p>";
}

//3. delete_category
if(isset($_GET['delete_category']) && $_GET['delete_category'] != '' ) {
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
    $id = $_GET['id'];
    $image = $_GET['image'];
	$sql = 'DELETE FROM `category` WHERE `id`='.$id;
	if($db->sql($sql)){
		if(!empty($image)){
			unlink($image);
		}
		
		// select sub category images & delete it
		$sql = 'select `image` FROM `subcategory` WHERE `maincat_id`='.$id;
		$db->sql($sql);
		$sub_category_images = $db->getResult();
		// print_r($sub_category_images);
		if(!empty($sub_category_images)){
			foreach($sub_category_images as $image)
			{
				if(!empty($image['image']))
					unlink('images/subcategory/'.$image['image']);
			}
		}
		
		$sql = 'DELETE FROM `subcategory` WHERE `maincat_id`='.$id;
		$db->sql($sql);
		
		$sql = 'select `image` FROM `question` WHERE `category`='.$id;
		$db->sql($sql);
		$question_images = $db->getResult();
		
		if(!empty($question_images)){
			foreach($question_images as $image)
			{
				if(!empty($image['image']))
					unlink('images/questions/'.$image['image']);
			}
		}
		
		$sql = 'DELETE FROM `question` WHERE `category`='.$id;
		$db->sql($sql);
		echo 1;
	}else{
		echo 0;
	}
}

//4. add_subcategory
if(isset($_POST['name']) and isset($_POST['add_subcategory'])){
	$name = $db->escapeString($_POST['name']);
	$maincat_id = $db->escapeString($_POST['maincat_id']);
	$language_id = ($fn->is_language_mode_enabled())?$db->escapeString($_POST['language_id']):0;
	
	$filename = '';
	// common image file extensions
	if($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0){
		if (!is_dir('images/subcategory')) {
			mkdir('images/subcategory', 0777, true);
		}
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		// $extension = explode(".", $_FILES["image"]["name"]);
		// $extension = end($extension);
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
		$target_path = 'images/subcategory/';
		$filename = microtime(true).'.'. strtolower($extension);
		$full_path = $target_path."".$filename;
		if(!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
	}
	
	$sql = "INSERT INTO `subcategory` (`language_id`,`maincat_id`,`subcategory_name`, `image`,`row_order`) VALUES ('".$language_id."','".$maincat_id."','".$name."','".$filename."','0')";
	//echo $sql;
	//return false;
	$db->sql($sql);
	echo '<label class="alert alert-success">Sub Category created successfully!</label>';
}

//5. update_subcategory
if(isset($_POST['subcategory_id']) && isset($_POST['update_subcategory'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$id = $_POST['subcategory_id'];
	$name = $db->escapeString($_POST['name']);
	$maincat_id = $db->escapeString($_POST['maincat_id']);
	$language_id = ($fn->is_language_mode_enabled())?$db->escapeString($_POST['language_id']):0;
	
	$status = $db->escapeString($_POST['status']);
	if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0)
	{
		if (!is_dir('images/subcategory')) {
			mkdir('images/subcategory', 0777, true);
		}
		//image isn't empty and update the image
		$image_url = $db->escapeString($_POST['image_url']);
		
		// common image file extensions
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		// $extension = end(explode(".", $_FILES["image"]["name"]));
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			echo '<p class="alert alert-danger">Image type is invalid</p>';
			return false;
		}
		$target_path = 'images/subcategory/';
		$filename = microtime(true).'.'. strtolower($extension);
		$full_path = $target_path."".$filename;
		if(!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)){
			echo '<p class="alert alert-danger">Image type is invalid</p>';
			return false;
		}
		if($image_url != "images/logo-half.png"){
			// if its not half logo image
			unlink($image_url);
		}
		$sql = "Update subcategory set `image`='".$filename."' where `id`=".$id;
		$db->sql($sql);
    } 
	
	$sql = "Update subcategory set `maincat_id`='".$maincat_id."', `subcategory_name`='".$name."', `status`='".$status."' ";
	$sql .= ($fn->is_language_mode_enabled())?", `language_id` = ".$language_id." ":"";
	$sql .= " where `id`=".$id;
	
	// echo $sql;
	// return false;
	$db->sql($sql);
	echo "<p class='alert alert-success'>Sub category updated successfully!</p>";
}

//6. delete_subcategory
if(isset($_GET['delete_subcategory']) && $_GET['delete_subcategory'] != '' ) {
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
    $id = $_GET['id'];
    $image = $_GET['image'];
    
	$sql = 'DELETE FROM `subcategory` WHERE `id`='.$id;
	if($db->sql($sql)){
		if(!empty($image))
			unlink($image);
		
		$sql = 'select `image` FROM `question` WHERE `subcategory`='.$id;
		$db->sql($sql);
		$question_images = $db->getResult();
		
		if(!empty($question_images)){
			foreach($question_images as $image)
			{
				if(!empty($image['image']))
					unlink('images/questions/'.$image['image']);
			}
		}
		
		$sql = 'DELETE FROM `question` WHERE `subcategory`='.$id;
		$db->sql($sql);
		echo 1;
	}else{
		echo 0;
	}
}

//7. get_subcategories_of_category - ajax dropdown menu options 
if(isset($_POST['get_subcategories_of_category']) && $_POST['get_subcategories_of_category'] != '' ) {
    $id = $_POST['category_id'];
    if(empty($id)){
		echo '<option value="">Select Sub Category</option>';
		return false;
	}
	$sql = 'select * FROM `subcategory` WHERE `maincat_id`='.$id.' ORDER BY row_order + 0 ASC';
	// echo $sql;
	$db->sql($sql);
	$res = $db->getResult();
	
	if(isset($_POST['sortable']) && $_POST['sortable'] == 'sortable'){
	    foreach($res as $category){
	      
        	if(!empty($category["image"])){
                $options .="<li id='".$category["id"]."'><big>".$category["row_order"].".</big> &nbsp;<img src='images/subcategory/$category[image]' height=30 > ".$category["subcategory_name"]."</li>";
            }else{
                $options .="<li id='".$category["id"]."'><big>".$category["row_order"].".</big> &nbsp;<img src='images/logo-half.png' height=30 > ".$category["subcategory_name"]."</li>";
            }
    	}
	}else{
	    $options = '<option value="">Select Sub Category</option>';
	    foreach($res as $option){
    		$options .="<option value='".$option['id']."'>".$option['subcategory_name']."</option>";
    	}  
	}
	echo $options;
}

//8. add_question
if(isset($_POST['question']) and isset($_POST['add_question'])){
	$question = $db->escapeString($_POST['question']);
	$category = $db->escapeString($_POST['category']);
	$subcategory = $db->escapeString($_POST['subcategory']);
	
	$language_id = ($fn->is_language_mode_enabled())?$db->escapeString($_POST['language_id']):0;
	
	$a = $db->escapeString($_POST['a']);
	$b = $db->escapeString($_POST['b']);
	$c = $db->escapeString($_POST['c']);
	$d = $db->escapeString($_POST['d']);
	$e = ($fn->is_option_e_mode_enabled())?$db->escapeString($_POST['e']):"";
	$level = $db->escapeString($_POST['level']);
	$answer = $db->escapeString($_POST['answer']);
	$note = $db->escapeString($_POST['note']);
	
	$filename = $full_path = '';
	// common image file extensions
	if($_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0){
		if (!is_dir('images/questions')) {
			mkdir('images/questions', 0777, true);
		}
		
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		// $extension = explode(".", $_FILES["image"]["name"]);
		// $extension = end($extension);
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
		$target_path = 'images/questions/';
		$filename = microtime(true).'.'. strtolower($extension);
		$full_path = $target_path."".$filename;
		if(!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
	}
	
	$sql = "INSERT INTO `question`(`category`, `subcategory`, `language_id`, `image`, `question`, `optiona`, `optionb`, `optionc`, `optiond`, `optione`, `level`, `answer`, `note`) VALUES 
	('".$category."','".$subcategory."','".$language_id."','".$filename."','".$question."','".$a."','".$b."','".$c."','".$d."','".$e."','".$level."','".$answer."','".$note."')";
	
	$db->sql($sql);
	$res = $db->getResult();
	echo '<label class="alert alert-success">Question created successfully!</label>';
}

//9. update_question
if(isset($_POST['question_id']) && isset($_POST['update_question'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$id = $_POST['question_id'];
	
	if ($_FILES['image']['size'] != 0 && $_FILES['image']['error'] == 0)
	{
		//image isn't empty and update the image
		$image_url = $db->escapeString($_POST['image_url']);
		
		// common image file extensions
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		// $extension = end(explode(".", $_FILES["image"]["name"]));
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			echo '<p class="alert alert-danger">Image type is invalid</p>';
			return false;
		}
		if (!is_dir('images/questions')) {
			mkdir('images/questions', 0777, true);
		}
		$target_path = 'images/questions/';
		$filename = microtime(true).'.'. strtolower($extension);
		$full_path = $target_path."".$filename;
		if(!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)){
			echo '<p class="alert alert-danger">Image type is invalid</p>';
			return false;
		}
		if(!empty($image_url))
			unlink($image_url);
		$sql = "Update `question` set `image`='".$filename."' where `id`=".$id;
		$db->sql($sql);
    } 
	
	$question = $db->escapeString($_POST['question']);
	$category = $db->escapeString($_POST['category']);
	$subcategory = $db->escapeString($_POST['subcategory']);
	
	$language_id = ($fn->is_language_mode_enabled())?$db->escapeString($_POST['language_id']):0;
	
	$a = $db->escapeString($_POST['a']);
	$b = $db->escapeString($_POST['b']);
	$c = $db->escapeString($_POST['c']);
	$d = $db->escapeString($_POST['d']);
if ($fn->is_option_e_mode_enabled()) {
    $e = $db->escapeString($_POST['e']);
   }
	$level = $db->escapeString($_POST['level']);
	$answer = $db->escapeString($_POST['answer']);
	$note = $db->escapeString($_POST['note']);
	
	$sql = "Update `question` set `question`='".$question."', `category`='".$category."', `subcategory`='".$subcategory."',`optiona`='".$a."',`optionb`='".$b."' ,`optionc`='".$c."' ,`optiond`='".$d."', `answer`='".$answer."' ,`level`='".$level."',`note`='".$note."'";
	$sql .= ($fn->is_option_e_mode_enabled())?",`optione`='".$e."'":"";
	$sql .= ($fn->is_language_mode_enabled())?", `language_id`=".$language_id:"";
	$sql .= " where `id`=".$id;
	
	// echo $sql;
	// return false;
	$db->sql($sql);
	echo "<p class='alert alert-success'>Question updated successfully!</p>";
}

//10. delete_question
if(isset($_GET['delete_question']) && $_GET['delete_question'] != '' ) {
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
    $id		= $_GET['id'];
    $image 	= $_GET['image'];
	
    $sql = 'DELETE FROM `question` WHERE `id`='.$id;
	if($db->sql($sql)){
		if(!empty($image))
			unlink($image);
		echo 1;
	}else{
		echo 0;
	}
}

//11. send_notifications - send notifications to users
if(isset($_POST['title']) and isset($_POST['send_notifications'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$sql = 'select `fcm_key` from `tbl_fcm_key` where id=1';
	$db->sql($sql);
	$res = $db->getResult();
	
	define('API_ACCESS_KEY', $res[0]['fcm_key']);
	
	//creating a new push
	$title = $db->escapeString($_POST['title']);
	$message = $db->escapeString($_POST['message']);
	$users = $db->escapeString($_POST['users']);
	$type = $db->escapeString($_POST['type']);
	$maxlevel = $no_of = "0";
	if($type=='category'){
		$maincat_id = $db->escapeString($_POST['maincat_id']);
		$sql = "select max(`level`)as `maxlevel` FROM question WHERE category = ".$maincat_id;
		$db->sql($sql);
		$res1 = $db->getResult();
		$maxlevel = $res1[0]['maxlevel'];
		
		$sql = "SELECT count(`id`) as no_of from subcategory s WHERE s.maincat_id = ".$maincat_id." and s.status = 1 ";
		$db->sql($sql);
		$res1 = $db->getResult();
		$no_of = $res1[0]['no_of'];
	}else{
		$maincat_id = 0;
	}
	
	if($users == 'all'){
		$sql = "select `fcm_id` from `users` ";
		$db->sql($sql);
		$res = $db->getResult();
		$fcm_ids = array();
		foreach($res as $fcm_id){
			$fcm_ids[] = $fcm_id['fcm_id'];
		}
	}elseif($users == 'selected'){
		$selected_list = $_POST['selected_list'];
		if(empty($selected_list)){
			$response['error']=true;
			$response['message']='Please Select the users from the table';
			echo json_encode($response);
			return false;
		}
		$fcm_ids = array();
		$fcm_ids = explode(",",$selected_list);
	}
	
	$registrationIDs = $fcm_ids;
	// print_r($fcm_ids);
	// return false;
	
	/*dynamically getting the domain of the app*/
	$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$url .= $_SERVER['SERVER_NAME'];
	$url .= $_SERVER['REQUEST_URI'];
	$server_url = dirname($url).'/';
		
	$push = null;
	$include_image = (isset($_POST['include_image']) && $_POST['include_image'] == 'on') ? TRUE : FALSE;
	if($include_image){
		if (!is_dir('images/notifications')) {
			mkdir('images/notifications', 0777, true);
		}
		// common image file extensions
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		// $extension = end(explode(".", $_FILES["image"]["name"]));
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
		$target_path = 'images/notifications/';
		$filename = microtime(true).'.'. strtolower($extension);
		$full_path = $target_path."".$filename;
		if(!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)){
			$response['error']=true;
			$response['message']='Image type is invalid';
			echo json_encode($response);
			return false;
		}
		 $sql = "INSERT INTO `notifications`(`title`,`message`,`users`,`type`,`type_id`,`image`) VALUES 
			('".$title."','".$message."','".$users."','".$type."',".$maincat_id.",'".$full_path."')"; 
	}else{
		 $sql = "INSERT INTO `notifications`(`title`,`message`,`users`,`type`,`type_id`) VALUES 
			('".$title."','".$message."','".$users."','".$type."',".$maincat_id.")"; 
	}

	$db->sql($sql);
	$newMsg = array();
	
	//first check if the push has an image with it
	if($include_image){
		$fcmMsg = array(
			'title' => $title,
			'message' => $message,
			'image' => DOMAIN_URL.'/'.$full_path,
			'type' => $type,
			'type_id' => $maincat_id,
			'maxlevel' => $maxlevel,
			'no_of' => $no_of
			//'image' => $server_url.''.$full_path
			//'sound' => "default",
			// 'color' => "#203E78" 
		);
		// print_r($fcmMsg);
		$newMsg['data'] = $fcmMsg;
	}else{
		//if the push don't have an image give null in place of image
		$fcmMsg = array(
			'title' => $title,
			'message' => $message,
			'image' => null,
			'type' => $type,
			'type_id' => $maincat_id,
			'maxlevel' => $maxlevel,
			'no_of' => $no_of
			//'sound' => "default",
			// 'color' => "#203E78" 
		);
		$newMsg['data'] = $fcmMsg;
	}
	$registrationIDs_chunks = array_chunk($registrationIDs,1000);
	// print_r($registrationIDs_chunks);
	$success = $failure = 0;
	
	foreach($registrationIDs_chunks as $registrationIDs){
    	$fcmFields = array(
    		// 'to' => $singleID,
    		'registration_ids' => $registrationIDs,  // expects an array of ids
    		'priority' => 'high',
    		'data' => $newMsg
    	);
    	//print_r(json_encode($fcmFields));
    	$headers = array(
    		'Authorization: key=' . API_ACCESS_KEY,
    		'Content-Type: application/json'
    	);
    	 
    	$ch = curl_init();
    	curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    	curl_setopt( $ch,CURLOPT_POST, true );
    	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
    	$result = curl_exec($ch );
    	curl_close( $ch );
    	//print_r($result);
    	$result = json_decode($result,1);
    	
    	$success += $result['success'];
		$failure += $result['failure'];
	}
	// echo '<label class="label label-success">'.$success.' user(s) Sent Successfully</label><label class="label label-danger">'.$failure.' user(s) Couldn\'t Send</label><br></br>';
	echo '<label class="label label-success">Notification Sent Successfully</label><br></br>';
}

// 12. delete_notification
if(isset($_POST['id']) && isset($_POST['delete_notification'])){
	$id = $_POST['id'];
	$sql = "DELETE FROM `notifications` WHERE `id`=".$id;
	if($db->sql($sql)){
		if(isset($_POST['image']) && $_POST['image'] != ''){
			$image = 'images/notifications/'.$_POST['image'];
			unlink($image);
		}
		echo 1;
	}
	else
		echo 0;	
}

// 13. update_fcm_server_key()
if(isset($_POST['update_fcm_server_key'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$fcm_key = $db->escapeString($_POST['fcm_key']);
	$update_fcm_server_key_id = $db->escapeString($_POST['update_fcm_server_key_id']);
	if(empty($_POST['update_fcm_server_key_id']))
	{
		$sql = "INSERT INTO tbl_fcm_key (fcm_key) VALUES ('".$fcm_key."')";
		$db->sql($sql);
		$res = $db->getResult(); 
		echo "<p class='alert alert-success'>FCM Key Inserted Successfully!</p><br>";
	}else{
		$sql = "Update `tbl_fcm_key` set `fcm_key`='".$fcm_key."' where `id`=".$update_fcm_server_key_id;
		$db->sql($sql);
		$res = $db->getResult();
		echo "<p class='alert alert-success'>FCM Key Updated Successfully!</p><br>";
	}
}

//14. delete_question_report
if(isset($_GET['delete_question_report']) && $_GET['delete_question_report'] != '' ) {
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
    $id		= $_GET['id'];
    
    $sql = 'DELETE FROM `question_reports` WHERE `id`='.$id;
	if($db->sql($sql)){
		echo 1;
	}else{
		echo 0;
	}
}

//15. import_questions - import questions to database from a CSV file
if(isset($_POST['import_questions']) && $_POST['import_questions']==1){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$count = 0;
	$filename=$_FILES["questions_file"]["tmp_name"];
	if($_FILES["questions_file"]["size"] > 0)
    {
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
        {
            $emapData[0] = $db->escapeString($emapData[0]);
            $emapData[1] = $db->escapeString($emapData[1]);
            $emapData[2] = ($fn->is_language_mode_enabled())?$db->escapeString($emapData[2]):0;
            $emapData[3] = $db->escapeString($emapData[3]);
            $emapData[4] = $db->escapeString($emapData[4]);
            $emapData[5] = $db->escapeString($emapData[5]);
            $emapData[6] = $db->escapeString($emapData[6]);
            $emapData[7] = $db->escapeString($emapData[7]);
            $emapData[8] = ($fn->is_option_e_mode_enabled())?$db->escapeString($emapData[8]):"";
            $emapData[9] = $db->escapeString($emapData[9]);
            $emapData[10] = $db->escapeString($emapData[10]);
            $emapData[11] = $db->escapeString($emapData[11]);
			$count++;
			if($count>1){
				if(!empty($emapData[0]) && !empty($emapData[1])){
					$sql = "INSERT INTO `question`(`category`, `subcategory`, `language_id`, `question`, `optiona`, `optionb`, `optionc`, `optiond`,  `optione`, `answer`, `level`, `note`) VALUES 
						('$emapData[0]','$emapData[1]','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[5]','$emapData[6]','$emapData[7]','$emapData[8]','$emapData[9]','$emapData[10]','$emapData[11]')";
					$db->sql($sql);
				}
			}
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    }
    else{
		echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}

// 16. update_category_order
if(isset($_POST['update_category_order']) && isset($_POST['update_category_order']) && $_POST['update_category_order']==1){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$id_ary = explode(",",$_POST["row_order"]);
	for($i=0;$i<count($id_ary);$i++){
		$sql = "UPDATE category SET row_order='" . $i . "' WHERE id=". $id_ary[$i];
		$db->sql($sql);
		$res = $db->getResult();
	}
	echo "<p class='alert alert-success'>Category order updated!</p>";
}

// 17. update_subcategory_order
if(isset($_POST['update_subcategory_order']) && isset($_POST['update_subcategory_order']) && $_POST['update_subcategory_order']==1){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$id_ary = explode(",",$_POST["row_order_2"]);
	for($i=0;$i<count($id_ary);$i++){
		$sql = "UPDATE subcategory SET row_order='" . $i . "' WHERE id=". $id_ary[$i];
		$db->sql($sql);
		$res = $db->getResult();
	}
	echo "<p class='alert alert-success'>Subcategory order updated!</p>";
}

//18. update_policy()
if(isset($_POST['update_policy'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$message = $db->escapeString($_POST['message']);
	$sql = "select * from `settings` where `type`='privacy_policy'";
	$db->sql($sql);
	$res = $db->getResult();
	if(!empty($res)){
		$sql = "Update `settings` set `message`='".$message."' where `type`='privacy_policy'";
	}else{
		$sql = "INSERT INTO `settings`(`type`, `message`, `status`) VALUES ('privacy_policy','".$message."',1)";
	}
	// echo $sql;
	// return false;
	$db->sql($sql);
	$res = $db->getResult(); 
	echo "<p class='alert alert-success'>Privacy policy updated Successfully!</p><br>" ;
}

//19. update_terms()
if(isset($_POST['update_terms'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$message = $db->escapeString($_POST['message']);
	$sql = "select * from `settings` where `type`='update_terms'";
	$db->sql($sql);
	$res = $db->getResult();
	if(!empty($res)){
		$sql = "Update `settings` set `message`='".$message."' where `type`='update_terms'";
	}else{
		$sql = "INSERT INTO `settings`(`type`, `message`, `status`) VALUES ('update_terms','".$message."',1)";
	}
	$db->sql($sql);
	$res = $db->getResult(); 
	echo "<p class='alert alert-success'>Terms and conditions updated Successfully!</p><br>" ;
}

//20. update_user()
if(isset($_POST['user_id']) && isset($_POST['update_user'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	// echo "<p class='alert alert-success'>User Status updated!</p>";
	// return false;
	// exit();
	$id=$_POST['user_id'];
	$status = $db->escapeString($_POST['status']);
	$sql = "Update users set `status`='".$status."' where `id`=".$id;
	$db->sql($sql);
	$res = $db->getResult();
	echo "<p class='alert alert-success'>User Status updated!</p>";
}


// 21. add_admin_form
if(isset($_POST["add_admin"]) && !empty($_POST["add_admin"]) && $_POST['add_admin']==1)
{
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$username = $db->escapeString($_POST['username']);
	$role = $db->escapeString($_POST['role']);
	$password = $db->escapeString($_POST['password']);
	$password = md5($password);
	$sql = "SELECT auth_username FROM authenticate WHERE auth_username='".$username."'";
	$db->sql($sql);
	$res = $db->getResult();
	if($res){
		echo "<p class='alert alert-warning'>$username is already exists.</p>";
	}else{
		$data = array('auth_username' => $username,'auth_pass' => $password,'role' => $role,'app_passcode' => '0','android_key' => '0');
		$db->insert('authenticate',$data);
		$res = $db->getResult();
		if($res){
			echo "<p class='alert alert-success'>".$username." added as ".$role."!</p>";
		}else{
			echo "<p class='alert alert-danger'>Admin registration is failed. try again.</p>";
		}
	}
}

// 22. update_admin
if(isset($_POST['update_admin']) && !empty($_POST['update_admin']) && $_POST['update_admin']==1 && !empty($_POST['update_admin_id']))
{
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	
	$update_admin_id = $db->escapeString($_POST['update_admin_id']);
	$update_username = $db->escapeString($_POST['update_username']);
	$update_role = $db->escapeString($_POST['update_role']);
	$sql = "UPDATE authenticate SET auth_username='".$update_username."',role='".$update_role."' WHERE auth_username='".$update_admin_id."'";
	$db->sql($sql);
	$res = $db->getResult();
	if($res){
		echo "<p class='alert alert-danger'>$update_username is not updated.</p>";
	}else{
		echo "<p class='alert alert-success'>$update_username is successfully updated.</p>";
	}
}

// 23. delete_admin
if(isset($_POST['delete_admin']) && !empty($_POST['id']) && $_POST['delete_admin']==1)
{
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
    $id	= $db->escapeString($_POST['id']);
    $sql = "DELETE FROM `authenticate` WHERE `auth_username`='".$id."'";
	if($db->sql($sql)){
		echo 1;
	}else{
		echo 0;
	}
}

//24. system_configurations
if(isset($_POST['app_link']) && isset($_POST['system_configurations'])){

	$date = $db->escapeString(date('Y-m-d'));
	if(!empty($_POST['system_configurations_id'])){
		$_POST['system_timezone_gmt'] = preg_replace('/\s+/', '', $_POST['system_timezone_gmt']);
		$_POST['system_timezone_gmt'] = ($_POST['system_timezone_gmt'] == '00:00' )?"+".$_POST['system_timezone_gmt']:$_POST['system_timezone_gmt'];
		$sql = "UPDATE settings SET message='". json_encode($_POST) ."' WHERE type='system_configurations'";
	}else{
		$sql = "INSERT INTO settings (type,message,status) VALUES ('system_configurations','". json_encode($_POST) ."','1')";
	}
	$db->sql($sql);
	$res = $db->getResult();
	echo "<p class='alert alert-success'>Settings Saved!</p>";
}

// 25. delete_multiple
if(isset($_GET['delete_multiple']) && $_GET['delete_multiple'] != '' ) {
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
    $ids		= $db->escapeString($_GET['ids']);
    $table 		= $db->escapeString($_GET['sec']);
    $is_image 	= $_GET['is_image'];
	
    if($is_image){
		$path = array(
			'question' => 'images/questions/',
			'notifications' => 'images/notifications/',
		);
		
		$sql = "select `image` from ".$table." where id in ( ".$ids." )";
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $image){
			if(!empty($image['image']) && is_dir($image['image'])){
				unlink($path[$table].$image['image']);
			}
		}
	}
	
	$sql = "DELETE FROM `".$table."` WHERE `id` in ( ".$ids." ) ";
	if($db->sql($sql)){
		echo 1;
	}else{
		echo 0;
	}
}

// 26. add_language
if(isset($_POST['name']) and isset($_POST['add_language'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$name = $db->escapeString($_POST['name']);
	$sql = "SELECT `language` FROM `languages` WHERE `language`='".$name."'";
	$db->sql($sql);
	$language = $db->getResult();
	if(empty($language)){
		$sql = "INSERT INTO `languages` (`language`,`status`) VALUES ('".$name."','0')";
		$db->sql($sql);
		echo '<label class="alert alert-success">Language created successfully!</label>';
	}else{
		echo '<label class="alert alert-danger">Language is already created</label>';
	}
}

// 27. update_language
if(isset($_POST['language_id']) && isset($_POST['update_language'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	$language_id = $db->escapeString($_POST['language_id']);
	$name = $db->escapeString($_POST['name']);
	$status = $db->escapeString($_POST['status']);
	$sql = "UPDATE `languages` SET `language`='".$name."',`status`='".$status."' WHERE `id` = ".$language_id;
	if($db->sql($sql)){
		echo "<p class='alert alert-success'>Language updated successfully!</p>";
	}else{
		echo "<p class='alert alert-danger'>Language not updated!</p>";
	}
}

// 28. delete_language
if(isset($_GET['delete_language']) && $_GET['delete_language'] == '1' ) {
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
    $id = $db->escapeString($_GET['id']);
	$sql = 'DELETE FROM `languages` WHERE `id`='.$id;
	if($db->sql($sql)){
		echo 1;
	}else{
		echo 0;
	}
}

//29. get_categories_of_language - ajax dropdown menu options 
if(isset($_POST['get_categories_of_language']) && $_POST['get_categories_of_language'] != '' ) {
    $id = $_POST['language_id'];
    if(empty($id)){
		echo '<option value="">Select Category</option>';
		return false;
	}
	$sql = 'select * FROM `category` WHERE `language_id`='.$id.' ORDER BY row_order + 0 ASC';
	// echo $sql;
	$db->sql($sql);
	$res = $db->getResult();
	
	if(isset($_POST['sortable']) && $_POST['sortable'] == 'sortable'){
	    foreach($res as $category){
	      
        	if(!empty($category["image"])){
                $options .="<li id='".$category["id"]."'><big>".$category["row_order"].".</big> &nbsp;<img src='images/category/$category[image]' height=30 > ".$category["category_name"]."</li>";
            }else{
                $options .="<li id='".$category["id"]."'><big>".$category["row_order"].".</big> &nbsp;<img src='images/logo-half.png' height=30 > ".$category["category_name"]."</li>";
            }
    	}
	}else{
	    $options = '<option value="">Select Category</option>';
	    foreach($res as $option){
    		$options .="<option value='".$option['id']."'>".$option['category_name']."</option>";
    	}  
	}
	echo $options;
}


//30. update_about_us()
if(isset($_POST['update_about_us'])){
	if(!checkadmin($auth_username)){
		echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
		return false;
	}
	
	$message = $db->escapeString($_POST['message']);
	$sql = "select * from `settings` where `type`='about_us'";
	$db->sql($sql);
	$res = $db->getResult();
	if(!empty($res)){
		$sql = "Update `settings` set `message`='".$message."' where `type`='about_us'";
	}else{
		$sql = "INSERT INTO `settings`(`type`, `message`, `status`) VALUES ('about_us','".$message."',1)";
	}
	// echo $sql;
	// return false;
	$db->sql($sql);
	$res = $db->getResult(); 
	echo "<p class='alert alert-success'>About us updated successfully!</p><br>" ;
}

?>