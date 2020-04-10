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
        <title>Users Details | <?=ucwords($_SESSION['company_name'])?> - Admin Panel </title>
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
                                    <h2>Users Details <small>View / Update / Delete</small></h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
									<div id="toolbar">
										<select id='export_select' class="form-control" >
											<option value="basic">Export This Page</option>
											<option value="all">Export All</option>
											<option value="selected">Export Selected</option>
										</select>
									</div>
									<table class='table-striped' id='users_list'
                                        data-toggle="table"
                                        data-url="get-list.php?table=users"
                                        data-click-to-select="true"
                                        data-side-pagination="server"
                                        data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]"
                                        data-search="true" data-show-columns="true"
                                        data-show-refresh="true" data-trim-on-search="false"
										data-sort-name="id" data-sort-order="desc"
										data-mobile-responsive="true"
										data-toolbar="#toolbar" data-show-export="false"
										data-maintain-selected="true"
										data-export-types='["txt","excel"]'
										data-export-options='{
											"fileName": "users-list-<?=date('d-m-y')?>",
											"ignoreColumn": ["state"]	
										}'
										data-query-params="queryParams_1"
										>
										<thead>
											<tr>
                                                <th data-field="state" data-checkbox="true"></th>
                                                <th data-field="id" data-sortable="true">ID</th>
                                                <th data-field="profile" data-sortable="true">Profile</th>
                                                <th data-field="name" data-sortable="true">Name</th>
                                                <th data-field="email" data-sortable="true">Email</th>
                                                <th data-field="mobile" data-sortable="true">Mobile</th>
                                                <th data-field="type" data-sortable="true">Type</th>
                                                <th data-field="coins" data-sortable="true" >Coins</th>
                                                <th data-field="refer_code" data-sortable="true" data-visible="false">Refer Code</th>
                                                <th data-field="friends_code" data-sortable="true" data-visible="false">Friends Code</th>
                                                <th data-field="fcm_id" data-sortable="true" data-visible="false">FCM ID</th>
                                                <th data-field="ip_address" data-sortable="true" data-visible="false">IP Address</th>
                                                <th data-field="status" data-sortable="true">Status</th>
                                                <th data-field="date_registered" data-sortable="true">Register Date</th>
                                                <th data-field="operate" data-sortable="true" data-events="actionEvents">Operate</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /page content -->
            <div class="modal fade" id='editUserModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update User Status</h4>
                        </div>
                        <div class="modal-body">
                            <form id="update_form"  method="POST" action ="db_operations.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="user_id" id="user_id" value=''/>
                                <input type='hidden' name="update_user" id="update_user" value='1'/>
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div id="status" class="btn-group" >
                                            <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0">  Deactive 
                                            </label>
                                            <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="submit_btn" class="btn btn-success">Submit</button>
                                    </div>
								</div>
                            </form>
                            <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="result"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- footer content -->
            <?php include 'footer.php';?>
            <!-- /footer content -->
        </div>
        </div>
        <!-- jQuery -->
		<script>
			var $table = $('#users_list');
			$('#toolbar').find('select').change(function () {
				$table.bootstrapTable('refreshOptions', {
					exportDataType: $(this).val()
				});
			});
		</script>
		<script>
		function queryParams_1(p){
			return {
				"status": $('#filter_status').val(),
				limit:p.limit,
				sort:p.sort,
				order:p.order,
				offset:p.offset,
				search:p.search
			};
		}
		</script>
		<script>
		$('#report_form').on('submit',function(e){
			e.preventDefault();
			$('#users_list').bootstrapTable('refresh');
		});
		</script>
		<script>
            window.actionEvents = {
            	'click .edit-users': function (e, value, row, index) {
            		// alert('You click remove icon, row: ' + JSON.stringify(row));
					$("input[name=status][value=1]").prop('checked', true);
            		if($(row.status).text() == 'Deactive')
						$("input[name=status][value=0]").prop('checked', true);
					$('#user_id').val(row.id);
            		$("input[name=status][value=" + row.status + "]").prop('checked', true);
            	}
            };
        </script>
        <script>
            $('#update_form').validate({
            	rules:{
					status:"required",
            	}
            });
        </script>
        <script>
            $('#update_form').on('submit',function(e){
            	e.preventDefault();
            	var formData = new FormData(this);
            	if($("#update_form").validate().form()){
					$.ajax({
					type:'POST',
					url: $(this).attr('action'),
					data:formData,
					beforeSend:function(){$('#submit_btn').html('Please wait..');},
					cache:false,
					contentType: false,
					processData: false,
					success:function(result){
						$('#result').html(result);
						$('#result').show().delay(3000).fadeOut();
						$('#submit_btn').html('Submit');
                        $('#users_list').bootstrapTable('refresh');
					}
					});
            	}
            }); 
        </script>
    </body>
</html>