<?php
// 后台选项页面
function wpjam_option_page($labels, $title='', $type='default'){
	extract($labels);
	?>
	<div class="wrap">
	<?php if($type == 'tab'){ ?>
		<h2 class="nav-tab-wrapper">
	        <?php foreach ( $sections as $section_name => $section) { ?>
	            <a class="nav-tab" href='javascript:;' id="tab-title-<?php echo $section_name; ?>"><?php echo $section['title']; ?></a>
	        <?php } ?>    
	    </h2>
		<form action="options.php" method="POST">
			<?php settings_fields( $option_group ); ?>
			<?php foreach ( $sections as $section_name => $section ) { ?>
	            <div id="tab-<?php echo $section_name; ?>" class="div-tab hidden">
	                <?php wpjam_option_do_settings_section($option_page, $section_name); ?>
	            </div>                      
	        <?php } ?>
			<input type="hidden" name="<?php echo $option_name;?>[current_tab]" id="current_tab" value="" />
			<?php submit_button(); ?>
		</form>
		<?php wpjam_option_tab_script($option_name);?>
	<?php }else{ ?>
		<?php if($title){?>
			<?php if(preg_match("/<[^<]+>/",$title,$m) != 0){ ?>
				<?php echo $title; ?>
			<?php } else { ?>
				<h2><?php echo $title; ?></h2>
			<?php } ?>
		<?php }?>
		<form action="options.php" method="POST">
			<?php settings_fields( $option_group ); ?>
			<?php do_settings_sections( $option_page ); ?>
			<?php submit_button(); ?>
		</form>
	<?php } ?>
	</div>
	<?php
}

// 拷贝自 do_settings_sections 函数，用于 tab 显示选项。
function wpjam_option_do_settings_section($option_page, $section_name){
	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[$option_page] ) )
		return;

	$section = $wp_settings_sections[$option_page][$section_name];

	if ( $section['title'] )
		echo "<h3>{$section['title']}</h3>\n";

	if ( $section['callback'] )
		call_user_func( $section['callback'], $section );

	if ( isset( $wp_settings_fields ) && isset( $wp_settings_fields[$option_page] ) && !empty($wp_settings_fields[$option_page][$section['id']] ) ){
		echo '<table class="form-table">';
		do_settings_fields( $option_page, $section['id'] );
		echo '</table>';
	}
}

// 后台选项 Tab 切换 JS 
function wpjam_option_tab_script($option_name='',$htag='h2'){
	$current_tab = '';

	if($option_name){
		$option = wpjam_get_option( $option_name );
		if(!empty($_GET['settings-updated'])){
			$current_tab = $option['current_tab'];
		}
	}
	?>
	<script type="text/javascript">
		jQuery('div.div-tab').hide();
	<?php if($current_tab){ ?>
		jQuery('#tab-title-<?php echo $current_tab; ?>').addClass('nav-tab-active');
		jQuery('#tab-<?php echo $current_tab; ?>').show();
		jQuery('#current_tab').val('<?php echo $current_tab; ?>');
	<?php } else{ ?>
		//设置第一个显示
		jQuery('<?php echo $htag; ?> a.nav-tab').first().addClass('nav-tab-active');
		jQuery('div.div-tab').first().show();
	<?php } ?>
		jQuery(function($){
			$('<?php echo $htag; ?> a.nav-tab').on('click',function(){
		        $('<?php echo $htag; ?> a.nav-tab').removeClass('nav-tab-active');
		        $(this).addClass('nav-tab-active');
		        $('div.div-tab').hide();
		        $('#'+jQuery(this)[0].id.replace('title-','')).show();
		        $('#current_tab').val($(this)[0].id.replace('tab-title-',''));
		    });
		});
	</script>
<?php
}

// 获取所有 checkbox 选项
function wpjam_option_get_checkbox_settings($labels){
	$sections = $labels['sections'];
	$checkbox_options = array();
	foreach ($sections as $section) {
		$fields = $section['fields'];
		foreach ($fields as $field_name => $field) {
			if($field['type'] == 'checkbox'){
				$checkbox_options[] = $field_name;
			}
		}
	}
	return $checkbox_options;
}

