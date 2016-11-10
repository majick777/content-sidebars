<?php

/*
Plugin Name: Content Sidebars
Plugin URI: http://wordquest.org/plugins/content-siderbars/
Author: Tony Hayes
Description: Adds Flexible Dynamic Sidebars to your Content Areas without editing your theme.
Version: 1.3.5
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
$wordquestplugins[$vslug]['version'] = $vfcsversion = '1.3.5';
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
global $vfcsoptions;
$vfcsoptions = get_option('content_sidebars');

// * Special * Add menu item to Appearance menu also (as relevant)
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
add_action('init','fcs_queue_styles');
function fcs_queue_styles() {
	$vcssmode = fcs_get_option('fcs_css_mode');
	if ($vcssmode == 'default') {
		$vflexisidebarcss = plugins_url('content-sidebars.css', __FILE__);
		wp_enqueue_style('flexi_content_sidebar_styles',$vflexisidebarcss);
	}
	elseif ($vcssmode == 'dynamic') {
		add_action('wp_ajax_fcs_dynamic_css', 'fcs_dynamic_css');
		add_action('wp_ajax_nopriv_fcs_dynamic_css', 'fcs_dynamic_css');
		wp_enqueue_style('fcs-dynamic', admin_url('admin-ajax.php').'?action=fcs_dynamic_css'); // $deps, $ver, $media
		function fcs_dynamic_css() {require(dirname(__FILE__).'/dynamic-css.php'); exit;}
	}
}

// Get Sidebar Function
// --------------------
function fcs_get_sidebar($vsidebar) {
	ob_start();
	dynamic_sidebar($vsidebar);
	$vsidebarcontents = ob_get_contents();
	ob_end_clean();
	return $vsidebarcontents;
}

// Get Plugin Option
// -----------------
// 1.3.5: use global options array
function fcs_get_option($vkey) {
	global $vfcsoptions;
	$vkey = str_replace('fcs_','',$vkey);
	if (isset($vfcsoptions[$vkey])) {return $vfcsoptions[$vkey];}
	else {return '';}
}

// maybe Transfer Old Settings
// ---------------------------
// 1.3.5: compact old settings into global array
if ( (get_option('fcs_abovebelow_method')) && (!get_option('content_sidebars')) ) {
	$vfcsoptionkeys = array('abovebelow_method',
		'abovecontent_hook','belowcontent_hook','loginsidebar_hook','membersidebar_hook',
		'abovecontent_priority','belowcontent_priority','loginsidebar_priority','membersidebar_priority',
		'abovecontent_fallback','belowcontent_fallback','loginsidebar_fallback',
		'abovecontent_sidebar_cpts','belowcontent_sidebar_cpts','inpost_sidebars_cpts',
		'loginsidebar_disable', 'membersidebar_disable', 'abovecontent_disable', 'belowcontent_disable',
		'shortcode1_disable', 'shortcode2_disable', 'shortcode3_disable',
		'inpost1_disable','inpost2_disable','inpost3_disable',
		'inpost_marker','inpost_positiona','inpost_positionb','inpost_positionc',
		'inpost_priority','css_mode','dynamic_css');

	foreach ($vfcsoptionkeys as $vkey) {$vfcsoptions[$vkey] = get_option('fcs_'.$vkey);}
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
	$vfcsoptions['abovecontent_hook'] = 'skeleton_before_content';
	$vfcsoptions['belowcontent_hook'] = 'skeleton_after_content';
	$vfcsoptions['loginsidebar_hook'] = 'skeleton_before_header';
	$vfcsoptions['membersidebar_hook'] = 'skeleton_before_header';

	// hook priorities
	$vfcsoptions['abovecontent_priority'] = '5';
	$vfcsoptions['belowcontent_priority'] = '5';
	$vfcsoptions['loginsidebar_priority'] = '5';
	$vfcsoptions['membersidebar_priority'] = '5';

	// fallback switches
	$vfcsoptions['abovecontent_fallback'] = '';
	$vfcsoptions['belowcontent_fallback'] = '';
	$vfcsoptions['loginsidebar_fallback'] = '';

	// post types
	$vfcsoptions['abovecontent_sidebar_cpts'] = 'post';
	$vfcsoptions['belowcontent_sidebar_cpts'] = 'post';
	// $vfcsoptions['loginsidebar_cpts'] = '';
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
	$vfcsoptions['inpost_priority'] = '999';

	// widget options
	$vfcsoptions['widget_text_shortcodes'] = 'yes';
	$vfcsoptions['widget_title_shortcodes'] = '';

	// css options
	$vdefaultcss = file_get_contents(dirname(__FILE__).'/content-sidebars.css');
	$vfcsoptions['css_mode'] = 'default';
	$vfcsoptions['dynamic_css'] = $vdefaultcss;

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
function fcs_reset_options() {delete_option('content_sidebars'); fcs_add_options();}
if ( (isset($_GET['contentsidebars'])) && ($_GET['contentsidebars'] == 'reset') ) {fcs_reset_options();}


// Update Options Trigger
// ----------------------
if (isset($_POST['fcs_update_options'])) {
	if ($_POST['fcs_update_options'] == 'yes') {
		add_action('init','fcs_update_options');
	}
}

// Update Options
// --------------
// 1.3.5 update to use global options array
function fcs_update_options() {

	if (current_user_can('manage_options')) {

		global $vfcsoptions;

		// update all option keys except CPT ones
		$vfcsoptionkeys = array('abovebelow_method',
			'abovecontent_hook','belowcontent_hook','loginsidebar_hook','membersidebar_hook',
			'abovecontent_priority','belowcontent_priority','loginsidebar_priority','membersidebar_priority',
			'abovecontent_fallback','belowcontent_fallback','loginsidebar_fallback',
			// 'abovecontent_sidebar_cpts','belowcontent_sidebar_cpts','inpost_sidebars_cpts',
			'membersidebar_disable', 'loginsidebar_disable', 'abovecontent_disable', 'belowcontent_disable',
			'shortcode1_disable', 'shortcode2_disable', 'shortcode3_disable',
			'inpost1_disable','inpost2_disable','inpost3_disable',
			'inpost_positiona','inpost_positionb','inpost_positionc','inpost_marker','inpost_priority',
			'widget_text_shortcodes','widget_title_shortcodes','css_mode','dynamic_css');

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
		// $vi = 0; $vnewcpts = array();
		// foreach ($vcpts as $vcpt) {
		//	$vpostkey = 'fcs_login_posttype_'.$vcpt;
		//	if (isset($_POST[$vpostkey])) {
		// 		if ($_POST[$vpostkey] == 'yes') {$vnewcpts[$vi] = $vcpt; $vi++;}
		// 	}
		// }
		// $vcptoptions = implode(',',$vnewcpts);
		// $vfcsoptions['loginsidebar_cpts'] = $vcptoptions;

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

		update_option('content_sidebars',$vfcsoptions);
	}
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
	echo "<td><h2>Content Sidebars</h2></td>";
	echo "<td width='20'></td>";
	echo "<td><h3>v".$vfcsversion."</h3></td>";
	echo "</td><td width='100'></td>";
	if ( (isset($_REQUEST['update'])) && ($_REQUEST['updated'] == 'yes') ) {
		echo "<td><table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
		echo "<tr><td><div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
		echo "Settings Updated.</font></div></td></tr></table></td>";
	}
	echo "</tr></table><br>";

	// get post types
	$vcpts[0] = 'post'; $vcpts[1] = 'page';
	$vargs = array('public'=>true, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vcpts = array_merge($vcpts,$vcptlist);

	echo "<div class='postbox' style='width:700px; line-height:2em;'><div class='inner' style='padding-left:20px;'>";
	echo "<h3>Extra Sidebars</h3>";
	echo "<form action='admin.php?page=".$vfcsslug."&updated=yes' method='post'>";
	echo "<input type='hidden' name='fcs_update_options' value='yes'>";

	echo "<table><tr><td><b>Positioning Mode</b></td><td></td>";
	echo "<td colspan='2'><input type='radio' name='fcs_abovebelow_method' value='hooks'";
	if (fcs_get_option('fcs_abovebelow_method') == 'hooks') {echo " checked";}
	echo "> Use Template Action Hooks</td>";
	echo "<td colspan='4'><input type='radio' name='fcs_abovebelow_method' value='filter'";
	if (fcs_get_option('fcs_abovebelow_method') == 'filter') {echo " checked";}
	echo "> Use Content Filter</td></tr>";
	echo "<tr><td colspan='10'>Note: Content Filter mode cannot account for the post title which is (usually) above the_content!<br>";
	echo "So if you want a sidebar above the title you will need to use Template Hooks (see readme.txt FAQ)</td></tr>";
	echo "<tr height='20'><td> </td></tr>";

	echo "<tr><td><b>Above Content Sidebar</b></td><td width='10'></td>";
	echo "<td class='small'>Hook: </td>";
	echo "<td><input type='text' class='small' name='fcs_abovecontent_hook' size='20' value='".fcs_get_option('fcs_abovecontent_hook')."'></td>";
	echo "<td class='small'>Priority: </td>";
	echo "<td><input type='text' class='small' name='fcs_abovecontent_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_abovecontent_priority')."'></td>";
	echo "<td class='small'>Fallback: </td>";
	echo "<td><input type='checkbox' name='fcs_abovecontent_fallback' value='yes'";
	if (fcs_get_option('fcs_abovecontent_fallback') == 'yes') {echo " checked";}
	echo "></td><td class='small'>Disable: </td>";
	echo "<td><input type='checkbox' name='fcs_abovecontent_disable' value='yes'";
	if (fcs_get_option('fcs_abovecontent_disable') == 'yes') {echo " checked";}
	echo "></td></tr>";

	// post type selection for above content sidebars
	$vgetcpts = fcs_get_option('fcs_abovecontent_sidebar_cpts');
	if (strstr($vgetcpts,',')) {$vabovecpts = explode(',',$vgetcpts);}
	else {$vabovecpts[0] = $vgetcpts;}

	echo "<tr><td align='center'>Activate for Post Types:</td>";
	echo "<td width='10'></td>";
	echo "<td colspan='5'>";
	if (count($vcpts) > 0) {
		echo "<ul>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_abovecontent_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vabovecpts)) {echo " checked>";} else {echo ">";}
			echo $vcpt."</li>";
		}
		echo "</ul>";
	}
	echo "</td></tr>";

	echo "<tr><td><b>Below Content Sidebar</b></td><td width='10'></td>";
	echo "<td class='small'>Hook: </td>";
	echo "<td><input type='text' class='small' name='fcs_belowcontent_hook' size='20' value='".fcs_get_option('fcs_belowcontent_hook')."'></td>";
	echo "<td class='small'>Priority: </td>";
	echo "<td><input type='text' class='small' name='fcs_belowcontent_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_belowcontent_priority')."'></td>";
	echo "<td class='small'>Fallback: </td>";
	echo "<td><input type='checkbox' name='fcs_belowcontent_fallback' value='yes'";
	if (fcs_get_option('fcs_belowcontent_fallback') == 'yes') {echo " checked";}
	echo "></td><td class='small'>Disable: </td>";
	echo "<td><input type='checkbox' name='fcs_belowcontent_disable' value='yes'";
	if (fcs_get_option('fcs_belowcontent_disable') == 'yes') {echo " checked";}
	echo "></td></tr>";

	// post type selection for below content sidebar
	$vgetcpts = fcs_get_option('fcs_belowcontent_sidebar_cpts');
	if (strstr($vgetcpts,',')) {$vbelowcpts = explode(',',$vgetcpts);}
	else {$vbelowcpts[0] = $vgetcpts;}

	echo "<tr><td align='center'>Activate for Post Types:</td>";
	echo "<td width='10'></td>";
	echo "<td colspan='5'>";
	if (count($vcpts) > 0) {
		echo "<ul>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_belowcontent_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vbelowcpts)) {echo " checked>";} else {echo ">";}
			echo $vcpt."</li>";
		}
		echo "</ul>";
	}
	echo "</td></tr>";

	echo "<tr><td><b>Login Sidebar</b></td><td width='10'></td>";
	echo "<td class='small'>Hook: </td>";
	echo "<td><input type='text' class='small' name='fcs_loginsidebar_hook' size='20' value='".fcs_get_option('fcs_loginsidebar_hook')."'></td>";
	echo "<td class='small'>Priority: </td>";
	echo "<td><input type='text' class='small' name='fcs_loginsidebar_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_loginsidebar_priority')."'></td>";
	echo "<td class='small'>Fallback: </td>";
	echo "<td><input type='checkbox' name='fcs_loginsidebar_fallback' value='yes'";
	if (fcs_get_option('fcs_loginsidebar_fallback') == 'yes') {echo " checked";}
	echo "></td><td class='small'>Disable: </td>";
	echo "<td><input type='checkbox' name='fcs_loginsidebar_disable' value='yes'";
	if (fcs_get_option('fcs_loginsidebar_disable') == 'yes') {echo " checked";}
	echo "></td></tr>";

	echo "<tr><td><b>Logged In Sidebar</b></td><td width='10'></td>";
	echo "<td class='small'>Hook: </td>";
	echo "<td><input type='text' class='small' name='fcs_membersidebar_hook' size='20' value='".fcs_get_option('fcs_membersidebar_hook')."'></td>";
	echo "<td class='small'>Priority: </td>";
	echo "<td><input type='text' class='small' name='fcs_membersidebar_priority' size='2' style='width:35px;' value='".fcs_get_option('fcs_membersidebar_priority')."'></td>";
	// echo "<td>Fallback: </td>";
	// echo "<td><input type='checkbox' name='fcs_membersidebar_fallback' value='yes'";
	// if (fcs_get_option('fcs_membersidebar_fallback') == 'yes') {echo " checked";}
	echo "<td align='center'>(fallback)</td><td></td>";
	echo "<td class='small'>Disable: </td>";
	echo "<td><input type='checkbox' name='fcs_membersidebar_disable' value='yes'";
	if (fcs_get_option('fcs_membersidebar_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table><br>";

	echo "(Ticked Fallbacks show Fallback Sidebar instead for Logged In Users, eg. Members Area Links.)<br><br>";

	// 1.3.5: add options for widget text/title shortcodes
	echo "<h3>Widget Shortcodes</h3>";
	echo "<table><tr><td><b>Process Shortcodes in Widget Text</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_widget_text_shortcodes' value='yes'";
	if (fcs_get_option('fcs_widget_text_shortcodes') == 'yes') {echo " checked";}
	echo "></td><td width='30'></td>";
	echo "<td><b>Process Shortcodes in Widget Titles</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_widget_title_shortcodes' value='yes'";
	if (fcs_get_option('fcs_widget_title_shortcodes') == 'yes') {echo " checked";}
	echo "></td></tr></table><br>";

	echo "<h3>Shortcode Sidebars</h3>";
	echo "<table><tr><td><b>Sidebar 1</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode1_disable' value='yes'";
	if (fcs_get_option('fcs_shortcode1_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>Disable</td><td width='40'></td>";

	echo "<td><b>Sidebar 2</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode2_disable' value='yes'";
	if (fcs_get_option('fcs_shortcode2_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>Disable</td><td width='40'></td>";

	echo "<td><b>Sidebar 3</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode3_disable' value='yes'";
	if (fcs_get_option('fcs_shortcode3_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>Disable</td></tr>";

	echo "<tr><td colspan='4'>[shortcode-sidebar-1]</td><td></td>";
	echo "<td colspan='4'>[shortcode-sidebar-2]</td><td></td>";
	echo "<td colspan='4'>[shortcode-sidebar-3]</td></tr></table><br>";

	echo "<h3>InPost Sidebars</h3>";

	$vcptoptions = fcs_get_option('fcs_inpost_sidebars_cpts');
	if (strstr($vcptoptions,',')) {$vinpostcpts = explode(',',$vcptoptions);}
	else {$vinpostcpts[0] = $vcptoptions;}

	echo "<table>";
	echo "<tr><td>Activate for Post Types:</td>";
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
	echo "<tr><td>Paragraph Split Marker:</td>";
	echo "<td width='10'></td>";
	echo "<td><input type='text' size='15' style='width:110px;' name='fcs_inpost_marker' value='".fcs_get_option('fcs_inpost_marker')."'></td>";
	echo "<td width='40'></td>";
	echo "<td>the_content Filter Priority:</td><td width='10'></td>";
	echo "<td><input type='text' size='3' style='width:40px;' name='fcs_inpost_priority' value='".fcs_get_option('fcs_inpost_priority')."'></td>";
	echo "</tr><tr><td colspan='3' align='center'>(Used to split and count paragraphs.)</td>";
	echo "</tr></table>";

	echo "<table><tr><td style='vertical-align:top;'>";
		echo "<table>";
		echo "<tr><td><b>InPost Sidebar 1</b> After Paragraph:</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positiona' value='".fcs_get_option('fcs_inpost_positiona')."'></td></tr>";
		echo "<tr><td><b>InPost Sidebar 2</b> After Paragraph:</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positionb' value='".fcs_get_option('fcs_inpost_positionb')."'></td></tr>";
		echo "<tr><td><b>InPost Sidebar 3</b> After Paragraph:</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positionc' value='".fcs_get_option('fcs_inpost_positionc')."'></td>";
		echo "</tr></table>";
	echo "</td><td width='20'></td><td style='vertical-align:top;'>";

	echo "<table><tr><td>InPost Sidebar 1</td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost1_disable' value='yes'";
	if (fcs_get_option('fcs_inpost1_disable') == 'yes') {echo " checked";}
	echo "> Disable</td></tr><tr height='10'><td> </td></tr>";
	echo "<tr><td>InPost Sidebar 2</td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost2_disable' value='yes'";
	if (fcs_get_option('fcs_inpost2_disable') == 'yes') {echo " checked";}
	echo "> Disable</td></tr><tr height='10'><td> </td></tr>";
	echo "<tr><td>InPost Sidebar 3</td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost3_disable' value='yes'";
	if (fcs_get_option('fcs_inpost3_disable') == 'yes') {echo " checked";}
	echo "> Disable</td></tr></table>";

	echo "</tr><tr height='20'><td></td></tr>";

	echo "<tr><td><h3>CSS Styles</h3></td></tr>";
	$vdefaultcss = file_get_contents(dirname(__FILE__).'/content-default.css');
	$vcssfile = file_get_contents(dirname(__FILE__).'/content-sidebars.css');
	$vsavedcss = fcs_get_option('fcs_dynamic_css');
	echo "<tr><td colspan='3'><table>";
		echo "<tr><td><b>CSS Mode</b>:</td><td width='10'></td>";
		echo "<td><input type='radio' name='fcs_css_mode' value='default'";
		if (fcs_get_option('fcs_css_mode') == 'default') {echo " checked";}
		echo "> Default (use content-sidebars.css) </td><td width='10'></td>";
		echo "<td><input type='radio' name='fcs_css_mode' value='dynamic'";
		if (fcs_get_option('fcs_css_mode') == 'dynamic') {echo " checked";}
		echo "> Dynamic CSS (below)</td></tr></table><br>";
	echo "</td></tr>";

	echo "<tr><td colspan='3'><b>Dynamic CSS</b>:<br>";
	echo "<textarea rows='7' cols='70' style='width:100%;' id='dynamiccss' name='fcs_dynamic_css'>".$vsavedcss."</textarea>";
	echo "</td></tr>";

	echo "<tr><td colspan='3'><table style='width:100%;'>";
		echo "<tr><td align='left' style='width:33%;'><input type='button' class='button-secondary' style='font-size:9pt;' onclick='loaddefaultcss();' value='Load Default CSS'></td>";
		echo "<td align='center' style='width:33%;'><input type='button' class='button-secondary' style='font-size:9pt;' onclick='loadcssfile();' value='Load CSS File'></td>";
		echo "<td align='right' style='width:33%;'><input type='button' class='button-secondary' style='font-size:9pt;' onclick='loadsavedcss();' value='Reload Saved CSS'></td></tr>";
	echo "</table></td></tr>";

	echo "<tr height='15'><td> </td></tr>";
	echo "<tr><td colspan='3' align='center'>";
	echo "<input type='submit' class='button-primary' id='plugin-settings-save' value='Save Settings'>";
	echo "</td></tr>";

	echo "</table><br></form>";

	// Dummy CSS Textareas
	echo "<textarea id='defaultcss' style='display:none'>".$vdefaultcss."</textarea>";
	echo "<textarea id='cssfile' style='display:none'>".$vcssfile."</textarea>";
	echo "<textarea id='savedcss' style='display:none'>".$vsavedcss."</textarea>";

	echo "<br><h4>CSS ID and Class Reference:</h4><br>
	<table cellpadding='5' cellspacing='5'>
	<tr><td><b>Sidebar ID</b></td><td><b>Sidebar Class</b></td><td><b>Widget Class</b></td><td><b>Widget Title Class</b></td></tr>
	<tr><td>#abovecontentsidebar</td><td>.flexisidebar</td><td>.abovecontentwidget</td><td>.abovecontenttitle</td></tr>
	<tr><td>#belowcontentsidebar</td><td>.flexisidebar</td><td>.belowcontentwidget</td><td>.belowcontenttitle</td></tr>
	<tr><td>#loginsidebar</td><td>.flexisidebar</td><td>.loginwidget</td><td>.loginwidgettitle</td></tr>
	<tr><td>#loggedinsidebar</td><td>.flexisidebar</td><td>.loggedinwidget</td><td>.loggedinwidgettitle</td></tr>
	<tr><td>#shortcodesidebar1</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#shortcodesidebar2</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#shortcodesidebar3</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#inpostsidebar1</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	<tr><td>#inpostsidebar2</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	<tr><td>#inpostsidebar3</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	</table>";

	// echo "Note: For individualized widget shortcodes you can use: <a href='http://wordpress.org/plugins/amr-shortcode-any-widget/' target=_blank>Shortcode Any Widget</a><br><br>";
	echo "</div></div>";

	// Call Sidebar
	// ------------
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

// -----------------------
// Register Flexi Sidebars
// -----------------------

add_action('wp_head','fcs_register_dynamic_sidebars');
add_action('admin_head','fcs_register_dynamic_sidebars');

function fcs_register_dynamic_sidebars() {

	if (function_exists('register_sidebar')) {

		if (fcs_get_option('fcs_abovecontent_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'AboveContent',
				'id'=>'AboveContent',
				'description'=>'Above Post Content',
				'before_widget' => '<div class="abovecontentwidget"><li>',
				'after_widget' => '</li></div>',
				'before_title' => '<div class="abovecontenttitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_belowcontent_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'BelowContent',
				'id'=>'BelowContent',
				'description'=>'Below Post Content',
				'before_widget' => '<div class="belowcontentwidget"><li>',
				'after_widget' => '</li></div>',
				'before_title' => '<div class="belowcontenttitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_loginsidebar_disable') != 'yes') {
			register_sidebar(array(
				'name' => 'LoginSidebar',
				'id'=>'LoginSidebar',
				'description'=>'Shows to Logged Out Users',
				'before_widget' => '<div class="loginwidget"><li>',
				'after_widget' => '</li></div>',
				'before_title' => '<div class="loginwidgettitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_membersidebar_disable') != 'yes') {
			register_sidebar(array(
				'name' => 'LoggedInSidebar',
				'id'=>'LoggedInSidebar',
				'description'=>'Fallback Sidebar for Logged In Users',
				'before_widget' => '<div class="loggedinwidget"><li>',
				'after_widget' => '</li></div>',
				'before_title' => '<div class="loggedinwidgettitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_shortcode1_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'ShortcodeSidebar1',
				'id'=>'ShortcodeSidebar1',
				'description'=>'Display with [shortcode-sidebar-1]',
				'before_widget' => '<div class="shortcodewidget"><li>',
				'after_widget' => '</li></div>',
				'before_title' => '<div class="shortcodewidgettitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_shortcode2_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'ShortcodeSidebar2',
				'id'=>'ShortcodeSidebar2',
				'description'=>'Display with [shortcode-sidebar-2]',
				'before_widget' => '<div class="shortcodewidget"><li>',
				'after_widget' => '</li></div>',
				'before_title' => '<div class="shortcodewidgetitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_shortcode3_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'ShortcodeSidebar3',
				'id'=>'ShortcodeSidebar3',
				'description'=>'Display with [shortcode-sidebar-3]',
				'before_widget' => '<div class="shortcodewidget"><li>',
				'after_widget' => '</li></div>',
				'before_title' => '<div class="shortcodewidgettitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_inpost1_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'InPost1',
				'id'=>'InPost1',
				'description'=>'Auto-spaced Contextual Sidebar',
				'before_widget' => '<div class="inpostwidget">',
				'after_widget' => '</div>',
				'before_title' => '<div class="inpostwidgettitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_inpost2_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'InPost2',
				'id'=>'InPost2',
				'description'=>'Auto-spaced Contextual Sidebar',
				'before_widget' => '<div class="inpostwidget">',
				'after_widget' => '</div>',
				'before_title' => '<div class="inpostwidgettitle">',
				'after_title' => '</div>',
			));
		}

		if (fcs_get_option('fcs_inpost3_disable') != 'yes') {
			register_sidebar(array(
				'name'=>'InPost3',
				'id'=>'InPost3',
				'description'=>'Auto-spaced Contextual Sidebar',
				'before_widget' => '<div class="inpostwidget">',
				'after_widget' => '</div>',
				'before_title' => '<div class="inpostwidgettitle">',
				'after_title' => '</div>',
			));
		}
	}
}

// Widget Shortcodes
// -----------------
// 1.3.5: added these widget shortcode filter options
if (fcs_get_option('fcs_widget_text_shortcodes')) {
	if (!has_filter('widget_text','do_shortcode')) {add_filter('widget_text','do_shortcode');}
}
if (fcs_get_option('fcs_widget_title_shortcodes')) {
	if (!has_filter('widget_title','do_shortcode')) {add_filter('widget_title','do_shortcode');}
}

// Register Discreet Text Widget
// -----------------------------
// ref: https://wordpress.org/plugins/hackadelic-discreet-text-widget/
// add_shortcode('test-shortcode', 'fcs_test_shortcode');
// function fcs_test_shortcode() {return '';}

add_action('widgets_init', 'fcs_discreet_text_widget', 11);
function fcs_discreet_text_widget() {
	if (!class_exists('DiscreetTextWidget')) {
		class DiscreetTextWidget extends WP_Widget_Text {
			function DiscreetTextWidget() {
				$vwidgetops = array('classname' => 'discreet_text_widget', 'description' => 'Arbitrary text or HTML, only shown if not empty.');
				$vcontrolops = array('width' => 400, 'height' => 350);
				$this->WP_Widget('discrete_text', 'Discreet Text', $vwidgetops, $vcontrolops);
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

if (fcs_get_option('fcs_loginsidebar_disable') != 'yes') {
	$vloginsidebarhook = fcs_get_option('fcs_loginsidebar_hook');
	$vloginsidebarpriority = fcs_get_option('fcs_loginsidebar_priority');
	add_action($vloginsidebarhook,'fcs_login_sidebar',$vloginsidebarpriority);
}

function fcs_login_sidebar() {

	global $post; $vpostid = $post->ID;

	// 1.3.0: fix for option typo
	if (fcs_get_option('fcs_loginsidebar_fallback') == 'yes') {
		$current_user = wp_get_current_user();
		if ($current_user->exists()) {
			if (get_post_meta($vpostid,'_disablemembersidebar',true) != 'yes') {
				$vsidebar = '<div id="loginsidebar" class="flexisidebar loggedinsidebar">';
				// 1.3.0: fix for logged in sidebar name
				$vsidebar .= fcs_get_sidebar('LoggedInSidebar');
				$vsidebar .= '</div>';
				$vsidebar = apply_filters('fcs_login_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_login_sidebar_loggedin',$vsidebar);
				echo $vsidebar; return;
			}
		}
	}

	if (get_post_meta($vpostid,'_disableloginsidebar',true) != 'yes') {
		$vsidebar = '<div id="loginsidebar" class="flexisidebar loggedoutsidebar">';
		$vsidebar .= fcs_get_sidebar('LoginSidebar');
		$vsidebar .= '</div>';
		$vsidebar = apply_filters('fcs_login_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_login_sidebar_loggedout',$vsidebar);
		echo $vsidebar;
	}
}


// Above/Below Method Actions
// --------------------------
$vabovebelowmethod = fcs_get_option('fcs_abovebelow_method');
if ($vabovebelowmethod == 'hooks') {
	if (fcs_get_option('fcs_abovecontent_disable') != 'yes') {
		$vaftercontenthook = fcs_get_option('fcs_belowcontent_hook');
		$vaftercontentpriority = fcs_get_option('fcs_belowcontent_priority');
		add_action($vaftercontenthook,'fcs_add_belowcontent_sidebar',$vaftercontentpriority);
	}
	if (fcs_get_option('fcs_belowcontent_disable') != 'yes') {
		$vbeforecontenthook = fcs_get_option('fcs_abovecontent_hook');
		$vbeforecontentpriority = fcs_get_option('fcs_abovecontent_priority');
		add_action($vbeforecontenthook,'fcs_add_abovecontent_sidebar',$vbeforecontentpriority);
	}
}
elseif ($vabovebelowmethod == 'filter') {
	// disable options within the function
	add_filter('the_content','fcs_add_content_sidebars',999);
}

// Above Content Sidebar
// ---------------------
function fcs_add_abovecontent_sidebar() {

	global $post; $vpostid  = $post->ID;

	// return if the post type is single and not selected for sidebar
	if ( is_singular() || is_page() ) {
		$vposttype = get_post_type($vpostid);
		$vcpts = fcs_get_option('fcs_abovecontent_sidebar_cpts');
		if (strstr($vcpts,',')) {$vabovecpts = explode(',',$vcpts);}
		else {$vabovecpts[0] = $vcpts;}
		if (!in_array($vposttype,$vabovecpts)) {return;}
	}

	// check if logged in and fallback
	if (fcs_get_option('fcs_abovecontent_fallback') == 'yes') {
		$current_user = wp_get_current_user();
		if ($current_user->exists()) {
			if (get_post_meta($vpostid,'_disablemembersidebar',true) != 'yes') {
				$vsidebar = '<div id="abovecontentsidebar" class="flexisidebar loggedinsidebar">';
				// 1.3.0: fix for logged in sidebar name
				$vsidebar .= fcs_get_sidebar('LoggedInSidebar');
				$vsidebar .= '</div>';

				$vsidebar = apply_filters('fcs_above_content_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_above_content_sidebar_loggedin',$vsidebar);
				echo $vsidebar; return;
			}
		}
	}

	// otherwise, fall forward haha
	if (get_post_meta($vpostid,'_disableabovecontentsidebar',true) != 'yes') {
		$vsidebar = '<div id="abovecontentsidebar" class="flexisidebar loggedoutsidebar">';
		$vsidebar .= fcs_get_sidebar('AboveContent');
		$vsidebar .= '</div>';

		$vsidebar = apply_filters('fcs_above_content_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_above_content_sidebar_loggedout',$vsidebar);
		echo $vsidebar;
	}
}

// Below Content Sidebar
// ---------------------
function fcs_add_belowcontent_sidebar() {

	global $post; $vpostid  = $post->ID;

	// return if the post type is single and not selected for sidebar
	if ( is_singular() || is_page() ) {
		$vposttype = get_post_type($vpostid);
		$vcpts = fcs_get_option('fcs_belowcontent_sidebar_cpts');
		if (strstr($vcpts,',')) {$vbelowcpts = explode(',',$vcpts);}
		else {$vbelowcpts[0] = $vcpts;}
		if (!in_array($vposttype,$vbelowcpts)) {return;}
	}

	// check if logged in and fall back
	if (fcs_get_option('fcs_belowcontent_fallback') == 'yes') {
		$current_user = wp_get_current_user();
		if ($current_user->exists()) {
			if (get_post_meta($vpostid,'_disablemembersidebar',true) != 'yes') {
				$vsidebar = '<div id="belowcontentsidebar" class="flexisidebar loggedinsidebar">';
				// 1.3.0: fix for logged in sidebar name
				$vsidebar .= fcs_get_sidebar('LoggedInSidebar');
				$vsidebar .= '</div>';

				$vsidebar = apply_filters('fcs_below_content_sidebar',$vsidebar);
				$vsidebar = apply_filters('fcs_below_content_sidebar_loggedin',$vsidebar);
				echo $vsidebar; return;
		 	}
		 }
	}

	// otherwise, fall sideways :-]
	if (get_post_meta($vpostid,'_disablebelowcontentsidebar',true) != 'yes') {
		$vsidebar = '<div id="belowcontentsidebar" class="flexisidebar loggedoutsidebar">';
		$vsidebar .= fcs_get_sidebar('BelowContent');
		$vsidebar .= '</div>';
		$vsidebar = apply_filters('fcs_below_content_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_below_content_sidebar_loggedout',$vsidebar);
		echo $vsidebar;
	}

}

// -----------------------------------
// Above/Below Content - Filter Method
// -----------------------------------

function fcs_add_content_sidebars($vcontent) {

	global $post; $vpostid = $post->ID;

	// if this is a single post, use the sidebar post type selections
	// ie. this just excludes the sidebar if the post type is not selected
	if ( is_singular() || is_page() ) {
		$vposttype = get_post_type($vpostid);

		$vcpts = fcs_get_option('fcs_abovecontent_sidebar_cpts');
		if (strstr($vcpts,',')) {$vabovecpts = explode(',',$vcpts);}
		else {$vabovecpts[0] = $vcpts;}
		$vcpts = fcs_get_option('fcs_belowcontent_sidebar_cpts');
		if (strstr($vcpts,',')) {$vbelowcpts = explode(',',$vcpts);}
		else {$vbelowcpts[0] = $vcpts;}

	 	if (!in_array($vposttype,$vabovecpts)) {$vabovesidebar == 'off';}
		if (!in_array($vposttype,$vbelowcpts)) {$vbelowsidebar == 'off';}
	}
	// otherwise, rely on the sidebar filters, as we may want these
	// sidebars on the archive, category, tag and search pages etc...

	$current_user = wp_get_current_user(); $vtopsidebar = ''; $vbottomsidebar = '';

	if ($vabovesidebar != 'off') {
		if (fcs_get_option('fcs_abovecontent_fallback') == 'yes') {
			if (!$current_user->exists()) {
				if (get_post_meta($vpostid,'_disableabovecontentsidebar',true) != 'yes') {
					$vtopsidebar = '<div id="abovecontentsidebar" class="flexisidebar loggedoutsidebar">';
					$vtopsidebar .= fcs_get_sidebar('AboveContent');
					$vtopsidebar .= '</div>';
					$vtopsidebar = apply_filters('fcs_above_content_sidebar',$vtopsidebar);
					$vtopsidebar = apply_filters('fcs_above_content_sidebar_loggedout',$vtopsidebar);
				}
			}
			else {
				if (get_post_meta($vpostid,'_disablemembersidebar',true) != 'yes') {
					$vtopsidebar = '<div id="abovecontentsidebar" class="flexisidebar loggedinsidebar">';
					// 1.3.0: fix for logged in sidebar name
					$vtopsidebar .= fcs_get_sidebar('LoggedInSidebar');
					$vtopsidebar .= '</div>';
					$vtopsidebar = apply_filters('fcs_above_content_sidebar',$vtopsidebar);
					$vtopsidebar = apply_filters('fcs_above_content_sidebar_loggedin',$vtopsidebar);
				}
			}
		}
		else {
			if (get_post_meta($vpostid,'_disableabovecontentsidebar',true) != 'yes') {
				$vtopsidebar = '<div id="abovecontentsidebar" class="flexisidebar">';
				$vtopsidebar .= fcs_get_sidebar('AboveContent');
				$vtopsidebar .= '</div>';
				$vtopsidebar = apply_filters('fcs_above_content_sidebar',$vtopsidebar);
			}
		}
	}


	if ($vbelowsidebar != 'off') {
		if (fcs_get_option('fcs_belowcontent_fallback') == 'yes') {
			if (!$current_user->exists()) {
				if (get_post_meta($vpostid,'_disablebelowcontentsidebar',true) != 'yes') {
					$vbottomsidebar = '<div id="belowcontentsidebar" class="flexisidebar loggedoutsidebar">';
					$vbottomsidebar .= fcs_get_sidebar('BelowContent');
					$vbottomsidebar .= '</div>';
					$vbottomsidebar = apply_filters('fcs_below_content_sidebar',$vbottomsidebar);
					$vbottomsidebar = apply_filters('fcs_below_content_sidebar_loggedout',$vbottomsidebar);
				}
			}
			else {
				if (get_post_meta($vpostid,'_disablemembersidebar',true) != 'yes') {
					$vbottomsidebar = '<div id="belowcontentsidebar" class="flexisidebar loggedinsidebar">';
					// 1.3.0: fix for logged in sidebar name
					$vbottomsidebar .= fcs_get_sidebar('LoggedInSidebar');
					$vbottomsidebar .= '</div>';
					$vbottomsidebar = apply_filters('fcs_below_content_sidebar',$vbottomsidebar);
					$vbottomsidebar = apply_filters('fcs_below_content_sidebar_loggedin',$vbottomsidebar);
				}
			}
		}
		else {
			if (get_post_meta($vpostid,'_disablebelowcontentsidebar',true) != 'yes') {
				$vbottomsidebar = '<div id="belowcontentsidebar" class="flexisidebar">';
				$vbottomsidebar .= fcs_get_sidebar('BelowContent');
				$vbottomsidebar .= '</div>';
				$vbottomsidebar = apply_filters('fcs_below_content_sidebar',$vbottomsidebar);
			}
		}
	}

	$vcontent = $vtopsidebar.$vcontent.$vbottomsidebar;
	return $vcontent;
}

// ------------------
// Shortcode Sidebars
// ------------------

add_action('init','fcs_sidebar_shortcodes');
function fcs_sidebar_shortcodes() {
	if (!is_admin()) {
		if (fcs_get_option('fcs_shortcode1_disable') != 'yes') {
			add_shortcode('shortcode-sidebar-1','fcs_shortcode_sidebar1');
		}
		if (fcs_get_option('fcs_shortcode2_disable') != 'yes') {
			add_shortcode('shortcode-sidebar-2','fcs_shortcode_sidebar2');
		}
		if (fcs_get_option('fcs_shortcode3_disable') != 'yes') {
			add_shortcode('shortcode-sidebar-3','fcs_shortcode_sidebar3');
		}
	}
}

// Shortcode Sidebar 1
function fcs_shortcode_sidebar1 () {
	global $post; $vpostid = $post->ID; $current_user = wp_get_current_user();
	if ($current_user->exists()) {$vkey = 'loggedin';} else {$vkey = 'loggedout';}
	if (get_post_meta($vpostid,'_disableshortcodesidebar1',true) != 'yes') {
		$vsidebar = '<div id="shortcodesidebar1" class="shortcodesidebar '.$vkey.'sidebar">';
		$vsidebar .= fcs_get_sidebar('ShortcodeSidebar1');
		$vsidebar .= '</div>';
		$vsidebar = apply_filters('fcs_shortcode_sidebar1',$vsidebar);
		$vsidebar = apply_filters('fcs_shortcode_sidebar1_'.$vkey,$vsidebar);
		return $vsidebar;
	}
}

// Shortcode Sidebar 2
function fcs_shortcode_sidebar2 () {
	global $post; $vpostid = $post->ID; $current_user = wp_get_current_user();
	if ($current_user->exists()) {$vkey = 'loggedin';} else {$vkey = 'loggedout';}
	if (get_post_meta($vpostid,'_disableshortcodesidebar2',true) != 'yes') {
		$vsidebar = '<div id="shortcodesidebar2" class="shortcodesidebar">';
		$vsidebar .= fcs_get_sidebar('ShortcodeSidebar2');
		$vsidebar .= '</div>';
		$vsidebar = apply_filters('fcs_shortcode_sidebar2',$vsidebar);
		$vsidebar = apply_filters('fcs_shortcode_sidebar2_'.$vkey,$vsidebar);
		return $vsidebar;
	}
}

// Shortcode Sidebar 3
function fcs_shortcode_sidebar3 () {
	global $post; $vpostid = $post->ID; $current_user = wp_get_current_user();
	if ($current_user->exists()) {$vkey = 'loggedin';} else {$vkey = 'loggedout';}
	if (get_post_meta($vpostid,'_disableshortcodesidebar3',true) != 'yes') {
		$vsidebar = '<div id="shortcodesidebar3" class="shortcodesidebar">';
		$vsidebar .= fcs_get_sidebar('ShortcodeSidebar3');
		$vsidebar .= '</div>';
		$vsidebar = apply_filters('fcs_shortcode_sidebar3',$vsidebar);
		$vsidebar = apply_filters('fcs_shortcode_sidebar3_'.$vkey,$vsidebar);
		return $vsidebar;
	}
}

// ---------------
// InPost Sidebars
// ---------------

add_action('init','fcs_inpost_sidebars');
function fcs_inpost_sidebars() {
	if (!is_admin()) {
		if ( (fcs_get_option('fcs_inpost1_disable') == 'yes')
		&& (fcs_get_option('fcs_inpost2_disable') == 'yes')
		&& (fcs_get_option('fcs_inpost3_disable') == 'yes') ) {return;}
		else {
			$vinpostpriority = fcs_get_option('fcs_inpost_priority');
			add_filter('the_content', 'fcs_do_inpost_sidebars', $vinpostpriority);
		}
	}
}

// Do InPost Sidebars
// ------------------
function fcs_do_inpost_sidebars($vpostcontent) {

	global $post; $vpostid = $post->ID; $current_user = wp_get_current_user();
	if ($current_user->exists()) {$vkey = 'loggedin';} else {$vkey = 'loggedout';}

	// get InPost post types
	$vcptoptions = fcs_get_option('fcs_inpost_sidebars_cpts');
	if (strstr($vcptoptions,',')) {$vinpostcpts = explode(',',$vcptoptions);}
	else {$vinpostcpts[0] = $vcptoptions;}
	$vinpostcpts = apply_filters('fcs_inpost_sidebars',$vinpostcpts);
	if (!is_array($vinpostcpts)) {return $vpostcontent;}

	// check current post type against CPT array
	$vposttype = get_post_type($vpostid);
	if (!in_array($vposttype,$vinpostcpts)) {return $vpostcontent;}

	// Content Marker
	$vcontentmarker = fcs_get_option('fcs_inpost_marker');

	if (!stristr($vpostcontent,$vcontentmarker)) {return $vpostcontent;}
	else {
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

		$vchunks = explode($vcontentmarker,$vpostcontent);

		$vpositiona = fcs_get_option('fcs_inpost_positiona');
		$vpositionb = fcs_get_option('fcs_inpost_positionb');
		$vpositionc = fcs_get_option('fcs_inpost_positionc');

		$vcount = 0; $vcontent = '';
		foreach ($vchunks as $vchunk) {
			$vcontent .= $vchunk;
			if ($vcount == $vpositiona) {
				if (get_post_meta($vpostid,'_disableinpostsidebar1',true) != 'yes') {
					if (get_post_meta($vpostid,'_disableinpostsidebar1') != 'yes') {
						$vsidebar .= '<div id="inpostsidebar1" class="inpostsidebar">';
						$vsidebar .= fcs_get_sidebar('InPost1');
						$vsidebar .= '</div>';
						$vsidebar = apply_filters('fcs_inpost_sidebar1',$vsidebar);
						$vsidebar = apply_filters('fcs_inpost_sidebar1_'.$vkey,$vsidebar);
						$vcontent .= $vsidebar;
					}
				}
				elseif ($vcount == $vpositionb) {
					if (get_post_meta($vpostid,'_disableinpostsidebar2',true) != 'yes') {
						$vsidebar .= '<div id="inpostsidebar2" class="inpostsidebar">';
						$vsidebar .= fcs_get_sidebar('InPost2');
						$vsidebar .= '</div>';
						$vsidebar = apply_filters('fcs_inpost_sidebar2',$vsidebar);
						$vsidebar = apply_filters('fcs_inpost_sidebar2_'.$vkey,$vsidebar);
						$vcontent .= $vsidebar;
					}
				}
				elseif ($vcount == $vpositionc) {
					if (get_post_meta($vpostid,'_disableinpostsidebar3',true) != 'yes') {
						$vsidebar .= '<div id="inpostsidebar3" class="inpostsidebar">';
						$vsidebar .= fcs_get_sidebar('InPost3');
						$vsidebar .= '</div>';
						$vsidebar = apply_filters('fcs_inpost_sidebar3',$vsidebar);
						$vsidebar = apply_filters('fcs_inpost_sidebar3_'.$vkey,$vsidebar);
						$vcontent .= $vsidebar;
					}
				}
				$vcount++;
			}
		}
		return $vcontent;
	}
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
	$vcpts = apply_filters('flexi_content_sidebar_posttypes',$vcpts);
	if (count($vcpst) > 0) {
		foreach ($vcpts as $vcpt) {
			add_meta_box('fcs_perpage_metabox', 'Flexi Content Sidebars', 'fcs_perpage_metabox', $vcpt, 'normal', 'low');
		}
	}
}

// Flexi Content Sidebars Metabox
// ------------------------------
function fcs_perpage_metabox() {
	global $post; $vpostid = $post->ID;
	echo "Check to Disable Flexi Sidebars for this Post/Page:<br>";
	echo "<table><tr>";
	echo "<td><input type='checkbox' name='fcs_abovecontent_disable' value='yes'";
	if (get_post_meta($vpostid,'_disableabovecontentsidebar',true) == 'yes') {echo " checked";}
	echo "> AboveContent</td>";
	echo "<td><input type='checkbox' name='fcs_belowcontent_disable' value='yes'";
	if (get_post_meta($vpostid,'_disablebelowcontentsidebar',true) == 'yes') {echo " checked";}
	echo "> BelowContent</td>";
	echo "<td><input type='checkbox' name='fcs_login_disable' value='yes'";
	if (get_post_meta($vpostid,'_disablealoginsidebar',true) == 'yes') {echo " checked";}
	echo "> Login</td>";
	echo "<td><input type='checkbox' name='fcs_member_disable' value='yes'";
	if (get_post_meta($vpostid,'_disablemembersidebar',true) == 'yes') {echo " checked";}
	echo "> LoggedIn Fallback</td><td></td></tr>";
	echo "<tr><td><input type='checkbox' name='fcs_shortcode1_disable' value='yes'";
	if (get_post_meta($vpostid,'_disableshortcodesidebar1',true) == 'yes') {echo " checked";}
	echo "> Shortcode1</td>";
	echo "<td><input type='checkbox' name='fcs_shortcode2_disable' value='yes'";
	if (get_post_meta($vpostid,'_disableshortcodesidebar2',true) == 'yes') {echo " checked";}
	echo "> Shortcode2</td>";
	echo "<td><input type='checkbox' name='fcs_shortcode3_disable' value='yes'";
	if (get_post_meta($vpostid,'_disableshortcodesidebar3',true) == 'yes') {echo " checked";}
	echo "> Shortcode3</td>";
	echo "<td><input type='checkbox' name='fcs_inpost1_disable' value='yes'";
	if (get_post_meta($vpostid,'_disableinpostsidebar1',true) == 'yes') {echo " checked";}
	echo "> InPost1</td>";
	echo "<td><input type='checkbox' name='fcs_inpost2_disable' value='yes'";
	if (get_post_meta($vpostid,'_disableinpostsidebar2',true) == 'yes') {echo " checked";}
	echo "> InPost2</td>";
	echo "<td><input type='checkbox' name='fcs_inpost3_disable' value='yes'";
	if (get_post_meta($vpostid,'_disableinpostsidebar3',true) == 'yes') {echo " checked";}
	echo "> InPost3</td>";
	echo "</tr></table>";
}

// Update Meta Values on Save
// --------------------------
add_action('publish_post','fcs_perpage_updates');
add_action('save_post','fcs_perpage_updates');

function fcs_perpage_updates() {
	global $post; $vpostid = $post->ID;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {return $vpostid;}
	if (!current_user_can('edit_post',$vpostid)) {return $vpostid;}

	if ($_POST['fcs_abovecontent_disable'] == 'yes') {add_post_meta($vpostid,'_disableabovecontentsidebar','yes',true);}
	else {delete_post_meta($vpostid,'_disableabovecontentsidebar');}
	if ($_POST['fcs_belowcontent_disable'] == 'yes') {add_post_meta($vpostid,'_disablebelowcontentsidebar','yes',true);}
	else {delete_post_meta($vpostid,'_disablebelowcontentsidebar');}

	if ($_POST['fcs_login_disable'] == 'yes') {add_post_meta($vpostid,'_disableloginsidebar','yes',true);}
	else {delete_post_meta($vpostid,'_disableloginsidebar');}
	if ($_POST['fcs_member_disable'] == 'yes') {add_post_meta($vpostid,'_disablemembersidebar','yes',true);}
	else {delete_post_meta($vpostid,'_disablemembersidebar');}

	if ($_POST['fcs_shortcode1_disable'] == 'yes') {add_post_meta($vpostid,'_disableshortcodesidebar1','yes',true);}
	else {delete_post_meta($vpostid,'_disableshortcodesidebar1');}
	if ($_POST['fcs_shortcode2_disable'] == 'yes') {add_post_meta($vpostid,'_disableshortcodesidebar2','yes',true);}
	else {delete_post_meta($vpostid,'_disableshortcodesidebar2');}
	if ($_POST['fcs_shortcode3_disable'] == 'yes') {add_post_meta($vpostid,'_disableshortcodesidebar3','yes',true);}
	else {delete_post_meta($vpostid,'_disableshortcodesidebar3');}

	if ($_POST['fcs_inpost1_disable'] == 'yes') {add_post_meta($vpostid,'_disableinpostsidebar1','yes',true);}
	else {delete_post_meta($vpostid,'_disableinpostsidebar1');}
	if ($_POST['fcs_inpost2_disable'] == 'yes') {add_post_meta($vpostid,'_disableinpostsidebar2','yes',true);}
	else {delete_post_meta($vpostid,'_disableinpostsidebar2');}
	if ($_POST['fcs_inpost3_disable'] == 'yes') {add_post_meta($vpostid,'_disableinpostsidebar3','yes',true);}
	else {delete_post_meta($vpostid,'_disableinpostsidebar3');}
}

?>