<?php
/*
	API v5.4
	Quiz Online - WRTeam.in 
	WRTeam Developers
*/


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");




session_start();
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('library/crud.php');
include('library/functions.php');

$db = new Database();
$db->connect();
// date_default_timezone_set('Asia/Kolkata');

$fn = new Functions();
$config = $fn->get_configurations();

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
$response = array();
$access_key = "6808";

/*
 	API methods
	------------------------------------
	1. get_categories()
	2. get_subcategory_by_maincategory()
	3. get_questions_by_subcategory()
	4. get_questions_by_category()
	5. report_question()
	6. get_privacy_policy_settings()
	7. user_signup()
	8. upload_profile_image()
	9. update_profile()
	10. get_monthly_leaderboard()
	11. get_random_questions()
	12. set_monthly_leaderboard()
	13. update_fcm_id()
	14. get_random_questions_for_computer()
	15. get_terms_conditions_settings()
	16. get_questions_by_level()
	17. get_monthly_users_score()
	18. get_user_by_id()
	19. get_datewise_leaderboard()
	20. get_global_leaderboard()
	21. get_system_configurations()
	22. get_languages()
	24. set_battle_statistics() 
	25. get_battle_statistics() 
	26. set_users_statistics()
	27. get_users_statistics()
	28. get_notifications()
	29. get_about_us()
	
	functions 
	------------------------------------
	1. get_fcm_id($user_id)
	2. checkBattleExists($match_id)
	3. send_message($token,$data)
*/
if(!isset($_POST['access_key']) && !isset($_POST['get_categories'])){
	$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		$response['post'] = $_POST;
		print_r(json_encode("zaid"));
		return false;
}
// 1. get_categories() - get category details
if(isset($_POST['access_key']) && isset($_POST['get_categories'])){
	/* Parameters to be passed
		access_key:6808
		get_categories:1
		id:31 //{optional}
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		$response['post'] = $_POST;
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['id'])){
		$id = $db->escapeString($_POST['id']);
		$sql = "SELECT *,(SELECT @no_of_subcategories := count(`id`) from subcategory s WHERE s.maincat_id = c.id and s.status = 1 ) as no_of, if(@no_of_subcategories = 0, (SELECT @maxlevel := MAX(`level`+0) from question q WHERE c.id = q.category ),@maxlevel := 0) as `maxlevel` FROM `category` c WHERE c.id = $id ORDER By CAST(c.row_order as unsigned) ASC";
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			$result[0]['image'] = (!empty($result[0]['image']))?DOMAIN_URL.'images/category/'.$result[0]['image']:'';
			 $result[0]['maxlevel'] = ($result[0]['maxlevel'] == '' || $result[0]['maxlevel'] == null ) ? '0' : $result[0]['maxlevel'];               
			$response['error'] = "false";
			$response['data'] = $result[0];
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
		print_r(json_encode($response));
	}else{
		$sql = "SELECT *,(SELECT @no_of_subcategories := count(`id`) from subcategory s WHERE s.maincat_id = c.id and s.status = 1 ) as no_of, if(@no_of_subcategories = 0, (SELECT @maxlevel := MAX(`level`+0) from question q WHERE c.id = q.category ),@maxlevel := 0) as `maxlevel` FROM `category` c ORDER By CAST(c.row_order as unsigned) ASC";
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			for($i=0;$i<count($result);$i++){
				$result[$i]['image'] = (!empty($result[$i]['image']))?DOMAIN_URL.'images/category/'.$result[$i]['image']:'';
				 $result[$i]['maxlevel'] = ($result[$i]['maxlevel'] == '' || $result[$i]['maxlevel'] == null ) ? '0' : $result[$i]['maxlevel'];
			}
			$response['error'] = "false";
			$response['data'] = $result;
		}else{

			$response['error'] = "true";
			$response['message'] = "No data found!";
		}

		print_r(json_encode($response));
	}
}

// 2. get_subcategory_by_maincategory() - get sub category details
if(isset($_POST['access_key']) && isset($_POST['get_subcategory_by_maincategory'])){
	/* Parameters to be passed
		access_key:6808
		get_subcategory_by_maincategory:1
		main_id:31
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['main_id'])){
		$id = $db->escapeString($_POST['main_id']);
		$sql = "SELECT *,(select max(`level` + 0) from question where question.subcategory=subcategory.id ) as maxlevel FROM `subcategory` WHERE `maincat_id`='$id' and `status`=1 ORDER BY CAST(row_order as unsigned) ASC";
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			for($i=0;$i<count($result);$i++){
				$result[$i]['image'] = (!empty($result[$i]['image']))?DOMAIN_URL.'images/subcategory/'.$result[$i]['image']:'';
				$result[$i]['maxlevel'] = ($result[$i]['maxlevel'] == '' || $result[$i]['maxlevel'] == null )?'0':$result[$i]['maxlevel'];
			}
			$response['error'] = "false";
			$response['data'] = $result;
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
		print_r(json_encode($response));
	}
}

// 3. get_questions_by_subcategory() - get sub category questions
if(isset($_POST['access_key']) && isset($_POST['get_questions_by_subcategory'])){
	/* Parameters to be passed
		access_key:6808
		get_questions_by_subcategory:1
		subcategory:115
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['subcategory'])){
		$id = $db->escapeString($_POST['subcategory']);
		$sql = "SELECT * FROM `question` where subcategory=".$id." ORDER by RAND()";
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			for($i=0;$i<count($result);$i++){
				$result[$i]['image'] = (!empty($result[$i]['image']))?DOMAIN_URL.'images/questions/'.$result[$i]['image']:'';
				$result[$i]['optione'] = ($fn->is_option_e_mode_enabled() && $result[$i]['optione'] != null)?trim($result[$i]['optione']):'';
				$result[$i]['optiona'] = trim($result[$i]['optiona']);
				$result[$i]['optionb'] = trim($result[$i]['optionb']);
				$result[$i]['optionc'] = trim($result[$i]['optionc']);
				$result[$i]['optiond'] = trim($result[$i]['optiond']);
			}
			$response['error'] = "false";
			$response['data'] = $result;
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
		print_r(json_encode($response));
	}
}

// 4. get_questions_by_category() - get category questions
if(isset($_POST['access_key']) && isset($_POST['get_questions_by_category'])){
	/* Parameters to be passed
		access_key:6808
		get_questions_by_category:1
		category:115
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['category'])){
		$id = $db->escapeString($_POST['category']);
		$sql = "SELECT * FROM `question` WHERE category=".$id." ORDER BY id DESC";
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			for($i=0;$i<count($result);$i++){
				$result[$i]['image'] = (!empty($result[$i]['image']))?DOMAIN_URL.'images/questions/'.$result[$i]['image']:'';
				$result[$i]['optione'] = ($fn->is_option_e_mode_enabled() && $result[$i]['optione'] != null)?trim($result[$i]['optione']):'';
				$result[$i]['optiona'] = trim($result[$i]['optiona']);
				$result[$i]['optionb'] = trim($result[$i]['optionb']);
				$result[$i]['optionc'] = trim($result[$i]['optionc']);
				$result[$i]['optiond'] = trim($result[$i]['optiond']);
			}
			$response['error'] = "false";
			$response['data'] = $result;
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	}
	print_r(json_encode($response));
}

//5. report_question() - report a question by user
if(isset($_POST['report_question']) && isset($_POST['access_key']) ){
	/* Parameters to be passed
		access_key:6808
		report_question:1
		question_id:115
		message: Any reporting message
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	$question_id = $_POST['question_id'];
	$message = $_POST['message'];
	if(!empty($question_id) && !empty($message)){
		$data = array(
			'question_id'	 => $question_id,
			'message' => $message,
			'date' => date("Y-m-d")//$datetime->format('Y\-m\-d\ h:i:s'),
		);
		// print_r($data);
		// return false;
		
		$db->insert('question_reports',$data);  // Table name, column names and respective values
		$res = $db->getResult();
		
		$response['error'] = false;
		$response['message'] = "Report submitted successfully";
		$response['id'] = $res[0];
	}else{
		$response['error'] = true;
		$response['message'] = "Please fill all the data and submit!";
	}
	print_r(json_encode($response));
}

// 6. get_privacy_policy_settings()
if(isset($_POST['access_key']) && isset($_POST['privacy_policy_settings']) AND $_POST['privacy_policy_settings']==1){
	/* Parameters to be passed
		access_key:6808
		privacy_policy_settings:1
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	if(!empty($_POST['access_key'])){
		$sql = "SELECT * FROM `settings` WHERE type='privacy_policy'";
        $db->sql($sql);
		$res = $db->getResult();
		if(!empty($res)) {
			$response['error'] = "false";
			$response['data'] = $res[0]['message'];
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	}else{
		$response['error'] = "true";
		$response['message'] = "Please pass all the fields";
	}
	print_r(json_encode($response));
}

// 7. user_signup()
if(isset($_POST['access_key']) && isset($_POST['user_signup'])){
	/*	Parameters to be passed
		access_key:6808
		user_signup:1
		name:Jaydeep Goswami
		email:jaydeepjgiri@yahoo.com
		profile:Image URL
		mobile:7894561230
		type: email / gmail / fb
		fcm_id: xyz123654
		refer_code:xyz123654
		friends_code:xyz123654
		ip_address: 191.1.0.4
		status:1   // 1 - Active & 0 Deactive
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	$email = $db->escapeString($_POST['email']);
	$profile = $db->escapeString($_POST['profile']);
	$name = $db->escapeString($_POST['name']);
	$ip_address = $db->escapeString($_POST['ip_address']);
	$mobile = (isset($_POST['mobile']))?$db->escapeString($_POST['mobile']):'';
	$type = $db->escapeString($_POST['type']);
	$fcm_id = (isset($_POST['fcm_id']))?$db->escapeString($_POST['fcm_id']):"";
	$refer_code = (isset($_POST['refer_code']))?$db->escapeString($_POST['refer_code']):'';
	$friends_code = (isset($_POST['friends_code']))?$db->escapeString($_POST['friends_code']):'';
	$points = '0';
	$status = '1';

    if(!empty($friends_code)){
	    $code = $fn->valid_friends_refer_code($friends_code);
	    if(!$code['is_valid']){
	    	$friends_code = '';
	    }
    }

	$sql = "SELECT id,email FROM users WHERE email='$email'";
	$db->sql($sql);
	$res = $db->getResult();
	if(!empty($res))
	{
		// log in..
		if(!empty($email) ){
			$user_id = $res[0]['id'];

		    $friends_code_is_used = $fn->check_friends_code_is_used_by_user($user_id);
			if(!($friends_code_is_used['is_used']) && $friends_code !=''){
				/* give coins to both the users 50 & 100 for each */
				$sql = "UPDATE `users` SET `friends_code`='".$friends_code."', `coins` = `coins` + 50  WHERE id = ".$res[0]['id'];
				$db->sql($sql);
				$resf = $db->getResult();
				$credited = $fn->credit_coins_to_friends_code($friends_code);
			}
			if(!empty($fcm_id)){
    			$sql = " UPDATE `users` SET fcm_id='".$fcm_id."'";
    			$sql .=(!$fn->is_refer_code_set($user_id) && !empty($refer_code))?", refer_code='".$refer_code."'":"";
    			$sql .=  " WHERE `id` = ".$res[0]['id'];
    			$db->sql($sql);
			}
			if(!empty($refer_code) && !$fn->is_refer_code_set($user_id) ){
			    $sql = " UPDATE `users`  refer_code='".$refer_code."'";
    			$sql .=  " WHERE `id` = ".$res[0]['id'];
    			$db->sql($sql);
			}
			$sql = "SELECT * FROM `users` WHERE `email` ='$email' AND `id`=".$res[0]['id'];
			$db->sql($sql);
			$res = $db->getResult();
			foreach($res as $row){
				if (filter_var($row['profile'], FILTER_VALIDATE_URL) === FALSE) {
				    // Not a valid URL. Its a image only or empty
				    $tempRow['profile'] = (!empty($row['profile']))?DOMAIN_URL.'uploads/profile/'.$row['profile']:'';
				
				}else{
				    /* if it is a ur than just pass url as it is */
                    $tempRow['profile'] = $row['profile'];
                }
				
				$tempRow['user_id'] = $row['id'];
				$tempRow['name'] = $row['name'];
				$tempRow['email'] = $row['email'];
				$tempRow['mobile'] = $row['mobile'];
				$tempRow['type'] = $row['type'];
				$tempRow['fcm_id'] = $row['fcm_id'];
				$tempRow['refer_code'] = $row['refer_code'];
				$tempRow['coins'] = $row['coins'];
				$tempRow['ip_address'] = $row['ip_address'];
				$tempRow['status'] = $row['status'];
				$tempRow['date_registered'] = $row['date_registered'];
				$newresult[] = $tempRow;
			}
			$response['error'] = "false";
			$response['message'] = "Successfully logged in";
			$response['data'] = $newresult[0];
		}else{
			$response['error'] = "true";
			$response['message'] = "Please fill all the data and submit!";
		}
	}
	else
	{
		// signup..
		if(!empty($email)){

			$data = array(
				'name'  => $name,
				'email'	 => $email,
				'mobile' => $mobile,
				'type' => $type,
				'profile'	 => $profile,
				'fcm_id' => $fcm_id,
				'refer_code' => $refer_code,
				'friends_code' => $friends_code,
				'coins' => '0',
				'ip_address' => $ip_address,
				'status' => $status
			);
			$sql = $db->insert('users',$data);
			$res = $db->getResult();
		    
			$data = array(
    			'user_id' => "$res[0]",
				'name'  => $name,
				'email'	 => $email,
				'profile'	 => $profile,
				'mobile' => $mobile,
				'fcm_id' => $fcm_id,
				'refer_code' => $refer_code,
				'coins' => '0',
				'type' => $type,
				'ip_address' => $ip_address,
				'status' => $status
			);
			
			if($friends_code !=''){
				$data['coins'] = "50";
				$sql = "UPDATE `users` SET `coins` = `coins` + 50  WHERE `id` = ".$res[0];
				$db->sql($sql);
				$credited = $fn->credit_coins_to_friends_code($friends_code);
			}

			$response['error'] = "false";
			$response['message'] = "User Registered successfully";
			$response['data'] = $data;
		}else{
			$response['error'] = "true";
			$response['message'] = "Please fill all the data and submit!";
		}
	}
	print_r(json_encode($response));
}