// 在选项页面添加设置
function wpjam_add_settings($labels){
	extract($labels);
	register_setting( $option_group, $option_name, $field_validate );

	$field_callback = empty($field_callback)?'wpjam_option_field_callback' : $field_callback;
	if($sections){
		foreach ($sections as $section_name => $section) {
			add_settings_section( $section_name, $section['title'], $section['callback'], $option_page );

			$fields = isset($section['fields'])?$section['fields']:(isset($section['fields'])?$section['fields']:''); // 尼玛写错英文单词的 fallback

			if($fields){
				foreach ($fields as $field_name=>$field) {
					$field['option']	= $option_name;
					$field['name']		= $field_name;

					$field_title		= $field['title'];

					//if(in_array($field['type'], array('text','password','select','datetime','textarea','checkbox'))){
					$field_title = '<label for="'.$field_name.'">'.$field_title.'</label>';
					//}

					add_settings_field( 
						$field_name,
						$field_title,		
						$field_callback,	
						$option_page, 
						$section_name,	
						$field
					);	
				}
			}
		}
	}
}

// 选项的每个字段回调函数，显示具体 HTML 结构
function wpjam_option_field_callback($field) {

	$field_name		= $field['name'];
	$field['key']	= $field_name;
	$field['name']	= $field['option'].'['.$field_name.']';

	$wpjam_option	= wpjam_get_option( $field['option'] );
	$field['value'] = (isset($wpjam_option[$field_name]))?$wpjam_option[$field_name]:'';

	echo wpjam_admin_get_field_html($field);
}

function wpjam_admin_get_field_html($field){

	$key		= $field['key'];
	$name		= $field['name'];
	$type		= $field['type'];
	$value		= $field['value'];

	$class		= isset($field['class'])?$field['class']:'regular-text';
	$description= (!empty($field['description']))?( ($type == 'checkbox')? ' <label for="'.$key.'">'.$field['description'].'</label>':'<p>'.$field['description'].'</p>'):'';

	$title 	= isset($field['title'])?$field['title']:$field['name'];
	$label 	= '<label for="'.$key.'">'.$title.'</label>';

	switch ($type) {
		case 'text':
		case 'password':
		case 'hidden':
		case 'url':
		case 'color':
		case 'url':
		case 'tel':
		case 'email':
		case 'month':
		case 'date':
		case 'datetime':
		case 'datetime-local':
		case 'week':
			$field_html = '<input name="'.$name.'" id="'. $key.'" type="'.$type.'"  value="'.esc_attr($value).'" class="'.$class.'" />';
			break;

		case 'range':
			$max	= isset($field['max'])?' max="'.$field['max'].'"':'';
			$min	= isset($field['min'])?' min="'.$field['min'].'"':'';
			$step	= isset($field['step'])?' step="'.$field['step'].'"':'';

			$field_html ='<input name="'.$name.'" id="'. $key.'" type="'.$type.'"  value="'.esc_attr($value).'"'.$max.$min.$step.' class="'.$class.'" onchange="jQuery(\'#'.$key.'_span\').html(jQuery(\'#'.$key.'\').val());"  /> <span id="'.$key.'_span">'.$value.'</span>';
			break;

		case 'number':
			$max	= isset($field['max'])?' max="'.$field['max'].'"':'';
			$min	= isset($field['min'])?' min="'.$field['min'].'"':'';
			$step	= isset($field['step'])?' step="'.$field['step'].'"':'';

			$field_html = '<input name="'.$name.'" id="'. $key.'" type="'.$type.'"  value="'.esc_attr($value).'" class="'.$class.'"'.$max.$min.$step.' />';
			break;

		case 'checkbox':
			$field_html = '<input name="'.$name.'" id="'. $key.'" type="checkbox"  value="1" '.checked("1",$value,false).' />';
			break;

		case 'textarea':

			$rows = isset($field['rows'])?$field['rows']:6;
			$field_html = '<textarea name="'.$name.'" id="'. $key.'" rows="'.$rows.'" cols="50"  class="'.$class.' code" >'.esc_attr($value).'</textarea>';
			break;

		case 'select':

			$field_html  = '<select name="'.$name.'" id="'. $key.'">';
			foreach ($field['options'] as $option_value => $option_title){ 
				$field_html .= '<option value="'.$option_value.'" '.selected($option_value,$value,false).'>'.$option_title.'</option>';
			}
			$field_html .= '</select>';
			
			break;

		case 'radio':
			$field_html  = '';
			foreach ($field['options'] as $option_value => $option_title) {
				$field_html  .= '<input name="'.$name.'" type="radio" id="'.$key.'" value="'.$option_value .'" '.checked($option_value,$value,false).' />'.$option_title.'<br />';
			}
			break;

		case 'image':
			$field_html = '<input name="'.$name.'" id="'.$key.'" type="url"  value="'.esc_attr($value).'" class="'.$class.'" /><input type="button" class="wpjam_upload button" style="width:80px;" value="选择图片">';
            break;
        case 'mulit_image':
        case 'multi_image':
        	$field_html  = '';
            if(is_array($value)){
                foreach($value as $image_key=>$image){
                    if(!empty($image)){
                    	$field_html .= '<span><input type="text" name="'.$name.'[]" id="'. $key.'" value="'.esc_attr($image).'"  class="'.$class.'" /><a href="javascript:;" class="button del_image">删除</a></span>';
                    }
                }
            }
            $field_html  = '<span><input type="text" name="'.$name.'[]" id="'.$key.'" value="" class="'.$class.'" /><input type="bu
            tton" class="wpjam_mulit_upload button" style="width:110px;" value="选择图片[多选]" title="按住Ctrl点击鼠标左键可以选择多张图片"></span>';
            break;
        case 'mulit_text':
        case 'multi_text':
        	$field_html  = '';
            if(is_array($value)){
                foreach($value as $text_key=>$item){
                    if(!empty($item)){
                    	$field_html .= '<span><input type="text" name="'.$name.'[]" id="'. $key.'" value="'.esc_attr($item).'"  class="'.$class.'" /><a href="javascript:;" class="button del_image">删除</a></span>';
                    }
                }
            }
            $field_html  = '<span><input type="text" name="'.$name.'[]" id="'.$key.'" value="" class="'.$class.'" /><a class="wpjam_mulit_text button">添加选项</a></span>';
            break;

        case 'file':
        	$field_html  = '<input type="file" name="'.$name.'" id="'. $key.'" />'.'已上传：'.wp_get_attachment_link($value);
            break;
		
		default:
			$field_html = '<input name="'.$name.'" id="'. $key.'" type="text"  value="'.esc_attr($value).'" class="'.$class.'" />';
			break;
	}

	return $field_html.$description;
}

