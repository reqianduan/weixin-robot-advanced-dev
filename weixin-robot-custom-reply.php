<?php 
function weixin_robot_reply_page(){
	global $plugin_page;

	$current_tab = isset($_GET['tab'])?$_GET['tab']:'custom-reply';

	$tabs = array(
		'custom-reply'	=> '自定义回复',
		'default-reply'	=> '默认回复',
		'advanced-reply'=> '高级回复',
		'builtin-reply'	=> '内置回复'
	);

	if($current_tab == 'custom-reply'){
		global $wpdb,$weixin_robot_custom_replies,$id,$succeed_msg;

		$wpdb->show_errors();
		
		if(isset($_GET['delete']) && isset($_GET['id']) && $_GET['id']){
			$wpdb->query("DELETE FROM $wpdb->weixin_custom_replies WHERE id = {$_GET['id']}");
			delete_transient('weixin_custom_keywords_full');
			delete_transient('weixin_custom_keywords_prefix');
			delete_transient('weixin_builtin_replies');
			delete_transient('weixin_builtin_replies_new');
		}

		if(isset($_GET['edit']) && isset($_GET['id'])){
			$id = (int)$_GET['id'];	
		}

		if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

			if ( !wp_verify_nonce($_POST['weixin_robot_custom_reply_nonce'],'weixin_robot') ){
				ob_clean();
				wp_die('非法操作');
			}

			$type		= stripslashes( trim( $_POST['type'] ));
			$match		= stripslashes( trim( $_POST['match'] ));
			$keyword	= stripslashes( trim( $_POST['keyword'] ));
			$reply		= stripslashes( trim( $_POST['reply'] ));
			$status		= isset($_POST['status'] )?1:0;
			$time		= stripslashes( trim( $_POST['time'] ));

			$data = compact('type','keyword','match','reply','time','status');
			
			if(empty($id)){
				$wpdb->insert($wpdb->weixin_custom_replies,$data); 
				//$id = $wpdb->insert_id;
				$succeed_msg = '添加成功';
			}else{
				$current_user = $user = wp_get_current_user();
				$wpdb->update($wpdb->weixin_custom_replies,$data,array('id'=>$id));
				$succeed_msg = '修改成功';
			}

			delete_transient('weixin_custom_keywords_full');
			delete_transient('weixin_custom_keywords_prefix');
			delete_transient('weixin_builtin_replies');
			delete_transient('weixin_builtin_replies_new');
		}
	}

	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
		<?php foreach ($tabs as $tab=>$tab_name) {?>
			<a class="nav-tab <?php if($current_tab == $tab){ echo 'nav-tab-active'; } ?>" href="<?php echo 'admin.php?page='.$plugin_page.'&tab='.$tab;?>"><?php echo $tab_name; ?></a>
	    <?php }?>    
	    </h2>
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
	    <?php call_user_func('weixin_robot_'.str_replace('-', '_', $current_tab).'_page'); ?>
	</div>
	<?php
}

function weixin_robot_default_reply_page(){
	settings_errors();
	$labels = weixin_robot_get_option_labels();
	wpjam_option_page($labels, $title='', $type='default');
}

function weixin_robot_advanced_reply_page() {
	settings_errors();
	$labels = weixin_robot_get_option_labels();
	wpjam_option_page($labels, $title='', $type='default');
}

function weixin_robot_builtin_reply_page(){
	global $plugin_page,$wpdb;
?>
	
	<?php $weixin_builtin_replies = weixin_robot_get_builtin_replies(); ?>

	<?php if($weixin_builtin_replies) { ?>
	<h3>插件或者扩展内置回复列表</h3>

	<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<?php /*<th style="width:40px">ID</th>*/?>
			<th>关键字</th>
			<th>类型</th>
			<th>描述</th>
			<th>处理函数</th>
		</tr>
	</thead>
	<tbody>
	<?php $alternate = '';?>
	<?php foreach($weixin_builtin_replies as $keyword => $weixin_builtin_reply){ $alternate = $alternate?'':'alternate';?>
		<?php if( $weixin_builtin_reply['function'] != 'wpjam_weixin_emotions_reply'){?>
		<tr class="<?php echo $alternate;?>">
			<td><?php echo $keyword; ?></td>
			<td><?php if($weixin_builtin_reply['type'] == 'full'){ echo '完全匹配'; }else{ echo '前缀匹配'; }; ?></td>
			<td><?php echo $weixin_builtin_reply['reply']; ?></td>
			<td><?php echo $weixin_builtin_reply['function']; ?></td>
		</tr>
		<?php } ?>
	<?php } ?>
	</tbody>
	</table>
	
	
	<?php } ?>
<?php
}

function weixin_robot_get_custom_reply_types(){

	$types = array(
		'text'		=> '文本回复',
		'img'		=> '图文回复',
		'function'	=> '函数回复'
	);

	if(weixin_robot_get_setting('weixin_3rd_url') && weixin_robot_get_setting('weixin_3rd_token')){
		$types['3rd'] = '第三方平台';
	}

	$types = apply_filters('weixin_custom_reply_types',$types);

	return $types;
}