// 8. upload_profile_image() - upload users profile pic
if(isset($_POST['access_key']) && isset($_POST['upload_profile_image'])){
	/* Parameters to be passed
		access_key:6808
		upload_profile_image:1
		user_id:37
		image: image file
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	// Path to move uploaded files
	$target_path = "uploads/profile/";
	// Folder create if not exists
	if(!is_dir($target_path)){
	    mkdir($target_path, 0777,true); /* 3rd parameter is required in recursive mode */
	}
	$id = $db->escapeString($_POST['user_id']);
	$old_profile = '';
	
	$sql = "select `profile` from `users` where id = ".$id;
	$db->sql($sql);
	$res = $db->getResult();
	
	if(!empty($res) && isset($res[0]['profile'])){
		if(filter_var($res[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
			// Not a valid URL. Its an image only 
			$old_profile = (!empty($res[0]['profile']))?$target_path.''.$res[0]['profile']:'';
		}
	}
	
	// final file url that is being uploaded
	$file_upload_url = $target_path;

	if (isset($_FILES['image']['name'])) {
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$extension = pathinfo($_FILES["image"]["name"])['extension'];
		if(!(in_array($extension, $allowedExts))){
			$response['error']=true;
			$response['message'] = 'Image type is invalid';
			echo json_encode($response);
			return false;
		}
		$filename = microtime(true).'.'. strtolower($extension);
		$target_path = $target_path . $filename;
		
		try {
			// Throws exception incase file is not being moved
			if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
				// make error flag true
				$response['error'] = true;
				$response['message'] = 'Could not move the file!';
			}
			$sql = "UPDATE `users` SET `profile`='".$filename."' WHERE `id`=".$id."";
			$db->sql($sql);
			if(!empty($old_profile)){
			    unlink($old_profile);
			}
			
			// File successfully uploaded
			$response['error'] = false;
			$response['message'] = 'File uploaded successfully!';
			$response['file_path'] = DOMAIN_URL.$file_upload_url.$filename;
			
		} catch (Exception $e) {
			// Exception occurred. Make error flag true
			$response['error'] = true;
			$response['message'] = $e->getMessage();
		}
	} else {
		// File parameter is missing
		$response['error'] = true;
		$response['message'] = 'Not received any file!';
	}
	 
	// Echo final json response to client
	echo json_encode($response);
}

