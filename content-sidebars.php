<?php

/*
Plugin Name: Content Sidebars
Plugin URI: http://wordquest.org/plugins/content-siderbars/
Author: Tony Hayes
Description: Adds Flexible Dynamic Sidebars to your Content Areas without editing your theme.
Version: 1.4.0
Author URI: http://wordquest.org/
*/

/*
// Note, for disambiguation in the context of this plugin only:
// Logged In User Sidebar = 'Member' Sidebar = 'Fallback' Sidebar
// 'Fallback' means it is displayed instead when there is a logged in user,
// and can be activated for the AboveContent, Below Content and Login Sidebars.
// 'Login' Sidebar === Login Widget Area for a Logged Out User
// (see readme.txt FAQ for more information.)

// credit to: http://ryanpricemedia.com/2007/04/08/howto-include-wordpress-widgets-in-post-or-page-body
// and: http://www.dollarshower.com/how-to-blend-your-adsense-block-within-the-wordpress-post-content-conditionally-display-it/
// also helpful: http://hackadelic.com/the-right-way-to-shortcodize-wordpress-widgets
*/

// --------------------
// === Plugin Setup ===
// --------------------

// -----------------
// Set Plugin Values
// -----------------
global $wordquestplugins;
$vslug = $vfcsslug = 'content-sidebars';
$wordquestplugins[$vslug]['version'] = $vfcsversion = '1.4.0';
$wordquestplugins[$vslug]['title'] = 'Content Sidebars';
$wordquestplugins[$vslug]['namespace'] = 'fcs';
$wordquestplugins[$vslug]['settings'] = 'fcs';
// $wordquestplugins[$vslug]['wporgslug'] = 'content-sidebars';

// --------------
// Update Checker
// --------------
// check if wordpress.org version
$vupdatechecker = dirname(__FILE__).'/updatechecker.php';
if (file_exists($vupdatechecker)) {
	include($vupdatechecker);
	$wordquestplugins[$vslug]['wporg'] = false;
	$vupdatecheck = new PluginUpdateChecker_2_1 (
		'http://wordquest.org/downloads/?action=get_metadata&slug='.$vslug, __FILE__, $vslug
	);
} else {$wordquestplugins[$vslug]['wporg'] = true;}

// -----------------------------------
// Load WordQuest Helper/Pro Functions
// -----------------------------------
if (is_admin()) {
	$wordquest = dirname(__FILE__).'/wordquest.php';
	if (file_exists($wordquest)) {include($wordquest);}
}
$vprofunctions = dirname(__FILE__).'/pro-functions.php';
if (file_exists($vprofunctions)) {include($vprofunctions); $wordquestplugins[$vslug]['plan'] = 'premium';}
else {$wordquestplugins[$vslug]['plan'] = 'free';}

// -------------
// Load Freemius
// -------------
function fcs_freemius($vslug) {
    global $wordquestplugins, $fcs_freemius;
    $vwporg = $wordquestplugins[$vslug]['wporg'];
	if ($wordquestplugins[$vslug]['plan'] == 'premium') {$vpremium = true;} else {$vpremium = false;}

	// redirect for support forum
	if ( (is_admin()) && (isset($_REQUEST['page'])) ) {
		if ($_REQUEST['page'] == $vslug.'-wp-support-forum') {
			if(!function_exists('wp_redirect')) {include(ABSPATH.WPINC.'/pluggable.php');}
			wp_redirect('http://wordquest.org/quest/quest-category/plugin-support/'.$vslug.'/'); exit;
		}
	}

    if (!isset($fcs_freemius)) {
        // include Freemius SDK
        if (!class_exists('Freemius')) {require_once(dirname(__FILE__).'/freemius/start.php');}

		$fcs_settings = array(
            'id'                => '163',
            'slug'              => $vslug,
            'public_key'        => 'pk_386ac55ea05fcdcd4daf27798b46c',
            'is_premium'        => $vpremium,
            'has_addons'        => false,
            'has_paid_plans'    => false,
            'is_org_compliant'  => $vwporg,
            'menu'              => array(
                'slug'       	=> $vslug,
                'first-path' 	=> 'admin.php?page='.$vslug.'&welcome=true',
                'parent'		=> array('slug'=>'wordquest'),
                'contact'		=> $vpremium,
                // 'support'    	=> false,
                // 'account'    	=> false,
            )
        );
        $fcs_freemius = fs_dynamic_init($fcs_settings);
    }
    return $fcs_freemius;
}
// initialize Freemius
$fcs_freemius = fcs_freemius($vslug);

// Custom Freemius Connect Message
// -------------------------------
function fcs_freemius_connect($message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link) {
	return sprintf(
		__fs('hey-x').'<br>'.
		__("In order to enjoy all this plugins features and functionality, %s needs to connect your user, %s at %s, to %s", 'wp-automedic'),
		$user_first_name, '<b>'.$plugin_title.'</b>', '<b>'.$user_login.'</b>', $site_link, $freemius_link
	);
}
fcs_freemius($vslug)->add_filter('connect_message', 'fcs_freemius_connect', WP_FS__DEFAULT_PRIORITY, 6);

// Add Admin Page
// --------------
if (is_admin()) {add_action('admin_menu','fcs_settings_menu',1);}
function fcs_settings_menu() {
	if (empty($GLOBALS['admin_page_hooks']['wordquest'])) {
		$vicon = plugin_dir_url(__FILE__).'images/wordquest-icon.png'; $vposition = apply_filters('wordquest_menu_position','3');
		add_menu_page('WordQuest Alliance', 'WordQuest', 'manage_options', 'wordquest', 'wqhelper_admin_page', $vicon, $vposition);
	}
	add_submenu_page('wordquest', 'Content Sidebars', 'Content Sidebars', 'manage_options', 'content-sidebars', 'fcs_options_page');

	// $vicon = plugin_dir_url(__FILE__).'images/icon.png';
	// add_menu_page('Content Sidebars', 'Content Sidebars', 'manage_options', 'content-sidebars', 'fcs_options_page', $vicon, '82');

	// Add icons and styling to the plugin submenu :-)
	add_action('admin_footer','fcs_admin_javascript');
	function fcs_admin_javascript() {
		global $vfcsslug; $vslug = $vfcsslug; $vcurrent = '0';
		$vicon = plugin_dir_url(__FILE__).'images/icon.png';
		if (isset($_REQUEST['page'])) {if ($_REQUEST['page'] == $vslug) {$vcurrent = '1';} }
		echo "<script>jQuery(document).ready(function() {if (typeof wordquestsubmenufix == 'function') {
		wordquestsubmenufix('".$vslug."','".$vicon."','".$vcurrent."');} });</script>";
	}

	// Add Plugin Settings Link
	add_filter('plugin_action_links', 'fcs_register_plugin_links', 10, 2);
	function fcs_register_plugin_links($vlinks, $vfile) {
		global $vfcsslug;
		$vthisplugin = plugin_basename(__FILE__);
		if ($vfile == $vthisplugin) {
			$vsettingslink = "<a href='admin.php?page=".$vfcsslug."'>Settings</a>";
			array_unshift($vlinks, $vsettingslink);
		}
		return $vlinks;
	}

}

// add Appearance menu too (as relevant)
// -----------------------
add_action('admin_menu','fcs_theme_options_menu');
function fcs_theme_options_menu() {
	add_theme_page('Content Sidebars', 'Content Sidebars', 'manage_options', 'flexi-sidebars', 'fcs_theme_options_dummy');
	function fcs_theme_options_dummy() {} // dummy menu item function
}
// appearance menu item redirect
function fcs_theme_options_page() {
	global $vfcsslug; wp_redirect(admin_url('admin.php').'?page='.$vfcsslug);
}
// trigger redirect to real admin menu item
if (strstr($_SERVER['REQUEST_URI'],'/wp-admin/themes.php')) {
	if (isset($_REQUEST['page'])) {if ($_REQUEST['page'] == 'flexi-sidebars') {add_action('init','fcs_theme_options_page');} }
}

// Load Sidebar Styles
// -------------------
// 1.3.5: changed to wp_enqueue_scripts
add_action('wp_enqueue_scripts','fcs_queue_styles');
function fcs_queue_styles() {
	$vcssmode = fcs_get_option('fcs_css_mode',true);
	if ($vcssmode == 'default') {
		$vflexisidebarcss = plugins_url('content-sidebars.css', __FILE__);
		wp_enqueue_style('flexi_content_sidebar_styles',$vflexisidebarcss);
	}
	elseif ($vcssmode == 'dynamic') {
		$vversion = fcs_get_option('last_saved');
		wp_enqueue_style('fcs-dynamic', admin_url('admin-ajax.php').'?action=fcs_dynamic_css', array(), $vversion); // $media
	}
}

// AJAX Dynamic CSS Output
// -----------------------
add_action('wp_ajax_fcs_dynamic_css', 'fcs_dynamic_css');
add_action('wp_ajax_nopriv_fcs_dynamic_css', 'fcs_dynamic_css');
function fcs_dynamic_css() {require(dirname(__FILE__).'/dynamic-css.php'); exit;}

