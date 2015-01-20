<?php
/*
 * Plugin Name: HTD 404
 * Plugin URI: http://www.htdsoftware.com
 * Description: This plugin will simply allow you to point to a page to serve up if you get a 404 error. Also it marks it as 404 
 * Version: 0.1.2
 * Author: Joshua Pack
 * Author URI: http://www.joshuapack.com
 */

//load_plugin_textdomain( 'HTD', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

add_action('admin_init', 'HTD_404_register_settings');
add_action('admin_menu', 'HTD_404_add_menu');
add_action('wp','determine_if_HTD_404'); 
add_filter('plugin_action_links', 'HTD_404_add_settings_link', 10, 2 );

function HTD_404_add_menu(){
    //Add menu page
    add_options_page('404 Setup', '404 Settings', 'manage_options', 'HTD_404', 'HTD_404_render_options');
}

function HTD_404_register_settings(){
    register_setting("HTD_404_options_group", 'HTD_404_options_group', 'HTD_404_options_validate');
    add_settings_section('HTD_404_main', '404 Settings', 'HTD_404_section_text', 'HTD_404');
    add_settings_field('HTD_404_url', 'URL to show', 'HTD_404_setting_string', 'HTD_404', 'HTD_404_main');
    add_settings_field('HTD_404_page', 'Edit HTML Page', 'HTD_404_setting_page', 'HTD_404', 'HTD_404_main');
}

function HTD_404_options_validate($input){
    //Autocomplete URL, just in case
    $url_redirect = $input['HTD_404_url'];
	if(!empty($url_redirect)) {
		if(strpos($url_redirect,'http://')===false && strpos($url_redirect,'https://')===false){
			$url_redirect = 'http://'.$url_redirect;
		}
		$validated['HTD_404_url'] = $url_redirect;
	}
    $validated['HTD_404_page'] = $input['HTD_404_page'];
	$fileName = realpath(dirname(__FILE__))."/404.html";
	file_put_contents($fileName,$validated['HTD_404_page']);
    return $validated;
}

function HTD_404_section_text(){
    ?>
    <p><?php echo 'This plugin checks to see if browser is going to hit a 404 HTTP error.<br/>If it does we prevents Wordpress to do any other processing and sends the user the contents of the page you specify.'; ?></p>
    <?php
}

function HTD_404_setting_string(){
    $options = get_option('HTD_404_options_group');
    echo "<input id='plugin_text_string' name='HTD_404_options_group[HTD_404_url]' style='width:80%;' type='text' value='{$options['HTD_404_url']}' /> <p class='howto'>".'Note: If this option is left empty the plugin will show our default 404 page.'."</p>";
}

function HTD_404_setting_page(){
    $options = get_option('HTD_404_options_group');
	$fileEdit = true;
	if ($options['HTD_404_page'] == '') {
		$fileName = realpath(dirname(__FILE__))."/404.html";
		if ($myfile = fopen($fileName, "r")) { 
			$options['HTD_404_page'] = fread($myfile,filesize($fileName));
			fclose($myfile);
		} else {
			echo "unable to open file";
			$fileEdit = false;
		}
	}
	if ($fileEdit) echo "<textarea id='plugin_text_page' name='HTD_404_options_group[HTD_404_page]' style='width:80%;height:200px;'>{$options['HTD_404_page']}</textarea> <p class='howto'>".'Note: If URL is blank above, this is the html that will be displayed. Use <i><%%URL%%>/</i> for plugin URL path'."</p>";
}

function HTD_404_render_options(){
	?>
	<div class="wrap">
   <form action="options.php" method="post">
        <?php settings_fields('HTD_404_options_group'); ?>
        <?php do_settings_sections( 'HTD_404' ); ?>
        <p class="submit"><input type="submit" value="<?php echo 'Save 404 Settings'; ?>" title="<?php echo 'Save 404 Settings'; ?>" class="button-primary"></p>
    </form>
    </div>
	<?php
    
}

function determine_if_HTD_404(&$arr){
    global $wp_query;
    
    if($wp_query->is_404){
        
        $options = get_option('HTD_404_options_group');
        if(!empty($options['HTD_404_url']) || $options['HTD_404_url'] != ''){
            $url_redirect = $options['HTD_404_url'];
        } else {
            //By default redirect to home
            $url_redirect = site_url()."/wp-content/plugins/htd-404/404.html";
        }
        header( "HTTP/1.1 404 Not Found" );
		if (function_exists('file_get_contents')) {
			$pageContents = file_get_contents($url_redirect, 10);
			$page404 = str_replace('<%%URL%%>', site_url()."/wp-content/plugins/htd-404", $pageContents);
			echo $page404;
		} else {
			echo "404 error page";
			header('Location: '.$url_redirect);
		}
        die;
    }
}

function HTD_404_add_settings_link($links, $file) {
    static $this_plugin;
    if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);

    if ($file == $this_plugin){
    $settings_link = '<a href="options-general.php?page=HTD_404.php">Settings</a>';
    array_unshift($links, $settings_link);
    }
    return $links;
 }
?>