// 9. update_profile() - update user profile
if(isset($_POST['access_key']) && isset($_POST['update_profile'])){
	/* Parameters to be passed
		access_key:6808
		update_profile:1
		email:jaydeepjgiri@yahoo.com
		name:Jaydeep Goswami
		mobile:7894561230
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	$email = $db->escapeString($_POST['email']);
	$name = $db->escapeString($_POST['name']);
	
	$sql = "UPDATE `users` SET `name`='".$name."'";
	$sql .= (isset($_POST['mobile']) && !empty($_POST['mobile']))?" ,`mobile`='".$_POST['mobile']."'":"";
	$sql .= (isset($_POST['level']) && !empty($_POST['level']))?" ,`level`=".$_POST['level']:"";
	$sql .= (isset($_POST['exp']) && !empty($_POST['exp']))?" ,`exp`=".$_POST['exp']:"";
	$sql .= " WHERE `email`='".$email."'";
	$db->sql($sql);
	
	$response['error'] = "false";
	$response['message'] = "Profile updated successfully";
	print_r(json_encode($response));
}

// 10. get_monthly_leaderboard()
if(isset($_POST['access_key']) && isset($_POST['get_monthly_leaderboard'])){
	/* Parameters to be passed
		access_key:6808
		get_monthly_leaderboard:1
		date:2019-02-01		// use date format = YYYY-MM-DD
		limit:10            // {optional} - Number of records per page
		offset:0            // {optional} - starting position
		user_id:54 			// for get current user rank (optional) (login user_id)
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}

	if(empty($_POST['date']) or !isset($_POST['date'])){
		$response['error'] = "true";
		$response['message'] = "Please fill all the data and submit!";
		print_r(json_encode($response));
		return false;
	}
	
	$limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))?$db->escapeString($_POST['limit']):25;
	$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))?$db->escapeString($_POST['offset']):0;
	
	$date = $db->escapeString($_POST['date']);
	
	/* get the total no of records */
	$sql = "SELECT COUNT(m.id) as `total` FROM `monthly_leaderboard` m WHERE ( MONTH( m.date_created ) = MONTH('".$date."') AND YEAR( m.date_created ) = YEAR('".$date."') ) ORDER BY m.score DESC";
	$db->sql($sql);
	$total = $db->getResult();
	
// 	$sql = "SELECT monthly_leaderboard.*,users.name,users.profile,users.email,FIND_IN_SET( score, (SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM monthly_leaderboard where ( month(monthly_leaderboard.date_created) = month('".$date."') and year(monthly_leaderboard.date_created) = year('".$date."') ) )) AS rank FROM `monthly_leaderboard` INNER JOIN users ON monthly_leaderboard.user_id = users.id where ( month(monthly_leaderboard.date_created) = month('".$date."') and year(monthly_leaderboard.date_created) = year('".$date."') )  ORDER BY score DESC LIMIT $offset, $limit ";
    $sql = "SELECT r.*,u.email,u.name,u.profile FROM (
        SELECT s.*, @rank := @rank + 1 rank FROM 
        ( SELECT user_id, sum(score) score FROM monthly_leaderboard m 
         WHERE ( MONTH( m.date_created ) = month('".$date."') AND YEAR( m.date_created ) = year('".$date."') )
         GROUP BY user_id) s,
        (SELECT @rank := 0) init ORDER BY score DESC
    ) r 
    INNER join users u on u.id = r.user_id ORDER BY r.rank ASC LIMIT $offset,$limit";
// 	echo $sql;
	$db->sql($sql);
	$res = $db->getResult();
	
	if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
		$user_id = $db->escapeString($_POST['user_id']);
		$sql = "SELECT m.id, user_id, score, u.profile,
    		    FIND_IN_SET( score, (SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM monthly_leaderboard m1 
    		        where ( month(m1.date_created) = month('".$date."') and year(m1.date_created) = year('".$date."') ) )) AS rank
		  FROM monthly_leaderboard m
		  INNER JOIN users u on u.id = m.user_id
		  WHERE user_id =".$user_id." and month(m.date_created) = month('".$date."') and year(m.date_created) = year('".$date."') ";
// 		echo $sql;
		$db->sql($sql);
		$my_rank = $db->getResult();
		if(!empty($my_rank)){
		    if(filter_var($my_rank[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                // Not a valid URL. Its a image only or empty
                $my_rank[0]['profile'] = (!empty($my_rank[0]['profile']))?DOMAIN_URL.'uploads/profile/'.$my_rank[0]['profile']:'';
            }
			$user_rank['my_rank'] = $my_rank[0];
// 			array_push($res,$user_rank);
			array_unshift($res,$user_rank);
// 			$res = array_reverse($res);
		}else{
		    $my_rank = array(
		        'id' => $user_id,
		        'rank' => 0
		    );
		    $user_rank['my_rank'] = $my_rank;
		    array_unshift($res,$user_rank);
		}
	}
	
	if(!empty($res)){
		foreach($res as $row){
		    if(isset($row['profile'])){
    		    if(filter_var($row['profile'], FILTER_VALIDATE_URL) === FALSE) {
    			    // Not a valid URL. Its a image only or empty
    			    $row['profile'] = (!empty($row['profile']))?DOMAIN_URL.'uploads/profile/'.$row['profile']:'';
                }
		    }
		    $tempRow[] = $row;
		}
		$response['error'] = "false";
		$response['total'] = $total[0]['total'];
		$response['data'] = $tempRow;
		print_r(json_encode($response));
	}else{
		$response['error'] = "true";
		$response['message'] = "Data not found";
		print_r(json_encode($response));
	}
}

