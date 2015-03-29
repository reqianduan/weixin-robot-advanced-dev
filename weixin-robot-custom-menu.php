<?php
function weixin_robot_custom_menu_page(){
	global $weixin_robot_custom_menus, $id, $succeed_msg;

	$weixin_robot_custom_menus = get_option('weixin-robot-custom-menus');
	if(!$weixin_robot_custom_menus) $weixin_robot_custom_menus = array();

	if(isset($_GET['delete']) && isset($_GET['id']) && $_GET['id']) {
		unset($weixin_robot_custom_menus[$_GET['id']]);
		update_option('weixin-robot-custom-menus',$weixin_robot_custom_menus);
		$succeed_msg = '删除成功';
	}

	if(isset($_GET['sync'])) {
		$succeed_msg = apply_filters('weixin_robot_post_custom_menus','', $weixin_robot_custom_menus);
	}elseif(isset($_GET['edit']) && isset($_GET['id'])){
		$id = (int)$_GET['id'];	
	}

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

		if ( !wp_verify_nonce($_POST['weixin_robot_custom_menu_nonce'],'weixin_robot') ){
			ob_clean();
			wp_die('非法操作');
		}

		$is_sub = isset($_POST['is_sub'])?1:0;
		
		$data = array(
			'name'			=> stripslashes( trim( $_POST['name'] )),
			'type'			=> stripslashes( trim( $_POST['type'] )),
			'key'			=> stripslashes( trim( $_POST['key'] )),
			'position'		=> $is_sub?'0':stripslashes( trim( $_POST['position'] )),
			'parent'		=> $is_sub?stripslashes( trim( $_POST['parent'] )):'0',
			'sub_position'	=> $is_sub?stripslashes( trim( $_POST['sub_position'] )):'0',
		);
		
		if(empty($id)){
			if($weixin_robot_custom_menus){
				end($weixin_robot_custom_menus);
				$id = key($weixin_robot_custom_menus)+1;
			}else{
				$id = 1;
			}
			$weixin_robot_custom_menus[$id]=$data;
			update_option('weixin-robot-custom-menus',$weixin_robot_custom_menus);
			$succeed_msg = '添加成功';
			$id = 0;
		}else{
			$weixin_robot_custom_menus[$id]=$data;
			update_option('weixin-robot-custom-menus',$weixin_robot_custom_menus);
			$succeed_msg = '修改成功';
		}
	}
?>
	<div class="wrap">
		<h2>自定义菜单 <a href="<?php echo admin_url('admin.php?page=weixin-robot-stats2&tab=custom-menu-stats'); ?>" class="add-new-h2">点击统计</a></h2>
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
		<?php weixin_robot_custom_menu_list(); ?>
		<?php weixin_robot_custom_menu_add(); ?>
		<?php wpjam_confim_delete_script(); ?>
	</div>
<?php
}

function weixin_robot_custom_menu_list(){
	global $plugin_page;

	$weixin_robot_custom_menus = get_option('weixin-robot-custom-menus');
	if(!$weixin_robot_custom_menus) $weixin_robot_custom_menus = array();
	?>
	
	<h3>自定义菜单列表</h3>
	<?php if($weixin_robot_custom_menus) { ?>
	<?php $weixin_robot_ordered_custom_menus = weixin_robot_get_ordered_custom_menus($weixin_robot_custom_menus);?>
	<form action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" method="POST">
		<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>按钮</th>
				<th>按钮位置/子按钮位置</th>
				<th>类型</th>
				<th>Key/URL</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
		<?php $alternate = '';?>
		<?php foreach($weixin_robot_ordered_custom_menus as $weixin_robot_custom_menu){ $alternate = $alternate?'':'alternate'; ?>
			<?php if(isset($weixin_robot_custom_menu['parent'])){?>
			<tr class="<?php echo $alternate; ?>">
				<td><?php echo $weixin_robot_custom_menu['parent']['name']; ?></td>
				<td><?php echo $weixin_robot_custom_menu['parent']['position']; ?></td>
				<td><?php echo $weixin_robot_custom_menu['parent']['type']; ?></td>
				<td><?php echo $weixin_robot_custom_menu['parent']['key']; ?></td>
				<?php $id = $weixin_robot_custom_menu['parent']['id'];?>
				<td><span><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&edit&id='.$id.'#edit'); ?>">编辑</a></span> | <span class="delete"><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&delete&id='.$id); ?>">删除</a></span></td>
			</tr>
			<?php } ?>
			<?php if(isset($weixin_robot_custom_menu['sub'])){  ?>
			<?php foreach($weixin_robot_custom_menu['sub'] as $weixin_robot_custom_menu_sub){ $alternate = $alternate?'':'alternate';?>
			<tr colspan="4" class="<?php echo $alternate; ?>">
				<td> └── <?php echo $weixin_robot_custom_menu_sub['name']; ?></td>
				<td> └── <?php echo $weixin_robot_custom_menu_sub['sub_position']; ?></td>
				<td><?php echo $weixin_robot_custom_menu_sub['type']; ?></td>
				<td><?php echo $weixin_robot_custom_menu_sub['key']; ?></td>
				<?php $id = $weixin_robot_custom_menu_sub['id'];?>
				<td><span><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&edit&id='.$id.'#edit'); ?>">编辑</a></span> | <span class="delete"><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&delete&id='.$id); ?>">删除</a></span></td>
			<tr>
			<?php }?>
			<?php } ?>
		<?php } ?>
		</tbody>
		</table>
		<p class="submit"><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&sync'); ?>" class="button-primary">同步自定义菜单</a></p>
	</form>
	<?php } ?>
<?php
}

