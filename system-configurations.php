<?php
session_start();
if(!isset($_SESSION['id']) && !isset($_SESSION['username'])){
    header("location:index.php");
    return false;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>System Settings | <?=ucwords($_SESSION['company_name'])?> - Admin Panel </title>
        <?php include 'include-css.php';?>
    </head>
    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                <?php include 'sidebar.php';?>
                <!-- page content -->
                <div class="right_col" role="main">
                    <!-- top tiles -->
                    <br />
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>System Settings for App <small>Note that this will directly reflect the changes in App</small></h2>
                                    <div class="clearfix"></div>
                                </div>
                                <?php
                                $sql = "SELECT * FROM settings WHERE type='system_configurations' LIMIT 1";
                                $db->sql($sql);
								$res = $db->getResult();
								if(!empty($res)){
									foreach ($res as $row){
										$id = $row['id'];
										$data = json_decode($row['message'], true);
									}
									// print_r($data);
								}
								// echo " Language Mode : ".$fn->is_language_mode_enabled();
								?>
                                <div class="x_content">
                                    <form id="system_configurations_form" method="POST" data-parsley-validate="" class="form-horizontal form-label-left">
										<input type="hidden" id="system_configurations" name="system_configurations" required="" value="1" aria-required="true">
										<input type="hidden" id="system_configurations_id" name="system_configurations_id" value="<?php if(!empty($id)){ echo $id; } ?>" aria-required="true">
										<input type="hidden" id="system_timezone_gmt" name="system_timezone_gmt" value="<?php if(!empty($data['system_timezone_gmt'])){ echo $data['system_timezone_gmt']; }else{echo '-11:00';} ?>" aria-required="true">
										
										<div class="row">
											<div class="col-md-12 col-xs-12">
												<div class="form-group">
													<label class="system_timezone" for="system_timezone">System Timezone</label>
													<?php $options = getTimezoneOptions();?>
													<select id="system_timezone" name="system_timezone" required class="form-control col-md-12">
														<?php foreach($options as $option){?>
														<option value="<?=$option[2]?>" data-gmt="<?=$option['1'];?>" <?=(isset($data['system_timezone']) && $data['system_timezone'] == $option[2])?'selected':'';?>><?=$option[2]?> - GMT <?=$option[1]?> - <?=$option[0]?></option>
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6 col-xs-12">
												<div class="form-group">
													<label class="" for="app_link">App Link</label>
													<input type="url" id="app_link" name="app_link" required="required" class="form-control col-md-12 col-xs-12" value="<?php if(!empty($data['app_link'])){ echo $data['app_link']; } ?>">
												</div>
											</div>
											<div class="col-md-6 col-xs-12">
												<div class="form-group">
													<label class="" for="more_apps">More Apps Link ( Your Google / iOS Market place URL )</label>
													<input type="url" id="more_apps" name="more_apps" required="required" class="form-control col-md-12 col-xs-12" value="<?php if(!empty($data['more_apps'])){ echo $data['more_apps']; } ?>">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-1 col-xs-12">
												<div class="form-group">
													<label class="" for="language_mode">Language Mode</label>
												</div>
												<div class="form-group">
													<input type="checkbox" id="language_mode_btn" class="js-switch" <?php if(!empty($data['language_mode']) && $data['language_mode'] == '1'){ echo 'checked'; }?>>
													<input type="hidden" id="language_mode" name="language_mode" value="<?=(!empty($data['language_mode']))?$data['language_mode']:0;?>">
												</div>
											</div>
											<div class="col-md-1 col-xs-12">
												<div class="form-group">
													<label class="" for="option_e_mode">Option E Mode</label>
												</div>
												<div class="form-group">
													<input type="checkbox" id="option_e_mode_btn" class="js-switch" <?php if(!empty($data['option_e_mode']) && $data['option_e_mode'] == '1'){ echo 'checked'; }?>>
													<input type="hidden" id="option_e_mode" name="option_e_mode" value="<?=(!empty($data['option_e_mode']))?$data['option_e_mode']:0;?>">
												</div>
											</div>
											<div class="col-md-2 col-xs-12">
												<div class="form-group">
													<label class="" for="app_version">App Version</label>
													<input type="text" id="app_version" name="app_version" required="required" class="form-control col-md-7 col-xs-12" value="<?php if(!empty($data['app_version'])){ echo $data['app_version']; } ?>">
												</div>
											</div>
											<div class="col-md-8 col-xs-12">
												<div class="form-group">
													<label class="" for="shareapp_text">Shareapp Text</label>
													<textarea id="shareapp_text" name="shareapp_text" required class="form-control col-md-7 col-xs-12"><?php if(!empty($data['shareapp_text'])){ echo $data['shareapp_text']; } ?></textarea>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12 col-xs-12">
												<div class="ln_solid"></div>
											<div id="result"></div>
											<div class="form-group">
												<div class="col-md-6 col-sm-6 col-xs-12">
													<button type="submit" id="submit_btn" class="btn btn-warning">Save Settings</button>
												</div>
											</div>
											</div>
										</div>
									</form>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- footer content -->
            <?php include 'footer.php';?>
            <!-- /footer content -->
            
            <?php function getTimezoneOptions(){
                $list = DateTimeZone::listAbbreviations();
                $idents = DateTimeZone::listIdentifiers();
                
                    $data = $offset = $added = array();
                    foreach ($list as $abbr => $info) {
                        foreach ($info as $zone) {
                            if ( ! empty($zone['timezone_id'])
                                AND
                                ! in_array($zone['timezone_id'], $added)
                                AND 
                                  in_array($zone['timezone_id'], $idents)) {
                                $z = new DateTimeZone($zone['timezone_id']);
                                $c = new DateTime(null, $z);
                                $zone['time'] = $c->format('H:i a');
                                $offset[] = $zone['offset'] = $z->getOffset($c);
                                $data[] = $zone;
                                $added[] = $zone['timezone_id'];
                            }
                        }
                    }
                
                    array_multisort($offset, SORT_ASC, $data);
                    /*$options = array();
                    foreach ($data as $key => $row) {
                        $options[$row['timezone_id']] = $row['time'] . ' - '
                            . formatOffset($row['offset']). ' ' . $row['timezone_id'];
                    }*/
                    $i = 0;$temp = array();
                    foreach ($data as $key => $row) {
                        $temp[0] = $row['time'];
                        $temp[1] = formatOffset($row['offset']);
                        $temp[2] = $row['timezone_id'];
                        $options[$i++] = $temp;
                    }
                    
                    // echo "<pre>";
                    // print_r($options);
                    return $options;
            }
            
            function formatOffset($offset) {
                $hours = $offset / 3600;
                $remainder = $offset % 3600;
                $sign = $hours > 0 ? '+' : '-';
                $hour = (int) abs($hours);
                $minutes = (int) abs($remainder / 60);
            
                if ($hour == 0 AND $minutes == 0) {
                    $sign = ' ';
                }
                return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT).':'. str_pad($minutes,2, '0');
            }
            ?>
            
        </div>
        </div>
        <!-- jQuery -->
		<script>
            /* on change of language mode btn - switchery js */
			var changeCheckbox = document.querySelector('#language_mode_btn');
			changeCheckbox.onchange = function() {
				// alert(changeCheckbox.checked);
				if(changeCheckbox.checked)
					$('#language_mode').val(1);
				else
					$('#language_mode').val(0);
			};
			/* on change of option e mode btn - switchery js */
			var changeCheckbox1 = document.querySelector('#option_e_mode_btn');
			changeCheckbox1.onchange = function() {
				// alert(changeCheckbox1.checked);
				if(changeCheckbox1.checked)
					$('#option_e_mode').val(1);
				else
					$('#option_e_mode').val(0);
			};
			
            $('#system_timezone').on('change',function(e){
                gmt = $(this).find(':selected').data('gmt');
                $('#system_timezone_gmt').val(gmt);
                
            });
                
            $('#system_configurations_form').on('submit',function(e){
            	e.preventDefault();
            	var formData = new FormData(this);
            	if($("#system_configurations_form").validate().form()){
            	    swal({
                        title: "Are you sure?",
                        text: "Changing Option E Mode / Language Mode On / Off Will affect the App. After disabling option E Mode some of your answer may go into locked status, So please verify all answers and than update.",
                        icon: "warning",
                        // buttons: true,
                        buttons: ["Cancel! Let me check", "Its okay! Update now"],
                        dangerMode: true,
                    }).then((willUpdate) => {
                        if(willUpdate) {
                            $.ajax({
            					type:'POST',
            					url:'db_operations.php',
            					data:formData,
            					beforeSend:function(){$('#submit_btn').html('Please wait..');},
            					cache:false,
            					contentType: false,
            					processData: false,
            					success:function(result){
            						$('#result').html(result);
            						$('#result').show().delay(5000).fadeOut();
            						$('#submit_btn').html('Save Settings');
            						// $('#system_configurations_form')[0].reset();
            						// location.reload();
            					}
        					});
                        } 
                    });
            	}
            }); 
            function confirmation(){
                /* sweet alert 2 */
                // Swal.fire({
                //     title: 'Are you sure?',
                //     text: "Changing Option E Mode / Language Mode On / Off Will affect the App. After disabling option E Mode some of your answer may go into locked status, So please verify all answers and than update.",
                //     type: 'warning',
                //     showCancelButton: true,
                //     confirmButtonColor: '#3085d6',
                //     cancelButtonColor: '#d33',
                //     confirmButtonText: 'Yes, update it!'
                //     }).then((result) => {
                //     if (result.value) {
                //         Swal.fire(
                //           'Updated!',
                //           'Your settings have been updated successfully.',
                //           'success'
                //         )
                //         return true;
                //     }
                // });

            }
		</script>
    </body>
</html>