// 11. get_random_questions() // max 10 rows
if(isset($_POST['access_key']) && isset($_POST['get_random_questions'])){
	/* Parameters to be passed
		access_key:6808
		get_random_questions:1
		match_id:your_match_id
		user_id1:1010
		fcm_id_1:xyzCode
		user_id2:1001
		fcm_id_2:pqrsCode
		destroy_match:0 / 1
		language_id:2   //{optional}
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	$match_id = $db->escapeString($_POST['match_id']);
	
	if(isset($_POST['destroy_match']) && $_POST['destroy_match'] == 1){
	    $sql = "DELETE FROM `battle_questions` WHERE `match_id` = '".$match_id."'";
	    $db->sql($sql);
        $response['error'] = "false";
        $response['message'] = "Battle destroyed successfully";
        print_r(json_encode($response));
        return false;
        exit();
	}
	
	/* delete old data automatically */
	$todays_date = date('Y-m-d');
	$sql = "DELETE FROM `battle_questions` WHERE date_created < ('".$todays_date."')";
	$db->sql($sql);
	
	$user_id1 = $db->escapeString($_POST['user_id1']);
	$user_id2 = $db->escapeString($_POST['user_id2']);
	
	$fcm_id_1 = (isset($_POST['fcm_id_1']))?$_POST['fcm_id_1']:'';
	$fcm_id_2 = (isset($_POST['fcm_id_2']))?$_POST['fcm_id_2']:'';
	
	$language_id = (isset($_POST['language_id']) && is_numeric($_POST['language_id']))?$db->escapeString($_POST['language_id']):'';
	
	if(empty($fcm_id_1)){
	    $fcm_id_1 = get_fcm_id($user_id1);
	}
	if(empty($fcm_id_2)){
	    $fcm_id_2 = get_fcm_id($user_id2);
	}
	
	if(!checkBattleExists($match_id)){
	    /* if match does not exist read and store the questions */
	    
    	$sql = "SELECT * FROM `question` ";
    	$sql .= (!empty($language_id))?" where `language_id` = $language_id ":"";
    	$sql .= " ORDER BY RAND() LIMIT 0,10";
    	$db->sql($sql);
    	$res = $db->getResult();	
    	
    	if(empty($res)){
    		$response['error'] = "true";
    		$response['message'] = "No questions found to compete with each other!";
    		print_r(json_encode($response));
    	}else{
    		$questions = $db->escapeString(json_encode($res));
    		$sql = "INSERT INTO `battle_questions` (`match_id`, `questions`) VALUES ('$match_id','$questions')";
    		$db->sql($sql);
    // 		echo $sql;
    		
    		foreach($res as $row){
    		    $row['image'] = (!empty($row['image']))?DOMAIN_URL.'images/questions/'.$row['image']:'';
    		    $row['optione'] = ($fn->is_option_e_mode_enabled() && $row['optione'] != null)?$row['optione']:'';
    		    $temp[] = $row;
    		}
    		
    		$res = $temp;
    		
    		$response['error'] = "false";
    		$response['message'] = "Data sent to devices via FCM 1";
    		$response['data'] = $res;
    		
    		$data['data'] = $res;
    // 		$result = send_message($fcm_id_1,$data);
    		$response['user_id1_status'] = ($result['success'] == 1)?'sent':'not sent';
    		
    // 		$result = send_message($fcm_id_2,$data);
    		$response['user_id2_status'] = ($result['success'] == 1)?'sent':'not sent';
    		
    		print_r(json_encode($response));
    	}
	}else{
	    /* read the questions and send it. */
	    $sql = "SELECT * FROM `battle_questions` WHERE `match_id` = '".$match_id."'";
	    //echo $sql;
	    $db->sql($sql);
	    $res = $db->getResult();
	    
	    $res = json_decode($res[0]['questions'],1);
	    foreach($res as $row){
		    $row['image'] = (!empty($row['image']))?DOMAIN_URL.'images/questions/'.$row['image']:'';
		    $row['optione'] = ($fn->is_option_e_mode_enabled() && $row['optione'] != null)?$row['optione']:'';
		    $temp[] = $row;
		}
		$res[0]['questions'] = json_encode($temp);
	    
	    $response['error'] = "false";
		$response['message'] = "Data sent to devices via FCM";
		$response['data'] = json_decode($res[0]['questions']);
		
		$data['data'] = json_decode($res[0]['questions']);
// 		$result = send_message($fcm_id_1,$data);
		$response['user_id1_status'] = ($result['success'] == 1)?'sent':'not sent';
		
// 		$result = send_message($fcm_id_2,$data);
		$response['user_id2_status'] = ($result['success'] == 1)?'sent':'not sent';
		
		print_r(json_encode($response));
	}
}

// 12. set_monthly_leaderboard()
if(isset($_POST['access_key']) && isset($_POST['set_monthly_leaderboard'])){
    /* Parameters to be passed
        access_key:6808
        set_monthly_leaderboard:1
        user_id:10
        score:100
    */
    if($access_key != $_POST['access_key']){
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }
    
    if(!empty($_POST['access_key']) && !empty($_POST['user_id']) && !empty($_POST['score'])){
        $user_id = $db->escapeString($_POST['user_id']);
        $score = $db->escapeString($_POST['score']);
        $date = date('Y-m-d');
        
        $sql = "SELECT id, user_id FROM `monthly_leaderboard` WHERE `user_id`=".$user_id." and month(monthly_leaderboard.date_created) = month('".$date."') 
            and year(monthly_leaderboard.date_created) = year('".$date."') ";
        $db->sql($sql);
        $result = $db->getResult();
        
        if(!empty($result)){
            $sql = "UPDATE `monthly_leaderboard` SET `score`= `score` + '".$score."' WHERE id = ".$result[0]['id']." and user_id=".$user_id;
            $db->sql($sql);
            $response['error'] = "false";
            $response['message'] = "successfully update score";
        }else{
            $sql = 'INSERT INTO `monthly_leaderboard` (`user_id`, `score`) VALUES ('.$user_id.','.$score.')';
            $db->sql($sql);
            $response['error'] = "false";
            $response['message'] = "successfully insert score";
        }
    }else{
        $response['error'] = "true";
        $response['message'] = "Please pass all the fields";
    }
    print_r(json_encode($response));
}

// 13. update_fcm_id() - update user FCM ID
if(isset($_POST['access_key']) && isset($_POST['update_fcm_id'])){
	/* Parameters to be passed
		access_key:6808
		update_fcm_id:1
		user_id:1
		fcm_id:xyzCode
		refer_code:myReferCode { optional }
		coins:100 { optional - Replacing coins not doing addition }
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	$fcm_id = $db->escapeString($_POST['fcm_id']);
	$refer_code = (isset($_POST['refer_code']) && !empty($_POST['refer_code']) )? $db->escapeString($_POST['refer_code']):'';
	$coins = (isset($_POST['coins']) && !empty($_POST['coins']) )? $db->escapeString($_POST['coins']):'';
	$id = $db->escapeString($_POST['user_id']);
	
	$sql = "UPDATE `users` SET `fcm_id`='".$fcm_id."'";
	$sql .= (!empty($refer_code))?", `refer_code`='".$refer_code."' ":"";
	$sql .= (!empty($coins))?", `coins`='".$coins."' ":"";
	$sql .= " WHERE `id`='".$id."'";
	$db->sql($sql);
	
	$response['error'] = "false";
	$response['message'] = " FCM updated successfully";
	print_r(json_encode($response));
}


// 14. get_random_questions_for_computer() // max 10 rows
if(isset($_POST['access_key']) && isset($_POST['get_random_questions_for_computer'])){
	/* Parameters to be passed
		access_key:6808
		get_random_questions_for_computer:1
		language_id:2   //{optional}
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	

    /* if match does not exist read and store the questions */
    
    $language_id = (isset($_POST['language_id']) && !empty($_POST['language_id']) && is_numeric($_POST['language_id']))?$db->escapeString($_POST['language_id']):'';
    
	$sql = "SELECT * FROM `question` ";
	$sql .= (!empty($language_id))?" where `language_id` = $language_id ":"";
	$sql .= " ORDER BY RAND() LIMIT 0,10";
	$db->sql($sql);
	$res = $db->getResult();	
	
	if(empty($res)){
		$response['error'] = "true";
		$response['message'] = "No questions found to compete with each other!";
		print_r(json_encode($response));
	}else{
	    $tempRow = array();
		foreach($res as $row){
			$tempRow['id'] = $row['id'];
			$tempRow['category'] = $row['category'];
			$tempRow['subcategory'] =  $row['subcategory'];
			$tempRow['image'] = (!empty($row['image']))?DOMAIN_URL.'images/questions/'.$row['image']:'';
			$tempRow['question'] = $row['question'];
			$tempRow['optiona'] = $row['optiona'];
			$tempRow['optionb'] =  $row['optionb'];
			$tempRow['optionc'] = $row['optionc'];
			$tempRow['optiond'] = $row['optiond'];
			$tempRow['optione'] = ($fn->is_option_e_mode_enabled() && $row['optione'] != null)?$row['optione']:'';
			$tempRow['answer'] = $row['answer'];
			$tempRow['level'] =  $row['level'];
			$tempRow['note'] = $row['note'];
			$newresult[] = $tempRow;
		}
		$response['error'] = "false";
		$response['message'] = "Data sent to devices via FCM 1";
		$response['data'] = $newresult;
		print_r(json_encode($response));
	}
}