function weixin_robot_custom_menu_add(){

	global $id, $plugin_page;

	$weixin_robot_custom_menus = get_option('weixin-robot-custom-menus');

	if($id && $weixin_robot_custom_menus && isset($weixin_robot_custom_menus[$id])){
		$weixin_robot_custom_menu = $weixin_robot_custom_menus[$id];
	}

	$parent_options 		= array('0'=>'','1'=>'1','2'=>'2','3'=>'3');
	$position_options 		= array('1'=>'1','2'=>'2','3'=>'3');
	$sub_position_options 	= array('0'=>'','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5');
	$type_options			= array('click'=>'点击事件', 'view'=>'访问网页');

	$form_fields = array(
		'name'			=> array('title'=>'按钮名称',			'type'=>'text',		'value'=>$id?$weixin_robot_custom_menu['name']:'',		'description'=>'按钮描述，既按钮名字，不超过16个字节，子菜单不超过40个字节'),
		'type'			=> array('title'=>'按钮类型',			'type'=>'select',	'value'=>$id?$weixin_robot_custom_menu['type']:'',		'description'=>'',	'options'=> $type_options),
		'key'			=> array('title'=>'按钮KEY值/URL',	'type'=>'text',		'value'=>$id?$weixin_robot_custom_menu['key']:'',		'description'=>'用于消息接口（event类型）推送，不超过128字节，如果按钮还有子按钮，可以不填，其他必填，否则报错。<br />如果类型为点击事件时候，则为按钮KEY值，如果类型为浏览网页，则为URL。<br />KEY值可以为搜索关键字，或者自定义回复定义的关键字。'),
		'is_sub'		=> array('title'=>'子按钮',			'type'=>'checkbox',	'value'=>$id?($weixin_robot_custom_menu['parent']?1:0):'','description'=>'是否激活' ),
		'position'		=> array('title'=>'位置',			'type'=>'select',	'value'=>$id?$weixin_robot_custom_menu['position']:'',	'description'=>'设置按钮的位置',	'options'=> $position_options ),
		'parent'		=> array('title'=>'所属父按钮位置',	'type'=>'select',	'value'=>$id?$weixin_robot_custom_menu['parent']:'',	'description'=>'如果是子按钮则需要设置所属父按钮的位置',	'options'=> $parent_options ),
		'sub_position'	=> array('title'=>'子按钮的位置',		'type'=>'select',	'value'=>$id?$weixin_robot_custom_menu['sub_position']:'','description'=>'设置子按钮的位置',	'options'=> $sub_position_options )
	);

	?>
	<h3 id="edit"><?php echo $id?'修改':'新增';?>自定义菜单 <?php if($id) { ?> <a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&add'); ?>" class="add-new-h2">新增另外一条自定义菜单</a> <?php } ?></h3>

	 <form method="post" action="<?php echo admin_url('admin.php?page='.$plugin_page.'&edit&id='.$id.'#edit'); ?>" enctype="multipart/form-data" id="form">
		<?php wpjam_admin_display_fields($form_fields); ?>
		<?php wp_nonce_field('weixin_robot','weixin_robot_custom_menu_nonce'); ?>
		<input type="hidden" name="action" value="edit" />
		<p class="submit"><input class="button-primary" type="submit" value="　　<?php echo $id?'修改':'新增';?>　　" /></p>
	</form>
	
	<script type="text/javascript">
	jQuery(function(){
		<?php if( $id && $weixin_robot_custom_menu['parent'] ){?>
		jQuery('#tr_position').hide();
		<?php } else {?>
		jQuery('#tr_parent').hide();
		jQuery('#tr_sub_position').hide();
		<?php }?>

		jQuery('#is_sub').mousedown(function(){
			jQuery('#tr_parent').toggle();
			jQuery('#tr_sub_position').toggle();
			jQuery('#tr_position').toggle();
		});

	});
	</script> 
<?php

}

add_filter('weixin_robot_post_custom_menus','weixin_robot_post_custom_menus',10,2);
function weixin_robot_post_custom_menus($message, $weixin_robot_custom_menus){

	if(weixin_robot_get_setting('weixin_app_id') && weixin_robot_get_setting('weixin_app_secret')){

		$weixin_robot_access_token = weixin_robot_get_access_token();

		if($weixin_robot_access_token){
			//$url = 'http://wpjam.net/api/weixin.php?action=create_menu&access_token='.$weixin_robot_access_token.'&domain='.wpjam_net_get_domain();
			$url =  'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$weixin_robot_access_token;
			$request = weixin_robot_create_buttons_request($weixin_robot_custom_menus);
			$result = weixin_robot_post_custom_menus_core($url,urldecode(json_encode($request)));

			$message = $message?$message.'<br />':$message;
			return $message.'微信：'.$result;
			
		}
	}
			
	return $message;
}

function weixin_robot_post_custom_menus_core($url,$request){
	
	$response = wp_remote_post($url,array( 'body' => $request,'sslverify'=>false));
			
	// if(is_wp_error($response)){
	// 	return $response->get_error_code().'：'. $response->get_error_message();
	// }else{
	// 	return $response['body'];
	// }

	if(is_wp_error($response)){
		return $response->get_error_code().'：'. $response->get_error_message();
	}

	$response = json_decode($response['body'],true);

	if($response['errcode']){
		return $response['errcode'].': '.$response['errmsg'];
	}else{
		return '自定义菜单成功同步';
	}
}

function weixin_robot_create_buttons_request($weixin_robot_custom_menus){

	$weixin_robot_ordered_custom_menus = weixin_robot_get_ordered_custom_menus($weixin_robot_custom_menus);

	$request = $buttons_json = $button_json = $sub_buttons_json = $sub_button_json = array();

	foreach($weixin_robot_ordered_custom_menus as $weixin_robot_custom_menu){ 
		if(isset($weixin_robot_custom_menu['parent']) && isset($weixin_robot_custom_menu['sub'])){
			$button_json['name']	= urlencode($weixin_robot_custom_menu['parent']['name']);

			foreach($weixin_robot_custom_menu['sub'] as $weixin_robot_custom_menu_sub){
				$sub_button_json['type']	= $weixin_robot_custom_menu_sub['type'];
				$sub_button_json['name']	= urlencode($weixin_robot_custom_menu_sub['name']);
				if($sub_button_json['type'] == 'click'){
					$sub_button_json['key']		= urlencode($weixin_robot_custom_menu_sub['key']);
				}elseif($sub_button_json['type'] == 'view'){
					$sub_button_json['url']		= urlencode($weixin_robot_custom_menu_sub['key']);
				}
				$sub_buttons_json[]			= $sub_button_json;
				unset($sub_button_json);
			}

			$button_json['sub_button']		= $sub_buttons_json;

			unset($sub_buttons_json);

			$buttons_json[]					= $button_json;
		}elseif(isset($weixin_robot_custom_menu['parent'])){
			$button_json['type']	= $weixin_robot_custom_menu['parent']['type'];
			$button_json['name']	= urlencode($weixin_robot_custom_menu['parent']['name']);
			if($button_json['type'] == 'click'){
				$button_json['key']		= urlencode($weixin_robot_custom_menu['parent']['key']);
			}elseif($button_json['type'] == 'view'){
				$button_json['url']		= urlencode($weixin_robot_custom_menu['parent']['key']);
			}
			$buttons_json[]			= $button_json;
		}

		unset($button_json);
	}

	$request['button'] = $buttons_json;

	unset($buttons_json);

	return $request;

}

function weixin_robot_get_ordered_custom_menus($weixin_robot_custom_menus){
	$weixin_robot_ordered_custom_menus = array();

	foreach ($weixin_robot_custom_menus as $id => $weixin_robot_custom_menu) {
		$weixin_robot_custom_menu['id'] = $id;
		if($weixin_robot_custom_menu['parent']){
			$weixin_robot_ordered_custom_menus[$weixin_robot_custom_menu['parent']]['sub'][$weixin_robot_custom_menu['sub_position']] = $weixin_robot_custom_menu;
		}else{
			$weixin_robot_ordered_custom_menus[$weixin_robot_custom_menu['position']]['parent'] = $weixin_robot_custom_menu;
		}
	}

	ksort($weixin_robot_ordered_custom_menus);

	foreach ($weixin_robot_ordered_custom_menus as $key => $weixin_robot_ordered_custom_menu) {
		if(isset($weixin_robot_ordered_custom_menu['sub'])){
			ksort($weixin_robot_ordered_custom_menus[$key]['sub']);
		}
	}

	return $weixin_robot_ordered_custom_menus;
}