<?php
# enqueue modal script & CSS

function rezbs_add_connectJS() {
	wp_enqueue_script('jqRBSConnect', plugins_url('/rezbs-connect/js/connect.js'), array('jquery'),'202005152',true);
}
add_action('wp_enqueue_scripts', 'rezbs_add_connectJS');
add_action('admin_enqueue_scripts', 'rezbs_add_connectJS');
?>