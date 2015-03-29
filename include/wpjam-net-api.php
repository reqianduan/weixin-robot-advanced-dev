<?php

if(!function_exists('wpjam_net_api_request')){
	function wpjam_net_api_request( $args ) {
								 
		$request = wp_remote_post( 'http://wpjam.net/api/', array( 'body' => $args, 'timeout'=>3 ) );

		if ( is_wp_error( $request ) || 200 != wp_remote_retrieve_response_code( $request ) ){
			if(isset($_GET['debug'])){
				echo '<div class="error" style="color:red;"><p>错误：'.$request->get_error_code().'：'. $request->get_error_message().'</p></div>';	
			}
			return false;
		}

		$response = unserialize( wp_remote_retrieve_body( $request ) );

		//var_dump($response); // 用于测试，感觉速度慢的时候，打开这里

		if ( is_object( $response ) ) 
			return $response;
		else
			return false;
	}
}

function wpjam_net_check_domain($id = 56){
	$domain = wpjam_net_get_domain();

	if(get_option('wpjam_net_domain_check_'.$id) == md5($domain.$id)){

		return 1;
	}

	return false;
	
	$domain_check = get_transient('wpjam_net_domain_check_'.$id);
	if($domain_check === false){
		$domain_check = apply_filters('wpjam_net_domain_check',false,$id);
		if($domain_check === false){
			$domain_check = wpjam_net_api_request( array( 'action' => 'domain', 'id' => $id , 'domain' => $domain ) );

			if($domain_check){
				$domain_check = $domain_check->domain;
				if($domain_check == 1){
					set_transient('wpjam_net_domain_check_'.$id,1,864000); 	// 确认的用户 10天检测一次
				}else{
					set_transient('wpjam_net_domain_check_'.$id,0,10);	// 未确认的用户，每10秒检测一次
				}
			}
		}
	}

	return $domain_check;
}

function wpjam_net_get_plugin_datas($paged = 1){
	$plugin_datas = get_transient('wpjam_net_plugin_datas');
	if($plugin_datas === false){
		$plugin_datas = wpjam_net_api_request( array( 'action' => 'get_all', 'paged' => $paged ) );
		if($plugin_datas){
			set_transient('wpjam_net_plugin_datas',$plugin_datas,600); 
		}
	}
	return $plugin_datas;
}

//add_action( 'admin_menu', 'wpjam_net_admin_menu' );
function wpjam_net_admin_menu() {
	$wpjam_net_admin_menu = apply_filters('wpjam_net_admin_menu',true);

	if($wpjam_net_admin_menu === true){
		$wpjam_net_count = get_transient('wpjam_net_count');

		$update_info = '';

		if($wpjam_net_count !== false){
			$update_plugins_count = $wpjam_net_count['update_plugins_count'];
			$update_domains_count = $wpjam_net_count['update_domains_count'];
				
			$total_count = $update_domains_count + $update_plugins_count;
			
			if($total_count > 0){
				$update_info = '<span title="待审核：'.(int)$update_domains_count.'，未更新:'.(int)$update_plugins_count.'" class="update-plugins count-1"><span class="update-count">'.$total_count.'</span></span>';
			}
		}

		add_menu_page(					'WPJAM应用商城', 						'WPJAM 商城'.$update_info,	'manage_options',	'wpjam-net',		'wpjam_net_page',	' dashicons-cart');
		add_submenu_page( 'wpjam-net', 	'所有产品 &lsaquo; WPJAM应用商城', 	'所有产品', 					'manage_options',	'wpjam-net',		'wpjam_net_page');
		add_submenu_page( 'wpjam-net', 	'我的产品 &lsaquo; WPJAM应用商城', 	'我的产品'.$update_info,		'manage_options',	'wpjam-net-my', 	'wpjam_net_my_page');
		//add_submenu_page( 'wpjam-net', 	'我要赚钱 &lsaquo; WPJAM应用商城', 	'我要赚钱', 					'manage_options',	'wpjam-net-about',	'wpjam_net_about_page');
	}
}

