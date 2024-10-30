<?php

/*
Plugin Name: Betting News
Plugin URI: http://www.gamblingnewscollection.com/
Description: Fetches publicly available news feeds from many Betting News sites and displays them in a highly configurable widget with many formatting options.
Author: PluginTaylor
Version: 0.8.1
Author URI: http://www.gamblingnewscollection.com/
*/

function BettingNews_init() {
	function BettingNews() {
		$options = get_option('BettingNews_Widget');
		$options = BettingNews_LoadDefaults($options);

		$q = 'HTTP_REFERER='.urlencode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'&REMOTE_ADDR='.urlencode($_SERVER['REMOTE_ADDR']).'&HTTP_USER_AGENT='.urlencode($_SERVER['HTTP_USER_AGENT']).'&PLUGIN=BettingNews';
		if($options) { foreach($options AS $p => $v) { $q .= '&'.urlencode($p).'='.urlencode($v); } }

    	$req =	"POST / HTTP/1.1\r\n".
    			"Content-Type: application/x-www-form-urlencoded\r\n".
    			"Host: www.gamblingnewscollection.com\r\n".
    			"Content-Length: ".strlen($q)."\r\n".
    			"Connection: close\r\n".
    			"\r\n".$q;

    	$fp = @fsockopen('www.gamblingnewscollection.com', 80, $errno, $errstr, 10);
    	if(!$fp) {  }
    	if(!fwrite($fp, $req)) { fclose($fp); }
    	$result = '';
    	while(!feof($fp)) { $result .= fgets($fp); }
    	fclose($fp);
    	$result = explode("\r\n\r\n", $result);

		return $result[1];
	}
	function BettingNews_Widget($args) {
		$options = get_option('BettingNews_Widget');
		$options = BettingNews_LoadDefaults($options);

		extract($args);
		echo $before_widget.$before_title.$options['title'].$after_title.BettingNews().$after_widget;
	}
	function BettingNews_LoadDefaults($options) {
		$options['title'] = empty($options['title']) ? __('Betting News') : $options['title'];
		$options['list_start'] = empty($options['list_start']) ? __('<ul>') : $options['list_start'];
		$options['list_end'] = empty($options['list_end']) ? __('</ul>') : $options['list_end'];
		$options['formatting'] = empty($options['formatting']) ? __('<li><a href="[link]" rel="nofollow" target="_blank">[date] - [title]</a></li>') : $options['formatting'];
		$options['count'] = empty($options['count']) ? __(5) : $options['count'];
		$options['description'] = empty($options['description']) ? __(20) : $options['description'];

		return $options;
	}
	function BettingNews_WidgetControl() {
		$options = $newoptions = get_option('BettingNews_Widget');
		if($_POST['BettingNews_WidgetSubmit']) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['BettingNews_WidgetTitle']));
			$newoptions['count'] = $_POST['BettingNews_ItemCount'];
			$newoptions['list_start'] = stripslashes($_POST['BettingNews_ListStart']);
			$newoptions['list_end'] = stripslashes($_POST['BettingNews_ListEnd']);
			$newoptions['formatting'] = stripslashes($_POST['BettingNews_ItemFormatting']);
			$newoptions['description'] = stripslashes($_POST['BettingNews_Description']);
		}
		if($options != $newoptions) {
			$options = $newoptions;
			update_option('BettingNews_Widget', $options);
		}
		$options = BettingNews_LoadDefaults($options);

		echo '
<h3>List</h3>
<p><label for="BettingNews_WidgetTitle">Title: <input id="BettingNews_WidgetTitle" name="BettingNews_WidgetTitle" type="text" value="'.attribute_escape($options['title']).'" /></label><br />
<label for="BettingNews_ListStart">Start: <input id="BettingNews_ListStart" name="BettingNews_ListStart" type="text" value="'.attribute_escape($options['list_start']).'" /></label><br />
<label for="BettingNews_ListEnd">End: <input id="BettingNews_ListEnd" name="BettingNews_ListEnd" type="text" value="'.attribute_escape($options['list_end']).'" /></label></p>
<label for="BettingNews_Description">Description: <input id="BettingNews_Description" name="BettingNews_Description" type="text" value="'.attribute_escape($options['description']).'" /> (characters)</label></p>
<i>(set to <b>0</b> to disable descriptions)</i>

<h3>Items</h3>
<p><label for="BettingNews_ItemCount">Item count: <select id="BettingNews_ItemCount" name="BettingNews_ItemCount">';
		for($i=1; $i <= 10; $i++) {
			if(attribute_escape($options['count']) == $i OR (attribute_escape($options['count']) <= 0 AND $i == 5)) { $selected = ' selected'; } else { $selected = FALSE; }
			echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
		echo '</select></label><br />
<label for="BettingNews_ItemFormatting">Item formatting:<br /><i>([link], [title], [date], [description])</i><br /><textarea style="font-size: 10px;" id="BettingNews_ItemFormatting" name="BettingNews_ItemFormatting">'.attribute_escape($options['formatting']).'</textarea /></label><br />
<input type="hidden" id="BettingNews_WidgetSubmit" name="BettingNews_WidgetSubmit" value="true" />';
	}

	register_sidebar_widget('Betting News', 'BettingNews_Widget');
	register_widget_control('Betting News', 'BettingNews_WidgetControl');
}
add_action('plugins_loaded', 'BettingNews_init');

?>