// 15. get_terms_conditions_settings()
if(isset($_POST['access_key']) && isset($_POST['get_terms_conditions_settings']) AND $_POST['get_terms_conditions_settings']==1){
	/* Parameters to be passed
		access_key:6808
		get_terms_conditions_settings:1
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	if(!empty($_POST['access_key'])){
		$sql = "SELECT * FROM `settings` WHERE type='update_terms'";
        $db->sql($sql);
		$res = $db->getResult();
		if(!empty($res)) {
			$response['error'] = "false";
			$response['data'] = $res[0]['message'];
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	}else{
		$response['error'] = "true";
		$response['message'] = "Please pass all the fields";
	}
	print_r(json_encode($response));
}


// 16. get_questions_by_level() - get questions by level
if(isset($_POST['access_key']) && isset($_POST['get_questions_by_level'])){
	/* Parameters to be passed
		access_key:6808
		get_questions_by_level:1
		
		level:1
		
		category:115 {or}
		subcategory:115 
		language_id:2   // {optional} 
		 
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['level']) && (isset($_POST['category']) || isset($_POST['subcategory']))){
		$level = $db->escapeString($_POST['level']);
		$language_id = (isset($_POST['language_id']) && is_numeric($_POST['language_id']))?$db->escapeString($_POST['language_id']):'';
		$id = (isset($_POST['category']))?$db->escapeString($_POST['category']):$db->escapeString($_POST['subcategory']);
		$sql = "SELECT * FROM `question` WHERE level=".$level;
		$sql .= (isset($_POST['category']))?" and `category`=".$id : " and `subcategory`=".$id;
		$sql .= (!empty($language_id))?" and `language_id`=".$language_id : "";
		$sql .= " ORDER BY rand() DESC";
		
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			for($i=0;$i<count($result);$i++){
				$result[$i]['image'] = (!empty($result[$i]['image']))?DOMAIN_URL.'images/questions/'.$result[$i]['image']:'';
				$result[$i]['optione'] = ($fn->is_option_e_mode_enabled() && $result[$i]['optione'] != null)?trim($result[$i]['optione']):'';
				$result[$i]['optiona'] = trim($result[$i]['optiona']);
				$result[$i]['optionb'] = trim($result[$i]['optionb']);
				$result[$i]['optionc'] = trim($result[$i]['optionc']);
				$result[$i]['optiond'] = trim($result[$i]['optiond']);
			}
			$response['error'] = "false";
			$response['data'] = $result;
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	}else{
	    $response['error'] = "true";
	    $response['message'] = "Pass all mandatory fields";
	}
	print_r(json_encode($response));
}

// 17. get_monthly_users_score()
if(isset($_POST['access_key']) && isset($_POST['get_monthly_users_score'])){
	/* Parameters to be passed
		access_key:6808
		get_monthly_users_score:1
		user_id:154 			// for get current user rank (optional) (login user_id)
		date:2019-02-01		// use date format = YYYY-MM-DD { optional }
		
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}

	if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
		$user_id = $db->escapeString($_POST['user_id']);
		if(isset($_POST['date'])){
		    /* monthly rank */
		    $date = $db->escapeString($_POST['date']);
		    $sql = "SELECT id, user_id, score, 
		    FIND_IN_SET( score, (SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM monthly_leaderboard where ( month(monthly_leaderboard.date_created) = month('".$date."')) )) AS rank 
		    FROM monthly_leaderboard WHERE user_id =".$user_id;
		}else{ /* global rank */
		    $sql = "SELECT id, user_id, score, 
		    FIND_IN_SET( score, (SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM monthly_leaderboard )) AS rank 
		    FROM monthly_leaderboard WHERE user_id =".$user_id;
		}
		
		$db->sql($sql);
		$my_rank = $db->getResult();
		
		if(!empty($my_rank)){
			$response['error'] = "false";
    		$response['data'] = $my_rank[0];
    		print_r(json_encode($response));
		}else{
		    $response['error'] = "true";
    		$response['message'] = "Data not found";
    		print_r(json_encode($response));
		}
	}else{
	    $response['error'] = "true";
		$response['message'] = "Pass all the fields!";
		print_r(json_encode($response));
	}
}

// 18. get_user_by_id() - get user details
if(isset($_POST['access_key']) && isset($_POST['get_user_by_id'])){
	/* Parameters to be passed
		access_key:6808
		get_user_by_id:1
		id:31
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['id'])){
		$id = $db->escapeString($_POST['id']);
		$sql = "SELECT * FROM `users` WHERE id = $id ";
		$db->sql($sql);
		$result = $db->getResult();
		$sql = "SELECT r.score,r.rank FROM 
                (SELECT s.*, @rank := @rank + 1 rank 
                    FROM ( SELECT user_id, sum(score) score 
                    FROM monthly_leaderboard m GROUP BY user_id ) s,
                (SELECT @rank := 0) init ORDER BY score DESC ) r 
                    INNER join users u on u.id = r.user_id WHERE r.user_id =".$id;
        
        $db->sql($sql);
        $my_rank = $db->getResult();
        
// 		print_r($my_rank);
		if (!empty($result)) {
			if (filter_var($result[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
				// Not a valid URL. Its a image only or empty
				$result[0]['profile'] = (!empty($result[0]['profile']))?DOMAIN_URL.'uploads/profile/'.$result[0]['profile']:'';
			
			}else{
				/* if it is a ur than just pass url as it is */
				$result[0]['profile'] = $result[0]['profile'];
			}
			$result[0]['all_time_score'] = (isset($my_rank[0]['score']))?$my_rank[0]['score']:0;
			$result[0]['all_time_rank'] = (isset($my_rank[0]['rank']))?$my_rank[0]['rank']:0;
			$response['error'] = "false";
			$response['data'] = $result[0];
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
		print_r(json_encode($response));
	}else{
		$response['error'] = "true";
		$response['message'] = "Please Pass all the fields!";
		print_r(json_encode($response));
	}
}

// 19. get_datewise_leaderboard()
if(isset($_POST['access_key']) && isset($_POST['get_datewise_leaderboard'])){
    /* Parameters to be passed
        access_key:6808
        get_datewise_leaderboard:1
        from:2019-06-01		// use date format = YYYY-MM-DD
        to:2019-06-07		// use date format = YYYY-MM-DD
        offset:0        // {optional} - Starting position
        limit:20        // {optional} - number of records per page
        user_id:25			// to get current user's rank (optional) ( login user_id )
    */
    if($access_key != $_POST['access_key']){
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }

    if( (empty($_POST['from']) || !isset($_POST['from'])) or (empty($_POST['to']) || !isset($_POST['to']))){
        $response['error'] = "true";
        $response['message'] = "Please fill all the data and submit!";
        print_r(json_encode($response));
        return false;
    }
    
    $from = $db->escapeString($_POST['from']);
    $to = $db->escapeString($_POST['to']);
    
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))?$db->escapeString($_POST['limit']):25;
	$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))?$db->escapeString($_POST['offset']):0;
    
    /* get the total no of records */
	$sql = "SELECT COUNT(m.id) as `total` FROM `monthly_leaderboard` m where (DATE(`last_updated`) BETWEEN date('".$from."') and date('".$to."')) ORDER BY score DESC";
	$db->sql($sql);
	$total = $db->getResult();
    
    /*$sql = "SELECT m.*,u.name,u.profile,u.email, 
            FIND_IN_SET( score, (SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM `monthly_leaderboard` m 
            where (DATE(`last_updated`) BETWEEN date('".$from."') and date('".$to."')) )) AS rank 

            FROM `monthly_leaderboard` m 
            INNER JOIN `users` u ON m.`user_id` = u.id 
            where (DATE(`last_updated`) BETWEEN date('".$from."') and date('".$to."')) 
            
            ORDER BY score DESC LIMIT $offset,$limit";*/
            
    $sql = "SELECT r.*,u.email,u.name,u.profile FROM (
        SELECT s.*, @rank := @rank + 1 rank FROM 
        ( SELECT user_id, sum(score) score FROM monthly_leaderboard m
        where (DATE(`last_updated`) BETWEEN date('".$from."') and date('".$to."')) GROUP BY user_id) s,
        (SELECT @rank := 0) init ORDER BY score DESC
    ) r 
    INNER join users u on u.id = r.user_id ORDER BY r.rank ASC LIMIT $offset,$limit";
    // echo $sql;
    $db->sql($sql);
    $res = $db->getResult();

    if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
        $user_id = $db->escapeString($_POST['user_id']);
        $sql = "SELECT monthly_leaderboard.*,u.name,u.profile,u.email, 
            FIND_IN_SET( score, (SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM monthly_leaderboard where (DATE(last_updated) BETWEEN date('".$from."') and date('".$to."')) )) AS rank 
            FROM monthly_leaderboard INNER JOIN `users` u ON monthly_leaderboard.`user_id` = u.id  
            where (DATE(last_updated) BETWEEN date('".$from."') and date('".$to."')) and user_id =".$user_id;
        $db->sql($sql);
        $my_rank = $db->getResult();
        if(!empty($my_rank)){
            if(filter_var($my_rank[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                // Not a valid URL. Its a image only or empty
                $my_rank[0]['profile'] = (!empty($my_rank[0]['profile']))?DOMAIN_URL.'uploads/profile/'.$my_rank[0]['profile']:'';
            }
            $user_rank['my_rank'] = $my_rank[0];
            array_unshift($res,$user_rank);
            // array_push($res,$user_rank);
            // $res = array_reverse($res);
        }else{
		    $my_rank = array(
		        'id' => $user_id,
		        'rank' => 0
		    );
		    $user_rank['my_rank'] = $my_rank;
		    array_unshift($res,$user_rank);
		}
    }

    if(!empty($res)){
        foreach($res as $row){
            if(isset($row['profile'])){
                if(filter_var($row['profile'], FILTER_VALIDATE_URL) === FALSE) {
                    // Not a valid URL. Its a image only or empty
                    $row['profile'] = (!empty($row['profile']))?DOMAIN_URL.'uploads/profile/'.$row['profile']:'';
                }
            }
            $tempRow[] = $row;
        }
        $response['error'] = "false";
        $response['total'] = $total[0]['total'];
        $response['data'] = $tempRow;
        print_r(json_encode($response));
    }else{
        $response['error'] = "true";
        $response['message'] = "Data not found";
        print_r(json_encode($response));
    }
}