function wpjam_net_my_page(){
	global $plugin_page;
	add_thickbox();
	$wpjam_net_plugin_datas =  wpjam_net_get_plugin_datas();
	?>
	<div class="wrap">
		<h2>我的产品</h2>

		<?php if($wpjam_net_plugin_datas){ ?>
		
		<?php $item_ids = apply_filters('wpjam_net_item_ids', array()); ?>

		<?php if($item_ids) { ?>

		<p>下面是你在 <a href="http://wpjam.net/">WPJAM应用商城</a>所购买的产品：</p>
		
		<table class="widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">插件</th>
					<th>介绍</th>
					<th>状态</th>
					<th style="min-width:48px;">动作</th>
				</th>
			</thead>
			<tbody>
			<?php $thickbox_content = ''; ?>
			<?php $alternate = '';?>
			<?php foreach ($item_ids as $id=>$plugin_file) {

				$alternate = $alternate?'':'alternate';

				$item = $wpjam_net_plugin_datas->items[$id];

				$plugin_status = wpjam_net_check_plugin_status($plugin_file,$item);

				$action = $plugin_status['action'];
				$status = $plugin_status['status'];

				?>
				<tr class="<?php echo $alternate; ?>">
					<td><img src="<?php echo $item->thumb; ?>" width="50" /></td>
					<td><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&inlineId=item_'.$item->id.'&TB_inline&width=500&height=600');?>" class="thickbox"><?php echo $item->name;?> </a></td>
					<td><?php echo $item->sections['description']; ?></td>
					<td><?php echo $status; ?></td>
					<td><?php echo $action; ?></td>
				</tr>
				<?php 
				ob_start();
				wpjam_net_single_product($item,$action);
				$thickbox_content .= ob_get_contents();
				ob_end_clean();
			}?>
			</tbody>
		</table>
		<p>* 每10分钟检查一次，如果你已经更新了，但是上面的状态还不对，请稍后再检查。</p>

		<?php echo $thickbox_content; ?>
		
		<?php }else{ ?>
		<p>你还未在 <a href="http://wpjam.net/">WPJAM应用商城</a>购买任何产品！</p>
		<?php } ?>
		<?php }else{ ?>
		<p>网络异常，请刷新！</p>
		<?php } ?>
	</div>
	<?php
}

function wpjam_net_page(){
	global $plugin_page;
	add_thickbox();

	$wpjam_net_plugin_datas =  wpjam_net_get_plugin_datas();
	?>
	<div class="wrap">
		<h2>所有产品</h2>
		<?php if($wpjam_net_plugin_datas){ ?>
		<table class="widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">插件</th>
					<th>介绍</th>
					<th style="width:48px;">最新版</th>
					<th style="width:32px;">价格</th>
					<th style="width:64px;">动作</th>
				</th>
			</thead>
			<tbody>
			<?php $thickbox_content = ''; ?>
			<?php $alternate = '';?>
			<?php foreach ($wpjam_net_plugin_datas->items as $item) { $alternate = $alternate?'':'alternate';?>
				<tr class="<?php echo $alternate;?>">
					<td><img src="<?php echo $item->thumb; ?>" width="50" /></td>
					<td><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&inlineId=item_'.$item->id.'&TB_inline&width=500&height=600'); ?>" class="thickbox"><?php echo $item->name; ?></a></td>
					<td><?php echo $item->sections['description']; ?></td>
					<td><?php echo $item->new_version; ?></td>
					<td><?php echo $item->price; ?></td>
					<td><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&inlineId=item_'.$item->id.'&TB_inline&width=500&height=600'); ?>" class="thickbox">详细介绍</a></td>
				</th>
				<?php

				$action = false;
				$item_ids = apply_filters('wpjam_net_item_ids', array());
				if(isset($item_ids[$item->id])){
					$plugin_file = $item_ids[$item->id];

					$plugin_status = wpjam_net_check_plugin_status($plugin_file,$item);

					$action = $plugin_status['action'];
				}

				ob_start();
				wpjam_net_single_product($item,$action);
				$thickbox_content .= ob_get_contents();
				ob_end_clean();
				?>
			<?php }?>
			</tbody>
		</table>
		<?php echo $thickbox_content;?>
		<?php }else{ ?>
		<p>网络异常，请刷新！</p>	
		<?php } ?>
	</div>
	<?php
}

function wpjam_net_about_page(){
?>
<div class="wrap">
<h2>我要赚钱</h2>

	<h3>1. 推荐好友</h3>

	<p>您可以在WPJAM商城的任意链接后面加上 <code>?ref=denishua</code>，如果您推荐的朋友在三天内购买了产品，您将从中获得销售额15%的提成。例如：</p>

	<ul>
		<li>首页推荐链接 <a href="http://wpjam.net/?ref=denishua">http://wpjam.net/?ref=denishua</a></li>
		<li>具体产品推荐链接 <a href="http://wpjam.net/item/weixin-robot-advanced/?ref=denishua">http://wpjam.net/item/weixin-robot-advanced/?ref=denishua</a></li>
	</ul>

	<h3>2. 发布产品</h3>

	<p>如果你会制作 WordPress 主题或者插件，你可以将你的产品（名称，图文介绍，150x150 截图，压缩包，演示地址）放到 WPJAM应用商城上架销售，详细请联系 Denis，QQ：11497107。</p>

</div>
<?php
}

function wpjam_net_check_plugin_status($local_file,$remote_item){
	$local = get_plugin_data( $local_file );
	$current_version = $local['Version'];

	$new_version = $remote_item->new_version;

	if(!wpjam_net_check_domain($remote_item->id)){
		$status = '你的域名还没有经过授权';
		$action = '<a href="http://wpjam.net/wp-admin/admin.php?page=orders&domain_limit=1&product_id='.$remote_item->id.'" class="button button-primary" target="_blank">授权域名</a>';
	}elseif($new_version > $current_version){
		$status = '你现在使用版本是：<strong style="color:red;">'.$current_version.'</strong>，服务器上最新版本是：<strong style="color:red;">'.$new_version;
		$action = '<a href="http://wpjam.net/wp-admin/admin.php?page=orders" class="button button-primary" target="_blank">下载最新版</a>';
	}else{
		$status = '你使用的是最新版，无需更新。使用愉快 :-) ';
		$action = '<a href="http://wpjam.net/wp-admin/admin.php?page=orders" class="button" target="_blank">已授权</a>';
	}

	return array('status'=>$status, 'action'=>$action);

}

