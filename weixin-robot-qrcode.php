<?php 
function weixin_robot_qrcode_page(){
	global $wpdb,$id,$succeed_msg;

	$wpdb->show_errors();
	
	if(isset($_GET['delete']) && isset($_GET['id']) && $_GET['id']){
		$wpdb->query("DELETE FROM $wpdb->weixin_qrcodes WHERE id = {$_GET['id']}");
	}

	if(isset($_GET['edit']) && isset($_GET['id'])){
		$id = (int)$_GET['id'];	
	}

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		if ( !wp_verify_nonce($_POST['weixin_robot_qrcode_nonce'],'weixin_robot') ){
			ob_clean();
			wp_die('非法操作');
		}

		$scene		= stripslashes( trim( $_POST['scene'] ));
		$name		= stripslashes( trim( $_POST['name'] ));
		$type		= stripslashes( trim( $_POST['type'] ));
		$expire		= stripslashes( trim( $_POST['expire'] ));

		if(weixin_robot_get_setting('weixin_app_id') && weixin_robot_get_setting('weixin_app_secret')){

			if(weixin_robot_create_qrcode($scene,$name,$type,$expire)){
				if(empty($id)){
					$succeed_msg = '添加成功';
				}else{
					$succeed_msg = '修改成功';
				}
			}
		}
	}
?>
	<div class="wrap">

		<h2>带参数的二维码 <a href="<?php echo admin_url('admin.php?page=weixin-robot-stats2&tab=qrcode-stats'); ?>" class="add-new-h2">使用统计</a></h2>

		<?php if(!empty($succeed_msg)){?>
		<div class="updated">
			<p><?php echo $succeed_msg;?></p>
		</div>
		<?php }?>
		<?php if(!empty($err_msg)){?>
		<div class="error" style="color:red;">
			<p>错误：<?php echo $err_msg;?></p>
		</div>
		<?php }?>
		
	    <?php weixin_robot_qrcode_list(); ?>
		<?php weixin_robot_qrcode_add(); ?>

		<?php wpjam_confim_delete_script(); ?>

	</div>
<?php
}



function weixin_robot_qrcode_list(){
	global $plugin_page,$wpdb;
?>

	<?php
		$weixin_robot_qrcodes = $wpdb->get_results("SELECT * FROM $wpdb->weixin_qrcodes;");
		$qrcode_types = weixin_robot_get_qrcode_types();
	?>
	<?php if($weixin_robot_qrcodes) { ?>
	<h3>带参数二维码列表</h3>
	<form action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" method="POST">
		
		<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<?php /*<th style="width:40px">ID</th>*/?>
				<th>场景 ID</th>
				<th>名称</th>
				<th>类型</th>
				<th>过期时间</th>
				<th>二维码</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
		<?php $alternate = '';?>
		<?php foreach($weixin_robot_qrcodes as $weixin_robot_qrcode){ ?>
			<?php 
			$alternate = $alternate?'':'alternate';
			$scene		= $weixin_robot_qrcode->scene;
			$type		= $weixin_robot_qrcode->type;
			$name		= $weixin_robot_qrcode->name;
			$ticket		= $weixin_robot_qrcode->ticket;
			$expire		= $weixin_robot_qrcode->expire;
			?>
			<tr class="<?php echo $alternate;?>">
				<?php /*<td><?php echo $weixin_robot_qrcode->id; ?></td>*/?>
				<td><?php echo $weixin_robot_qrcode->scene; ?></td>
				<td><?php echo $name; ?></td>
				<td><?php echo $qrcode_types[$type]; ?></td>
				<td><?php echo ($type=='QR_SCENE')?(($expire-time()>0)?$expire-time():'已过期'):''; ?></td>
				<td><img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=<?php echo urlencode($ticket); ?>" width="100"></td>
				<td><span><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&edit&id='.$weixin_robot_qrcode->id."#edit"); ?>">编辑</a></span> | <span class="delete"><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&delete&id='.$weixin_robot_qrcode->id); ?>">删除</a></span></td>
			</tr>
		<?php } ?>
		</tbody>
		</table>
	</form>
	<?php } else{ ?>

	<?php } ?>
<?php
}

function weixin_robot_qrcode_add(){
	global $wpdb,$id,$plugin_page;
	
	if(isset($id)){
		$weixin_robot_qrcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->weixin_qrcodes WHERE id=%d LIMIT 1",$id));
		$type		= $weixin_robot_qrcode->type;
		$scene		= $weixin_robot_qrcode->scene;
		$name		= $weixin_robot_qrcode->name;
		$expire		= $weixin_robot_qrcode->expire-time();
	}else{
		$id = '';
	}

	?>
	<h3 id="edit"><?php echo $id?'修改':'新增';?>带参数的二维码 <?php if($id) { ?> <a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&add'); ?>" class="add-new-h2">新增另外一条自定义回复</a> <?php } ?></h3>

	<?php 
	$form_fields = array(
		'scene'	=> array('title'=>'场景 ID',	'type'=>'text',		'value'=>$id?$scene:'',	'description'=>'临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）'),
		'name'		=> array('title'=>'名称',	'type'=>'text',		'value'=>$id?$name:'',		'description'=>'二维码名称无实际用途，仅用于更加容易区分。'),
		'type'		=> array('title'=>'类型',	'type'=>'select',	'value'=>$id?$type:'',		'options'=> weixin_robot_get_qrcode_types()),
		'expire'	=> array('title'=>'过期时间',	'type'=>'text',		'value'=>$id?$expire:'',	'description'=> '二维码有效时间，以秒为单位。最大不超过1800'),
	); 

	?>
	<form method="post" action="<?php echo admin_url('admin.php?page='.$plugin_page.'&edit&id='.$id); ?>" enctype="multipart/form-data" id="form">
		<?php wpjam_admin_display_fields($form_fields); ?>
		<?php wp_nonce_field('weixin_robot','weixin_robot_qrcode_nonce'); ?>
		<input type="hidden" name="action" value="edit" />
		<p class="submit"><input class="button-primary" type="submit" value="　　<?php echo $id?'修改':'新增';?>　　" /></p>
	</form>
	<script type="text/javascript">
	jQuery(function(){
		jQuery('#tr_expire').hide();
	<?php if($id){?>
		jQuery('#scene').attr('readonly','readonly'); 
		<?php if($type == 'QR_SCENE' ){?>
			jQuery('#tr_expire').show();
		<?php } ?>
	<?php }?>
		jQuery("select#type").change(function(){
			var selected = jQuery("select#type").val();

			if(selected == 'QR_LIMIT_SCENE'){
				jQuery('#tr_expire').hide();
			}else if(selected == 'QR_SCENE'){
				jQuery('#tr_expire').show();
			}
		});
	});
	</script> 
<?php
}

function weixin_robot_get_qrcode_types(){
	return  array(
		'QR_LIMIT_SCENE'	=> '永久二维码',
		'QR_SCENE'			=> '临时二维码'
	);
}