// 20. get_global_leaderboard()
if(isset($_POST['access_key']) && isset($_POST['get_global_leaderboard'])){
    /* Parameters to be passed
        access_key:6808
        get_global_leaderboard:1
        offset:0        // {optional} - Starting position
        limit:20        // {optional} - number of records per page
        user_id:25		// to get current user's rank (optional) ( login user_id )
    */
    if($access_key != $_POST['access_key']){
        $response['error'] = "true";
        $response['message'] = "Invalid Access Key";
        print_r(json_encode($response));
        return false;
    }
    
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))?$db->escapeString($_POST['limit']):25;
	$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))?$db->escapeString($_POST['offset']):0;
	
	/* get the total no of records */
	$sql = "SELECT COUNT(m.id) as `total` FROM `monthly_leaderboard` m ";
	$db->sql($sql);
	$total = $db->getResult();
	
    $sql = "SELECT r.*,u.email,u.name,u.profile FROM (
		SELECT s.*, @rank := @rank + 1 rank FROM (
			SELECT user_id, sum(score) score FROM monthly_leaderboard m
			GROUP BY user_id) s,
			(SELECT @rank := 0) init
		ORDER BY score DESC) r 
		INNER join users u on u.id = r.user_id ORDER BY r.rank ASC LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();

    if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
        $user_id = $db->escapeString($_POST['user_id']);
        $sql = "SELECT r.*,u.email,u.name,u.profile FROM 
                (SELECT s.*, @rank := @rank + 1 rank 
                    FROM ( SELECT user_id, sum(score) score 
                    FROM monthly_leaderboard m GROUP BY user_id ) s,
                (SELECT @rank := 0) init ORDER BY score DESC ) r 
                    INNER join users u on u.id = r.user_id WHERE r.user_id =".$user_id;
        
        $db->sql($sql);
        $my_rank = $db->getResult();
        if(!empty($my_rank)){
            if(filter_var($my_rank[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                // Not a valid URL. Its a image only or empty
                $my_rank[0]['profile'] = (!empty($my_rank[0]['profile']))?DOMAIN_URL.'uploads/profile/'.$my_rank[0]['profile']:'';
            }
            
            $user_rank['my_rank'] = $my_rank[0];
            // array_push($res,$user_rank);
            array_unshift($res,$user_rank);
            // $res = array_reverse($res);
        }else{
            $my_rank = array(
                'id' => $user_id,
                'rank' => 0,
            );
            $user_rank['my_rank'] = $my_rank;
            array_unshift($res,$user_rank);
        }
    }

    if(!empty($res)){
        foreach($res as $row){
            if(isset($row['profile'])){
                if(filter_var($row['profile'], FILTER_VALIDATE_URL) === FALSE) {
                    // Not a valid URL. Its a image only or empty
                    $row['profile'] = (!empty($row['profile']))?DOMAIN_URL.'uploads/profile/'.$row['profile']:'';
                }
            }
            $tempRow[] = $row;
        }
        $response['error'] = "false";
        $response['total'] = $total[0]['total'];
        $response['data'] = $tempRow;
        print_r(json_encode($response));
    }else{
        $response['error'] = "true";
        $response['message'] = "Data not found";
        print_r(json_encode($response));
    }
}

// 21. get_system_configurations() - get user details
if(isset($_POST['access_key']) && isset($_POST['get_system_configurations'])){
	/* Parameters to be passed
		access_key:6808
		get_system_configurations:1
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(!empty($config)){
		$response['error'] = "false";
		$response['data'] = $config;
	}else{
		$response['error'] = "true";
		$response['message'] = "No configurations found yet!";
	}
	print_r(json_encode($response));
}

// 22. get_languages()
if(isset($_POST['access_key']) && isset($_POST['get_languages']) AND $_POST['get_languages']==1){
	/* Parameters to be passed
		access_key:6808
		get_languages:1
		id:1 // {optional}
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	if(isset($_POST['id']) && !empty($_POST['id'])){
		$id = $db->escapeString($_POST['id']);
		$sql = "SELECT * FROM `languages` WHERE `id`=".$id." ORDER BY id ASC";
	}else{
		$sql = "SELECT * FROM `languages` ORDER BY id ASC";
	}
	$db->sql($sql);
	$res = $db->getResult();
	if(!empty($res)) {
		$response['error'] = "false";
		$response['data'] = $res;
	}else{
		$response['error'] = "true";
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));
}

// 23. get_categories_by_language() - get categories list by language id 
if(isset($_POST['access_key']) && isset($_POST['get_categories_by_language'])){
	/* Parameters to be passed
		access_key:6808
		get_categories_by_language:1
		language_id:1
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['language_id']) && !empty($_POST['language_id'])){
		$language_id = $db->escapeString($_POST['language_id']);
		$sql = "SELECT *,(SELECT @no_of_subcategories := count(*) from subcategory s WHERE s.maincat_id = c.id and s.status = 1 ) as no_of, 
			(select `language` from `languages` l where l.id = c.language_id ) as language,
			if(@no_of_subcategories = 0, (SELECT @maxlevel := MAX(`level`) from question q WHERE c.id = q.category ),@maxlevel := 0) as `maxlevel` 
			FROM `category` c where `language_id` = ".$language_id." ORDER By CAST(c.row_order as unsigned) ASC";
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		if (!empty($result)) {
			for($i=0;$i<count($result);$i++){
				$result[$i]['image'] = (!empty($result[$i]['image']))?DOMAIN_URL.'images/category/'.$result[$i]['image']:'';
			}
			$response['error'] = "false";
			$response['data'] = $result;
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	}else{
		$response['error'] = "true";
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));
}

// 24. set_battle_statistics() 
if(isset($_POST['access_key']) && isset($_POST['set_battle_statistics'])){
	/* Parameters to be passed
		access_key:6808
		set_battle_statistics:1
		user_id1:709 
		user_id2:710
		winner_id:710
		is_drawn:0 / 1 (0->no_drawn,1->drawn)
		
	*/

		if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}

		$user_id1 = $db->escapeString($_POST['user_id1']);
		$user_id2 = $db->escapeString($_POST['user_id2']);
		$winner_id = $db->escapeString($_POST['winner_id']);
		$is_drawn = $db->escapeString($_POST['is_drawn']);

	if(isset($_POST['user_id1']) && isset($_POST['user_id2'])){

		$sql = "INSERT INTO `battle_statistics` (`user_id1`,`user_id2`,`is_drawn`,`winner_id`) VALUES ('".$user_id1."','".$user_id2."','".$is_drawn."','".$winner_id."')";
		$db->sql($sql);
		$result = $db->getResult();

		// if(isset($_POST['match_id'])){
		// 	$match_id = $db->escapeString($_POST['match_id']);
		//     $sql = "DELETE FROM `challenge_player` WHERE id =".$match_id;
		//     // echo $sql;
		//     $db->sql($sql);
	 //    }

		$response['error'] = "false";
			$response['message'] = " Insert successfully";
			print_r(json_encode($response));
	}else{
		$response['error'] = "true";
		$response['message'] = "Please fill all the data and submit!";
		print_r(json_encode($response));
	}	
}

