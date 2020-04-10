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

/*
	1. category
	2. subcategory
	3. users
	4. monthly_leaderboard
	5. admin
	6. question
	7. question_reports
	8. notifications
	9. languages
	10. battle_statistics
*/


// 1. category
if(isset($_GET['table']) && $_GET['table'] == 'category'){
	$offset = 0;$limit = 10;
	$sort = '`row_order` + 0 '; $order = 'ASC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_GET['sort'])){
		$sort = $_GET['sort'];
		if($sort == 'row_order')
			$sort = '`row_order` + 0 ';
	}
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
		
	if(isset($_GET['language']) && !empty($_GET['language'])){
		$where = 'where `language_id` = '.$_GET['language'];
	}
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = " where ( c.`id` like '%".$search."%' OR c.`category_name` like '%".$search."%' OR l.`language` like '%".$search."%' )";
    	if(isset($_GET['language']) && !empty($_GET['language'])){
    		$where .= ' and `language_id` = '.$_GET['language'];
    	}
	}
	
	$left_join = " LEFT JOIN languages l on l.id = c.language_id ";
	
	$sql = "SELECT COUNT(c.id) as total FROM `category` c ".$left_join." ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT c.*,l.language as language FROM `category` c ".$left_join." ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	
	foreach($res as $row){
		$image = (!empty($row['image']))?'images/category/'.$row['image']:'';
		$operate = "<a class='btn btn-xs btn-primary edit-category' data-id='".$row['id']."' data-toggle='modal' data-target='#editCategoryModal' title='Edit'><i class='fas fa-edit'></i></a>";
		$operate .= "<a class='btn btn-xs btn-danger delete-category' data-id='".$row['id']."' data-image='".$image."' title='Delete'><i class='fas fa-trash'></i></a>";
		
		$tempRow['id'] = $row['id'];
		$tempRow['language'] = $row['language'];
		$tempRow['language_id'] = $row['language_id'];
		$tempRow['category_name'] = $row['category_name'];
		$tempRow['row_order'] = $row['row_order'];
		$tempRow['image'] = (!empty($row['image']))?'<a href="images/category/'.$row['image'].'" data-lightbox="Category Images"><img src="images/category/'.$row['image'].'" height=30 ></a>':'<img src="images/logo-half.png" height=30>';
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 2. subcategory
if(isset($_GET['table']) && $_GET['table'] == 'subcategory'){
	$offset = 0;$limit = 10;
	$sort = 'row_order'; $order = 'ASC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_GET['sort'])){
		$sort = $_GET['sort'];
		if($sort == 'row_order')
			$sort = 's.`row_order` + 0 ';
	}
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
		
	if(isset($_GET['language']) && !empty($_GET['language'])){
		$where = ' where s.`language_id` = '.$_GET['language'];
		if(isset($_GET['category']) && !empty($_GET['category'])){
			$where .= ' and `maincat_id`='.$_GET['category'];
		}
	}elseif(isset($_GET['category']) && !empty($_GET['category'])){
		$where .= ' where `maincat_id`='.$_GET['category'];
	}
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = " where (s.`id` like '%".$search."%' OR s.`maincat_id` like '%".$search."%' OR s.`subcategory_name` like '%".$search."%' OR l.`language` like '%".$search."%' OR c.`category_name` like '%".$search."%' )";
	    if(isset($_GET['language']) && !empty($_GET['language'])){
		$where .= ' and s.`language_id` = '.$_GET['language'];
    		if(isset($_GET['category']) && !empty($_GET['category'])){
    			$where .= ' and `maincat_id`='.$_GET['category'];
    		}
    	}elseif(isset($_GET['category']) && !empty($_GET['category'])){
    		$where .= ' and `maincat_id`='.$_GET['category'];
    	}
	    
	}
	
	$left_join = " LEFT JOIN languages l on l.id = s.language_id LEFT JOIN category c ON c.id = s.maincat_id ";
	
	$sql = "SELECT COUNT(s.id) as total FROM `subcategory` s ".$left_join." ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT s.*,l.language,c.`category_name` FROM `subcategory` s ".$left_join." ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
