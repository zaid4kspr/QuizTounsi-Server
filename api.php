<?php

include './shreeLib/dbconn.php';
$file_path = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
$data = array();
if (isset($_GET['category'])) {

    $query = "select *from tbl_category order by id desc";
    $res = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $row['image'] = $file_path . "/category/" . $row['image'];
        $data[] = $row;
    }
    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
} elseif (isset($_GET['subcategory'])) {
    $query = "select *from tbl_subcategory order by id desc";
    $res = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $row['image'] = $file_path . "/subcategory/" . $row['image'];
        $data[] = $row;
    }
    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
} elseif (isset($_GET['state'])) {
    $query = "select *from tbl_state order by id desc";
    $res = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
} elseif (isset($_GET['city'])) {
    $query = "select *from tbl_city c inner join tbl_state s on state_id = s.id order by c.id desc";
    $res = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $city['id'] = $row['id'];
        $city['city'] = $row['city'];
        $city['state_id'] = $row['state_id'];
        $city['state_name'] = $row['state'];
        $data[] = $city;
    }
    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
} elseif (isset($_GET['cate_id'])) {
    $cate = $_GET['cate_id'];
    $query = "select *from tbl_subcategory where cate_id='" . $cate . "' order by id desc";
    $res = mysqli_query($con, $query);
    $num = mysqli_num_rows($res);
    if ($num > 0) {
        
        while ($row = mysqli_fetch_assoc($res)) {
            $row['image'] = $file_path . "/subcategory/" . $row['image'];

            $bsql = "select *from tbl_business where instr(subcate_id," . $row['id'] . ") AND status=1 order by id desc";
            $bres = mysqli_query($con, $bsql);
            $bdata = array();

            while ($brow = mysqli_fetch_assoc($bres)) {

                $brow['subcate_id'] = $row['id'];
                $gsql = "select *from tbl_gallery where business_id='" . $brow['id'] . "' order by id desc";
                $gres = mysqli_query($con, $gsql);
                $gdata = array();
                while ($grow = mysqli_fetch_assoc($gres)) {
                    $grow['image'] = $file_path . "/business/" . $grow['business_id'] . "/" . $grow['image'];
                    $gdata[] = $grow;
                }
                $brow['gallery'] = $gdata;
                $bdata[] = $brow;
            }

            $row['data'] = $bdata;

            $data[] = $row;
        }
       
         echo str_replace("\\/", "/", json_encode(array('error'=>false,'Business' => $data)));
    } else {
        $data['error']='true';
        $data['message']='Data does not exist.';
       echo json_encode($data);
    }
   
} elseif (isset($_GET['user'])) {
    $query = "select *from tbl_user order by id desc";
    $res = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $row['picture'] = $file_path . "/profile/" . $row['picture'];
        $data[] = $row;
    }
    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
} elseif (isset($_GET['state_id'])) {
    $query = "select *from tbl_city where state_id=" . $_GET['state_id'] . " order by id desc";
    $res = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($res)) {

        $data[] = $row;
    }
    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
} elseif (isset($_GET['slider'])) {
    $query = "select *from tbl_slider order by id desc";
    $res = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $row['image'] = $file_path . "/slider/" . $row['image'];
        $data[] = $row;
    }
    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
} elseif (isset($_GET['city_id'])) {
    $city_id = $_GET['city_id'];
    $bsql = "select *from tbl_business b inner join tbl_category c on c.id=b.cate_id  where b.city='" . $city_id . "' order by b.id desc";
    $bres = mysqli_query($con, $bsql);

    while ($brow = mysqli_fetch_assoc($bres)) {
        $cate['cate'] = $brow['cate'];
        $cate['cate_id'] = $brow['cate_id'];
//          $gsql="select *from tbl_gallery where business_id='".$brow['id']."' order by id desc";
//          $gres= mysqli_query($con, $gsql);
//          $gdata=array();
//          while($grow= mysqli_fetch_assoc($gres)){
//             $grow['image']=$file_path . "/business/".$grow['business_id']."/". $grow['image'];
//             $gdata[]=$grow;
//          }
//          $brow['gallery']=$gdata;
        $data[] = $cate;
    }




    echo str_replace("\\/", "/", json_encode(array('Business' => $data)));
}