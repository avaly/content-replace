<?php
/*
Plugin Name: Content Replace
Plugin URI: http://agachi.name/#content-replace
Description: Content replace functions for all content areas
Version: 1.1
Author: Valentin Agachi
Author URI: http://agachi.name
License: GPL2
*/



function cr_plugin_menu()
{
	add_submenu_page('tools.php', 'Content Replace', 'Content Replace', 'manage_options', 'cr-replace1', 'cr_replace1');
}

add_action('admin_menu', 'cr_plugin_menu');



function cr_plugin_action_links($data)
{
	$data[] = '<b><a href="tools.php?page=cr-replace1">Replace</a></b>';
	return $data;
}

add_filter('plugin_action_links_'.basename(__FILE__), 'cr_plugin_action_links');



function cr_replace1()
{
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$res = cr_do_replace();

?>
	<div class="wrap">

	<h2>Content Replace</h2>

<?php
	
	if ($res && is_array($res))
	{
		$count = $res[2];
//		var_dump($count);
		echo '<p>Replaced<br/><strong>'.$res[0].'</strong><br/>with<br/><strong>'.$res[1].'</strong></p>';
		echo '<ul>';
			echo '<li>posts.post_content: <strong>'.$count[0].'</strong></li>';
			echo '<li>posts.guid: <strong>'.$count[1].'</strong></li>';
			echo '<li>postmeta.meta_value: <strong>'.$count[2].'</strong></li>';
			echo '<li>widgets.text: <strong>'.$count[3].'</strong></li>';
		echo '</ul><br/>';
	}

?>

	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<table cellspacing="0" class="form-table">
		<tbody>
			<tr>
				<th>Find this text</th>
				<td><input type="text" class="text" name="find" value="<?php echo htmlentities($_POST['find']); ?>" size="70"/></td>
			</tr>
			<tr>
				<th>Replace with text</th>
				<td><input type="text" class="text" name="replace" value="<?php echo htmlentities($_POST['replace']); ?>" size="70"/></td>
			</tr>
		</tbody>
		</table>
		<p class="submit"><input type="submit" class="button-primary" value="Replace Content"/></p>
	</form>

	</div>
<?php
}



function cr_do_replace()
{
	global $wpdb;

	if (!is_array($_POST) || !count($_POST))
		return false;

//	if (get_magic_quotes_gpc())
//		array_walk($_POST, 'stripslashes');
//var_dump($_POST);

	$find = stripslashes($_POST['find']);
	$replace = stripslashes($_POST['replace']);

	$findq = $wpdb->escape($find);
	$replaceq = $wpdb->escape($replace);

	$count = array();


	$count[] = $wpdb->query('UPDATE '.$wpdb->posts.' SET post_content=REPLACE(post_content, \''.$findq.'\', \''.$replaceq.'\')');

	$count[] = $wpdb->query('UPDATE '.$wpdb->posts.' SET guid=REPLACE(guid, \''.$findq.'\', \''.$replaceq.'\')');

	$count[] = $wpdb->query('UPDATE '.$wpdb->postmeta.' SET meta_value=REPLACE(meta_value, \''.$findq.'\', \''.$replaceq.'\')');


	// widget text
	$cnt = 0;
	$widgets = get_option('widget_text');
	foreach ($widgets as $k => $widget)
	{
		if (isset($widget['text']))
		{
			$c = 0;
			$widgets[$k]['text'] = str_replace($find, $replace, $widget['text'], $c);
			$cnt += $c;
		}
	}
	update_option('widget_text', $widgets);
	$count[] = $cnt;
	
//var_dump($wpdb->queries);

	return array($find, $replace, $count);
}


?>