// 后台显示字段
function wpjam_admin_display_fields($fields, $fields_type = 'table'){
	$new_fields = array();
	foreach($fields as $key => $field){ 
		
		$field['key']	= $field['name']	= $key;
		$field_html 	= wpjam_admin_get_field_html($field);

		$title 	= $field['title'];
		$label 	= '<label for="'.$key.'">'.$title.'</label>';
		$new_fields[$key] = array('title'=>$title, 'label'=>$label, 'html'=>$field_html);
	}
	
	?>
	<?php if($fields_type == 'list'){ ?>
	<ul>
	<?php foreach ($new_fields as $key=>$field) { ?>
		<li><?php echo $field['label']; ?> <?php echo $field['html']; ?> </li>
	<?php } ?>
	</ul>
	<?php } elseif($fields_type == 'table'){ ?>
	<table class="form-table" cellspacing="0">
		<tbody>
		<?php foreach ($new_fields as $key=>$field) { ?>
			<tr valign="top" id="tr_<?php echo $key; ?>">
			<?php if($field['title']) { ?>
				<th scope="row"><?php echo $field['label']; ?></th>
				<td><?php echo $field['html']; ?></td>
			<?php } else { ?>
				<td colspan="2"><?php echo $field['html']; ?></td>
			<?php } ?>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php } elseif($fields_type == 'tr') { ?>
		<?php foreach ($new_fields as $key=>$field) { ?>
			<tr id="tr_<?php echo $key; ?>">
			<?php if($field['title']) { ?>
				<th scope="row"><?php echo $field['label']; ?></th>
				<td><?php echo $field['html']; ?></td>
			<?php } else { ?>
				<td colspan="2"><?php echo $field['html']; ?></td>
			<?php } ?>
			</tr>
		<?php } ?>
	<?php } else { ?> 
		<?php foreach ($new_fields as $key=>$field) { ?>
			<p id="tr_<?php echo $key; ?>">
			<?php if($field['title']) { ?>
				<?php echo $field['label']; ?>：
				<?php echo $field['html']; ?>
			<?php } else { ?>
				<?php echo $field['html']; ?>
			<?php } ?>
			</p>
		<?php } ?>
	<?php } ?>
	<?php
}

function wpjam_admin_get_column($column_key, $column_name, $style = ''){
	return '<th scope="col" id="'.$column_key.'" class="manage-column column-'.$column_key.'" style="'.$style.'">'.$column_name.'</th>';
}

function wpjam_admin_get_sortable_column($column_key, $column_name, $style=''){

	$orderby		= isset($_GET['orderby'])?$_GET['orderby']:'';
	$order 			= isset($_GET['order'])?$_GET['order']:'desc';

	$base_url = remove_query_arg(array('orderby','order','paged'), wpjam_get_current_page_url());

	if($orderby == $column_key){
		$class = 'sorted '.$order;
		$order = ($order == 'desc')?'asc':'desc';
		$url   = $base_url.'&orderby='.$column_key.'&order='.$order;
	}else{
		$class = 'sortable asc';
		$url   = $base_url.'&orderby='.$column_key.'&order=desc';
	} 

	return '<th scope="col" id="'.$column_key.'" class="manage-column column-'.$column_key.' '.$class.'" style="'.$style.'"><a href="'.$url.'"><span>'.$column_name.'</span><span class="sorting-indicator"></a></th>';
}

function wpjam_admin_pagenavi($total_count, $number_per_page=50){

	$current_page = isset($_GET['paged'])?$_GET['paged']:1;

	$base_url = remove_query_arg(array('paged'), wpjam_get_current_page_url());

	$total_pages	= ceil($total_count/$number_per_page);

	$first_page_url	= $base_url.'&amp;paged=1';
	$last_page_url	= $base_url.'&amp;paged='.$total_pages;
	
	if($current_page > 1 && $current_page < $total_pages){
		$prev_page		= $current_page-1;
		$prev_page_url	= $base_url.'&amp;paged='.$prev_page;

		$next_page		= $current_page+1;
		$next_page_url	= $base_url.'&amp;paged='.$next_page;
	}elseif($current_page == 1){
		$prev_page_url	= '#';
		$first_page_url	= '#';
		if($total_pages > 1){
			$next_page		= $current_page+1;
			$next_page_url	= $base_url.'&amp;paged='.$next_page;
		}else{
			$next_page_url	= '#';
		}
	}elseif($current_page == $total_pages){
		$prev_page		= $current_page-1;
		$prev_page_url	= $base_url.'&amp;paged='.$prev_page;
		$next_page_url	= '#';
		$last_page_url	= '#';
	}
	?>
	<div class="tablenav-pages">
		<span class="displaying-num"><?php /*每页 <?php echo $number_per_page;?> 个项目，*/?>共 <?php echo $total_count;?> 个项目</span>
		<span class="pagination-links">
			<a class="first-page <?php if($current_page==1) echo 'disabled'; ?>" title="前往第一页" href="<?php echo $first_page_url;?>">«</a>
			<a class="prev-page <?php if($current_page==1) echo 'disabled'; ?>" title="前往上一页" href="<?php echo $prev_page_url;?>">‹</a>
			<span class="paging-input">第 <?php echo $current_page;?> 页，共 <span class="total-pages"><?php echo $total_pages; ?></span> 页</span>
			<a class="next-page <?php if($current_page==$total_pages) echo 'disabled'; ?>" title="前往下一页" href="<?php echo $next_page_url;?>">›</a>
			<a class="last-page <?php if($current_page==$total_pages) echo 'disabled'; ?>" title="前往最后一页" href="<?php echo $last_page_url;?>">»</a>
		</span>
	</div>
	<br class="clear">
	<?php
}

function wpjam_confim_delete_script(){
	?>
	<script type="text/javascript">
	jQuery(function(){
		jQuery('span.delete a').click(function(){
			return confirm('确定要删除吗?'); 
		}); 
	});
	</script> 
	<?php
}

// 获取自定义字段设置
function wpjam_get_post_options(){
    $wpjam_options = apply_filters('wpjam_options', array());
    return $wpjam_options;
}

//输出自定义字段表单
add_action('admin_head', 'wpjam_post_options_box');
function wpjam_post_options_box() {
   	$wpjam_options = wpjam_get_post_options();
    if($wpjam_options){
    	foreach($wpjam_options as $meta_box=>$wpjam_option){
    		$context	= isset($wpjam_option['context'])?$wpjam_option['context']:'normal';
    		$priority	= isset($wpjam_option['priority'])?$wpjam_option['priority']:'high';
    		if($wpjam_option['post_types'] == null){
    			global $pagenow;
				if($pagenow != 'post.php' && $pagenow != 'post-new.php'){
					return;
				}
    			add_meta_box($meta_box, $wpjam_option['name'], 'wpjam_post_options_callback', null, $context, $priority, array('meta_box'=>$meta_box));
    		}else{
    			foreach($wpjam_option['post_types'] as $post_type){
		        	add_meta_box($meta_box, $wpjam_option['name'], 'wpjam_post_options_callback', $post_type, 'normal', 'high', array('meta_box'=>$meta_box));
		        }
    		}
	    }
    }
}

function wpjam_post_options_callback( $post, $meta_box){
    if(isset($meta_box['args']['meta_box'])){
        $meta_box = $meta_box['args']['meta_box'];
    } else{
        $meta_box = '';
    }
    $wpjam_options = wpjam_get_post_options();
    foreach ($wpjam_options[$meta_box]['fields'] as $key => $wpjam_field) {
        if(isset($_REQUEST[$key])){
            $value  = $_REQUEST[$key];
        }else{
            $value = get_post_meta($post->ID, $key, true);
        }
        $wpjam_options[$meta_box]['fields'][$key]['value'] = $value;
    }
    $fields_type = (isset($wpjam_options[$meta_box]['context']) && $wpjam_options[$meta_box]['context'] == 'side')?'list':'table';
    wpjam_admin_display_fields($wpjam_options[$meta_box]['fields'] , $fields_type);
    ?>
    <script type="text/javascript">
        jQuery(function(){
            jQuery("form#post").attr('enctype','multipart/form-data');
        });
    </script>
<?php
}

//保存自定义字段
add_action('save_post', 'wpjam_save_post_options', 999);
function wpjam_save_post_options($post_id){
    // to prevent metadata or custom fields from disappearing...
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;

    global $pagenow;

	if($pagenow != 'post.php' && $pagenow != 'post-new.php'){
		return;
	}

    $post = get_post($post_id);
    $wpjam_options = wpjam_get_post_options();
    foreach ($wpjam_options as $meta_box => $wpjam_group) {
        if($wpjam_group['post_types'] == null || in_array($post->post_type, $wpjam_group['post_types'])){
            foreach($wpjam_group['fields'] as $key=>$wpjam_field){
                switch($wpjam_field['type']){
                    case 'file':
                        if($_POST['wpjam_delete_field'][$key]){
                            delete_post_meta($post_id,$key,$_POST['wpjam_delete_field'][$key]);
                        }
                        if(isset($_FILES[$key]) && $_FILES[$key]){
                            require_once(ABSPATH . 'wp-admin/includes/admin.php');
                            $attachment_id=media_handle_upload($key,$post_id);
                            if(!is_wp_error($attachment_id)){
                                update_post_meta($post_id,$key,$attachment_id);
                            }
                            unset($attachment_id);
                        }
                        break;
                    case 'checkbox':
                    	//xxx特殊设置，防止在前台修改此值
                        if(is_admin()){
                        	if(isset($_POST[$key])){
                                update_post_meta($post_id,$key,$_POST[$key]);
                            }else{
                            	if(get_post_meta($post_id, $key, true)){
									delete_post_meta($post_id, $key);
								}
                            }
                        }
                        break;
                    case 'mulit_image':
                    case 'multi_image':
                        if(isset($_POST[$key]) && is_array($_POST[$key])){
                            //删除空图片
                            foreach($_POST[$key] as $image_key=>$image_value){
                                if(empty($image_value))
                                    unset($_POST[$key][$image_key]);
                            }
                            update_post_meta($post_id,$key,$_POST[$key]);
                        }
                        break;
                    case 'mulit_text':
                    case 'multi_text':
                        if(isset($_POST[$key]) && is_array($_POST[$key])){
                            foreach($_POST[$key] as $multiple_text_key=>$item_value){
                                if(empty($item_value))
                                    unset($_POST[$key][$multiple_text_key]);
                            }
                            update_post_meta($post_id,$key,$_POST[$key]);
                        }
                        break;
                    default:
                        if(isset($_POST[$key]) && $_POST[$key]){
                            update_post_meta($post_id,$key,$_POST[$key]);
                        }else{
                        	if(get_post_meta($post_id, $key, true)){
								delete_post_meta($post_id, $key);
							}
                        }
                }
            }
        }
    }
}

// 上传图片的 JS
add_action('admin_enqueue_scripts', 'wpjam_upload_image_enqueue_scripts');
function wpjam_upload_image_enqueue_scripts() {
    wp_enqueue_media();
    wp_enqueue_script('wpjam-upload-image', plugins_url('/wpjam-setting.js', __FILE__), array('jquery'));
}

// 获取设置
function wpjam_get_setting($option, $setting_name){
	if(isset($option[$setting_name])){
		return str_replace("\r\n", "\n", $option[$setting_name]);
	}else{
		return '';
	}
}

// 获取选项
function wpjam_get_option($option_name){
	$option = get_option( $option_name );
	if($option && !is_admin()){
		return $option;
	}else{
		$defaults = apply_filters($option_name.'_defaults', array());
		return wp_parse_args($option, $defaults);
	}
}

// 获取当前页面 url
function wpjam_get_current_page_url(){
    $ssl        = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
    $sp         = strtolower($_SERVER['SERVER_PROTOCOL']);
    $protocol   = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port       = $_SERVER['SERVER_PORT'];
    $port       = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host       = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    return $protocol . '://' . $host . $port . $_SERVER['REQUEST_URI'];
}