// Widget Page Styles
// ------------------
// 1.4.0: style the sidebar on widget page
if (is_admin() && ($pagenow == 'widgets.php')) {
	add_action('admin_head','fcs_widget_page_styles');
	function fcs_widget_page_styles() {
		echo "<style>.sidebar-content-on {background-color:#E9F0FF;} .sidebar-content-on h2 {font-size: 12pt;}
		.sidebar-content-off {background-color:#EFF3FF;} .sidebar-content-off h2 {font-weight: normal; font-size: 10pt;}</style>";
	}
}

// Widget Page Message
// -------------------
add_action('widgets_admin_page','fcs_widget_page_message',11);
function fcs_widget_page_message() {
	$vmessage = __('Note: Inactive Content Sidebars are listed with lowercase titles. Activate them via Content Sidebars settings.', 'csidebars');
	echo "<div class='message'>".$vmessage."</div>";
}

// CSS Hero Integration
// --------------------
// 1.3.0: added CSS Hero script workaround
// TODO: test in combination with theme declarations?
if ( (isset($_GET['csshero_action'])) && ($_GET['csshero_action'] == 'edit_page') ) {
	// add_action('wp_loaded','fcs_csshero_script_dir',1);
	function fcs_csshero_script_dir() {
		add_filter('stylesheet_directory_uri','fcs_csshero_script_url',11,3);
		function fcs_csshero_script_url($stylesheet_dir_uri, $stylesheet, $theme_root_uri) {
			$vcsshero = dirname(__FILE__);
			if (file_exists($vcsshero.'/csshero.js')) {
				$vcssherouri = plugin_dir_url(__FILE__);
				// workaround to add additional script URL
				$stylesheet_dir_uri .= "/csshero.js'><script type='text/javascript' src='".$vcssherouri; // '
			}
			return $stylesheet_dir_uri;
		}
	}
}


// -------------------
// === Load Plugin ===
// -------------------

// 1.3.5: get global plugin options
global $vfcsoptions; $vfcsoptions = get_option('content_sidebars');
// print_r($vfcsoptions); // debug point

// Set Login State
// ---------------
// 1.3.5: set login state once for efficiency
global $vfcsstate;
add_action('init','fcs_set_login_state');
function fcs_set_login_state() {
	global $vfcsstate;
	$current_user = wp_get_current_user();
	if ($current_user->exists()) {$vfcsstate = 'loggedin';}
	else {$vfcsstate = 'loggedout';}
}

// Get Sidebar Overrides
// ---------------------
add_action('init','fcs_get_overrides');
function fcs_get_overrides() {
	global $post, $vfcsoverrides;
	if (is_object($post)) {
		$vpostid = $post->ID;
		$vfcsoverrides = get_post_meta($vpostid,'content_sidebars',true);

		// maybe set new key value, checking for existing disable metakeys
		if (!$vfcsoverrides) {
			$voptionkeys = array(
				'abovecontentsidebar','belowcontentsidebar','loginsidebar','membersidebar',
				'shortcodesidebar1','shortcodesidebar2','shortcodesidebar3',
				'inpostsidebar1','inpostsidebar2','inpostsidebar3'
			);
			foreach ($voptionkeys as $voptionkey) {
				$vnewkey = str_replace('sidebar','',$voptionkey);
				if (get_post_meta($vpostid,'_disable'.$voptionkey,true) == 'yes') {
					$vfcsoverrides[$vnewkey] = 'disable';
				} else {$vfcsoverrides[$vnewkey] = '';}
			}
			add_post_meta($vpostid,'content_sidebars',$vfcsoverrides,true);
		}
	}
}

// Get Sidebar Helper
// ------------------
function fcs_get_sidebar($vsidebar) {
	ob_start();
	// $index = ( is_int($vsidebar) ) ? "sidebar-$vsidebar" : sanitize_title($vsidebar);
	// echo "***".$index."***"; // debug point
	dynamic_sidebar($vsidebar);
	$vsidebarcontents = ob_get_contents();
	ob_end_clean();
	return $vsidebarcontents;
}


// --------------
// Plugin Options
// --------------

// Get Plugin Option
// -----------------
// 1.3.5: use global options array
function fcs_get_option($vkey,$vfilter=false) {
	global $vfcsoptions;
	$vkey = str_replace('fcs_','',$vkey);
	if (isset($vfcsoptions[$vkey])) {
		if ( (strstr($vkey,'_fallback')) && ($vfcsoptions[$vkey] == 'yes') ) {$vfcsoptions[$vkey] = 'fallback';}
		if ($vfilter) {return apply_filters($vkey,$vfcsoptions[$vkey]);}
		else {return $vfcsoptions[$vkey];}
	} else {return '';}
}

// maybe Transfer Old Settings
// ---------------------------
// 1.3.5: compact old settings into global array
if ( (get_option('fcs_abovebelow_method')) && (!get_option('content_sidebars')) ) {
	$vfcsoptionkeys = array(
		'abovebelow_method','abovecontent_hook','belowcontent_hook','loginsidebar_hook','membersidebar_hook',
		'abovecontent_priority','belowcontent_priority','loginsidebar_priority','membersidebar_priority',
		'abovecontent_fallback','belowcontent_fallback','loginsidebar_fallback',
		'abovecontent_sidebar_cpts','belowcontent_sidebar_cpts','inpost_sidebars_cpts',
		'loginsidebar_disable', 'membersidebar_disable', 'abovecontent_disable', 'belowcontent_disable',
		'shortcode1_disable', 'shortcode2_disable', 'shortcode3_disable',
		'inpost1_disable','inpost2_disable','inpost3_disable','inpost_marker','inpost_priority',
		'inpost_positiona','inpost_positionb','inpost_positionc','inpost1_float','inpost2_float','inpost3_float',
		'css_mode','dynamic_css');

	foreach ($vfcsoptionkeys as $vkey) {
		$vfcsoptions[$vkey] = get_option('fcs_'.$vkey);
		// 1.4.0: convert old fallback value
		if ( (strstr($vkey,'_fallback')) && ($vfcsoptions[$vkey] == 'yes') ) {$vfcsoptions[$vkey] = 'fallback';}
	}
	$fcsoptions['last_saved'] = time();

	if (add_option('content_sidebars',$vfcsoptions)) {
		foreach ($vfcsoptionkeys as $vkey) {delete_option('fcs_'.$vkey);}
	}
}

// Add Plugin Options
// ------------------
register_activation_hook(__FILE__,'fcs_add_options');

// 1.3.5: use global options array
function fcs_add_options() {

	global $vfcsoptions;

	// method
	$vfcsoptions['abovebelow_method'] = 'hooks';

	// template hooks
	$vfcsoptions['abovecontent_hook'] = 'skeleton_before_loop';
	$vfcsoptions['belowcontent_hook'] = 'skeleton_after_loop';
	$vfcsoptions['loginsidebar_hook'] = 'skeleton_before_header';
	$vfcsoptions['membersidebar_hook'] = '';

	// hook priorities
	$vfcsoptions['abovecontent_priority'] = '5';
	$vfcsoptions['belowcontent_priority'] = '5';
	$vfcsoptions['loginsidebar_priority'] = '5';
	$vfcsoptions['membersidebar_priority'] = '5';

	// fallback switches
	$vfcsoptions['abovecontent_fallback'] = '';
	$vfcsoptions['belowcontent_fallback'] = '';
	$vfcsoptions['loginsidebar_fallback'] = 'fallback';

	// post types
	$vfcsoptions['abovecontent_sidebar_cpts'] = 'page';
	$vfcsoptions['belowcontent_sidebar_cpts'] = 'post';
	$vfcsoptions['login_sidebar_cpts'] = 'post,page';
	$vfcsoptions['member_sidebar_cpts'] = 'post,page';
	$vfcsoptions['inpost_sidebars_cpts'] = 'article';

	// disablers
	$vfcsoptions['loginsidebar_disable'] = '';
	$vfcsoptions['membersidebar_disable'] = '';
	$vfcsoptions['abovecontent_disable'] = '';
	$vfcsoptions['belowcontent_disable'] = '';
	$vfcsoptions['shortcode1_disable'] = '';
	$vfcsoptions['shortcode2_disable'] = '';
	$vfcsoptions['shortcode3_disable'] = '';

	// inpost sidebars
	$vfcsoptions['inpost1_disable'] = 'yes';
	$vfcsoptions['inpost2_disable'] = 'yes';
	$vfcsoptions['inpost3_disable'] = 'yes';
	$vfcsoptions['inpost_marker'] = '</p>';
	$vfcsoptions['inpost_positiona'] = '4';
	$vfcsoptions['inpost_positionb'] = '8';
	$vfcsoptions['inpost_positionc'] = '12';
	$vfcsoptions['inpost1_float'] = 'right';
	$vfcsoptions['inpost2_float'] = 'left';
	$vfcsoptions['inpost3_float'] = 'right';
	$vfcsoptions['inpost_priority'] = '100';

	// widget options
	$vfcsoptions['widget_text_shortcodes'] = 'yes';
	$vfcsoptions['widget_title_shortcodes'] = '';

	// css options
	$vdefaultcss = file_get_contents(dirname(__FILE__).'/content-sidebars.css');
	$vfcsoptions['css_mode'] = 'default';
	$vfcsoptions['dynamic_css'] = $vdefaultcss;
	$vfcsoptions['last_saved'] = time();

	// add global option array
	add_option('content_sidebars',$vfcsoptions);

	// sidebar options
	if (file_exists(dirname(__FILE__).'/updatechecker.php')) {$vadsboxoff = '';} else {$vadsboxoff = 'checked';}
	$sidebaroptions = array('adsboxoff'=>$vadsboxoff,'donationboxoff'=>'','reportboxoff'=>'','installdate'=>date('Y-m-d'));
	add_option('fcs_sidebar_options',$sidebaroptions);
}


// Reset Options
// -------------
// 1.3.5: reset options function
if ( (isset($_GET['contentsidebars'])) && ($_GET['contentsidebars'] == 'reset') ) {add_action('init','fcs_reset_options',0);}
function fcs_reset_options() {
	if (current_user_can('manage_options')) {delete_option('content_sidebars'); fcs_add_options();}
}

// Update Options Trigger
// ----------------------
if ( (isset($_POST['fcs_update_options'])) && ($_POST['fcs_update_options'] == 'yes') ) {add_action('init','fcs_update_options');}

// Update Options
// --------------
// 1.3.5 update to use global options array
function fcs_update_options() {

	if (!current_user_can('manage_options')) {return;}

	global $vfcsoptions;

	// update all option keys here except the CPT ones
	$vfcsoptionkeys = array('abovebelow_method',
		'abovecontent_hook','belowcontent_hook','loginsidebar_hook','membersidebar_hook',
		'abovecontent_priority','belowcontent_priority','loginsidebar_priority','membersidebar_priority',
		'abovecontent_fallback','belowcontent_fallback','loginsidebar_fallback',
		'membersidebar_disable', 'loginsidebar_disable', 'abovecontent_disable', 'belowcontent_disable',
		'shortcode1_disable', 'shortcode2_disable', 'shortcode3_disable',
		'inpost1_disable','inpost2_disable','inpost3_disable','inpost_marker','inpost_priority',
		'inpost_positiona','inpost_positionb','inpost_positionc','inpost1_float','inpost2_float','inpost3_float',
		'widget_text_shortcodes','widget_title_shortcodes','css_mode','dynamic_css'
	);

	foreach ($vfcsoptionkeys as $vkey) {
		if (isset($_POST['fcs_'.$vkey])) {$vfcsoptions[$vkey] = $_POST['fcs_'.$vkey];}
		else {$vfcsoptions[$vkey] = '';}
	}

	// get all the post types
	$vcpts[0] = 'post'; $vcpts[1] = 'page';
	$vargs = array('public'=>true, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vcpts = array_merge($vcpts,$vcptlist);

	// above content post types
	$vi = 0; $vnewcpts = array();
	foreach ($vcpts as $vcpt) {
		$vpostkey = 'fcs_abovecontent_posttype_'.$vcpt;
		if (isset($_POST[$vpostkey])) {
			if ($_POST[$vpostkey] == 'yes') {$vnewcpts[$vi] = $vcpt; $vi++;}
		}
	}
	$vcptoptions = implode(',',$vnewcpts);
	$vfcsoptions['abovecontent_sidebar_cpts'] = $vcptoptions;

	// below content post types
	$vi = 0; $vnewcpts = array();
	foreach ($vcpts as $vcpt) {
		$vpostkey = 'fcs_belowcontent_posttype_'.$vcpt;
		if (isset($_POST[$vpostkey])) {
			if ($_POST[$vpostkey] == 'yes') {$vnewcpts[$vi] = $vcpt; $vi++;}
		}
	}
	$vcptoptions = implode(',',$vnewcpts);
	$vfcsoptions['belowcontent_sidebar_cpts'] = $vcptoptions;

	// login sidebar post types
	$vi = 0; $vnewcpts = array();
	foreach ($vcpts as $vcpt) {
		$vpostkey = 'fcs_login_posttype_'.$vcpt;
		if (isset($_POST[$vpostkey])) {
	 		if ($_POST[$vpostkey] == 'yes') {$vnewcpts[$vi] = $vcpt; $vi++;}
	 	}
	}
	$vcptoptions = implode(',',$vnewcpts);
	$vfcsoptions['login_sidebar_cpts'] = $vcptoptions;

	// login sidebar post types
	$vi = 0; $vnewcpts = array();
	foreach ($vcpts as $vcpt) {
		$vpostkey = 'fcs_member_posttype_'.$vcpt;
		if (isset($_POST[$vpostkey])) {
	 		if ($_POST[$vpostkey] == 'yes') {$vnewcpts[$vi] = $vcpt; $vi++;}
	 	}
	}
	$vcptoptions = implode(',',$vnewcpts);
	$vfcsoptions['member_sidebar_cpts'] = $vcptoptions;

	// no shortcode post types needed

	// inpost sidebar post types
	$vi = 0; $vnewcpts = array();
	foreach ($vcpts as $vcpt) {
		$vpostkey = 'fcs_inpost_posttype_'.$vcpt;
		if (isset($_POST[$vpostkey])) {
			if ($_POST[$vpostkey] == 'yes') {$vnewcpts[$vi] = $vcpt; $vi++;}
		}
	}
	$vcptoptions = implode(',',$vnewcpts);
	$vfcsoptions['inpost_sidebars_cpts'] = $vcptoptions;

	$vfcsoptions['last_saved'] = time();
	update_option('content_sidebars',$vfcsoptions);
}

// Options Page
// ------------
function fcs_options_page() {

	global $vfcsversion, $vfcsslug;

	echo "<script language='javascript' type='text/javascript'>
	function loaddefaultcss() {document.getElementById('dynamiccss').value = document.getElementById('defaultcss').value;}
	function loadcssfile() {document.getElementById('dynamiccss').value = document.getElementById('cssfile').value;}
	function loadsavedcss() {document.getElementById('dynamiccss').value = document.getElementById('savedcss').value;}</script>";

	echo "<style>.small {font-size:9pt;}</style>";

	echo '<div class="wrap">';

	// Admin Notices Boxer
	if (function_exists('wqhelper_admin_notice_boxer')) {wqhelper_admin_notice_boxer();} else {echo "<h2> </h2>";}

	// Plugin Page Title
	// -----------------
	$viconurl = plugin_dir_url(__FILE__)."images/content-sidebars.png";
	echo "<table><tr><td><img src='".$viconurl."'></td>";
	echo "<td width='20'></td>";
	echo "<td><h2>".__('Content Sidebars','csidebars')."</h2></td>";
	echo "<td width='20'></td>";
	echo "<td><h3>v".$vfcsversion."</h3></td>";
	echo "</td><td width='100'></td>";
	if ( (isset($_REQUEST['update'])) && ($_REQUEST['updated'] == 'yes') ) {
		echo "<td><table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
		echo "<tr><td><div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
		echo __('Settings Updated.','csidebars')."</font></div></td></tr></table></td>";
	}
	echo "</tr></table><br>";

	$vfallbackoptions = array('output' => 'Output', 'hidden' => 'Hide', 'fallback' => 'Fallback', 'nooutput' => 'No Output');

	// get post types
	$vcpts[0] = 'post'; $vcpts[1] = 'page';
	$vargs = array('public'=>true, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vcpts = array_merge($vcpts,$vcptlist);

	echo "<div class='postbox' style='width:700px; line-height:2em;'><div class='inner' style='padding-left:20px;'>";
	echo "<h3>".__('Extra Sidebars','csidebars')."</h3>";
	echo "<form action='admin.php?page=".$vfcsslug."&updated=yes' method='post'>";
	echo "<input type='hidden' name='fcs_update_options' value='yes'>";

	echo "<table><tr><td><b>".__('Positioning Mode','csidebars')."</b></td><td></td>";
	echo "<td colspan='2'><input type='radio' name='fcs_abovebelow_method' value='hooks'";
	if (fcs_get_option('fcs_abovebelow_method') == 'hooks') {echo " checked";}
	echo "> ".__('Use Template Action Hooks','csidebars')."</td>";
	echo "<td colspan='4'><input type='radio' name='fcs_abovebelow_method' value='filter'";
	if (fcs_get_option('fcs_abovebelow_method') == 'filter') {echo " checked";}
	echo "> ".__('Use Content Filter','csidebars')."</td></tr>";
	echo "<tr><td colspan='10'>".__('Note: Content Filter mode cannot account for the post title which is (usually) above','csidebars')." the_content!<br>";
	echo __('So if you want a sidebar above the title you will need to use Template Hooks','csidebars')." (see readme.txt FAQ)</td></tr>";
	echo "<tr height='20'><td> </td></tr>";

	echo "<tr><td><b>".__('Above Content Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_abovecontent_hook' size='20' value='".fcs_get_option('fcs_abovecontent_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_abovecontent_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_abovecontent_priority')."'></td>";
	echo "<td class='small'>".__('Logged In','csidebars').": </td>";
	echo "<td><select name='fcs_abovecontent_fallback'>";
		$vfallback = fcs_get_option('fcs_abovecontent_fallback');
		foreach ($vfallbackoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if ($vfallback == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr>";

	echo "<tr><td align='center'><table><tr><td><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_abovecontent_disable' value='yes'";
	if (fcs_get_option('fcs_abovecontent_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	// post type selection for above content sidebars
	$vgetcpts = fcs_get_option('fcs_abovecontent_sidebar_cpts');
	if (strstr($vgetcpts,',')) {$vabovecpts = explode(',',$vgetcpts);}
	else {$vabovecpts[0] = $vgetcpts;}

	echo "<td align='left' colspan='6'>";
	if (count($vcpts) > 0) {
		echo "<ul><li style='display:inline-block; margin:0 10px 0 0;'>".__('Output on','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_abovecontent_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vabovecpts)) {echo " checked>";} else {echo ">";}
			echo $vcpt."</li>";
		}
		echo "</ul>";
	}
	echo "</td></tr>";

	echo "<tr><td><b>".__('Below Content Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_belowcontent_hook' size='20' value='".fcs_get_option('fcs_belowcontent_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_belowcontent_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_belowcontent_priority')."'></td>";
	echo "<td class='small'>".__('Logged In','csidebars').": </td>";
	echo "<td><select name='fcs_belowcontent_fallback'>";
		$vfallback = fcs_get_option('fcs_belowcontent_fallback');
		foreach ($vfallbackoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if ($vfallback == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr>";

	echo "<tr><td align='center'><table><tr><td><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_belowcontent_disable' value='yes'";
	if (fcs_get_option('fcs_belowcontent_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	// post type selection for below content sidebar
	$vgetcpts = fcs_get_option('fcs_belowcontent_sidebar_cpts');
	if (strstr($vgetcpts,',')) {$vbelowcpts = explode(',',$vgetcpts);}
	else {$vbelowcpts[0] = $vgetcpts;}

	echo "<td align='left' colspan='6'>";
	if (count($vcpts) > 0) {
		echo "<ul><li style='display:inline-block; margin:0 10px 0 0;'>".__('Output on','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_belowcontent_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vbelowcpts)) {echo " checked>";} else {echo ">";}
			echo $vcpt."</li>";
		}
		echo "</ul>";
	}
	echo "</td></tr>";

	echo "<tr><td><b>".__('Login Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_loginsidebar_hook' size='20' value='".fcs_get_option('fcs_loginsidebar_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_loginsidebar_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_loginsidebar_priority')."'></td>";
	echo "<td class='small'>".__('Logged In','csidebars').": </td>";
	echo "<td><select name='fcs_loginsidebar_fallback'>";
		$vfallback = fcs_get_option('fcs_loginsidebar_fallback');
		foreach ($vfallbackoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if ($vfallback == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr>";


	echo "<tr><td align='center'><table><tr><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_loginsidebar_disable' value='yes'";
	if (fcs_get_option('fcs_loginsidebar_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	// post type selection for login sidebar
	$vgetcpts = fcs_get_option('fcs_login_sidebar_cpts');
	if (strstr($vgetcpts,',')) {$vlogincpts = explode(',',$vgetcpts);}
	else {$vlogincpts[0] = $vgetcpts;}

	echo "<td align='left' colspan='6'>";
	if (count($vcpts) > 0) {
		echo "<ul><li style='display:inline-block; margin:0 10px 0 0;'>".__('Output on','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_login_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vlogincpts)) {echo " checked>";} else {echo ">";}
			echo $vcpt."</li>";
		}
		echo "</ul>";
	}
	echo "</td></tr>";

	echo "<tr><td><b>".__('Logged In Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_membersidebar_hook' size='20' value='".fcs_get_option('fcs_membersidebar_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_membersidebar_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_membersidebar_priority')."'></td>";
	echo "<td align='center' colspan='2'>(".__('logged in fallback','csidebars').")</td></tr>";

	echo "<tr><td align='center'><table><tr><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_membersidebar_disable' value='yes'";
	if (fcs_get_option('fcs_membersidebar_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	// post type selection for member sidebar
	$vgetcpts = fcs_get_option('fcs_member_sidebar_cpts');
	if (strstr($vgetcpts,',')) {$vmembercpts = explode(',',$vgetcpts);}
	else {$vmembercpts[0] = $vgetcpts;}

	echo "<td align='left' colspan='6'>";
	if (count($vcpts) > 0) {
		echo "<ul><li style='display:inline-block; margin:0 10px 0 0;'>".__('Output on','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_member_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vmembercpts)) {echo " checked>";} else {echo ">";}
			echo $vcpt."</li>";
		}
		echo "</ul>";
		}
	echo "</td></tr>";

	echo "</table><br>";
	echo "(".__('Ticked Fallbacks show Fallback Sidebar instead for Logged In Users, eg. Members Area Links.','csidebars').")<br><br>";

	// 1.3.5: add options for widget text/title shortcodes
	echo "<h3>".__('Widget Shortcodes','csidebars')."</h3>";
	echo "<table><tr><td><b>".__('Process Shortcodes in Widget Text','csidebars')."</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_widget_text_shortcodes' value='yes'";
	if (fcs_get_option('fcs_widget_text_shortcodes') == 'yes') {echo " checked";}
	echo "></td><td width='30'></td>";
	echo "<td><b>".__('Process Shortcodes in Widget Titles','csidebars')."</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_widget_title_shortcodes' value='yes'";
	if (fcs_get_option('fcs_widget_title_shortcodes') == 'yes') {echo " checked";}
	echo "></td></tr></table><br>";

	echo "<h3>".__('Shortcode Sidebars','csidebars')."</h3>";

	echo "<table><tr><td><b>".__('Sidebar','csidebars')." 1</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode1_disable' value='yes'";
	if (fcs_get_option('fcs_shortcode1_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 2</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode2_disable' value='yes'";
	if (fcs_get_option('fcs_shortcode2_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 3</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode3_disable' value='yes'";
	if (fcs_get_option('fcs_shortcode3_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td></tr>";

	echo "<tr><td colspan='4'>[shortcode-sidebar-1]</td><td></td>";
	echo "<td colspan='4'>[shortcode-sidebar-2]</td><td></td>";
	echo "<td colspan='4'>[shortcode-sidebar-3]</td></tr></table><br>";

	echo "<h3>".__('InPost Sidebars','csidebars')."</h3>";

	echo "<table><tr><td><b>".__('Sidebar','csidebars')." 1</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost1_disable' value='yes'";
	if (fcs_get_option('fcs_inpost1_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 2</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost2_disable' value='yes'";
	if (fcs_get_option('fcs_inpost2_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 3</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost3_disable' value='yes'";
	if (fcs_get_option('fcs_inpost3_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td></tr>";
	echo "</table>";

	$vcptoptions = fcs_get_option('fcs_inpost_sidebars_cpts');
	if (strstr($vcptoptions,',')) {$vinpostcpts = explode(',',$vcptoptions);}
	else {$vinpostcpts[0] = $vcptoptions;}

	echo "<table>";
	echo "<tr><td>".__('Activate for Post Types','csidebars').":</td>";
	echo "<td width='10'></td>";
	echo "<td colspan='5'>";
	if (count($vcpts) > 0) {
		echo "<ul>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'><input type='checkbox' name='fcs_inpost_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vinpostcpts)) {echo " checked>";} else {echo ">";}
			echo $vcpt."</li>";
		}
		echo "</ul>";
	}
	echo "</td>";
	echo "<tr><td>".__('Paragraph Split Marker','csidebars').":</td>";
	echo "<td width='10'></td>";
	echo "<td><input type='text' size='15' style='width:110px;' name='fcs_inpost_marker' value='".fcs_get_option('fcs_inpost_marker')."'></td>";
	echo "<td width='40'></td>";
	echo "<td>the_content ".__('Filter Priority','csidebars').":</td><td width='10'></td>";
	echo "<td><input type='text' size='3' style='width:40px;' name='fcs_inpost_priority' value='".fcs_get_option('fcs_inpost_priority')."'></td>";
	echo "</tr><tr><td colspan='3' align='center'>(".__('Used to count and split paragraphs.','csidebars').")</td>";
	echo "</tr></table>";

	echo "<table><tr><td style='vertical-align:top;'>";
		echo "<table>";
		echo "<tr height='30'><td>".__('Insert Sidebar','csidebars')." 1 ".__('After Paragraph','csidebars')." #</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positiona' value='".fcs_get_option('fcs_inpost_positiona')."'></td></tr>";
		echo "<tr height='5'><td> </td></tr>";
		echo "<tr height='30'><td>".__('Insert Sidebar','csidebars')." 2 ".__('After Paragraph','csidebars')." #</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positionb' value='".fcs_get_option('fcs_inpost_positionb')."'></td></tr>";
		echo "<tr height='5'><td> </td></tr>";
		echo "<tr height='30'><td>".__('Insert Sidebar','csidebars')." 3 ".__('After Paragraph','csidebars')." #</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positionc' value='".fcs_get_option('fcs_inpost_positionc')."'></td>";
		echo "</tr></table>";
	echo "</td><td width='20'></td><td style='vertical-align:top;'>";

	$vfloatoptions = array('' => 'Do Not Set', 'none' => 'None', 'left' => 'Left', 'right' => 'Right');

	echo "<table><tr height='30'><td>".__('Float Sidebar','csidebars')." 1: </td><td width='10'></td>";
	echo "<td><select name='fcs_inpost1_float'>";
		foreach ($vfloatoptions as $vkey => $vlabel) {
		 	echo "<option value='".$vkey."'";
			if (fcs_get_option('fcs_inpost1_float') == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr><tr height='5'><td> </td></tr>";
	echo "<tr height='30'><td>".__('Float Sidebar','csidebars')." 2: </td><td width='10'></td>";
	echo "<td><select name='fcs_inpost2_float'>";
		foreach ($vfloatoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if (fcs_get_option('fcs_inpost2_float') == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr><tr height='5'><td> </td></tr>";
	echo "<tr height='30'><td>".__('Float Sidebar','csidebars')." 3: </td><td width='10'></td>";
	echo "<td><select name='fcs_inpost3_float'>";
		foreach ($vfloatoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if (fcs_get_option('fcs_inpost3_float') == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr></table>";

	echo "</td></tr><tr height='20'><td></td></tr>";

	echo "<tr><td><h3>".__('CSS Styles','csidebars')."</h3></td></tr>";
	$vdefaultcss = file_get_contents(dirname(__FILE__).'/content-default.css');
	$vcssfile = file_get_contents(dirname(__FILE__).'/content-sidebars.css');
	$vsavedcss = fcs_get_option('fcs_dynamic_css');
	echo "<tr><td colspan='3'><table>";
		echo "<tr><td><b>".__('CSS Mode','csidebars')."</b>:</td><td width='10'></td>";
		echo "<td><input type='radio' name='fcs_css_mode' value='default'";
		if (fcs_get_option('fcs_css_mode') == 'default') {echo " checked";}
		echo "> ".__('Default','csidebars')." (use content-sidebars.css) </td><td width='10'></td>";
		echo "<td><input type='radio' name='fcs_css_mode' value='dynamic'";
		if (fcs_get_option('fcs_css_mode') == 'dynamic') {echo " checked";}
		echo "> ".__('Dynamic CSS (below)','csidebars')."</td></tr></table><br>";
	echo "</td></tr>";

	echo "<tr><td colspan='3'><b>".__('Dynamic CSS','csidebars')."</b>:<br>";
	echo "<textarea rows='7' cols='70' style='width:100%;' id='dynamiccss' name='fcs_dynamic_css'>".$vsavedcss."</textarea>";
	echo "</td></tr>";

	echo "<tr><td colspan='3'><table style='width:100%;'>";
		echo "<tr><td align='left' style='width:33%;'><input type='button' class='button-secondary' style='font-size:9pt;' onclick='loaddefaultcss();' value='".__('Load Default CSS','csidebars')."'></td>";
		echo "<td align='center' style='width:33%;'><input type='button' class='button-secondary' style='font-size:9pt;' onclick='loadcssfile();' value='".__('Load CSS File','csidebars')."'></td>";
		echo "<td align='right' style='width:33%;'><input type='button' class='button-secondary' style='font-size:9pt;' onclick='loadsavedcss();' value='".__('Reload Saved CSS','csidebars')."'></td></tr>";
	echo "</table></td></tr>";

	echo "<tr height='15'><td> </td></tr>";
	echo "<tr><td colspan='3' align='center'>";
	echo "<input type='submit' class='button-primary' id='plugin-settings-save' value='".__('Save Settings','csidebars')."'>";
	echo "</td></tr>";

	echo "</table><br></form>";

	// Dummy CSS Textareas
	echo "<textarea id='defaultcss' style='display:none'>".$vdefaultcss."</textarea>";
	echo "<textarea id='cssfile' style='display:none'>".$vcssfile."</textarea>";
	echo "<textarea id='savedcss' style='display:none'>".$vsavedcss."</textarea>";

	echo "<br><h4>".__('CSS ID and Class Reference','csidebars').":</h4><br>
	<table cellpadding='5' cellspacing='5'>
	<tr><td><b>".__('Sidebar ID','csidebars')."</b></td><td><b>".__('Sidebar Class','csidebars')."</b></td>
	<td><b>".__('Widget Class','csidebars')."</b></td><td><b>".__('Widget Title Class','csidebars')."</b></td></tr>
	<tr><td>#abovecontentsidebar</td><td>.contentsidebar</td><td>.abovecontentwidget</td><td>.abovecontenttitle</td></tr>
	<tr><td>#belowcontentsidebar</td><td>.contentsidebar</td><td>.belowcontentwidget</td><td>.belowcontenttitle</td></tr>
	<tr><td>#loginsidebar</td><td>.contentsidebar</td><td>.loginwidget</td><td>.loginwidgettitle</td></tr>
	<tr><td>* .loggedinsidebar</td><td>.contentsidebar</td><td>.loggedinwidget</td><td>.loggedinwidgettitle</td></tr>
	<tr><td>#shortcodesidebar1</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#shortcodesidebar2</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#shortcodesidebar3</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#inpostsidebar1</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	<tr><td>#inpostsidebar2</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	<tr><td>#inpostsidebar3</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	</table>";

	// echo "Note: For individualized widget shortcodes you can use: <a href='http://wordpress.org/plugins/amr-shortcode-any-widget/' target=_blank>Shortcode Any Widget</a><br><br>";
	echo "</div></div>";

	// Call Plugin Sidebar
	// -------------------
	global $vfcsversion;
	// $vargs = array('fcs','content-sidebars','free','content-sidebars','','Content Sidebars',$vfcsversion);
	$vargs = array('content-sidebars','yes'); // trimmed settings
	if (function_exists('wqhelper_sidebar_floatbox')) {
		wqhelper_sidebar_floatbox($vargs);
		echo wqhelper_sidebar_floatmenuscript();

		echo '<script language="javascript" type="text/javascript">
		floatingMenu.add("floatdiv", {targetRight: 10, targetTop: 20, centerX: false, centerY: false});
		function move_upper_right() {
			floatingArray[0].targetTop=20;
			floatingArray[0].targetBottom=undefined;
			floatingArray[0].targetLeft=undefined;
			floatingArray[0].targetRight=10;
			floatingArray[0].centerX=undefined;
			floatingArray[0].centerY=undefined;
		}
		move_upper_right();
		</script></div>';
	}

	echo "</div>";
}

// -------------------------
// Register Content Sidebars
// -------------------------

// 1.3.5: added register sidebar abstract helper
function fcs_register_sidebar($vsettings) {
	register_sidebar(array(
		'name' => $vsettings['name'],
		'id' => sanitize_title($vsettings['id']),
		'description' => $vsettings['description'],
		'class' => 'content-'.$vsettings['class'],
		'before_widget' => $vsettings['before_widget'],
		'after_widget' => $vsettings['after_widget'],
		'before_title' => $vsettings['before_title'],
		'after_title' => $vsettings['after_title'],
	) );
}

// 1.3.5: use widgets_init action hook instead
// 1.4.0: declare active and inactive with different priorities
// add_action('wp_head','fcs_register_dynamic_sidebars');
// add_action('admin_head','fcs_register_dynamic_sidebars');
add_action('widgets_init','fcs_register_active_sidebars',11);
add_action('widgets_init','fcs_register_inactive_sidebars',13);
function fcs_register_active_sidebars() {fcs_register_dynamic_sidebars(true);}
function fcs_register_inactive_sidebars() {fcs_register_dynamic_sidebars(false);}

// 1.3.5: register all but split active and inactive sidebars
function fcs_register_dynamic_sidebars($vactive=true) {

	$vactivesidebars = array(); $vinactivesidebars = array();

	if (function_exists('register_sidebar')) {

		$vsidebar = array(
			'name' => __('Above Content','csidebars'),
			'id' => 'AboveContent',
			'class' => 'on',
			'description' => __('Above Post Content','csidebars'),
			'before_widget' => '<div class="abovecontentwidget"><li>',
			'after_widget' => '</li></div>',
			'before_title' => '<div class="abovecontenttitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_abovecontent_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('Below Content','csidebars'),
			'id' => 'BelowContent',
			'class' => 'on',
			'description' => __('Below Post Content','csidebars'),
			'before_widget' => '<div class="belowcontentwidget"><li>',
			'after_widget' => '</li></div>',
			'before_title' => '<div class="belowcontenttitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_belowcontent_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('Login Sidebar','csidebars'),
			'id' => 'LoginSidebar',
			'class' => 'on',
			'description' => __('Shows to Logged Out Users','csidebars'),
			'before_widget' => '<div class="loginwidget"><li>',
			'after_widget' => '</li></div>',
			'before_title' => '<div class="loginwidgettitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_loginsidebar_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('Logged In Sidebar','csidebars'),
			'id' => 'LoggedInSidebar',
			'class' => 'on',
			'description' => __('Fallback Sidebar for Logged In Users','csidebars'),
			'before_widget' => '<div class="loggedinwidget"><li>',
			'after_widget' => '</li></div>',
			'before_title' => '<div class="loggedinwidgettitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_membersidebar_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('Shortcode Sidebar','csidebars').' 1',
			'id' => 'ShortcodeSidebar1',
			'class' => 'on',
			'description' => __('Display with','csidebars').' [shortcode-sidebar-1]',
			'before_widget' => '<div class="shortcodewidget"><li>',
			'after_widget' => '</li></div>',
			'before_title' => '<div class="shortcodewidgettitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_shortcode1_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('Shortcode Sidebar','csidebars').' 2',
			'id' => 'ShortcodeSidebar2',
			'class' => 'on',
			'description' => __('Display with','csidebars').' [shortcode-sidebar-2]',
			'before_widget' => '<div class="shortcodewidget"><li>',
			'after_widget' => '</li></div>',
			'before_title' => '<div class="shortcodewidgetitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_shortcode2_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('Shortcode Sidebar','csidebars').' 3',
			'id' => 'ShortcodeSidebar3',
			'class' => 'on',
			'description' => __('Display with','csidebars').' [shortcode-sidebar-3]',
			'before_widget' => '<div class="shortcodewidget"><li>',
			'after_widget' => '</li></div>',
			'before_title' => '<div class="shortcodewidgettitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_shortcode3_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('InPost','csidebars').' 1',
			'id' => 'InPost1',
			'class' => 'on',
			'description' => __('Auto-spaced Contextual Sidebar','csidebars'),
			'before_widget' => '<div class="inpostwidget">',
			'after_widget' => '</div>',
			'before_title' => '<div class="inpostwidgettitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_inpost1_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('InPost','csidebars').' 2',
			'id' => 'InPost2',
			'class' => 'on',
			'description' => __('Auto-spaced Contextual Sidebar','csidebars'),
			'before_widget' => '<div class="inpostwidget">',
			'after_widget' => '</div>',
			'before_title' => '<div class="inpostwidgettitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_inpost2_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vsidebar = array(
			'name' => __('InPost','csidebars').' 3',
			'id' => 'InPost3',
			'class' => 'on',
			'description' => __('Auto-spaced Contextual Sidebar','csidebars'),
			'before_widget' => '<div class="inpostwidget">',
			'after_widget' => '</div>',
			'before_title' => '<div class="inpostwidgettitle">',
			'after_title' => '</div>',
		);
		if (fcs_get_option('fcs_inpost3_disable') == 'yes') {
			$vsidebar['name'] = strtolower($vsidebar['name']);
			$vsidebar['class'] = 'off'; $vinactivesidebars[] = $vsidebar;
		} else {$vactivesidebars[] = $vsidebar;}

		$vallwidgets = wp_get_sidebars_widgets();
		// print_r($vallwidgets);

		// 1.3.5: register active then inactive sidebars
		// 1.4.0: register with different priorities
		if ( ($vactive) && (count($vactivesidebars) > 0) ) {
			foreach ($vactivesidebars as $vsidebar) {
				// 1.4.0: add widget count to sidebar label
				if ( (is_admin()) && (is_active_sidebar($vsidebar['id'])) ) {
					$vwidgetcount = count($vallwidgets[strtolower($vsidebar['id'])]);
					$vsidebar['name'] .= ' ('.$vwidgetcount.')';
				}
				fcs_register_sidebar($vsidebar);
			}
		}
		if ( (!$vactive) && (count($vinactivesidebars) > 0) ) {
			foreach ($vinactivesidebars as $vsidebar) {
				// 1.4.0: add widget count to sidebar label
				if ( (is_admin()) && (is_active_sidebar($vsidebar['id'])) ) {
					$vwidgetcount = count($vallwidgets[strtolower($vsidebar['id'])]);
					$vsidebar['name'] .= ' ('.$vwidgetcount.')';
				}
				fcs_register_sidebar($vsidebar);
			}
		}

	}
}

// Widget Shortcodes
// -----------------
// 1.3.5: added these widget shortcode filter options
add_action('init','fcs_widget_shortcodes');
function fcs_widget_shortcodes() {
	if (fcs_get_option('fcs_widget_text_shortcodes',true)) {
		if (!has_filter('widget_text','do_shortcode')) {add_filter('widget_text','do_shortcode');}
	}
	if (fcs_get_option('fcs_widget_title_shortcodes',true)) {
		if (!has_filter('widget_title','do_shortcode')) {add_filter('widget_title','do_shortcode');}
	}
}

// Register Discreet Text Widget
// -----------------------------
// 1.3.5: added this super-handy widget type
// ref: https://wordpress.org/plugins/hackadelic-discreet-text-widget/
// add_shortcode('test-shortcode', 'fcs_test_shortcode');
// function fcs_test_shortcode() {return '';}

add_action('widgets_init', 'fcs_discreet_text_widget', 11);
function fcs_discreet_text_widget() {
	if (!class_exists('DiscreetTextWidget')) {
		class DiscreetTextWidget extends WP_Widget_Text {
			function __construct() {
				$vwidgetops = array('classname' => 'discreet_text_widget', 'description' => __('Arbitrary text or HTML, only shown if not empty.','csidebars'));
				$vcontrolops = array('width' => 400, 'height' => 350);
				// 1.4.0: fix to deprecated class construction method
				call_user_func(array(get_parent_class(get_parent_class($this)), '__construct'), 'discrete_text', __('Discreet Text','csidebars'), $vwidgetops, $vcontrolops);
				// parent::__construct('discrete_text', __('Discreet Text','csidebars'), $vwidgetops, $vcontrolops);
				// $this->WP_Widget('discrete_text', 'Discreet Text', $vwidgetops, $vcontrolops);
			}
			function widget($vargs,$vinstance) {
				// echo "<!-- DEBUG"; print_r($vargs); print_r($vinstance); echo "-->";
				$vtext = apply_filters('widget_text', $vinstance['text']);
				if (empty($vtext)) {return;}

				echo $vargs['before_widget'];
				$vtitle = apply_filters('widget_title', empty($vinstance['title']) ? '' : $vinstance['title']);
				if (!empty($vtitle)) {echo $vargs['before_title'].$vtitle.$vargs['after_title'];}
				echo '<div class="textwidget">';
				echo $vinstance['filter'] ? wpautop($vtext) : $vtext;
				echo '</div>';
				echo $vargs['after_widget'];
			}
		}
		return register_widget("DiscreetTextWidget");
	}
}

// -------------
// Login Sidebar
// -------------

// 1.3.5: just enqueue and perform checks within action
add_action('init','fcs_login_sidebar_setup');
function fcs_login_sidebar_setup() {
	$vloginsidebarhook = fcs_get_option('fcs_loginsidebar_hook',true);
	$vloginsidebarpriority = fcs_get_option('fcs_loginsidebar_priority',true);
	add_action($vloginsidebarhook,'fcs_login_sidebar',$vloginsidebarpriority);
}

// Login Sidebar
// -------------
function fcs_login_sidebar() {

	global $post, $vfcsoverrides, $vfcsstate;
	if (is_object($post)) {$vpostid = $post->ID;}
	$vdisable = fcs_get_option('fcs_loginsidebar_disable');

	// maybe disable if sidebar not active for this CPT
	if (is_object($post)) {
		$vpostid = $post->ID; $vposttype = get_post_type($vpostid);
		$vcpts = fcs_get_option('fcs_login_sidebar_cpts',true);
		if (strstr($vcpts,',')) {$vlogincpts = explode(',',$vcpts);}
		else {$vlogincpts[0] = $vcpts;}
	 	if (!in_array($vposttype,$vlogincpts)) {$vdisable = 'yes';}
	}
	$vdisable = apply_filters('fcs_loginsidebar_disable',$vdisable);

	// 1.3.0: fix for option typo
	$vfallback = fcs_get_option('fcs_loginsidebar_fallback',true);
	if ( ($vfallback == 'nooutput') && ($vfcsstate == 'loggedin') ) {return;}
	if ($vfallback == 'fallback') {
		if ( ($vdisable != 'yes') && ($vfcsstate == 'loggedin') ) {

			$vdisable = fcs_get_option('fcs_membersidebar_disable');

			// maybe disable if sidebar not active for this CPT
			if (is_object($post)) {
				$vcpts = fcs_get_option('fcs_member_sidebar_cpts',true);
				if (strstr($vcpts,',')) {$vmembercpts = explode(',',$vcpts);}
				else {$vmembercpts[0] = $vcpts;}
			 	if (!in_array($vposttype,$vmembercpts)) {$vdisable = 'yes';}
			}
			$vdisable = apply_filters('fcs_membersidebar_disable',$vdisable);

			// if (get_post_meta($vpostid,'_disablemembersidebar',true) == 'yes') {$vdisable = 'yes';}
			if (isset($vfcsoverrides['member'])) {
				if ($vfcsoverrides['member'] == 'enable') {$vdisable = '';}
				if ($vfcsoverrides['member'] == 'disable') {$vdisable = 'yes';}
			}

			if ($vdisable != 'yes') {
				if (is_active_sidebar('LoggedInSidebar')) {
					$vsidebar = '<div id="loginsidebar" class="contentsidebar loggedinsidebar">';
					// 1.3.0: fix for logged in sidebar name
					$vsidebar .= fcs_get_sidebar('LoggedInSidebar');
					$vsidebar .= '</div>';
				} else {$vsidebar = '';}
				$vsidebar = apply_filters('fcs_login_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_login_sidebar_loggedin',$vsidebar);
				echo $vsidebar;
			}
			return;
		}
	}

	// if (get_post_meta($vpostid,'_disableloginsidebar',true) == 'yes') {$vdisable = 'yes';}
	if (isset($vfcsoverrides['login'])) {
		if ($vfcsoverrides['login'] == 'enable') {$vdisable = '';}
		if ($vfcsoverrides['login'] == 'disable') {$vdisable = 'yes';}
	}

	if ($vdisable != 'yes') {
		if (is_active_sidebar('LoginSidebar')) {
			if ($vfallback == 'hidden') {$vhidden = ' style="display:none;"';} else {$vhidden = '';}
			$vsidebar = '<div id="loginsidebar" class="contentsidebar loggedoutsidebar"'.$vhidden.'>';
			$vsidebar .= fcs_get_sidebar('LoginSidebar');
			$vsidebar .= '</div>';
		} else {$vsidebar = '';}
		$vsidebar = apply_filters('fcs_login_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_login_sidebar_loggedout',$vsidebar);
		echo $vsidebar;
	}
}


// Above/Below Method Actions
// --------------------------
// 1.3.5: just enqueue and check disable within actions
// 1.3.5: added filters to hooks and priorities
add_action('init','fcs_content_sidebars_setup');
function fcs_content_sidebars_setup() {
	$vmethod = fcs_get_option('fcs_abovebelow_method',true);
	if ($vmethod == 'hooks') {
		// add to above content hook
		$vhook = fcs_get_option('fcs_abovecontent_hook',true);
		$vpriority = fcs_get_option('fcs_abovecontent_priority',true);
		add_action($vhook,'fcs_add_abovecontent_sidebar',$vpriority);

		// add to below content hook
		$vhook = fcs_get_option('fcs_belowcontent_hook',true);
		$vpriority = fcs_get_option('fcs_belowcontent_priority',true);
		add_action($vhook,'fcs_add_belowcontent_sidebar',$vpriority);
	}
	elseif ($vmethod == 'filter') {
		add_filter('the_content','fcs_add_content_sidebars',999);
	}
}

// Above Content Sidebar
// ---------------------
function fcs_add_abovecontent_sidebar() {

	global $post, $vfcsoverrides, $vfcsstate;
	$vdisable = fcs_get_option('fcs_abovecontent_disable');

	// maybe disable if sidebar not active for this CPT
	if (is_object($post)) {
		$vpostid = $post->ID; $vposttype = get_post_type($vpostid);
		$vcpts = fcs_get_option('fcs_abovecontent_sidebar_cpts',true);
		if (strstr($vcpts,',')) {$vabovecpts = explode(',',$vcpts);}
		else {$vabovecpts[0] = $vcpts;}
		if (!in_array($vposttype,$vabovecpts)) {$vdisable = 'yes';}
	}
	$vdisable = apply_filters('fcs_abovecontent_disable',$vdisable);

	// check if logged in and fallback
	$vfallback = fcs_get_option('fcs_abovecontent_fallback',true);
	if ( ($vfallback == 'nooutput') && ($vfcsstate == 'loggedin') ) {return;}
	if ($vfallback == 'fallback') {
		if ( ($vdisable != 'yes') && ($vfcsstate == 'loggedin') ) {

			$vdisable = fcs_get_option('fcs_membersidebar_disable');

			// maybe disable if sidebar not active for this CPT
			if (is_object($post)) {
				$vcpts = fcs_get_option('fcs_member_sidebar_cpts',true);
				if (strstr($vcpts,',')) {$vmembercpts = explode(',',$vcpts);}
				else {$vmembercpts[0] = $vcpts;}
			 	if (!in_array($vposttype,$vmembercpts)) {$vdisable = 'yes';}
			}
			$vdisable = apply_filters('fcs_membersidebar_disable',$vdisable);

			// if (get_post_meta($vpostid,'_disablemembersidebar',true) == 'yes') {$vdisable = 'yes';}
			if (isset($vfcsoverrides['member'])) {
				if ($vfcsoverrides['member'] == 'disable') {$vdisable = 'yes';}
				if ($vfcsoverrides['member'] == 'enable') {$vdisable = '';}
			}

			if ($vdisable != 'yes') {
				if (is_active_sidebar('LoggedInSidebar')) {
					$vsidebar = '<div id="abovecontentsidebar" class="contentsidebar loggedinsidebar">';
					// 1.3.0: fix for logged in sidebar name
					$vsidebar .= fcs_get_sidebar('LoggedInSidebar');
					$vsidebar .= '</div>';
				} else {$vsidebar = '';}

				$vsidebar = apply_filters('fcs_member_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_abovecontent_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_abovecontent_sidebar_loggedin',$vsidebar);
				echo $vsidebar;
			}
			return;
		}
	}

	// otherwise, fall forward haha
	// if (get_post_meta($vpostid,'_disableabovecontentsidebar',true) == 'yes') {$vdisable = 'yes';}
	if (isset($vfcsoverrides['abovecontent'])) {
		if ($vfcsoverrides['abovecontent'] == 'disable') {$vdisable = 'yes';}
		if ($vfcsoverrides['abovecontent'] == 'enable') {$vdisable = '';}
	}
	if ($vdisable != 'yes') {
		if (is_active_sidebar('AboveContent')) {
			if ($vfallback == 'hidden') {$vhidden = ' style="display:none;"';} else {$vhidden = '';}
			$vsidebar = '<div id="abovecontentsidebar" class="contentsidebar loggedoutsidebar"'.$vhidden.'>';
			$vsidebar .= fcs_get_sidebar('AboveContent');
			$vsidebar .= '</div>';
		}

		$vsidebar = apply_filters('fcs_abovecontent_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_abovecontent_sidebar_loggedout',$vsidebar);
		echo $vsidebar;
	}
}

// Below Content Sidebar
// ---------------------
function fcs_add_belowcontent_sidebar() {

	global $post, $vfcsoverrides, $vfcsstate;
	$vdisable = fcs_get_option('fcs_belowcontent_disable');

	// maybe disable if sidebar not active for this CPT
	if (is_object($post)) {
		$vpostid  = $post->ID; $vposttype = get_post_type($vpostid);
		$vcpts = fcs_get_option('fcs_belowcontent_sidebar_cpts',true);
		if (strstr($vcpts,',')) {$vbelowcpts = explode(',',$vcpts);}
		else {$vbelowcpts[0] = $vcpts;}
		if (!in_array($vposttype,$vbelowcpts)) {$vdisable = 'yes';}
	}
	$vdisable = apply_filters('fcs_belowcontent_disable',$vdisable);

	// check if logged in and fall back
	$vfallback = fcs_get_option('fcs_belowcontent_fallback',true);
	if ( ($vfallback == 'nooutput') && ($vfcsstate == 'loggedin') ) {return;}
	if ($vfallback == 'fallback') {
		if ( ($vdisable != 'yes') && ($vfcsstate == 'loggedin') ) {

			$vdisable = fcs_get_option('fcs_membersidebar_disable');

			// maybe disable if sidebar not active for this CPT
			if (is_object($post)) {
				$vcpts = fcs_get_option('fcs_member_sidebar_cpts',true);
				if (strstr($vcpts,',')) {$vmembercpts = explode(',',$vcpts);}
				else {$vmembercpts[0] = $vcpts;}
			 	if (!in_array($vposttype,$vmembercpts)) {$vdisable = 'yes';}
			}
			$vdisable = apply_filters('fcs_membersidebar_disable',$vdisable);

			// if (get_post_meta($vpostid,'_disablemembersidebar',true) == 'yes') {$vdisable = 'yes';}
			if (isset($vfcsoverrides['member'])) {
				if ($vfcsoverrides['member'] == 'disable') {$vdisable = 'yes';}
				elseif ($vfcsoverrides['member'] == 'enable') {$vdisable = '';}
			}

			if ($vdisable != 'yes') {
				if (is_active_sidebar('LoggedInSidebar')) {
					if ($vfallback == 'hidden') {$vhidden = ' style="display:none;"';} else {$vhidden = '';}
					$vsidebar = '<div id="belowcontentsidebar" class="contentsidebar loggedinsidebar"'.$vhidden.'>';
					// 1.3.0: fix for logged in sidebar name
					$vsidebar .= fcs_get_sidebar('LoggedInSidebar');
					$vsidebar .= '</div>';
				} else {$vsidebar = '';}

				$vsidebar = apply_filters('fcs_member_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_belowcontent_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_belowcontent_sidebar_loggedin',$vsidebar);
				echo $vsidebar;
		 	}
		 	return;
		 }
	}

	// otherwise, fall sideways :-]
	// if (get_post_meta($vpostid,'_disablebelowcontentsidebar',true) == 'yes') {$vdisable = 'yes';}
	if (isset($vfcsoverrides['belowcontent'])) {
		if ($vfcsoverrides['belowcontent'] == 'disable') {$vdisable = 'yes';}
		if ($vfcsoverrides['belowcontent'] == 'enable') {$vdisable = '';}
	}
	if ($vdisable != 'yes') {
		if (is_active_sidebar('BelowContent')) {
			$vsidebar = '<div id="belowcontentsidebar" class="contentsidebar loggedoutsidebar">';
			$vsidebar .= fcs_get_sidebar('BelowContent');
			$vsidebar .= '</div>';
		} else {$vsidebar = '';}

		$vsidebar = apply_filters('fcs_belowcontent_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_belowcontent_sidebar_loggedout',$vsidebar);
		echo $vsidebar;
	}
}

// -----------------------------------
// Above/Below Content - Filter Method
// -----------------------------------
// 1.3.5: removed code duplication (now just use above functions)
function fcs_add_content_sidebars($vcontent) {
	// above content sidebar
	ob_start(); fcs_add_abovecontent_sidebar();
	$vtopsidebar = ob_get_contents(); ob_end_clean();

	// below content sidebar
	ob_start(); fcs_add_belowcontent_sidebar();
	$vbottomsidebar = ob_get_contents(); ob_end_clean();

	$vcontent = $vtopsidebar.$vcontent.$vbottomsidebar;
	return $vcontent;
}

// ------------------
// Shortcode Sidebars
// ------------------
// 1.3.5: just add and check disable/overrides within shortcodes
add_action('init','fcs_sidebar_shortcodes');
function fcs_sidebar_shortcodes() {
	if (!is_admin()) {
		add_shortcode('shortcode-sidebar-1','fcs_shortcode_sidebar1');
		add_shortcode('shortcode-sidebar-2','fcs_shortcode_sidebar2');
		add_shortcode('shortcode-sidebar-3','fcs_shortcode_sidebar3');
	}
}
// 1.3.5: replaced individual shortcodes with abstract calls
function fcs_shortcode_sidebar1() {return fcs_shortcode_sidebar('1');}
function fcs_shortcode_sidebar2() {return fcs_shortcode_sidebar('2');}
function fcs_shortcode_sidebar3() {return fcs_shortcode_sidebar('3');}

// Shortcode Sidebar Abstract
// --------------------------
// 1.3.5: replace individual functions with abstracted function
function fcs_shortcode_sidebar($vid) {
	global $post, $vfcsoverrides, $vfcsstate;
	$vdisable = fcs_get_option('fcs_shortcode'.$vid.'_disable',true);

	if (is_object($post)) {
		$vpostid = $post->ID;
		if (get_post_meta($vpostid,'_disableshortcodesidebar'.$vid,true) == 'yes') {$vdisable = 'yes';}
		if (isset($vfcsoverrides['shortcodesidebar'.$vid])) {
			if ($vfcsoverrides['shortcodesidebar'.$vid] == 'disable') {$vdisable = 'yes';}
			elseif ($vfcsoverrides['shortcodesidebar'.$vid] == 'enable') {$vdisable = '';}
		}
	}
	if ($vdisable == 'yes') {return '';}

	if (is_active_sidebar('ShortcodeSidebar'.$vid)) {
		$vsidebar = '<div id="shortcodesidebar'.$vid.'" class="shortcodesidebar '.$vfcsstate.'sidebar">';
		$vsidebar .= fcs_get_sidebar('ShortcodeSidebar'.$vid);
		$vsidebar .= '</div>';
	} else {$vsidebar = '';}

	$vsidebar = apply_filters('fcs_shortcode_sidebar'.$vid,$vsidebar);
	$vsidebar = apply_filters('fcs_shortcode_sidebar'.$vid.'_'.$vfcsstate,$vsidebar);
	return $vsidebar;
}

// ---------------
// InPost Sidebars
// ---------------

add_action('init','fcs_inpost_sidebars');
function fcs_inpost_sidebars() {
	if (!is_admin()) {
		// 1.3.5: just add filter and check states within function
		$vinpostpriority = fcs_get_option('fcs_inpost_priority',true);
		add_filter('the_content', 'fcs_do_inpost_sidebars', $vinpostpriority);
	}
}

// Do InPost Sidebars
// ------------------
function fcs_do_inpost_sidebars($vpostcontent) {

	global $post, $vfcsoverrides, $vfcsstate;

	// check for Content Marker (case insensitive)
	$vcontentmarker = fcs_get_option('fcs_inpost_marker',true);
	if (!stristr($vpostcontent,$vcontentmarker)) {return $vpostcontent;}

	// get general disable options
	$vinpostdisable1 = fcs_get_option('fcs_inpost1_disable');
	$vinpostdisable2 = fcs_get_option('fcs_inpost2_disable');
	$vinpostdisable3 = fcs_get_option('fcs_inpost3_disable');

	// check InPost disable options
	if (is_object($post)) {
		$vpostid = $post->ID;
		$vcptoptions = fcs_get_option('fcs_inpost_sidebars_cpts',true);
		if (strstr($vcptoptions,',')) {$vinpostcpts = explode(',',$vcptoptions);}
		else {$vinpostcpts[0] = $vcptoptions;}
		$vinpostcpts = apply_filters('fcs_inpost_sidebars_cpts',$vinpostcpts);

		// 1.3.5: maybe disable for specified post types
		if (is_array($vinpostcpts)) {
			// check current post type against CPT array
			$vposttype = get_post_type($vpostid);
			if (!in_array($vposttype,$vinpostcpts)) {
				$vinpostdisable1 = 'yes'; $vinpostdisable2 = 'yes'; $vinpostdisable3 = 'yes';
			}
		}

		// 1.3.5: allow for disable option filtering
		$vinpostdisable1 = apply_filters('fcs_inpost1_disable',$vinpostdisable1);
		$vinpostdisable2 = apply_filters('fcs_inpost1_disable',$vinpostdisable2);
		$vinpostdisable3 = apply_filters('fcs_inpost1_disable',$vinpostdisable3);

		// 1.3.5: check meta overrides here
		if (isset($vfcsoverrides['inpost1'])) {
			if ($vfcsoverrides['inpost1'] == 'disable') {$vinpostdisable1 = 'yes';}
			if ($vfcsoverrides['inpost1'] == 'enable') {$vinpostdisable1 = '';}
		}
		if (isset($vfcsoverrides['inpost2'])) {
			if ($vfcsoverrides['inpost2'] == 'disable') {$vinpostdisable2 = 'yes';}
			if ($vfcsoverrides['inpost2'] == 'enable') {$vinpostdisable2 = '';}
		}
		if (isset($vfcsoverrides['inpost3'])) {
			if ($vfcsoverrides['inpost3'] == 'disable') {$vinpostdisable3 = 'yes';}
			if ($vfcsoverrides['inpost3'] == 'enable') {$vinpostdisable3 = '';}
		}
	}
	// bug out if all inpost sidebars are disabled
	if ( ($vinpostdisable1 == 'yes') && ($vinpostdisable2 == 'yes') && ($vinpostdisable3 == 'yes') ) {return $vpostcontent;}


	// Convert marker case - 'just in case'...
	if ($vcontentmarker == strtolower($vcontentmarker)) {
		if (strstr($vpostcontent,strtoupper($vcontentmarker))) {
			$vpostcontent = str_replace(strtoupper($vcontentmarker),$vcontentmaker,$vpostcontent);
		}
	}
	if ($vcontentmarker == strtoupper($vcontentmarker)) {
		if (strstr($vpostcontent,strtolower($vcontentmarker))) {
			$vpostcontent = str_replace(strtolower($vcontentmarker),$vcontentmaker,$vpostcontent);
		}
	}

	// get inpost content positions
	$vpositiona = fcs_get_option('fcs_inpost_positiona',true);
	$vpositionb = fcs_get_option('fcs_inpost_positionb',true);
	$vpositionc = fcs_get_option('fcs_inpost_positionc',true);
	if (!is_numeric($vpositiona)) {$vpositiona = -1;}
	if (!is_numeric($vpositionb)) {$vpositionb = -1;}
	if (!is_numeric($vpositionc)) {$vpositionc = -1;}

	// chunk the post content
	$vchunks = explode($vcontentmarker,$vpostcontent);

	// 1.4.0: start count at 1 not 0
	$vcount = 1; $vcontent = '';
	foreach ($vchunks as $vchunk) {
		$vcontent .= $vchunk;
		if ( ($vcount == $vpositiona) && ($vinpostdisable1 != 'yes') ) {
			if (is_active_sidebar('InPost1')) {
				$vsidebar = '<div id="inpostsidebar1" class="inpostsidebar"';
				// 1.4.0: added float style option
				$vfloat = fcs_get_option('fcs_inpost1_float',true);
				if ($vfloat != '') {
					$vsidebar .= ' style="float:'.$vfloat.';';
					if ($vfloat == 'left') {$vsidebar .= 'margin-right:30px;"';}
					elseif ($vfloat == 'right') {$vsidebar .= 'margin-left:30px;"';}
					else {$vsidebar .= '"';}
				}
				$vsidebar .= '>';
				$vsidebar .= fcs_get_sidebar('InPost1');
				$vsidebar .= '</div>';
			} else {$vsidebar = '';}
			$vsidebar = apply_filters('fcs_inpost_sidebar1',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar1_'.$vfcsstate,$vsidebar);
			$vcontent .= $vsidebar;
		}
		elseif ( ($vcount == $vpositionb) && ($vinpostdisable2 != 'yes') ) {
			if (is_active_sidebar('InPost2')) {
				$vsidebar = '<div id="inpostsidebar2" class="inpostsidebar"';
				// 1.4.0: added float style option
				$vfloat = fcs_get_option('fcs_inpost2_float',true);
				if ($vfloat != '') {
					$vsidebar .= ' style="float:'.$vfloat.';';
					if ($vfloat == 'left') {$vsidebar .= 'margin-right:30px;"';}
					elseif ($vfloat == 'right') {$vsidebar .= 'margin-left:30px;"';}
					else {$vsidebar .= '"';}
				}
				$vsidebar .= '>';
				$vsidebar .= fcs_get_sidebar('InPost2');
				$vsidebar .= '</div>';
			} else {$vsidebar = '';}
			$vsidebar = apply_filters('fcs_inpost_sidebar2',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar2_'.$vfcsstate,$vsidebar);
			$vcontent .= $vsidebar;
		}
		elseif ( ($vcount == $vpositionc) && ($vinpostdisable3 != 'yes') ) {
			if (is_active_sidebar('InPost3')) {
				$vsidebar = '<div id="inpostsidebar3" class="inpostsidebar"';
				// 1.4.0: added float style option
				$vfloat = fcs_get_option('fcs_inpost3_float',true);
				if ($vfloat != '') {
					$vsidebar .= ' style="float:'.$vfloat.';';
					if ($vfloat == 'left') {$vsidebar .= 'margin-right:30px;"';}
					elseif ($vfloat == 'right') {$vsidebar .= 'margin-left:30px;"';}
					else {$vsidebar .= '"';}
				}
				$vsidebar .= '>';
				$vsidebar .= fcs_get_sidebar('InPost3');
				$vsidebar .= '</div>';
			} else {$vsidebar = '';}
			$vsidebar = apply_filters('fcs_inpost_sidebar3',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar3_'.$vfcsstate,$vsidebar);
			$vcontent .= $vsidebar;
		}
		$vcount++;
	}
	return $vcontent;
}

// -----------------
// Metabox Overrides
// -----------------

add_action('add_meta_boxes','fcs_add_perpage_metabox');
function fcs_add_perpage_metabox() {

	$vcpts[0] = 'post'; $vcpts[1] = 'page';
	$vargs = array('public'=>true, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vcpts = array_merge($vcpts,$vcptlist);

	// you can use this filter to conditionally adjust
	// the post types for which the metabox is shown
	// 1.3.5: changed this filter name to match purpose
	$vcpts = apply_filters('fcs_metabox_cpts',$vcpts);

	// 1.3.5: fix to variable typo here
	if (count($vcpts) > 0) {
		foreach ($vcpts as $vcpt) {
			add_meta_box('fcs_perpage_metabox', 'Content Sidebars', 'fcs_perpage_metabox', $vcpt, 'normal', 'low');
		}
	}
}

// Content Sidebars Metabox
// ------------------------
function fcs_perpage_metabox() {

	global $post, $vfcsoverrides;
	if (is_object($post)) {
		$vpostid = $post->ID;
		$vposttype = get_post_type($vpostid);
		$vposttypeobject = get_post_type_object($vposttype);
		$vposttypedisplay = $vposttypeobject->labels->singular_name;
	} else {$vposttypedisplay = 'Post';}

	echo "<style>.fcs-small {font-size:8pt;}</style>";

	echo __('Override','csidebars');
	echo " <a href='/admin.php?page=content-sidebars'><b>";
	echo __('current settings','csidebars');
	echo "</b></a> ";
	echo __('for Content Sidebar Output on this','csidebars');
	echo " ".$vposttypedisplay.":<br>";

	// Above/Below, Login/LoggedIn
	echo "<table><tr>";
	echo "<td>".__('Above Content','csidebars')."</td>";
	fcs_output_setting_cell('abovecontent');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('Below Content','csidebars')."</td>";
	fcs_output_setting_cell('belowcontent');
	echo "</tr>";
	echo "<tr><td>".__('Login','csidebars')."</td>";
	fcs_output_setting_cell('login');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td><span class='fcs-small'>".__('Logged In (fallback)','csidebars')."</span></td>";
	fcs_output_setting_cell('member');
	echo "</tr>";

	// Shortcode and InPost Sidebars
	echo "<tr><td>".__('Shortcode','csidebars').' 1'."</td>";
	fcs_output_setting_cell('shortcode1');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('InPost','csidebars')." 1</td>";
	fcs_output_setting_cell('inpost1');
	echo "</tr>";
	echo "<tr><td>".__('Shortcode','csidebars')." 2</td>";
	fcs_output_setting_cell('shortcode2');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('InPost','csidebars')." 2</td>";
	fcs_output_setting_cell('inpost2');
	echo "</tr>";
	echo "<tr><td>".__('Shortcode','csidebars')." 3</td>";
	fcs_output_setting_cell('shortcode3');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('InPost','csidebars')." 3</td>";
	fcs_output_setting_cell('inpost3');
	echo "</tr>";

	echo "</table>";
}

function fcs_output_setting_cell($vid) {
	global $vfcsoverrides, $post;

	// check sidebar state
	$vdisable = fcs_get_option($vid.'_disable');
	if ($vdisable == 'yes') {$vstate = 'off';}
	else {
		$vstate = 'on';
		// check output for this post type
		if ( (is_object($post)) && (!strstr($vid,'shortcode')) ) {
			if (strstr($vid,'inpost')) {$vsidebarcpts = fcs_get_option('fcs_inpost_sidebars_cpts');}
			else {$vsidebarcpts = fcs_get_option($vid.'_sidebar_cpts');}
			if (strstr($vsidebarcpts,',')) {$vcpts = explode(',',$vsidebarcpts);}
			else {$vcpts[0] = $vsidebarcpts;}
			$vposttype = get_post_type($post->ID);
			// echo $vid.'--'.$vposttype; print_r($vcpts); echo "<br>";
			if (!in_array($vposttype,$vcpts)) {$vstate = 'off';}
		}
	}
	if ($vstate == 'on') {$voff = __('Off','csidebars'); $von = "<b>".__('On','csidebars')."</b>";}
	if ($vstate == 'off') {$von = __('On','csidebars'); $voff = "<b>".__('Off','csidebars')."</b>";}

	// filter disable check
	if ( ($vid == 'login') || ($vid == 'member') ) {$vfilter = $vid.'sidebar_disable';} else {$vfilter = $vid.'_disable';}
	$vfiltered = apply_filters($vfilter,$vstate);
	if ($vfiltered != $vstate) {
		if ($vfiltered == 'yes') {$von = __('On','csidebars'); $voff = "<b>".__('Off','csidebars')."</b>*";}
		elseif ($vfiltered == '') {$voff = __('Off','csidebars'); $von = "<b>".__('On','csidebars')."</b>*";}
	}

	echo "<td> <input type='radio' name='fcs_".$vid."' value=''";
	if ($vfcsoverrides[$vid] == '') {echo " checked";}
	echo "> <span class='fcs-small'>".__('Current','csidebars')."</span></td><td width='5'></td>";
	echo "<td> <input type='radio' name='fcs_".$vid."' value='enable'";
	if ($vfcsoverrides[$vid] == 'enable') {echo " checked";}
	echo "> ".$von."</td><td width='5'></td>";
	echo "<td> <input type='radio' name='fcs_".$vid."' value=''";
	if ($vfcsoverrides[$vid] == 'disable') {echo " checked";}
	echo "> ".$voff."</td>";
}

// Update Meta Values on Save
// --------------------------
add_action('publish_post','fcs_perpage_updates');
add_action('save_post','fcs_perpage_updates');

// 1.3.5: efficient save using single postmeta value
function fcs_perpage_updates() {

	// 1.3.5: return if post object is empty
	global $post, $vfcsoverrides;
	if (!is_object($post)) {return;}
	$vpostid = $post->ID;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {return $vpostid;}
	if (!current_user_can('edit_posts')) {return $vpostid;}
	if (!current_user_can('edit_post',$vpostid)) {return $vpostid;}

	$voptionkeys = array(
		'abovecontent','belowcontent','login','member','shortcode1','shortcode2','shortcode3','inpost1','inpost2','inpost3'
	);

	$vfcsoverrides = array();
	foreach ($voptionkeys as $voptionkey) {
		if (isset($_POST['fcs_'.$voptionkey])) {
			$vfcsoverrides[$voptionkey] = $_POST['fcs_'.$voptionkey];
		} else {$vfcsoverrides[$voptionkey] = '';}
	}
	delete_post_meta($vpostid,'content_sidebars');
	add_post_meta($vpostid,'content_sidebars',$vfcsoverrides);

}

?>