// 25. get_battle_statistics() 
if(isset($_POST['access_key']) && isset($_POST['get_battle_statistics'])){
	/* Parameters to be passed
		access_key:6808
		get_battle_statistics:1
		user_id:12
		sort:id / is_drawn / winner_id // {optional} 
		order:DESC / ASC // {optional} 
		offset:0    // {optional} - Starting position
        limit:20    // {optional} - number of records per page
	*/
		
		if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}

	$user_id = $db->escapeString($_POST['user_id']);
	$limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))?$db->escapeString($_POST['limit']):5;
	$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))?$db->escapeString($_POST['offset']):0;
	
	$sort = (isset($_POST['sort']) && !empty($_POST['sort']))?$db->escapeString($_POST['sort']):'id';
	$order = (isset($_POST['order']) && !empty($_POST['order']))?$db->escapeString($_POST['order']):'DESC';
	
	if(isset($_POST['user_id'])){
		$sql = "SELECT 
		    (SELECT COUNT(*) FROM (SELECT DISTINCT `date_created` from `battle_statistics` WHERE winner_id = $user_id)as w ) AS Victories,
		    (SELECT COUNT(*) FROM (SELECT DISTINCT `date_created` from `battle_statistics` WHERE (user_id1= $user_id || user_id2= $user_id)AND is_drawn=1)as d) AS Drawn,
		    (SELECT COUNT(*) FROM (SELECT DISTINCT `date_created` from `battle_statistics` WHERE (user_id1= $user_id || user_id2= $user_id) AND winner_id != $user_id and is_drawn = 0)as l )AS Loose";
// 		$sql = "SELECT (SELECT COUNT(`winner_id`) FROM battle_statistics WHERE winner_id= $user_id) AS Victories,(SELECT COUNT(`is_drawn`) FROM battle_statistics WHERE (user_id1= $user_id || user_id2= $user_id)AND is_drawn=1) AS Drawn,(SELECT COUNT(`winner_id`) FROM battle_statistics WHERE (user_id1= $user_id || user_id2= $user_id) AND winner_id != $user_id and is_drawn = 0 )AS Loose";
// 		echo $sql;
		$db->sql($sql);
		$result = $db->getResult();
		$response['myreport'] = $result;
		
		
		$matches = $temp = array();
		$sql ="SELECT *,
		    (select `name` from users u WHERE u.id = m.user_id1 ) as user_1,
		    (select `name` from users u WHERE u.id = m.user_id2 ) as user_2, 
		    (select `profile` from users u WHERE u.id = m.user_id1 ) as user_profile1, 
		    (select `profile` from users u WHERE u.id = m.user_id2 ) as user_profile2 
		    FROM `battle_statistics` m where user_id1 = $user_id or user_id2 = $user_id GROUP BY `date_created` ORDER BY $sort $order limit $offset,$limit";
		  //  ECHO $sql;
		$db->sql($sql);
		$result = $db->getResult();
		if(!empty($result)){
		foreach ($result as $row) {
			$temp['opponent_id'] = ($row['user_id1'] == $user_id)?$row['user_id2']:$row['user_id1'];
			$temp['opponent_name'] = ($row['user_id1'] == $user_id)?$row['user_2']:$row['user_1'];
			$temp['opponent_profile'] = ($row['user_id1'] == $user_id)?$row['user_profile2']:$row['user_profile1'];
			if(!empty($temp['opponent_profile']) || $temp['opponent_profile'] != null ){
    			if(filter_var($temp['opponent_profile'], FILTER_VALIDATE_URL) === FALSE) {
    				// Not a valid URL. Its a image only or empty
    				$temp['opponent_profile'] = (!empty($temp['opponent_profile']))?DOMAIN_URL.'uploads/profile/'.$temp['opponent_profile']:'';
    			}else{
    				/* if it is a ur than just pass url as it is */
    				$temp['opponent_profile'] = $temp['opponent_profile'];
    			}
			}
			
			if( $row['is_drawn'] == 1 ){
				$temp['mystatus'] = "Draw";
			}else{
				$temp['mystatus'] = ($row['winner_id'] == $user_id)?"Won":"Lost";
			}
			$temp['date_created'] = $row['date_created'];
			$matches[] = $temp;
			}
			$response['error'] = "false";
			$response['data'] = $matches;
		}else{
			$response['error'] = "true";
			$response['message'] = "No matches played. Play the match now";
		}

		}
		print_r(json_encode($response));
}

