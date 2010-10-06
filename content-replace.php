<?php
/*
Plugin Name: Content Replace
Plugin URI: http://github.com/avaly/content-replace
Description: Content replace functions for all content areas
Version: 1.2
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
		echo '<p>Replaced<br/><strong>'.$res[0].'</strong><br/>with<br/><strong>'.$res[1].'</strong></p>';
		echo '<ul>';
			echo '<li>posts.post_content: <strong>'.$count[0].'</strong></li>';
			echo '<li>posts.guid: <strong>'.$count[1].'</strong></li>';
			echo '<li>postmeta.meta_value: <strong>'.$count[2].'</strong></li>';
			echo '<li>widgets.text: <strong>'.$count[3].'</strong></li>';
			if (isset($count[4]) && is_array($count[4]))
				foreach ($count[4] as $key => $cnt)
					echo '<li>'.$key.': <strong>'.$cnt.'</strong></li>';
		echo '</ul><br/>';
	}

?>

	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<table cellspacing="0" class="form-table">
		<tbody>
			<tr>
				<th>Find this text</th>
				<td><input type="text" class="text" name="find" value="<?php echo (!empty($_POST['find']) ? htmlentities($_POST['find']) : ''); ?>" size="70"/></td>
			</tr>
			<tr>
				<th>Replace with text</th>
				<td><input type="text" class="text" name="replace" value="<?php echo (!empty($_POST['replace']) ? htmlentities($_POST['replace']) : ''); ?>" size="70"/></td>
			</tr>
			<tr>
				<th>Extra replacement locations</th>
				<td>
					<textarea name="extra" rows="3" cols="30"><?php echo (!empty($_POST['extra']) ? htmlentities($_POST['extra']) : ''); ?></textarea><br/>
					<p class="description">Include extra locations to perform the string replacement, each line in the following syntax: <b>TABLE.FIELD</b></p>
				</td>
			</tr>
		</tbody>
		</table>
		<p class="submit"><input type="submit" class="button-primary" value="Replace Content"/></p>
	</form>

	<p class="created" style="text-align:right"><em>Created by <a href="http://agachi.name/">Valentin Agachi</a></em></p>

	</div>
<?php
}



function cr_do_replace()
{
	global $wpdb;

	if (!is_array($_POST) || !count($_POST))
		return false;

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

	// extra table.field
	if (!empty($_POST['extra']))
	{
		$cnt = array();

		$extra = explode("\n", str_replace("\r", '', trim(stripslashes($_POST['extra']))));

		foreach ($extra as $line)
		{
			if (!preg_match('~^([^\.]+)\.([^\.]+)$~', $line, $match))
				continue;
	
			$cnt[$line] = $wpdb->query('UPDATE '.$match[1].' SET '.$match[2].'=REPLACE('.$match[2].', \''.$findq.'\', \''.$replaceq.'\')');		
		}

		$count[4] = $cnt;
	}

	return array($find, $replace, $count);
}


?>