function wpjam_net_single_product($item,$action=false){
	?>
	<div style="display:none;" id="item_<?php echo $item->id;?>">
		<h2><?php echo $item->name; ?></h2>

		<p>
			<img src="<?php echo $item->thumb; ?>" style="float:left; padding:0 20px 0 0;" />
			<?php if($action) { ?>
				<?php echo str_replace('class="button button-primary"', 'class="button button-primary" style="float:right; color:#fff;"', $action); ?>
			<?php }else{?>
				<a href="<?php echo $item->homepage; ?>" class="button button-primary" style="float:right; color:#fff;"  target="_blank"><?php if($item->price == '免费'){echo '免费下载';}else{ echo '点击购买'; } ?></a>
			<?php } ?>
			最新版：<strong style="color:red;"><?php echo $item->new_version; ?></strong><br />
			价格：<strong style="color:red;"><?php echo $item->price; ?></strong><br />
		</p>

		<p><?php echo $item->sections['description'];?> [<a href="<?php echo $item->homepage; ?>" target="_blank">详细介绍...</a>]</p>

		<div style="clear:both;"></div>

		<?php if($item->sections['changelog']){ ?>
		<h3>更新历史</h3>
		<?php echo $item->sections['changelog'];?>
		<?php } ?>

	</div>
	<?php
}


//if(is_admin()){
//	add_action('init','wpjam_net_init');
//}

//function wpjam_net_init(){
//	add_action('wp_dashboard_setup', 'wpjam_net_dashboard_setup' );
//}

function wpjam_net_dashboard_setup() {
	if(current_user_can('manage_options')){

		$wpjam_net_count = wpjam_net_get_count();

		$update_plugins_count = $wpjam_net_count['update_plugins_count'];
		$update_domains_count = $wpjam_net_count['update_domains_count'];
			
		$total_count = $update_domains_count + $update_plugins_count;

		$wpjam_net_sale = get_transient('wpjam_net_sale');

		if($wpjam_net_sale === false){
			$wpjam_net_sale = wpjam_net_api_request( array( 'action' => 'sale' ) );

			if($wpjam_net_sale){
				
				$wpjam_net_sale = $wpjam_net_sale->sale;
				set_transient('wpjam_net_sale',$wpjam_net_sale,300);
			}
		}

		global $wpjam_net_info;
		$wpjam_net_info = '';
		if($total_count > 0 ){
			$wpjam_net_info .= '<p>待审核：'.(int)$update_domains_count.'，未更新：'.(int)$update_plugins_count.'，请点击<a href="'.admin_url('admin.php?page=wpjam-net-my').'">这里查看详情</a>。</p>';
		}

		if($wpjam_net_sale){
			$wpjam_net_info .= wpautop($wpjam_net_sale);
		}

		if($wpjam_net_info){
			add_meta_box( 'wpjam_net_dashboard_widget', '<span style="color:red;">WPJAM 商城</span>', 'wpjam_net_dashboard_widget','dashboard', 'normal', 'core' );
		}

	}
}

function wpjam_net_dashboard_widget(){
	
	global $wpjam_net_info;
	echo '<div style="color:red; font-weight:bold;">'.$wpjam_net_info.'</div>';
}

function wpjam_net_get_domain(){
	$url_parts = parse_url(home_url());
	if(isset($url_parts['host'])){
		return $url_parts['host'];
	}else{
		return '';
	}
}

function wpjam_net_get_count(){
	$wpjam_net_count = get_transient('wpjam_net_count');
	if($wpjam_net_count === false){
		$item_ids = apply_filters('wpjam_net_item_ids', array());
		$wpjam_net_plugin_datas =  wpjam_net_get_plugin_datas();

		$update_plugins_count = 0;
		$update_domains_count = 0;

		if($item_ids && $wpjam_net_plugin_datas){
			foreach ($item_ids as $id=>$plugin_file) {
				$plugin_data = get_plugin_data( $plugin_file );
				$current_version = $plugin_data['Version'];

				$item = $wpjam_net_plugin_datas->items[$id];
				$new_version = $item->new_version;			

				if(!wpjam_net_check_domain($item->id)){
					$update_domains_count++;
				}elseif($new_version > $current_version){
					$update_plugins_count++;
				}
			}
		}
		$wpjam_net_count = array('update_plugins_count'=>$update_plugins_count, 'update_domains_count'=>$update_domains_count);
		set_transient('wpjam_net_count',$wpjam_net_count,300);
	}
	return $wpjam_net_count;
}