// 	echo $sql;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	
	foreach($res as $row){
		$image = (!empty($row['image']))?'images/subcategory/'.$row['image']:'';
		$operate = "<a class='btn btn-xs btn-primary edit-subcategory' data-id='".$row['id']."' data-toggle='modal' data-target='#editCategoryModal' title='Edit'><i class='fas fa-edit'></i></a>";
		$operate .= "<a class='btn btn-xs btn-danger delete-subcategory' data-id='".$row['id']."' data-image='".$image."' title='Delete'><i class='fas fa-trash'></i></a>";
		
		$tempRow['id'] = $row['id'];
		$tempRow['language_id'] = $row['language_id'];
		$tempRow['language'] = $row['language'];
		$tempRow['maincat_id'] = $row['maincat_id'];
		$tempRow['category_name'] = $row['category_name'];
		$tempRow['subcategory_name'] = $row['subcategory_name'];
		$tempRow['row_order'] = $row['row_order'];
		$tempRow['image'] = (!empty($row['image']))?'<a href="images/subcategory/'.$row['image'].'" data-lightbox="Sub Category Images"><img src="images/subcategory/'.$row['image'].'" height=30 ></a>':'<img src="images/logo-half.png" height=30>';
		$tempRow['status'] = ($row['status'])?'<label class="label label-success">Active</label>':'<label class="label label-danger">Deactive</label>';
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 3. users
if(isset($_GET['table']) && $_GET['table'] == 'users'){
	$offset = 0;$limit = 10;
	$sort = 'id'; $order = 'DESC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_GET['sort']))
		$sort = $_GET['sort'];
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	
	if(isset($_GET['sort']))
		$sort = $_GET['sort'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
	
	if(isset($_GET['status'])){
		$status = $_GET['status'];
		if($_GET['status']!= '')
			$where = "where (`status` = ".$status.")";
	}
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		if(isset($_GET['status']) && $_GET['status']!= '')
			$where .= " AND (`id` like '%".$search."%' OR `name` like '%".$search."%' OR `mobile` like '%".$search."%' OR `email` like '%".$search."%' OR `date_registered` like '%".$search."%' )";
		else
			$where = " where (`id` like '%".$search."%' OR `name` like '%".$search."%' OR `mobile` like '%".$search."%' OR `email` like '%".$search."%' OR `date_registered` like '%".$search."%' )";
	}
	
	$sql = "SELECT COUNT(*) as total FROM `users` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT * FROM `users` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	$icon = array(
		'email' => 'far fa-envelope-open', 
		'gmail' => 'fab fa-google-plus-square text-danger',
		'fb' => 'fab fa-facebook-square text-primary'
	);
	 
	foreach($res as $row){
		$operate = "<a class='btn btn-xs btn-primary edit-users' data-id='".$row['id']."' data-toggle='modal' data-target='#editUserModal' title='Edit'><i class='far fa-edit'></i></a>";
		$operate .= "<a class='btn btn-xs btn-success' href='monthly-leaderboard-user.php?user_id=".$row['id']."' target='_blank' title='Monthly Leaderboard'><i class='fas fa-th'></i></a>";
		
		$operate .= "<a class='btn btn-xs btn-warning' href='battle-statistics.php?user_id=".$row['id']."' target='_blank' title='User Statistics'><i class='far fa-chart-bar'></i></a>";
		
		if (filter_var($row['profile'], FILTER_VALIDATE_URL) === FALSE) {
		    // Not a valid URL. Its a image only or empty
		    $tempRow['profile'] = (!empty($row['profile']))?DOMAIN_URL.'uploads/profile/'.$row['profile']:'';
		
		}else{
		    /* if it is a ur than just pass url as it is */
            $tempRow['profile'] = $row['profile'];
        }
		
		$tempRow['id'] = $row['id'];
		$tempRow['profile'] = (!empty($tempRow['profile']))?"<a data-lightbox='Profile Picture' href='".$tempRow['profile']."'><img src='".$tempRow['profile']."' width='80'/></a>":"No Image";
		$tempRow['name'] = $row['name'];
		$tempRow['email'] = $row['email'];
		$tempRow['mobile'] = $row['mobile'];
		$tempRow['type'] = (isset($row['type']) && $row['type'] !='')?'<i class="'.$icon[trim($row['type'])].' fa-2x"></i>':'<i class="'.$icon['email'].' fa-2x"></i>';
		$tempRow['fcm_id'] = $row['fcm_id'];
		$tempRow['coins'] = $row['coins'];
		$tempRow['refer_code'] = $row['refer_code'];
		$tempRow['friends_code'] = $row['friends_code'];
		$tempRow['ip_address'] = $row['ip_address'];
		$tempRow['date_registered'] = date('d-M-Y h:i A',strtotime($row['date_registered']));
		$tempRow['status'] = ($row['status'])?"<label class='label label-success'>Active</label>":"<label class='label label-danger'>Deactive</label>";
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 4. monthly_leaderboard
if(isset($_GET['table']) && $_GET['table'] == 'monthly_leaderboard'){
	$offset = 0;$limit = 10;
	$sort = 'monthly_leaderboard.score'; $order = 'DESC';
	$where = $where_sub = '';
	$table = $_GET['table'];
	
	if(isset($_POST['id']))
		$id = $_POST['id'];
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	if(isset($_GET['sort']))
		$sort = $_GET['sort'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
	if(isset($_GET['user_id'])){
		$user_id = $_GET['user_id'];
		if($_GET['user_id']!= '')
			$where = "WHERE user_id=".$user_id;
	}

	if(isset($_GET['date'])){
		$date = $_GET['date'];
		if($_GET['date']!= '')
			$where = "WHERE ( MONTH(monthly_leaderboard.date_created) = MONTH('".$date."') AND YEAR(monthly_leaderboard.date_created) = YEAR('".$date."') ) ";
			$where_sub = "WHERE ( MONTH(monthly_leaderboard.date_created) = MONTH('".$date."') AND YEAR(monthly_leaderboard.date_created) = YEAR('".$date."') ) ";
	}
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		if($_GET['date']!= '')
			$where .= " AND (monthly_leaderboard.id like '%".$search."%' OR users.name like '%".$search."%' OR users.email like '%".$search."%' )";
		else
			$where = " WHERE (monthly_leaderboard.id like '%".$search."%' OR users.name like '%".$search."%' OR users.email like '%".$search."%' )";
	}
	
	$sql = "SELECT COUNT(*) AS total FROM `monthly_leaderboard` INNER JOIN users ON monthly_leaderboard.user_id=users.id ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT monthly_leaderboard.*,users.name,users.email,FIND_IN_SET( score, (SELECT GROUP_CONCAT( score ORDER BY score DESC ) FROM monthly_leaderboard ".$where_sub." )) AS rank FROM `monthly_leaderboard` INNER JOIN users ON monthly_leaderboard.user_id=users.id ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();

	foreach($res as $row){
		//$operate = "<a class='btn btn-xs btn-primary edit-users' data-id='".$row['id']."' data-toggle='modal' data-target='#editUserModal' title='Edit'><i class='far fa-edit'></i></a>";
		
		$tempRow['id'] = $row['id'];
		$tempRow['name'] = $row['name'];
		$tempRow['email'] = $row['email'];
		$tempRow['user_id'] = $row['user_id'];
		$tempRow['score'] = $row['score'];
		$tempRow['rank'] = $row['rank'];
		$tempRow['last_updated'] = date("d-m-Y H:m:s", strtotime($row['last_updated']));
		$tempRow['date_created'] = date("d-m-Y H:m:s", strtotime($row['date_created']));
		//$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 5. admin
if(isset($_GET['table']) && $_GET['table'] == 'admin'){
	session_start();
	$username = $_SESSION['username'];
	$offset = 0;$limit = 10;
	$sort = 'auth_username'; $order = 'DESC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	
	if(isset($_GET['sort']))
		$sort = $_GET['sort'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = "where (`auth_username` like '%".$search."%' OR `role` like '%".$search."%')";
	}/*else{
		$where = "where `auth_username` != '$username' AND `role` != 'admin'";
	}*/
	
	$sql = "SELECT COUNT(*) as total FROM `authenticate` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}

	$sql = "SELECT * FROM `authenticate` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	
	foreach($res as $row){
		$operate = "<a class='btn btn-xs btn-primary edit-admin' data-id='".$row['auth_username']."'- data-toggle='modal' data-target='#editAdminModal' title='Edit'><i class='fas fa-edit'></i></a>";
		$operate .= "<a class='btn btn-xs btn-danger delete-admin' data-id=".$row['auth_username']."  title='Delete'><i class='fas fa-trash'></i></a>";
		
		$tempRow['auth_username'] = $row['auth_username'];
		$tempRow['role'] = $row['role'];
		$tempRow['created'] = $row['created'];
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 6. question
if(isset($_GET['table']) && $_GET['table'] == 'question'){
	$offset = 0;$limit = 10;
	$sort = 'q.id'; $order = 'DESC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_POST['id']))
		$id = $_POST['id'];
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	
	if(isset($_GET['sort'])){
		$sort = ($_GET['sort'] == 'id')?"q.".$_GET['sort']:$_GET['sort'];
	}
	
	if(isset($_GET['order']))
		$order = $_GET['order'];
		
	if(isset($_GET['language']) && !empty($_GET['language'])){
		$where = 'where `language_id` = '.$_GET['language'];
		if(isset($_GET['category']) && !empty($_GET['category'])){
			$where .= ' and `category`='.$_GET['category'];
			if(isset($_GET['subcategory']) && !empty($_GET['subcategory'])){
    			$where .= ' and `subcategory`='.$_GET['subcategory'];
    		}
		}
	}elseif(isset($_GET['category']) && !empty($_GET['category'])){
		$where = 'where `category` = '.$_GET['category'];
		if(isset($_GET['subcategory']) && !empty($_GET['subcategory'])){
			$where .= ' and `subcategory`='.$_GET['subcategory'];
		}
	}
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = " where (q.`id` like '%".$search."%' OR `question` like '%".$search."%' OR `optiona` like '%".$search."%' OR `optionb` like '%".$search."%' OR `optionc` like '%".$search."%' OR `optiond` like '%".$search."%' OR `answer` like '%".$search."%' )";
	    if(isset($_GET['language']) && !empty($_GET['language'])){
    		$where .= ' and `language_id` = '.$_GET['language'];
    		if(isset($_GET['category']) && !empty($_GET['category'])){
    			$where .= ' and `category`='.$_GET['category'];
    			if(isset($_GET['subcategory']) && !empty($_GET['subcategory'])){
        			$where .= ' and `subcategory`='.$_GET['subcategory'];
        		}
    		}
    	}elseif(isset($_GET['category']) && !empty($_GET['category'])){
    		$where .= ' and `category` = '.$_GET['category'];
    		if(isset($_GET['subcategory']) && !empty($_GET['subcategory'])){
    			$where .= ' and `subcategory`='.$_GET['subcategory'];
    		}
    	}
	}
	
	$left_join = " LEFT JOIN languages l on l.id = q.language_id ";
	
	$sql = "SELECT COUNT(q.id) as total FROM `question` q ".$left_join." ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT q.*,l.language FROM `question` q ".$left_join." ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	// echo $sql;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	
	foreach($res as $row){
		$image = (!empty($row['image']))?'images/questions/'.$row['image']:'';
		$operate = "<a class='btn btn-xs btn-primary edit-question' data-id='".$row['id']."' data-toggle='modal' data-target='#editQuestionModal' title='Edit'><i class='fas fa-edit'></i></a>";
		$operate .= "<a class='btn btn-xs btn-danger delete-question' data-id='".$row['id']."' data-image='".$image."' title='Delete'><i class='fas fa-trash'></i></a>";
		
		$tempRow['id'] = $row['id'];
		$tempRow['category'] = $row['category'];
		$tempRow['subcategory'] = $row['subcategory'];
		$tempRow['language_id'] = $row['language_id'];
		$tempRow['language'] = $row['language'];
		$tempRow['image'] = (!empty($row['image']))?'<a data-lightbox="Question-Image" href="images/questions/'.$row['image'].'" data-caption="'.$row['question'].'"><img src="images/questions/'.$row['image'].'" height=30 ></a>':'No image';
		$tempRow['question'] = $row['question'];
		$tempRow['optiona'] = $row['optiona'];
		$tempRow['optionb'] = $row['optionb'];
		$tempRow['optionc'] = $row['optionc'];
		$tempRow['optiond'] = $row['optiond'];
		$tempRow['optione'] = $row['optione'];
		$tempRow['answer'] = $row['answer'];
		$tempRow['level'] = $row['level'];
		$tempRow['note'] = $row['note'];
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 7. question_reports
if(isset($_GET['table']) && $_GET['table'] == 'question_reports'){
	$offset = 0;$limit = 10;
	$sort = 'id'; $order = 'DESC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_POST['id']))
		$id = $_POST['id'];
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	
	if(isset($_GET['sort']))
		$sort = $_GET['sort'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = " where (`id` like '%".$search."%' OR `username` like '%".$search."%' OR `payment_address` like '%".$search."%' OR `request_type` like '%".$search."%' OR `request_amount` like '%".$search."%' OR `points_used` like '%".$search."%' OR `date` like '%".$search."%' )";
	}
	
	$sql = "SELECT COUNT(*) as total FROM `question_reports` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT *,(select `question` from `question` where `question_reports`.`question_id` = `question`.`id` ) as `question` FROM `question_reports` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	
	foreach($res as $row){
		$operate = "<a class='btn btn-xs btn-danger delete-report' data-id='".$row['id']."' title='Delete'><i class='fas fa-trash'></i></a>";
		
		$tempRow['id'] = $row['id'];
		$tempRow['question_id'] = $row['question_id'];
		$tempRow['question'] = $row['question'];
		$tempRow['message'] = $row['message'];
		$tempRow['date'] = $row['date'];
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 8. notifications
if(isset($_GET['table']) && $_GET['table'] == 'notifications'){
	$offset = 0;$limit = 10;
	$sort = 'id'; $order = 'DESC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_POST['id']))
		$id = $_POST['id'];
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	
	if(isset($_GET['sort']))
		$sort = $_GET['sort'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = " where (`id` like '%".$search."%' OR `name` like '%".$search."%' OR `description` like '%".$search."%' OR `url` like '%".$search."%')";
	}
	
	$sql = "SELECT COUNT(`id`) as total FROM `notifications` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT * FROM `notifications` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	
	foreach($res as $row){
		$operate = "<a class='btn btn-xs btn-danger delete-notification' data-id='".$row['id']."' data-image='".$row['image']."' title='Delete Notification'><i class='fas fa-trash'></i></a>";
		
		$tempRow['id'] = $row['id'];
		$tempRow['title'] = $row['title'];
		$tempRow['message'] = $row['message'];
		$tempRow['image'] = ($row['image']!='')?"<a data-lightbox='notification' href='images/notifications/".$row['image']."' data-caption='".$row['title']."'><img src='images/notifications/".$row['image']."' title='".$row['title']."' width='80'/></a>":'no image';
		$tempRow['users'] = ucwords($row['users']);
		$tempRow['type'] = ucwords($row['type']);
		$tempRow['type_id'] = ucwords($row['type_id']);
		$tempRow['date_sent'] = $row['date_sent'];
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 9. languages
if(isset($_GET['table']) && $_GET['table'] == 'languages'){
	$offset = 0;$limit = 10;
	$sort = 'id'; $order = 'ASC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_POST['id']))
		$id = $_POST['id'];
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = " where (`id` like '%".$search."%' OR `lang` like '%".$search."%' )";
	}
	
	$sql = "SELECT COUNT(*) as total FROM `languages` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}
	
	$sql = "SELECT * FROM `languages` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();
	
	foreach($res as $row){
		$operate = "<a class='btn btn-xs btn-primary edit-language' data-id='".$row['id']."' data-toggle='modal' data-target='#editlanguageModal' title='Edit'><i class='fas fa-edit'></i></a>";
		$operate .= "<a class='btn btn-xs btn-danger delete-language' data-id='".$row['id']."' title='Delete'><i class='fas fa-trash'></i></a>";
		
		$tempRow['id'] = $row['id'];
		$tempRow['language'] = $row['language'];
		$tempRow['status'] = ($row['status'] == 1)?"<label class='label label-success'>Enabled</label>":"<label class='label label-warning'>Disabled</label>";
		$tempRow['operate'] = $operate;
		$rows[] = $tempRow;
	}
	
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}

// 10. battle_statistics
if(isset($_GET['table']) && $_GET['table'] == 'battle_statistics'){
	$offset = 0; $limit = 10;
	$sort = 'id'; $order = 'DESC';
	$where = '';
	$table = $_GET['table'];
	
	if(isset($_GET['offset']))
		$offset = $_GET['offset'];
	if(isset($_GET['limit']))
		$limit = $_GET['limit'];
	
	if(isset($_GET['sort']))
		$sort = $_GET['sort'];
	if(isset($_GET['order']))
		$order = $_GET['order'];
	
	if(isset($_GET['search'])){
		$search = $_GET['search'];
		$where = " where (`id` like '%".$search."%' OR `user_1` like '%".$search."%' OR `user_2` like '%".$search."%' OR `name` like '%".$search."%')";
	}
	
	$sql = "SELECT COUNT(`id`) as total FROM `battle_statistics` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row){
		$total = $row['total'];
	}

	$bulkData = array();
	$bulkData['total'] = $total;
	$rows = array();
	$tempRow = array();

	if(isset($_GET['user_id'])){
		$user_id = $db->escapeString($_GET['user_id']);
		$where = "where user_id1 = $user_id or user_id2 = $user_id";
	}

		$sql ="SELECT *,(select `name` from users u WHERE u.id = m.user_id1 ) as user_1,(select `name` from users u WHERE u.id = m.user_id2 ) as user_2 FROM `battle_statistics` m ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		// echo $sql;
		$db->sql($sql);
		$result = $db->getResult();
		// print_r($result);
		// return false;

		if(!empty($result)){
		foreach ($result as $row) {
			$tempRow['id'] = $row['id'];
			$tempRow['opponent_id'] = ($row['user_id1'] == $user_id)?$row['user_id2']:$row['user_id1'];
			$tempRow['opponent_name'] = ($row['user_id1'] == $user_id)?$row['user_2']:$row['user_1'];

			if( $row['is_drawn'] == 1 ){
				$tempRow['mystatus'] = "Draw";
			}else{
				$tempRow['mystatus'] = ($row['winner_id'] == $user_id)?"Won":"Lost";
			}
			$rows[] = $tempRow;
			}	

		}
	// print_r($rows);
	$bulkData['rows'] = $rows;
	print_r(json_encode($bulkData));
}
?>