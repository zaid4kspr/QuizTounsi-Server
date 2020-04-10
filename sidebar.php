<?php
include 'library/crud.php';
include 'library/functions.php';
$fn = new Functions();

$db = new Database();
$db->connect();
function get_count($field,$table,$where = ''){
if(!empty($where))
	$where = "where ".$where;

$sql = "SELECT COUNT(".$field.") as total FROM ".$table." ".$where;
global $db;
$db->sql($sql);
$res = $db->getResult();
foreach($res as $row)
return $row['total'];
}
$auth_username = $db->escapeString($_SESSION["username"]);
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
if(!checkadmin($auth_username)){
    $pages = array('languages.php','users.php','monthly-leaderboard.php','send-notifications.php','user-accounts-rights.php','notification-settings.php','privacy-policy.php');
    foreach($pages as $page){
        if(basename($_SERVER['PHP_SELF'])==$page){
             exit("<center><h2 style='color:#fff;'><br><br><br><br><i style='color:#f7d701;' class='fas fa-exclamation-triangle fa-4x'></i><br><br>Access denied - You are not authorized to access this.</h2></center>");
        }
    }
}
if( basename($_SERVER['PHP_SELF']) == 'languages.php' && !$fn->is_language_mode_enabled()){
	exit("<center><h2 style='color:#fff;'><br><br><br><br><i style='color:#f7d701;' class='fas fa-exclamation-triangle fa-4x'></i><br><br>Language mode is disabled - You are not allowed to access this page.</h2></center>");
}
?>
<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title text-center" style="border: 0;">
            <img src="images/logo-460x114.png" width="230" class="md">
			<img src="images/logo-half.png" width="56" class="sm">
            <!--<a href="home.php" class="site_title"><i class="fa fa-building"></i> <span>Shree Ram</span></a>-->
        </div>
        <div class="clearfix"></div>
        <!-- menu profile quick info -->
        <div class="profile clearfix">
            <div class="profile_pic">
            </div>
            <div class="profile_info">
                <h2> Admin Panel</h2>
            </div>
        </div>
        <!-- /menu profile quick info -->
        <br />
        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <h3><?=ucwords($_SESSION['company_name'])?></h3>
                <ul class="nav side-menu">
                    <li><a href ="home.php"><i class="fas fa-home"></i> Home</a></li>
                    <?php if(checkadmin($auth_username)){ ?>
                    <li><a href ="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href ="monthly-leaderboard.php"><i class="fas fa-th"></i> Monthly Leaderboard</a></li>
					<?php if($fn->is_language_mode_enabled()){?>
						<li><a href="languages.php"><i class="fas fa-language"></i> Languages</a></li>
					<?php }
					} ?>
                    <li>
						<a><i class="fas fa-gift"></i> Categories<span class="fas fa-caret-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="main-category.php">Main Category</a></li>
                            <li><a href="sub-category.php">Sub Category</a></li>
                            <li><a href="category-order.php">Category Order</a></li>
                        </ul>
                    </li>
					<li><a href="questions.php"><i class="fas fa-trophy"></i> Questions</a></li>
					<li><a href="question-reports.php"><i class="far fa-question-circle"></i> Question Reports</a></li>
                    <?php if(checkadmin($auth_username)){ ?>
					<li><a href="send-notifications.php"><i class="fas fa-bullhorn"></i> Send Notifications</a></li>
                    <?php } ?>
					<li><a href="import-questions.php"><i class="fas fa-upload"></i> Import Questions</a></li>
                    <?php if(checkadmin($auth_username)){ ?>
                    <li><a href="user-accounts-rights.php"><i class="fas fa-user"></i> User Accounts and Rights</a></li>
                    <li>
                        <a><i class="fas fa-cog"></i> Settings<span class="fas fa-caret-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="system-configurations.php">System Configurations</a></li>
                            <li><a href="notification-settings.php">Notification Settings</a></li>
                            <li><a href="about-us.php">About Us</a></li>
                            <li><a href="privacy-policy.php">Privacy Policy</a></li>
                            <li><a href="terms-conditions.php">Terms Conditions</a></li>
                        </ul>
                    </li>
                    <?php } ?>
                </ul>
            </div>
		</div>
    </div>
</div>
<!-- top navigation -->
<div class="top_nav">
    <div class="nav_menu">
        <nav>
			<div class="nav toggle">
				<a id="menu_toggle"><i class="fa fa-bars"></i></a>
			</div>
            <ul class="nav navbar-nav navbar-right">
                <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <?=$_SESSION["username"]?>
                    <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu pull-right">
                        <li><a href="password.php"><i class="fa fa-key pull-right"></i> Change Password</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt pull-right"></i> Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</div>
<!-- /top navigation -->