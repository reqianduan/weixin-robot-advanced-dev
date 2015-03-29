<?php
add_action('admin_head','weixin_robot_admin_head');
function weixin_robot_admin_head(){
	global $plugin_page;
	if(in_array($plugin_page, array('weixin-robot-stats2','yixin-robot-stats2'))){
?>
<link rel="stylesheet" href="http://cdn.staticfile.org/morris.js/0.4.3/morris.css" />
<script type='text/javascript' src="http://cdn.staticfile.org/raphael/2.1.2/raphael-min.js"></script>
<script type='text/javascript' src="http://cdn.staticfile.org/morris.js/0.4.3/morris.min.js"></script>
<style type="text/css">
input[type="date"]{ background-color: #fff; border-color: #dfdfdf; border-radius: 3px; border-width: 1px; border-style: solid; color: #333; outline: 0; box-sizing: border-box; }
</style>
<?php
	}
}

function weixin_robot_stats_get_start_date(){
	$start_date	= (isset($_REQUEST['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_REQUEST['start_date']))?$_REQUEST['start_date']:'';
	if(!$start_date) $start_date=gmdate('Y-m-d',current_time('timestamp')-(60*60*24*30));
	return $start_date;
}

function weixin_robot_stats_get_end_date(){
	$end_date 	= (isset($_REQUEST['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_REQUEST['end_date']))?$_REQUEST['end_date']:'';
	if(!$end_date) $end_date=gmdate('Y-m-d',current_time('timestamp'));
	return $end_date;
}

function weixin_robot_stats_get_type(){
	return isset($_REQUEST['type'])?$_REQUEST['type']:'';
}

function weixin_robot_stats_header(){
	global $plugin_page;
	$start_date		= weixin_robot_stats_get_start_date();
	$end_date		= weixin_robot_stats_get_end_date();
	$current_tab	= isset($_GET['tab'])?$_GET['tab']:'message-stats';
	?>
	<div class="tablenav">
    <div class="alignleft actions">
        <form method="get" action="admin.php" target="_self" id="export-filter" style="float:left;">
        	<input type="hidden" name="page" value="<?php echo $plugin_page;?>" />
        	<input type="hidden" name="tab" id="tab" value="<?php echo $current_tab;?>" />
            日期:
            <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($start_date);?>" size="11" />
            -
            <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($end_date);?>" size="11" />
            <input type="submit" value="　显示　" class="button-secondary" name="">
        </form>
    </div>
	</div>
    <h3><?php echo $start_date,' - ',$end_date; ?> 汇总数据：</h3>
<?php
}

function weixin_robot_stats_get_types(){
	return array(
		'total'			=>'所有类型',
		'text'			=>'文本消息', 
		'event'			=>'事件消息', 
		'subscribe'		=>'用户订阅', 
		'unsubscribe'	=>'取消订阅', 
		'netuser'		=>'净增长', 
		'location'		=>'位置消息', 
		'image'			=>'图片消息', 
		'link'			=>'链接消息', 
		'voice'			=>'语音消息'
	);
}

function weixin_robot_get_response_types(){
	$response_types = array(
		'total'			=> '所有类型',
		'advanced'		=> '高级回复',
		'welcome'		=> '欢迎语',
		'tag'			=> '标签最新日志',
		'cat'			=> '分类最新日志',
		'custom-text'	=> '自定义文本回复',
		'custom-img'	=> '自定义图文回复',
		'custom-image'	=> '自定义图片回复',
		'custom-voice'	=> '自定义音频回复',
		'custom-music'	=> '自定义音乐回复',
		'custom-video'	=> '自定义视频回复',
		'query'			=> '搜索查询回复',
		'too-long'		=> '关键字太长',
		'not-found'		=> '没有匹配内容',
		'voice'			=> '语音自动回复',
		'loction'		=> '位置自动回复',
		'link'			=> '链接自动回复',
		'image'			=> '图片自动回复',
		'enter-reply'	=> '进入微信回复',
		'3rd'			=> '第三方回复',
		'view'			=> '打开网页',

		'checkin'		=> '回复签到',
		'credit'		=> '回复积分'
	);

	return apply_filters('weixin_response_types',$response_types);
}

function weixin_robot_stats2_page(){
	global $wpdb, $plugin_page;

	$start_date	= weixin_robot_stats_get_start_date();
	$end_date 	= weixin_robot_stats_get_end_date();

	$current_tab = isset($_GET['tab'])?$_GET['tab']:'message-stats';

	$tabs = array('message-stats'=>'消息','reply-stats'=>'回复','user-stats'=>'活跃用户');
	if(weixin_robot_get_setting('weixin_app_id') && weixin_robot_get_setting('weixin_app_secret')) {
		$tabs['custom-menu-stats']	= '自定义菜单';
		if(weixin_robot_get_setting('weixin_advanced_api')) {
			$tabs['qrcode-stats']	= '二维码扫描';
		}
	}
	//if(weixin_robot_get_setting('weixin_credit')){
	//	$tabs['post-share']	= '文章分享';
	//	$tabs['user-share']	= '用户分享';
	//}
	$tabs = apply_filters('weixin_stats_tabs', $tabs);
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
		<?php foreach ($tabs as $tab=>$tab_name) {?>
			<a class="nav-tab <?php if($current_tab == $tab){ echo 'nav-tab-active'; } ?>" href="<?php echo 'admin.php?page='.$plugin_page.'&tab='.$tab.'&start_date='.$start_date.'&end_date='.$end_date;?>"><?php echo $tab_name; ?></a>
	    <?php }?>    
	    </h2>
	    <?php call_user_func('weixin_robot_'.str_replace('-', '_', $current_tab).'_page'); ?>
	</div>
	<?php
}

function weixin_robot_message_stats_page() {
	?>
	<h3>消息统计分析</h3>
	<?php
	global $wpdb, $plugin_page;

	$start_date	= weixin_robot_stats_get_start_date();
	$end_date 	= weixin_robot_stats_get_end_date();
	$end_time	= $end_date.' 23:59:59';

	$type = weixin_robot_stats_get_type();
	if(!$type) $type = 'total';

	$types = weixin_robot_stats_get_types();

	$where = 'CreateTime > '.strtotime($start_date).' AND CreateTime < '.strtotime($end_time);
	$sum = "
	SUM(case when MsgType='text' then 1 else 0 end) as text,
	SUM(case when MsgType='event' AND Event!='subscribe' AND Event!='unsubscribe' then 1 else 0 end) as event, 
	SUM(case when MsgType='event' AND Event='subscribe' then 1 else 0 end) as subscribe, 
	SUM(case when MsgType='event' AND Event='unsubscribe' then 1 else 0 end) as unsubscribe,
	SUM(case when MsgType='event' AND Event='subscribe' then 1 when MsgType='event' AND Event='unsubscribe' then -1 else 0 end ) as netuser,
	SUM(case when MsgType='location' then 1 else 0 end) as location, 
	SUM(case when MsgType='image' then 1 else 0 end) as image, 
	SUM(case when MsgType='link' then 1 else 0 end) as link, 
	SUM(case when MsgType='voice' then 1 else 0 end) as voice
	";

	weixin_robot_stats_header();

	$sql = "SELECT {$sum} FROM {$wpdb->weixin_messages} WHERE {$where}";

	$count = $wpdb->get_row($sql);

	?>
	<div style="display:table;">

		<div style="display: table-row;">

			<div id="total-chart" style="display: table-cell; width:450px; float:left;"></div>

			<div style="display: table-cell; float:left; width:240px;">
				<table class="widefat" cellspacing="0">
					<thead>
						<tr>
							<th>类型</th>
							<th>数量</th>
						</tr>
					</thead>
					<tbody>
					<?php $data = array();?>
					<?php $alternate = '';?>
					<?php foreach ($types as $key=>$value) { $alternate = $alternate?'':'alternate';?>
						<?php if($key != 'total' && $count->$key){?>
						<?php $data []= '{"label": "'.$value.'", "value": '.$count->$key.' }'; ?>
						<tr class="<?php echo $alternate; ?>">
							<td><?php echo $value; ?></td>
							<td><?php echo $count->$key; ?></td>
						</tr>
						<?php }?>
					<?php } ?>
					<?php $data = "\n".implode(",\n", $data)."\n";?>
					</tbody>
				</table>
			</div>

		</div>

	</div>

	<script type="text/javascript">
		Morris.Donut({
		  element: 'total-chart',
		  data: [<?php echo $data;?>]
		});
	</script>

	<div style="clear:both;"></div>

	<h3>每日详细数据</h3>

	<ul class="subsubsub">
		<?php $current_page_base_url = 'admin.php?page='.$plugin_page.'&start_date='.$start_date.'&end_date='.$end_date; ?>
		<?php foreach ($types as $key=>$value) { ?>
		<li class="<?php echo $key?>"><a href="<?php echo admin_url($current_page_base_url.'&type='.$key)?>" <?php if($type == $key) {?> class="current"<?php } ?>><?php echo $value;?></a> |</li>
		<?php }?>
	</ul>

	<div style="clear:both;"></div>

	<?php

	$sql = "SELECT FROM_UNIXTIME(CreateTime, '%Y-%m-%d') as day, count(id) as total, {$sum} FROM {$wpdb->weixin_messages} WHERE {$where} GROUP BY day ORDER BY day;";

	$counts = $wpdb->get_results($sql);

	$data = array();

	if($type == 'total'){	
		$morris_ykeys = array('total','text','event','subscribe','netuser');

		$morris_labels = array();
		foreach ($morris_ykeys as $morris_ykey) {
			$morris_labels[] = $types[$morris_ykey];
		}

		foreach ($counts as $count) {
			$morris_data = '';
			foreach ($morris_ykeys as $morris_ykey) {
				$morris_data .= ', "'.$morris_ykey.'": '.$count->$morris_ykey;
			}
			$data []= '{"day": "'.$count->day.'"'.$morris_data.' }';
		}

		$morris_ykeys = "'".implode("','", $morris_ykeys)."'";
		$morris_labels = "'".implode("','", $morris_labels)."'";

	}else{
		$morris_ykeys = "'".$type."'";
		$morris_labels = "'".$types[$type]."'";

		foreach ($counts as $count) {
			$data []= '{"day": "'.$count->day.'"'.', "'.$type.'": '.$count->$type.' }';
		}
	}

	$data = "\n".implode(",\n", $data)."\n";

	?>
	
	<div id="daily-chart"></div>

	<script type="text/javascript">
		Morris.Line({
			element: 'daily-chart',
			data: [<?php echo $data;?>],
			xkey: 'day',
			ykeys: [<?php echo $morris_ykeys;?>],
			labels: [<?php echo $morris_labels;?>]
		});
	</script>
	
	<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<th>日期</th>
			<?php foreach ($types as $key=>$value) {?>
			<th><?php echo $value;?></th>
			<?php }?>
		</tr>
	</thead>
	<tbody>
	<?php $alternate = ''; ?>
	<?php foreach (array_reverse($counts) as $count) { $alternate = $alternate?'':'alternate';?>
		<tr class="<?php echo $alternate; ?>">
			<td><?php echo $count->day; ?></td>
			<?php foreach ($types as $key=>$value) {?>
			<td><?php echo $count->$key;?></td>
			<?php }?>
		</tr>
	<?php } ?>
	</tbody>
	</table>
	<?php
}

function weixin_robot_reply_stats_page(){ 
	?>
	<h3>回复统计分析</h3>
	<?php 
	global $wpdb, $plugin_page;

	$start_date	= weixin_robot_stats_get_start_date();
	$end_date 	= weixin_robot_stats_get_end_date();
	$end_time	= $end_date.' 23:59:59';

	$response_types = weixin_robot_get_response_types();
	$response_type = isset($_GET['response_type'])?$_GET['response_type']:'total';

	$where = 'CreateTime > '.strtotime($start_date).' AND CreateTime < '.strtotime($end_time);

	weixin_robot_stats_header();

	$sql = "SELECT COUNT( * ) AS count, Response FROM {$wpdb->weixin_messages} WHERE {$where} AND (MsgType ='text' OR MsgType = 'event') AND Event!='subscribe' AND Event!='unsubscribe' GROUP BY Response ORDER BY count DESC";

	$counts = $wpdb->get_results($sql);
	?>
	<div style="display:table;">

		<div style="display: table-row;">

			<div id="total-chart" style="display: table-cell; width:450px; float:left;"></div>

			<div style="display: table-cell; float:left; width:240px;">
				<table class="widefat" cellspacing="0">
					<thead>
						<tr>
							<th>回复类型</th>
							<th>数量</th>
						</tr>
					</thead>
					<tbody>
					<?php $data = array(); $i=0;?>
					<?php $alternate = '';?>
					<?php foreach ($counts as $count) { $alternate = $alternate?'':'alternate';?>
						<?php if($count->Response && isset($response_types[$count->Response])){?>
						<?php $data []= '{"label": "'.$response_types[$count->Response].'", "value": '.$count->count.' }'; $i ++; ?>
						<?php if ($i < 15 ) {?>
						<tr class="<?php echo $alternate;?>">
							<td><?php echo $response_types[$count->Response]; ?></td>
							<td><?php echo $count->count; ?></td>
						</tr>
						<?php } ?>
						<?php }?>
					<?php } ?>
					<?php $data = "\n".implode(",\n", $data)."\n";?>
					</tbody>
				</table>
			</div>

		</div>

	</div>

	<script type="text/javascript">
		Morris.Donut({
		  element: 'total-chart',
		  data: [<?php echo $data;?>]
		});
	</script>

	<div style="clear:both;"></div>

	<h3>详细回复统计分析</h3>

	<ul class="subsubsub">
		<?php $current_page_base_url = 'admin.php?page='.$plugin_page.'&tab=summary&start_date='.$start_date.'&end_date='.$end_date; ?>
		<li class="<?php echo 'total'?>"><a href="<?php echo admin_url($current_page_base_url.'&response_type=total')?>" <?php if($response_type == 'total') {?> class="current"<?php } ?>>全部</a> |</li>
		<?php foreach ($counts as $count) { ?>
		<?php if($count->Response && isset($response_types[$count->Response])){?>
		<li class="<?php echo $count->Response;?>"><a href="<?php echo admin_url($current_page_base_url.'&response_type='.$count->Response)?>" <?php if($response_type == $count->Response) {?> class="current"<?php } ?>><?php echo $response_types[$count->Response];?></a> |</li>
		<?php } ?>
		<?php }?>
	</ul>

	<?php

	if($response_type == 'total'){
		$where .= " AND Response != ''";
	}else{
		$where .= " AND Response = '{$response_type}'";
	}

	$sql = "SELECT COUNT( * ) AS count, Response, MsgType, (case when Content='' then EventKey else Content end) as Content FROM {$wpdb->weixin_messages} WHERE {$where} AND ( MsgType ='text' OR (MsgType = 'event'  AND Event!='subscribe' AND Event!='unsubscribe')) GROUP BY LOWER(Content) ORDER BY count DESC LIMIT 0 , 100";

	$sql = "SELECT COUNT( * ) AS count, Response, MsgType, Content FROM ( SELECT Response, MsgType, LOWER(Content) as Content FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType ='text' UNION ALL SELECT Response, MsgType,  LOWER(EventKey) as Content FROM {$wpdb->weixin_messages} WHERE {$where} AND MsgType = 'event'  AND Event!='subscribe' AND Event!='unsubscribe') as T1 GROUP BY Content ORDER BY count DESC LIMIT 0 , 100";

	
	$weixin_hot_messages = $wpdb->get_results($sql);
	?>
	<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<th style="width:60px">排名</th>
			<th style="width:80px">数量</th>
			<th>关键词</th>
			<th style="width:150px">回复类型</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$i = 0;
	$alternate = '';
	foreach ($weixin_hot_messages as $weixin_message) {
		if(isset($response_types[$weixin_message->Response])){
		$alternate = $alternate?'':'alternate';
		$i++;
	?>
		<tr class="<?php echo $alternate; ?>">
			<td><?php echo $i; ?></td>
			<td><?php echo $weixin_message->count; ?></td>
			<td><?php echo $weixin_message->Content; ?></td>
			<td><?php echo $response_types[$weixin_message->Response]; ?></td>
		</tr>
		<?php } ?>
	<?php } ?>
	</tbody>
	</table>
	<?php
}

function weixin_robot_user_stats_page(){ 
	?>
	<h3>用户活跃度统计分析</h3>
	<?php 
	global $wpdb, $plugin_page;

	$start_date	= weixin_robot_stats_get_start_date();
	$end_date 	= weixin_robot_stats_get_end_date();
	$end_time	= $end_date.' 23:59:59';

	$where = 'CreateTime > '.strtotime($start_date).' AND CreateTime < '.strtotime($end_time);

	weixin_robot_stats_header();

	$sum = "
	SUM(case when MsgType='text' then 1 else 0 end) as text,
	SUM(case when MsgType='event' AND Event!='subscribe' AND Event!='unsubscribe' then 1 else 0 end) as event, 
	SUM(case when MsgType='location' then 1 else 0 end) as location, 
	SUM(case when MsgType='image' then 1 else 0 end) as image, 
	SUM(case when MsgType='link' then 1 else 0 end) as link, 
	SUM(case when MsgType='voice' then 1 else 0 end) as voice
	";

	$sql = "SELECT COUNT( * ) AS total, FromUserName, {$sum} FROM {$wpdb->weixin_messages} WHERE {$where} GROUP BY FromUserName ORDER BY total DESC LIMIT 0,100 ";

	$counts = $wpdb->get_results($sql);

	$types = weixin_robot_stats_get_types();
	unset($types['subscribe']);
	unset($types['unsubscribe']);
	unset($types['netuser']);

	?>
	
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<?php if(weixin_robot_get_setting('weixin_advanced_api') && strpos($wpdb->weixin_messages, 'weixin')){?>
				<th colspan="2">用户</th>
				<?php } else { ?>
				<th>用户</th>
				<?php }?>
				<?php foreach ($types as $key=>$value) {?>
				<th><?php echo $value;?></th>
				<?php }?>
			</tr>
		</thead>
		<tbody>
		<?php $data = array(); $i=0;?>
		<?php $alternate = '';?>
		<?php foreach ($counts as $count) { $alternate = $alternate?'':'alternate';?>
			<?php if($count->FromUserName){?>
			<?php $weixin_openid=$count->FromUserName; ?>
			<tr class="<?php echo $alternate;?>">
			<?php if(weixin_robot_get_setting('weixin_advanced_api') && strpos($wpdb->weixin_messages, 'weixin')){?>
				<?php $weixin_user = weixin_robot_get_user($weixin_openid); ?>
				<?php if($weixin_user['subscribe']){ ?>
				<td>
				<?php 
				$weixin_user_avatar = '';
				if(!empty($weixin_user['headimgurl'])){
					$weixin_user_avatar = $weixin_user['headimgurl'];
				?>
					<a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&openid='.$weixin_openid)?>"><img src="<?php echo $weixin_user_avatar; ?>" width="32" /></a>
				<?php }?>
				</td>
				<td>
					<?php echo $weixin_user['nickname']; ?>（<?php if($weixin_user['sex']==1){ echo '男'; } elseif($weixin_user['sex']==2) { echo '女'; }else{ echo "未知"; }?>）<br />
					<?php echo $weixin_user['country'].' '.$weixin_user['province'].' '.$weixin_user['city'];?><br />
				</td>
				<?php } else{ ?>
				<td colspan="2">
					<span style="color:red;">*已经取消关注</span>
				</td>
				<?php } ?>
			<?php }else{ ?>
				<td><?php echo $weixin_openid; ?></td>
			<?php } ?>
			<?php foreach ($types as $key=>$value) {?>
				<td><?php echo $count->$key;?></td>
			<?php }?>
			</tr>
			<?php }?>
		<?php } ?>
		<?php $data = "\n".implode(",\n", $data)."\n";?>
		</tbody>
	</table>
	<?php
}

function weixin_robot_custom_menu_stats_page(){
?>
	<h3>自定义菜单点击统计分析</h3>
	<?php 

	global $wpdb, $plugin_page;

	$start_date	= weixin_robot_stats_get_start_date();
	$end_date 	= weixin_robot_stats_get_end_date();
	$end_time	= $end_date.' 23:59:59';

	$where = 'CreateTime > '.strtotime($start_date).' AND CreateTime < '.strtotime($end_time);

	weixin_robot_stats_header();


	$weixin_robot_custom_menus = get_option('weixin-robot-custom-menus');

	$click_keys = array();

	if($weixin_robot_custom_menus){
		foreach($weixin_robot_custom_menus as $weixin_robot_custom_menu){
			if( $weixin_robot_custom_menu['key'] ){
				$click_keys[] = $weixin_robot_custom_menu['key'];
			}	
		}

		if($click_keys){
			$click_keys = "'".implode("','", $click_keys)."'";

			$sql = "SELECT EventKey, count(*) as count FROM {$wpdb->weixin_messages} WHERE 1=1 AND {$where} AND MsgType = 'event' AND EventKey in({$click_keys}) GROUP BY EventKey";

			$counts = $wpdb->get_results($sql,OBJECT_K);

			$sql = "SELECT count(*) as total FROM {$wpdb->weixin_messages} WHERE 1=1 AND {$where} AND MsgType = 'event' AND EventKey in({$click_keys})";

			$total = $wpdb->get_var($sql);
		}
	}	

	?>
	
	<?php if($weixin_robot_custom_menus && $click_keys) { ?>

	<!--<p>只有点击类型的菜单才能统计！！</p>-->

	<?php $weixin_robot_ordered_custom_menus = weixin_robot_get_ordered_custom_menus($weixin_robot_custom_menus);?>
	
	<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<th>按钮</th>
			<th>按钮位置/子按钮位置</th>
			<th>类型</th>
			<th>Key/URL</th>
			<th>点击数</th>
			<th>比率</th>
		</tr>
	</thead>
	<tbody>
	<?php $alternate = '';?>
	<?php foreach($weixin_robot_ordered_custom_menus as $weixin_robot_custom_menu){ $alternate = $alternate?'':'alternate'; ?>
		<?php if(isset($weixin_robot_custom_menu['parent'])){ $weixin_menu = $weixin_robot_custom_menu['parent'];?>
		<tr class="<?php echo $alternate; ?>">
			<td><?php echo $weixin_menu['name']; ?></td>
			<td><?php echo $weixin_menu['position']; ?></td>
			<td><?php echo $weixin_menu['type']; ?></td>
			<td <?php if(empty($counts[$weixin_menu['key']]) ) {echo 'colspan="3"';} ?>><?php echo $weixin_menu['key']; ?></td>
			<?php $id = $weixin_menu['id'];?>
			<?php if(isset($counts[$weixin_menu['key']])) { $count = $counts[$weixin_menu['key']]->count; ?>
			<td><?php echo $count;?></td>
			<td><?php echo round($count/$total*100,2).'%'; ?>
			<?php }?>
		</tr>
		<?php } ?>
		<?php if(isset($weixin_robot_custom_menu['sub'])){  ?>
		<?php foreach($weixin_robot_custom_menu['sub'] as $weixin_menu){ $alternate = $alternate?'':'alternate';?>
		<tr colspan="4" class="<?php echo $alternate; ?>">
			<td> └── <?php echo $weixin_menu['name']; ?></td>
			<td> └── <?php echo $weixin_menu['sub_position']; ?></td>
			<td><?php echo $weixin_menu['type']; ?></td>
			<td><?php echo $weixin_menu['key']; ?></td>
			<?php $id = $weixin_menu['id'];?>
			<?php if(isset($counts[$weixin_menu['key']])) { $count = $counts[$weixin_menu['key']]->count; ?>
			<td><?php echo $count;?></td>
			<td><?php echo round($count/$total*100,2).'%'; ?>
			<?php }?>
		<tr>
		<?php }?>
		<?php } ?>
	<?php } ?>
	</tbody>
	</table>
	<?php }
}

function weixin_robot_qrcode_stats_page(){
?>
	<h3>二维码扫描统计分析</h3>
	<?php 

	global $wpdb, $plugin_page;

	$qrcode_types = weixin_robot_get_qrcode_types();

	$start_date	= weixin_robot_stats_get_start_date();
	$end_date 	= weixin_robot_stats_get_end_date();
	$end_time	= $end_date.' 23:59:59';

	$where = 'CreateTime > '.strtotime($start_date).' AND CreateTime < '.strtotime($end_time);

	weixin_robot_stats_header();

	$weixin_robot_qrcodes = $wpdb->get_results("SELECT * FROM $wpdb->weixin_qrcodes;");

	if($weixin_robot_qrcodes){
		$scenes		= array();
		$tickets	= array();
		foreach ($weixin_robot_qrcodes as $weixin_robot_qrcode) {
			$scenes[]	= $weixin_robot_qrcode->scene;
			$qrscenes[]	= 'qrscene_'.$weixin_robot_qrcode->scene;
		}

		if ($scenes) {
			$scenes = implode(',', $scenes);

			$sql = "SELECT EventKey, count(*) as count FROM {$wpdb->weixin_messages} WHERE 1=1 AND {$where} AND MsgType = 'event' AND Event = 'SCAN' AND EventKey in({$scenes}) GROUP BY EventKey";

			$scene_counts = $wpdb->get_results($sql,OBJECT_K);
		}

		if($qrscenes){
			$qrscenes = "'".implode("','", $qrscenes)."'";

			$sql = "SELECT EventKey, count(*) as count FROM {$wpdb->weixin_messages} WHERE 1=1 AND {$where} AND MsgType = 'event' AND Event = 'subscribe' AND EventKey in({$qrscenes}) GROUP BY EventKey";

			$qrscene_counts = $wpdb->get_results($sql,OBJECT_K);
		}
	}
	?>
	
	<?php if($weixin_robot_qrcodes && $scene_counts) { ?>
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
				<th>关注</th>
				<th>扫描</th>
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
				<td><?php echo isset($qrscene_counts['qrscene_'.$weixin_robot_qrcode->scene])?$qrscene_counts['qrscene_'.$weixin_robot_qrcode->scene]->count:''; ?></td>
				<td><?php echo isset($scene_counts[$weixin_robot_qrcode->scene])?$scene_counts[$weixin_robot_qrcode->scene]->count:''; ?></td>
			</tr>
		<?php } ?>
		</tbody>
		</table>
	</form>
	<?php }
}

function weixin_robot_bulk_send_message_page(){
?>
	<div class="wrap">
		<?php 

		global $wpdb,$plugin_page;

		$timestamp_48 = current_time('timestamp') - (48+get_option('gmt_offset'))*3600;
		$sql = "SELECT FromUserName FROM {$wpdb->weixin_messages}  WHERE CreateTime > $timestamp_48 GROUP BY FromUserName;";

		$weixin_openids	= $wpdb->get_col($sql);

		if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
			if ( !wp_verify_nonce($_POST['weixin_robot_bulk_send_message_nonce'],'weixin_robot') ){
				ob_clean();
				wp_die('非法操作');
			}
			$reply_type 	= stripslashes( trim( $_POST['reply_type'] ));
			$content 		= stripslashes( trim( $_POST['content'] ));

			foreach ($weixin_openids as $weixin_openid) {
				if($content){	
					$succeed_msg = weixin_rebot_sent_user($weixin_openid, $content, $reply_type);
				}	
			}
		}
		?>

		<?php if(!empty($succeed_msg)){?>
		<div class="updated">
			<p><?php echo $succeed_msg;?></p>
		</div>
		<?php }?>
		
		<h2>群发消息</h2>
		<form action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" method="POST">
			<p>
				<textarea name="content" id="content" rows="5" cols="50" class="regular-text code"></textarea>
			</p>
			<p>
				<select name="reply_type" id="reply_type" >
					<option value="img">图文回复</option>
					<option value="text">文本回复</option>
				</select>
			</p>
			<?php wp_nonce_field('weixin_robot','weixin_robot_bulk_send_message_nonce'); ?>
			<p><input type="submit" name="submit" id="submit" class="button button-primary" value="群发消息"></p>
			<p>* 消息将群发到<?php echo count($weixin_openids); ?>用户</p>
		</form>
	</div>
<?php
}

function weixin_robot_messages_page() {
	?>
	<div class="wrap">
		
		<h2>最新消息</h2>
		<p>下面是你公众号上最新的消息，你可以直接删除（WordPress 本地删除，公众号后台不受影响）！</p>

		<?php
		global $wpdb,$plugin_page;

		if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
			if ( !wp_verify_nonce($_POST['weixin_robot_send_user_nonce'],'weixin_robot') ){
				ob_clean();
				wp_die('非法操作');
			}

			$weixin_openid	= stripslashes( trim( $_POST['weixin_openid'] ));
			$reply_id 		= stripslashes( trim( $_POST['reply_id'] ));
			$reply_type 	= stripslashes( trim( $_POST['reply_type'] ));
			$content 		= stripslashes( trim( $_POST['content'] ));

			if($weixin_openid && $message_id && $content){
				$data = array(
					'MsgType'		=> 'manual',
					'FromUserName'	=> $weixin_openid,
					'CreateTime'	=> current_time('timestamp')-get_option('gmt_offset')*3600,
					'Content'		=> $content,
				);

				$insert_id = $wpdb->insert($wpdb->weixin_messages,$data); 

				$wpdb->update($wpdb->weixin_messages, array('Response'=>$wpdb->insert_id),array('id'=>$reply_id));

				$succeed_msg = weixin_rebot_sent_user($weixin_openid, $content, $reply_type);
			}
		}

		$response_types = weixin_robot_get_response_types();

		$types = weixin_robot_stats_get_types();
		unset($types['subscribe']);
		unset($types['unsubscribe']);

		$types['manual'] = '需要人工回复';

		$type = weixin_robot_stats_get_type();
		if(!$type){
			$type = 'total';
		}

		$Response =  isset($_REQUEST['Response'])?$_REQUEST['Response']:'';

		if(isset($_GET['delete']) && isset($_GET['id']) && $_GET['id']){
			$wpdb->query("DELETE FROM $wpdb->weixin_messages WHERE id = {$_GET['id']}");
		}

		$current_page 		= isset($_GET['paged'])?$_GET['paged']:1;
		$number_per_page	= 100;
		$start_count		= ($current_page-1)*$number_per_page;
		$limit 				= 'LIMIT '.$start_count.','.$number_per_page;

		if($type =='total'){
			$where = '';
		}elseif($type == 'manual'){
			$where = "AND Response in('not-found','too-long')";
		}else{
			$where = "AND MsgType = '{$type}'";					
		}

		if(isset($_GET['openid'])){
			$where = "AND FromUserName = '{$_GET['openid']}'";	
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->weixin_messages} WHERE 1=1 {$where} AND MsgType != 'manual' AND Event!= 'subscribe' AND Event != 'unsubscribe'  ORDER BY CreateTime DESC ".$limit;

		$weixin_messages = $wpdb->get_results($sql);

		$total_count	= $wpdb->get_var("SELECT FOUND_ROWS();");

		?>

		<?php if(!empty($succeed_msg)){?>
		<div class="updated">
			<p><?php echo $succeed_msg;?></p>
		</div>
		<?php }?>

		<ul class="subsubsub">
		<?php foreach ($types as $key=>$value) { ?>
			<li class="<?php echo $key;?>"><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&type='.$key)?>" <?php if($type == $key) {?> class="current"<?php } ?>><?php echo $value;?></a> |</li>
		<?php }?>
		<?php /*	<li class="not-found"><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&Response=not-found')?>" <?php if($Response == 'need-manual') {?> class="current"<?php } ?>>需要回复</a></li>*/?>
		</ul>
		<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<?php if(weixin_robot_get_setting('weixin_advanced_api') && strpos($wpdb->weixin_messages, 'weixin')){?>
				<th colspan="2">用户</th>
				<?php } else { ?>
				<th>用户</th>
				<?php }?>
				<th style="min-width:200px;width:40%;">内容</th>
				<th>类型</th>
				<th>回复类型</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
		<?php
		
		$alternate = '';
		foreach($weixin_messages as $weixin_message){ 
			$MsgType = $weixin_message->MsgType; $alternate = $alternate?'':'alternate';
			$weixin_openid = $weixin_message->FromUserName;
			?>
			<tr id="<?php echo $weixin_message->id;?>" class="<?php echo $alternate; ?>">
			<?php if(weixin_robot_get_setting('weixin_advanced_api') && strpos($wpdb->weixin_messages, 'weixin')){?>
				<?php $weixin_user = weixin_robot_get_user($weixin_openid); ?>
				<?php if($weixin_user['subscribe']){ ?>
				<td>
				<?php 
				$weixin_user_avatar = '';
				if(!empty($weixin_user['headimgurl'])){
					$weixin_user_avatar = $weixin_user['headimgurl'];
				?>
					<a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&openid='.$weixin_openid)?>"><img src="<?php echo $weixin_user_avatar; ?>" width="32" /></a>
				<?php }?>
				</td>
				<td>
					<?php echo $weixin_user['nickname']; ?>（<?php if($weixin_user['sex']==1){ echo '男'; } elseif($weixin_user['sex']==2) { echo '女'; }else{ echo "未知"; }?>）<br />
					<?php echo $weixin_user['country'].' '.$weixin_user['province'].' '.$weixin_user['city'];?><br />
				</td>
				<?php } else{ ?>
				<td colspan="2">
					<span style="color:red;">*已经取消关注</span>
				</td>
				<?php } ?>
			<?php }else{ ?>
				<td><?php echo $weixin_openid; ?></td>
			<?php } ?>
				<td class="content">
				<?php
				if($MsgType == 'text'){
					echo $weixin_message->Content; 
				}elseif($MsgType == 'link'){
					echo '<a href="'.$weixin_message->Url.'" target="_blank">'.$weixin_message->Title.'</a>';
				}elseif($MsgType == 'image'){
					echo '<a href="'.$weixin_message->PicUrl.'" target="_blank" title="'.$weixin_message->MediaId.'"><img src="'.$weixin_message->PicUrl.'" alt="'.$weixin_message->MediaId.'" width="100px;"></a>';
					if(isset($_GET['debug'])){
						echo '<br />MediaId：'.$weixin_message->MediaId;
					}
				}elseif($MsgType == 'location'){
					echo '<a href="http://ditu.google.cn/maps?q='.urlencode($weixin_message->label).'&amp;ll='.$weixin_message->Location_X.','.$weixin_message->Location_Y.'&amp;source=embed" target="_blank">'.$weixin_message->label.'</a>';
				}elseif($MsgType == 'event'){
					echo '['.$weixin_message->Event.'] '.$weixin_message->EventKey; 
				}elseif($MsgType == 'voice'){
					if($weixin_message->Recognition){
						echo '语音识别成：';
						echo $weixin_message->Recognition;
					}else{
						echo '未识别';
					}
					if(isset($_GET['debug'])){
						echo '<br />MediaId：'.$weixin_message->MediaId;
					}
				}else{
					echo $MsgType;
					echo '该类型的内容无法显示，请直接访问微信公众号后台进行操作！';
				}
				if(is_numeric($weixin_message->Response)){
					$weixin_reply_message = weixin_robot_get_message($weixin_message->Response);
					echo '<br /><span style="background-color:yellow; padding:2px; ">人工回复：'.$weixin_reply_message->Content.'</span>';
				}
				?>
				</td>
				<td><?php echo $types[$MsgType]; ?><br /><?php echo date('Y-m-d H:i:s',$weixin_message->CreateTime+get_option('gmt_offset')*3600); ?></td>
				<td>
					<?php 
					if(is_numeric($weixin_message->Response) ){
						echo '人工回复';
					}elseif(isset($response_types[$weixin_message->Response])){
						echo $response_types[$weixin_message->Response];	
					}
					?>
				</td>
				<td class="action">
				<?php if(weixin_robot_get_setting('weixin_advanced_api') && strpos($wpdb->weixin_messages, 'weixin') && (current_time('timestamp')-$weixin_message->CreateTime < (48+get_option('gmt_offset'))*3600) ){?>
					<?php if(is_numeric($weixin_message->Response)){ ?>
					<span>已经回复</span>
					<?php } elseif($weixin_user['subscribe']){ ?>
					<span class="reply"><a href="javascript:;" onclick="reply_to_weixin('<?php echo $weixin_openid; ?>', '<?php echo $weixin_message->id; ?>')">回复</a></span>
					<?php } ?>
					
				<?php } else {?>
					<span class="delete"><a href="<?php echo admin_url('admin.php?page=weixin-robot-messages&delete&id='.$weixin_message->id); ?>">删除</a></span>
				<?php } ?>
				</td>
			</tr>
			<?php } ?>

			<?php if(weixin_robot_get_setting('weixin_advanced_api') && strpos($wpdb->weixin_messages, 'weixin')){?>
			<tr id="reply_form" style="display:none;" >
				<td colspan="2">&nbsp;</td>
				<td colspan="4">
				<form action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" method="POST">
					<p>
						<textarea name="content" id="content" rows="5" class="large-text code"></textarea>
					</p>
					<p>
						<select name="reply_type" id="reply_type" >
							<option value="text">文本回复</option>
							<option value="img">图文回复</option>
						</select>
					</p>
					<input type="hidden" name="weixin_openid" id="weixin_openid" value="" />
					<input type="hidden" name="reply_id" id="reply_id" value="" />
					<?php wp_nonce_field('weixin_robot','weixin_robot_send_user_nonce'); ?>
					<p><input type="submit" name="submit" id="submit" class="button button-primary" value="回复用户" style="float:right; margin-right:20px;"></p>
				</form>
				</td>
			</tr>
			<?php } ?>
		</tbody>
		</table>
		<?php wpjam_admin_pagenavi($total_count,$number_per_page); ?>
		<?php if(weixin_robot_get_setting('weixin_advanced_api') && strpos($wpdb->weixin_messages, 'weixin')){?>
		<script type="text/javascript">
			function reply_to_weixin(weixin_openid, id){
				jQuery('input#weixin_openid')[0].value = weixin_openid;
				jQuery('input#reply_id')[0].value = id;
				jQuery('tr#'+id).after(jQuery('#reply_form'));
				jQuery('tr#reply_form').show();
			}

			jQuery(function(){

				jQuery('form').submit(function( event ) {
					var reply_id		= jQuery('input#reply_id')[0].value;
					var reply_type		= jQuery('select#reply_type')[0].value;
					var weixin_openid	= jQuery('input#weixin_openid')[0].value;
					var reply_content	= jQuery('textarea#content')[0].value;

					if(jQuery('textarea#content')[0].value != ''){
						jQuery.ajax({
							type: 'post',
							url: '<?php echo admin_url('admin-ajax.php');?>',
							data: { 
								action: 'weixin_reply', 
								weixin_openid: weixin_openid,
								reply_id: reply_id, 
								reply_type: reply_type, 
								content: reply_content,
								_ajax_nonce: '<?php echo wp_create_nonce('weixin_robot_ajax_nonce');?>'
							},
							success: function(html){
								reply_content = jQuery('tr#'+reply_id+' td.content').html()+'<br /><span style="background-color:yellow; padding:2px; ">人工回复：'+reply_content+'</span>';
								jQuery('tr#'+reply_id+' td.content').html(reply_content);
								jQuery('tr#'+reply_id+' td.action').html('已经回复');
								jQuery('textarea#content')[0].value = '';
								jQuery('tr#reply_form').hide();
							}
						});
					}else{
						alert('回复的内容不能为空');
						jQuery('textarea#content').focus();
					}
					
					event.preventDefault();
				});
			});
		</script>		
		<?php wpjam_confim_delete_script(); ?>
		<?php } ?>
<?php }

add_action('wp_ajax_weixin_reply', 'weixin_robot_reply_message_action_callback');
add_action('wp_ajax_nopriv_weixin_reply', 'weixin_robot_reply_message_action_callback');
function weixin_robot_reply_message_action_callback(){
	check_ajax_referer( "weixin_robot_ajax_nonce" );

	$weixin_openid	= $_POST['weixin_openid'];
	$reply_id		= $_POST['reply_id'];
	$reply_type		= $_POST['reply_type'];
	$content		= $_POST['content'];


	if(empty($weixin_openid) || empty($reply_id) || empty($content)) return;

	$data = array(
		'MsgType'		=> 'manual',
		'FromUserName'	=> $weixin_openid,
		'CreateTime'	=> current_time('timestamp')-get_option('gmt_offset')*3600,
		'Content'		=> $content,
	);

	global $wpdb;

	$insert_id = $wpdb->insert($wpdb->weixin_messages,$data); 

	$wpdb->update($wpdb->weixin_messages, array('Response'=>$wpdb->insert_id),array('id'=>$reply_id));

	$succeed_msg = weixin_rebot_sent_user($weixin_openid, $content, $reply_type);

	echo $succeed_msg;
}