// 26. set_users_statistics()
if(isset($_POST['access_key']) && isset($_POST['set_users_statistics'])){
	/* Parameters to be passed
		access_key:6808
		set_users_statistics:1
		user_id:10
		questions_answered:100
		correct_answers:10
		category_id:1 //(id of category which user played) 
		ratio: 50 // (In percenatge)
		coins:20 // {optional}
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	if(!empty($_POST['access_key']) && !empty($_POST['user_id']) && !empty($_POST['category_id']) && !empty($_POST['ratio'])){
		// print_r($_POST);

		$user_id = $db->escapeString($_POST['user_id']);
		$questions_answered = $db->escapeString($_POST['questions_answered']);
		$correct_answers = $db->escapeString($_POST['correct_answers']);
		$category_id = $db->escapeString($_POST['category_id']);
		$ratio = $db->escapeString($_POST['ratio']);
		
		// update users coins if set
		if(isset($_POST['coins']) && !empty($_POST['coins']) && is_numeric($_POST['coins'])){
		    $coins = $db->escapeString($_POST['coins']);
		    $sql = "UPDATE `users` SET `coins` = $coins  WHERE id = ".$user_id;
		    $db->sql($sql);
		}

		$sql = "SELECT * FROM `users_statistics` WHERE `user_id`=".$user_id."";
		$db->sql($sql);
		$result1 = $db->getResult();
// 		print_r($result1);
		
	    if(!empty($result1)){
	    	$qa = $result1[0]['questions_answered'];
	    	$ca = $result1[0]['correct_answers']; 
	    	$sc = $result1[0]['strong_category'];
	    	$r1 = $result1[0]['ratio1'];
	    	$wc = $result1[0]['weak_category'];
	    	$r2 = $result1[0]['ratio2'];
	    	$bp = $result1[0]['best_position'];

		    $sql = "SELECT r.* FROM 
		            (SELECT s.*, @rank := @rank + 1 rank 
		                FROM ( SELECT user_id, sum(score) score 
		                FROM monthly_leaderboard m GROUP BY user_id ) s,
		            (SELECT @rank := 0) init ORDER BY score DESC ) r 
		                INNER join users u on u.id = r.user_id WHERE r.user_id =".$user_id;
		    $db->sql($sql);
		    $my_rank = $db->getResult();
		    $rank = $my_rank[0]['rank'];
		    if($rank < $bp || $bp == 0 ){
		    	$bp = $rank;
		    	$sql = "UPDATE `users_statistics` SET `best_position`= '".$bp."' WHERE user_id=".$user_id;
				$db->sql($sql);
		    }

	    	if($ratio > 50){
	    	    /* update strong category */
	    	    /* when ratio is > 50 he is strong in this particular category */
    			$sql = "UPDATE `users_statistics` SET `questions_answered`= `questions_answered` + '".$questions_answered."', `correct_answers`= `correct_answers` + '".$correct_answers."',"; 
				$sql .= ( $ratio > $r1 || $sc == 0 )?"`strong_category`= '".$category_id."', `ratio1`= '".$ratio."', " : "";
				$sql .= ( $wc == $category_id )?"`weak_category`= '0', " : "";
				$sql .= "`best_position`= '".$bp."' WHERE user_id=".$user_id;
				$db->sql($sql);
				$response['error'] = "false";
				$response['message'] = "Strong Updated successfully";
	    	}else{
	    	    /* update weak category */
	    	    /* when ratio is < 50 he is weak in this particular category */ 
    			$sql = "UPDATE `users_statistics` SET `questions_answered`= `questions_answered` + '".$questions_answered."', `correct_answers`= `correct_answers` + '".$correct_answers."',"; 
				$sql .= ( $ratio < $r2 || $wc == 0 )?"`weak_category`= '".$category_id."',`ratio2`= '".$ratio."',":"";
				$sql .= ( $sc == $category_id )?"`strong_category`= '0', " : "";
				$sql .= " `best_position`= '".$bp."' WHERE user_id=".$user_id;
				$db->sql($sql);
				$response['error'] = "false";
				$response['message'] = "Weak Updated successfully";
	    	}
        }else{
        	if($ratio > 50){
	    		$sql = "INSERT INTO `users_statistics` (`user_id`, `questions_answered`,`correct_answers`, `strong_category`, `ratio1`, `weak_category`, `ratio2`, `best_position`) VALUES ('".$user_id."','".$questions_answered."','".$correct_answers."','".$category_id."','".$ratio."','0','0','0')";
          		$db->sql($sql);
          		$response['error'] = "false";
				$response['message'] = "Strong inserted successfully";
	    	}else{
	    		$sql = "INSERT INTO `users_statistics` (`user_id`, `questions_answered`,`correct_answers`, `strong_category`, `ratio1`, `weak_category`, `ratio2`, `best_position`) VALUES ('".$user_id."','".$questions_answered."','".$correct_answers."','0','0','".$category_id."','".$ratio."','0')";
          		$db->sql($sql);
          		$response['error'] = "false";
				$response['message'] = "Weak inserted successfully";
	    	}
        }
        
	}else{
		$response['error'] = "true";
		$response['message'] = "Please pass all the fields";
	}
	print_r(json_encode($response));
}

// 27. get_users_statistics()
if(isset($_POST['access_key']) && isset($_POST['get_users_statistics'])){
	/* Parameters to be passed
		access_key:6808
		get_users_statistics:1
		user_id:31
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	if(isset($_POST['user_id'])){
		$user_id = $db->escapeString($_POST['user_id']);
		$sql = "SELECT us.*,u.name,u.profile,(SELECT category_name FROM category c WHERE c.id=us.strong_category) as strong_category, (SELECT category_name FROM category c WHERE c.id=us.weak_category) as weak_category FROM `users_statistics` us
			LEFT JOIN users u on u.id = us.user_id WHERE `user_id`=".$user_id;
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		// return false;
		if (!empty($result)) {
			if (filter_var($result[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
				// Not a valid URL. Its a image only or empty
				$result[0]['profile'] = (!empty($result[0]['profile']))?DOMAIN_URL.'uploads/profile/'.$result[0]['profile']:'';
			
			}else{
				/* if it is a ur than just pass url as it is */
				$result[0]['profile'] = $result[0]['profile'];
			}
			$response['error'] = "false";
			$response['data'] = $result[0];
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	}else{
		$response['error'] = "true";
		$response['message'] = "Please Pass all the fields!";
	}
	print_r(json_encode($response));
}

// 28. get_notifications() 
if(isset($_POST['access_key']) && isset($_POST['get_notifications'])){
	/* Parameters to be passed
		access_key:6808
		get_notifications:1
		sort:id / users / type // {optional} 
		order:DESC / ASC // {optional} 
		offset:0    // {optional} - Starting position
        limit:20    // {optional} - number of records per page
	*/
		
		if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}

	$limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))?$db->escapeString($_POST['limit']):10;
	$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))?$db->escapeString($_POST['offset']):0;
	
	$sort = (isset($_POST['sort']) && !empty($_POST['sort']))?$db->escapeString($_POST['sort']):'id';
	$order = (isset($_POST['order']) && !empty($_POST['order']))?$db->escapeString($_POST['order']):'DESC';
	
	$sql ="SELECT * FROM `notifications` m where users = 'all' ORDER BY $sort $order limit $offset,$limit";
	$db->sql($sql);
	$result = $db->getResult();
	if(!empty($result)){
    	for($i=0;$i<count($result);$i++) {
    		if(filter_var($result[$i]['image'], FILTER_VALIDATE_URL) === FALSE) {
    		    /* Not a valid URL. Its a image only or empty */
    			$result[$i]['image'] = (!empty($result[$i]['image']))?DOMAIN_URL.'images/notifications/'.$result[$i]['image']:'';
    		}else{
    		    /* if it is a ur than just pass url as it is */
    		    $result[$i]['image'] = $result[$i]['image'];
    		}
    	}
    	$response['error'] = "false";
    	$response['data'] = $result;
	}else{
		$response['error'] = "true";
		$response['message'] = "No notifications to read.";
	}
	print_r(json_encode($response));
}

// 29. get_about_us()
if(isset($_POST['access_key']) && isset($_POST['get_about_us']) AND $_POST['get_about_us']==1){
	/* Parameters to be passed
		access_key:6808
		get_about_us:1
	*/
	if($access_key != $_POST['access_key']){
		$response['error'] = "true";
		$response['message'] = "Invalid Access Key";
		print_r(json_encode($response));
		return false;
	}
	
	if(!empty($_POST['access_key'])){
		$sql = "SELECT * FROM `settings` WHERE type='about_us'";
        $db->sql($sql);
		$res = $db->getResult();
		if(!empty($res)) {
			$response['error'] = "false";
			$response['data'] = $res[0]['message'];
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	}else{
		$response['error'] = "true";
		$response['message'] = "Please pass all the fields";
	}
	print_r(json_encode($response));
}


function get_fcm_id($user_id){
    $db = new Database();
    $db->connect();
    
    $sql = "SELECT `fcm_id` FROM `users` where `id` = ".$user_id;
    $db->sql($sql);
    $res = $db->getResult();
    return $res[0]['fcm_id'];
}

function checkBattleExists($match_id){
    $db = new Database();
    $db->connect();
    
    $sql = "SELECT `id` FROM `battle_questions` where `match_id` = '".$match_id."'";
    $db->sql($sql);
    $res = $db->getResult();
    return $res;
    if(empty($res)){
        return false;
    }else{
        return true;
    }
}

/*firebase send message function*/
function send_message($token,$data){
    $db = new Database();
    $db->connect();
    
	$sql = 'select `fcm_key` from `tbl_fcm_key` where id=1';
	$db->sql($sql);
	$res = $db->getResult();
	
	define('API_ACCESS_KEY', $res[0]['fcm_key']);
	
	$fcmFields = array(
		//  'registration_ids' => $token,  // expects an array of ids
// 		'registration_ids' => array($token),
		'to' => $token,
		'priority' => 'high',
		'data' => $data
	);
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
	
	$result = json_decode($result,1);
	return $result;
}



function utf8ize($d) {
	if (is_array($d)) {
		foreach ($d as $k => $v) {
			$d[$k] = utf8ize($v);
		}
	} else if (is_string ($d)) {
		return utf8_encode($d);
	}
	return $d;
	}









?>