function weixin_robot_custom_reply_page(){
	global $wpdb,$id,$plugin_page;
?>
	<h3>自定义回复列表</h3>

	<?php 
		$weixin_robot_custom_replies = $wpdb->get_results("SELECT * FROM $wpdb->weixin_custom_replies;");
		$custom_reply_types = weixin_robot_get_custom_reply_types();
	?>
	<?php if($weixin_robot_custom_replies) { ?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<?php /*<th style="width:40px">ID</th>*/?>
				<th>关键字</th>
				<th>回复类型</th>
				<th style="width:40%;min-width:200px;">回复内容</th>
				<th>添加时间</th>
				<th>状态</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
		<?php $alternate = '';?>
		<?php foreach($weixin_robot_custom_replies as $weixin_robot_custom_reply){ ?>
			<?php 
			$alternate = $alternate?'':'alternate';
			$type		= $weixin_robot_custom_reply->type;
			$reply		= $weixin_robot_custom_reply->reply;
			$time		= $weixin_robot_custom_reply->time;
			$status		= $weixin_robot_custom_reply->status;
			$keyword	= $weixin_robot_custom_reply->keyword;
			$match		= $weixin_robot_custom_reply->match;
			$match		= ($match=='prefix')?'前缀匹配':'完全匹配';
			if($type == 'function' ){
				$reply	= $match.'：'.$reply;
			}elseif( $type == '3rd'){
				$reply	= $match;
			}
			?>
			<tr class="<?php echo $alternate;?>">
				<?php /*<td><?php echo $weixin_robot_custom_reply->id; ?></td>*/?>
				<td><?php echo $weixin_robot_custom_reply->keyword; ?></td>
				<td><?php echo $custom_reply_types[$type]; ?></td>
				<td><?php echo $reply; ?></td>
				<td><?php echo $time; ?></td>
				<td><?php echo $status?'使用中':'未使用'; ?></td>
				<td><span><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&tab=custom-reply
	&edit&id='.$weixin_robot_custom_reply->id."#edit"); ?>">编辑</a></span> | <span class="delete"><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&tab=custom-reply
	&delete&id='.$weixin_robot_custom_reply->id); ?>">删除</a></span></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php } else{ ?>
	
	<p>你还没有添加自定义回复，开始添加第一条自定义回复！</p>

	<?php } ?>
	<?php

	if(!empty($id)){
		$weixin_robot_custom_reply = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->weixin_custom_replies WHERE id=%d LIMIT 1",$id));
		$type		= $weixin_robot_custom_reply->type;
		$keyword	= $weixin_robot_custom_reply->keyword;
		$reply		= $weixin_robot_custom_reply->reply;
		$time		= $weixin_robot_custom_reply->time;
		$match		= $weixin_robot_custom_reply->match;
		$status		= $weixin_robot_custom_reply->status;
	}else{
		$id = '';
	}

	?>
	<h3 id="edit"><?php echo $id?'修改':'新增';?>自定义回复 <?php if($id) { ?> <a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&tab=custom-reply&add#edit'); ?>" class="add-new-h2">新增另外一条自定义回复</a> <?php } ?></h3>

	<?php 
	$form_fields = array(
		'keyword'	=> array('title'=>'关键字',	'type'=>'text',		'value'=>$id?$keyword:'',	'description'=>'多个关键字请用英文逗号区分开，如：<code>七牛, qiniu, 七牛云存储, 七牛镜像存储</code>'),
		'type'		=> array('title'=>'回复类型',	'type'=>'select',	'value'=>$id?$type:'',		'options'=> weixin_robot_get_custom_reply_types()),
		'reply'		=> array('title'=>'回复内容',	'type'=>'textarea',	'value'=>$id?$reply:'',		'description'=>'回复类型为图文时，请输入构成图文回复的单篇或者多篇日志的ID，并用英文逗号区分开，如：<code>123,234,345</code>，并且 ID 数量不要超过基本设置里面的返回结果最大条数。<br />回复类型为函数时，请输入相应的处理函数'),
		'match'		=> array('title'=>'匹配方式',	'type'=>'select',	'value'=>$id?$match:'',		'options'=> array('full'=>'完全匹配','prefix'=>'前缀匹配'),	'description'=>'前缀匹配方式只支持匹配前两个中文字或者字母。'),
		'time'		=> array('title'=>'添加时间',	'type'=>'datetime',	'value'=>$id?$time:current_time('mysql')),
		'status'	=> array('title'=>'状态',	'type'=>'checkbox',	'value'=>$id?$status:'',				'description'=>'是否激活')
	); 

	?>
	<form method="post" action="<?php echo admin_url('admin.php?page='.$plugin_page.'&tab=custom-reply
	&edit&id='.$id); ?>" enctype="multipart/form-data" id="form">
		<?php wpjam_admin_display_fields($form_fields); ?>
		<?php wp_nonce_field('weixin_robot','weixin_robot_custom_reply_nonce'); ?>
		<input type="hidden" name="action" value="edit" />
		<p class="submit"><input class="button-primary" type="submit" value="　　<?php echo $id?'修改':'新增';?>　　" /></p>
	</form>
	<script type="text/javascript">
	jQuery(function(){
	<?php if( $id && $type == 'function' ){?>
		jQuery('#tr_match').show();
	<?php } elseif( $id && $type == '3rd' ) {?>
		jQuery('#tr_match').show();
		jQuery('#tr_reply').hide();
	<?php } else {?>
		jQuery('#tr_match').hide();
	<?php }?>
		jQuery("select#type").change(function(){
			var selected = jQuery("select#type").val();

			jQuery('#tr_match').hide();
			jQuery('#tr_reply').show();

			if(selected == '3rd'){
				jQuery('#tr_reply').hide();
				jQuery('#tr_match').show();
			}else if(selected == 'function'){
				jQuery('#tr_match').show();
			}
		});
	});
	</script> 
<?php
}

