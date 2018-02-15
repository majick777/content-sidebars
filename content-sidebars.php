<?php

/*
Plugin Name: Content Sidebars
Plugin URI: http://wordquest.org/plugins/content-siderbars/
Author: Tony Hayes
Description: Adds Flexible Dynamic Sidebars to your Content Areas without editing your theme.
Version: 1.6.1
Author URI: http://wordquest.org/
GitHub Plugin URI: majick777/content-sidebars
@fs_premium_only pro-functions.php
*/

/* "Do you like seaside bars? I like seaside bars." */

// TODO: define content sidebar hook definitions for layout manager implementation

/*
// Note, for disambiguation, in the context of this plugin only:
// Logged In User Sidebar = 'Member' Sidebar
// 'Fallback' means it is displayed instead when there is a logged in user,
// and can be activated for the Above Content, Below Content and Login Sidebars.
// 'Login' Sidebar === Login Widget Area (for a Logged Out User)
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
global $wordquestplugins, $csidebarsslug, $vcsidebarsversion;
$vslug = $vcsidebarsslug = 'content-sidebars';
$wordquestplugins[$vslug]['version'] = $vcsidebarsversion = '1.6.1';
$wordquestplugins[$vslug]['title'] = 'Content Sidebars';
$wordquestplugins[$vslug]['namespace'] = 'csidebars';
$wordquestplugins[$vslug]['settings'] = $vpre = 'fcs';
$wordquestplugins[$vslug]['hasplans'] = false;
$wordquestplugins[$vslug]['wporgslug'] = 'content-sidebars';

// ------------------------
// Check for Update Checker
// ------------------------
// note: lack of updatechecker.php file indicates WordPress.Org SVN version
// presence of updatechecker.php indicates site download or GitHub version
$vfile = __FILE__; $vupdatechecker = dirname($vfile).'/updatechecker.php';
if (!file_exists($vupdatechecker)) {$wordquestplugins[$vslug]['wporg'] = true;}
else {include($vupdatechecker); $wordquestplugins[$vslug]['wporg'] = false;}

// -----------------------------------
// Load WordQuest Helper/Pro Functions
// -----------------------------------
$wordquest = dirname(__FILE__).'/wordquest.php';
if ( (is_admin()) && (file_exists($wordquest)) ) {include($wordquest);}
$vprofunctions = dirname(__FILE__).'/pro-functions.php';
if (file_exists($vprofunctions)) {include($vprofunctions); $wordquestplugins[$vslug]['plan'] = 'premium';}
else {$wordquestplugins[$vslug]['plan'] = 'free';}

// -------------
// Load Freemius
// -------------
function csidebars_freemius($vslug) {
    global $wordquestplugins, $csidebars_freemius;
    $vwporg = $wordquestplugins[$vslug]['wporg'];
	if ($wordquestplugins[$vslug]['plan'] == 'premium') {$vpremium = true;} else {$vpremium = false;}
	$vhasplans = $wordquestplugins[$vslug]['hasplans'];

	// redirect for support forum
	if ( (is_admin()) && (isset($_REQUEST['page'])) ) {
		if ($_REQUEST['page'] == $vslug.'-wp-support-forum') {
			if (!function_exists('wp_redirect')) {include(ABSPATH.WPINC.'/pluggable.php');}
			wp_redirect('http://wordquest.org/quest/quest-category/plugin-support/'.$vslug.'/'); exit;
		}
	}

    if (!isset($csidebars_freemius)) {

        // start the Freemius SDK
        if (!class_exists('Freemius')) {
        	$vfreemiuspath = dirname(__FILE__).'/freemius/start.php';
        	if (!file_exists($vfreemiuspath)) {return;}
        	require_once($vfreemiuspath);
        }

		// 1.6.1: added type plugin to settings
		$csidebars_settings = array(
            'id'                => '163',
            'slug'              => $vslug,
            'type'				=> 'plugin',
            'public_key'        => 'pk_386ac55ea05fcdcd4daf27798b46c',
            'is_premium'        => $vpremium,
            'has_addons'        => false,
            'has_paid_plans'    => $vhasplans,
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
        $csidebars_freemius = fs_dynamic_init($csidebars_settings);
    }
    return $csidebars_freemius;
}
// initialize Freemius
$csidebars_freemius = csidebars_freemius($vslug);

// Custom Freemius Connect Message
// -------------------------------
function csidebars_freemius_connect($message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link) {
	return sprintf(
		__fs('hey-x').'<br>'.
		__("If you want to more easily provide feedback for this plugins features and functionality, %s can connect your user, %s at %s, to %s", 'csidebars'),
		$user_first_name, '<b>'.$plugin_title.'</b>', '<b>'.$user_login.'</b>', $site_link, $freemius_link
	);
}
// 1.5.9: add object and method exists checks
if ( (is_object($csidebars_freemius)) && (method_exists($csidebars_freemius,'add_filter')) ) {
	$csidebars_freemius->add_filter('connect_message', 'csidebars_freemius_connect', WP_FS__DEFAULT_PRIORITY, 6);
}

// Add Admin Page
// --------------
add_action('admin_menu','csidebars_settings_menu',1);
function csidebars_settings_menu() {

	// maybe add Wordquest top level menu
	if (empty($GLOBALS['admin_page_hooks']['wordquest'])) {
		$vicon = plugins_url('images/wordquest-icon.png',__FILE__); $vposition = apply_filters('wordquest_menu_position','3');
		add_menu_page('WordQuest Alliance', 'WordQuest', 'manage_options', 'wordquest', 'wqhelper_admin_page', $vicon, $vposition);
	}

	// add plugin submenu
	add_submenu_page('wordquest', 'Content Sidebars', 'Content Sidebars', 'manage_options', 'content-sidebars', 'csidebars_options_page');

	// add icons and styling to the plugin submenu :-)
	add_action('admin_footer','csidebars_admin_javascript');
	function csidebars_admin_javascript() {
		global $vcsidebarsslug; $vslug = $vcsidebarsslug; $vcurrent = '0';
		$vicon = plugins_url('images/icon.png',__FILE__);
		if ( (isset($_REQUEST['page'])) && ($_REQUEST['page'] == $vslug) ) {$vcurrent = '1';}
		echo "<script>jQuery(document).ready(function() {if (typeof wordquestsubmenufix == 'function') {
		wordquestsubmenufix('".$vslug."','".$vicon."','".$vcurrent."');} });</script>";
	}

	// add Plugin Settings Link
	add_filter('plugin_action_links', 'csidebars_register_plugin_links', 10, 2);
	function csidebars_register_plugin_links($vlinks, $vfile) {
		global $vcsidebarsslug;
		$vthisplugin = plugin_basename(__FILE__);
		if ($vfile == $vthisplugin) {
			$vsettingslink = "<a href='".admin_url('admin.php')."?page=".$vcsidebarsslug."'>".__('Settings','csidebars')."</a>";
			array_unshift($vlinks, $vsettingslink);
		}
		return $vlinks;
	}

}

// add Appearance menu too (as relevant)
// -----------------------
add_action('admin_menu','csidebars_theme_options_menu');
function csidebars_theme_options_menu() {
	add_theme_page('Content Sidebars', 'Content Sidebars', 'manage_options', 'flexi-content-sidebars', 'csidebars_theme_options_dummy');
	function csidebars_theme_options_dummy() {} // dummy menu item function
}
// appearance menu item redirect
function csidebars_theme_options_page() {
	global $vcsidebarsslug; wp_redirect(admin_url('admin.php').'?page='.$vcsidebarsslug);
}
// trigger redirect to real admin menu item
if (strstr($_SERVER['REQUEST_URI'],'/themes.php')) {
	if ( (isset($_REQUEST['page'])) && ($_REQUEST['page'] == 'flexi-content-sidebars') ) {
		add_action('init', 'csidebars_theme_options_page');
	}
}

// Load Sidebar Styles
// -------------------
// 1.3.5: changed to wp_enqueue_scripts hook
add_action('wp_enqueue_scripts', 'csidebars_queue_styles');
function csidebars_queue_styles() {

	$vcssmode = csidebars_get_option('css_mode',true);

	if ( ($vcssmode == 'direct') || ($vcssmode == 'dynamic') ) {
	 	// 1.4.5: added direct URL load option as new default
		// 1.5.6: check/write exact ABSPATH for safe wp-load
		// 1.5.7: only write single require line to wp-loader.php
		// 1.5.8: remove direct dynamic PHP to CSS mode
		$vcssmode = 'write';
	}

	if ($vcssmode == 'default') {
		// 1.5.8: added check for default style file just in case
		$vcssfile = dirname(__FILE__).'/content-default.css';
		if (!file_exists($vcssfile)) {$vcssmode = 'adminajax';}
		else {
			$vcssurl = plugins_url('content-default.css', __FILE__);
			// 1.6.1: remove doubled css suffix
			wp_enqueue_style('content-sidebars', $vcssurl);
		}
	}

	if ($vcssmode == 'write') {
		// 1.5.6: added write/check method
		$vcssfile = dirname(__FILE__).'/content-sidebars.css';
		$vcssurl = plugins_url('content-sidebars.css', __FILE__);
		$vcss = csidebars_get_option('dynamic_css',true);
		if (file_get_contents($vcssfile) != $vcss) {
			// rewrite the file as saved the CSS has changed
			// 1.5.7: check the WP Filesystem before writing
			$vcheckmethod = get_filesystem_method(array(),$vdirpath,false);
			if ($vcheckmethod !== 'direct') {$vcssmode = 'adminajax';}
			else {$vfh = fopen($vcssfile,'w'); fwrite($vfh,$vcss); fclose($vfh);}
		}
		if ($vcssmode != 'adminajax') {
			$vversion = csidebars_get_option('last_saved');
			// 1.6.1: remove doubled css suffix
			wp_enqueue_style('content-sidebars', $vcssurl, array(), $vversion);
		}
	}

	// 1.5.7: AJAX mode also used as fallback
	if ($vcssmode == 'adminajax') {
		$vversion = csidebars_get_option('last_saved');
		$vajaxurl = admin_url('admin-ajax.php').'?action=csidebars_dynamic_css';
		// 1.6.1: remove doubled css suffix
		wp_enqueue_style('content-sidebars', $vajaxurl, array(), $vversion);
	}
}

// Check File System Credentials
// -----------------------------
// function csidebars_filesystem_check_creds($vurl, $vmethod, $vcontext, $vextrafields) {
//	global $wp_filesystem;
//	if (empty($wp_filesystem)) {
//		$vfilefunctions = ABSPATH.'/wp-admin/includes/file.php';
//		if (!file_exists($vfilefunctions)) {return false;}
//		else {require_once($vfilefunctions); WP_Filesystem();}
//	}
//	$vcredentials = request_filesystem_credentials($vurl, $vmethod, false, $vcontext, $vextrafields);
//	if ($vcredentials === false) {return false;}
//	if (!WP_Filesystem($vcredentials)) {return false;}
//	return true;
// }

// AJAX Dynamic CSS Output
// -----------------------
add_action('wp_ajax_csidebars_dynamic_css', 'csidebars_dynamic_css');
add_action('wp_ajax_nopriv_csidebars_dynamic_css', 'csidebars_dynamic_css');
function csidebars_dynamic_css() {require(dirname(__FILE__).'/content-sidebars-css.php'); exit;}

// Widget Page Styles
// ------------------
// 1.4.0: style the sidebar on widget page
if (is_admin() && ($pagenow == 'widgets.php')) {
	add_action('admin_head','csidebars_widget_page_styles');
	function csidebars_widget_page_styles() {
		echo "<style>.sidebar-content-on {background-color:#E9F0FF;} .sidebar-content-on h2 {font-size: 12pt;}
		.sidebar-content-off {background-color:#EFF3FF;} .sidebar-content-off h2 {font-weight: normal; font-size: 10pt;}</style>";
	}
}

// Widget Page Message
// -------------------
add_action('widgets_admin_page','csidebars_widget_page_message',11);
function csidebars_widget_page_message() {
	$vmessage = __('Note: Inactive Content Sidebars are listed with lowercase titles. Activate them via Content Sidebars settings.', 'csidebars');
	echo "<div class='message'>".$vmessage."</div>";
}

// CSS Hero Integration
// --------------------
// 1.3.0: added CSS Hero script workaround
// TODO: test in combination with theme declarations?
if ( (isset($_GET['csshero_action'])) && ($_GET['csshero_action'] == 'edit_page') ) {
	// add_action('wp_loaded','csidebars_csshero_script_dir',1);
	function csidebars_csshero_script_dir() {
		add_filter('stylesheet_directory_uri','csidebars_csshero_script_url',11,3);
		function csidebars_csshero_script_url($stylesheet_dir_uri, $stylesheet, $theme_root_uri) {
			$vcsshero = dirname(__FILE__);
			if (file_exists($vcsshero.'/csshero.js')) {
				$vcssherouri = plugins_url('',__FILE__);
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
global $vcsidebars; $vcsidebars = get_option('content_sidebars');
// print_r($vcsidebarsoptions); // debug point

// set Excerpt State Filters
// -------------------------
// 1.4.5: added to better handle excerpt output
global $vcsidebarsexcerpt; $vcsidebarsexcerpt = false;
add_filter('get_the_excerpt','csidebars_doing_excerpt_on',0);
add_filter('get_the_excerpt','csidebars_doing_excerpt_off',999);
function csidebars_doing_excerpt_on($vexcerpt) {global $vcsidebarsexcerpt; $vcsidebarsexcerpt = true; return $vexcerpt;}
function csidebars_doing_excerpt_off($vexcerpt) {global $vcsidebarsexcerpt; $vcsidebarsexcerpt = false; return $vexcerpt;}

// set Login State
// ---------------
// 1.3.5: set login state once for efficiency
global $vcsidebarsstate;
add_action('init','csidebars_set_login_state');
function csidebars_set_login_state() {
	global $vcsidebarsstate; $current_user = wp_get_current_user();
	if ($current_user->exists()) {$vcsidebarsstate = 'loggedin';}
	else {$vcsidebarsstate = 'loggedout';}
}

// Set Pageload Context
// --------------------
// 1.4.5: added this once-off context checker
add_action('wp','csidebars_set_page_context');
function csidebars_set_page_context() {
	global $vcsidebarscontext, $vcsidebarsarchive;
	$vcsidebarscontext = ''; $vcsidebarsarchive = '';
	if (is_front_page()) {$vcsidebarscontext = 'frontpage';}
	elseif (is_home()) {$vcsidebarscontext = 'home';}
	elseif (is_404()) {$vcsidebarscontext = '404';}
	elseif (is_search()) {$vcsidebarscontext = 'search';}
	elseif (is_singular()) {$vcsidebarscontext = 'singular';}
	elseif (is_archive()) {
		$vcsidebarscontext = 'archive';
		if (is_tag()) {$vcsidebarsarchive = 'tag';}
		elseif (is_category()) {$vcsidebarsarchive = 'category';}
		elseif (is_tax()) {$vcsidebarsarchive = 'taxonomy';}
		elseif (is_author()) {$vcsidebarsarchive = 'author';}
		elseif (is_date()) {$vcsidebarsarchive = 'date';}
	}
}

// Get Sidebar Overrides
// ---------------------
add_action('init','csidebars_get_overrides');
function csidebars_get_overrides() {
	global $post, $vcsidebarsoverrides;
	if (is_object($post)) {
		$vpostid = $post->ID;
		$vcsidebarsoverrides = get_post_meta($vpostid,'content_sidebars',true);

		// maybe set new key value, checking for existing disable metakeys
		if (!$vcsidebarsoverrides) {
			$voptionkeys = array(
				'abovecontentsidebar','belowcontentsidebar','loginsidebar','membersidebar',
				'shortcodesidebar1','shortcodesidebar2','shortcodesidebar3',
				'inpostsidebar1','inpostsidebar2','inpostsidebar3'
			);
			foreach ($voptionkeys as $voptionkey) {
				$vnewkey = str_replace('sidebar','',$voptionkey);
				if (get_post_meta($vpostid,'_disable'.$voptionkey,true) == 'yes') {
					$vcsidebarsoverrides[$vnewkey] = 'disable';
				} else {$vcsidebarsoverrides[$vnewkey] = '';}
			}
			add_post_meta($vpostid,'content_sidebars',$vcsidebarsoverrides,true);
		}
	}
}

// Get Sidebar Helper
// ------------------
function csidebars_get_sidebar($vsidebar) {
	ob_start();
	// $index = ( is_int($vsidebar) ) ? "sidebar-$vsidebar" : sanitize_title($vsidebar);
	// echo "***".$index."***"; // debug point
	dynamic_sidebar($vsidebar);
	$vsidebarcontents = ob_get_contents();
	ob_end_clean();
	return $vsidebarcontents;
}

// Check Context Helper
// --------------------
function csidebars_check_context($vdisable,$vsidebar) {
	global $vcsidebarscontext, $vcsidebarsarchive;

	$vdisablein = $vdisable;
	if ($vcsidebarscontext == 'singular') {
		// maybe disable if sidebar not active for this CPT
		global $post; $vpostid = $post->ID; $vposttype = get_post_type($vpostid);
		$vcptoptions = csidebars_get_option($vsidebar.'_sidebar_cpts',true);
		if (strstr($vcptoptions,',')) {$vactivecpts = explode(',',$vcptoptions);}
		else {$vactivecpts[0] = $vcptoptions;}
	 	if (!in_array($vposttype,$vactivecpts)) {$vdisable = 'yes';}
	 	$vdebug = 'Post Type: '.$vposttype.' in '.$vcptoptions;
	} elseif ($vcsidebarscontext == 'archive') {
		// maybe disable if sidebar not active for this archive
		$varchiveoptions = csidebars_get_option($vsidebar.'_sidebar_archives');
		if (strstr($varchiveoptions,',')) {$varchives = explode(',',$varchiveoptions);}
		else {$varchives[0] = $varchiveoptions;}
		if (!in_array('archive',$varchives)) {
			if (!in_array($vcsidebarsarchive,$varchives)) {$vdisable = 'yes';}
		}
		$vdebug = 'Archive: '.$vcsidebarsarchive.' in '.$varchiveoptions;
	} elseif ($vcsidebarscontext != '') {
		// maybe disable if sidebar not active for this context
		$vpageoptions = csidebars_get_option($vsidebar.'_sidebar_pages');
		if (strstr($vpageoptions,',')) {$vcontexts = explode(',',$vpageoptions);}
		else {$vcontexts[0] = $vpageoptions;}
		if (!in_array($vcsidebarscontext,$vcontexts)) {$vdisable = 'yes';}
		$vdebug = 'Page Context: '.$vcsidebarscontext.' in '.$vpageoptions;
	} else {$vdisable = 'yes';}

	// debug point for disable change
	if ($vdisablein != $vdisable) {
		// echo "<!-- ".$vsidebar." sidebar disabled (".$vdebug.") -->";
	}

	return $vdisable;
}

// --------------
// Plugin Options
// --------------

// Get Plugin Option
// -----------------
// 1.3.5: use global options array
// 1.6.1: fix and streamline function
function csidebars_get_option($vkey, $vfilter=false) {
	global $vcsidebars, $vcsidebarsdefaults;
	// $vkey = str_replace('fcs_','',$vkey);
	if (isset($vcsidebars[$vkey])) {
		if ( (strstr($vkey, '_fallback')) && ($vcsidebars[$vkey] == 'yes') ) {$vcsidebars[$vkey] = 'fallback';}
		$vvalue = $vcsidebars[$vkey];
	} else {
		// 1.5.9: fallback to default option
		if (!isset($vcsidebarsdefaults)) {$vcsidebarsdefaults = csidebars_default_options();}
		if (isset($vcsidebardefaults[$vkey])) {$vvalue = $vcsidebarsdefaults[$vkey];}
		else {$vvalue = null;}
	}
	// 1.5.5: apply backwards compatible and new filter
	if ($vfilter) {
		$vvalue = apply_filters('fcs_'.$vkey, $vvalue);
		$vvalue = apply_filters('csidebars_'.$vkey, $vvalue);
	}
	return $vvalue;
}

// maybe Transfer Old Settings
// ---------------------------
// 1.3.5: compact old settings into global array
if ( (get_option('fcs_abovebelow_method')) && (!get_option('content_sidebars')) ) {
	$vfcsoptionkeys = array(
		'abovebelow_method','abovecontent_hook','belowcontent_hook','loginsidebar_hook','membersidebar_hook',
		'abovecontent_priority','belowcontent_priority','loginsidebar_priority','membersidebar_priority',
		'abovecontent_fallback','belowcontent_fallback','loginsidebar_fallback', 'membersidebar_mode',
		'abovecontent_sidebar_cpts','belowcontent_sidebar_cpts','inpost_sidebars_cpts','member_sidebar_cpts',
		'abovecontent_sidebar_pages','belowcontent_sidebar_pages','login_sidebar_pages','member_sidebar_pages',
		'abovecontent_sidebar_archives','belowcontent_sidebar_archives','login_sidebar_archives','member_sidebar_archives',
		'loginsidebar_disable', 'membersidebar_disable', 'abovecontent_disable', 'belowcontent_disable',
		'widget_text_shortcodes','widget_title_shortcodes','excerpt_shortcodes','sidebars_in_excerpts',
		'shortcode1_disable', 'shortcode2_disable', 'shortcode3_disable',
		'inpost1_disable','inpost2_disable','inpost3_disable','inpost_marker','inpost_priority',
		'inpost_positiona','inpost_positionb','inpost_positionc','inpost1_float','inpost2_float','inpost3_float',
		'css_mode','dynamic_css');

	foreach ($vfcsoptionkeys as $vkey) {
		$vcsidebarsoptions[$vkey] = get_option('fcs_'.$vkey);
		// 1.4.0: convert old fallback value
		if ( (strstr($vkey,'_fallback')) && ($vcsidebarsoptions[$vkey] == 'yes') ) {$vcsidebars[$vkey] = 'fallback';}
	}
	$vcsidebars['last_saved'] = time();

	if (add_option('content_sidebars',$vcsidebars)) {
		foreach ($vfcsoptionkeys as $vkey) {delete_option('fcs_'.$vkey);}
	}
}

// Add Plugin Options
// ------------------
register_activation_hook(__FILE__,'csidebars_add_options');

// 1.3.5: use global options array
function csidebars_add_options() {

	global $vcsidebars, $vcsidebarsslug, $wordquestplugins;

	// 1.5.9: use default options function
	$vcsidebars = csidebars_default_options();

	// add global option array
	add_option('content_sidebars',$vcsidebars);

	// sidebar options
	if (file_exists(dirname(__FILE__).'/updatechecker.php')) {$vadsboxoff = '';} else {$vadsboxoff = 'checked';}
	$sidebaroptions = array('adsboxoff'=>$vadsboxoff,'donationboxoff'=>'','reportboxoff'=>'','installdate'=>date('Y-m-d'));
	$vpre = $wordquestplugins[$vcsidebarsslug]['settings'];
	add_option($vpre.'_sidebar_options',$sidebaroptions);
}

// get Default Options
// -------------------
// 1.5.9: separate default options function
function csidebars_default_options() {

	// method
	// 1.5.7: fix to global variable name
	$vcsidebars['abovebelow_method'] = 'hooks';

	// template hooks
	$vcsidebars['abovecontent_hook'] = 'skeleton_before_loop';
	$vcsidebars['belowcontent_hook'] = 'skeleton_after_loop';
	$vcsidebars['loginsidebar_hook'] = 'skeleton_before_header';
	$vcsidebars['membersidebar_hook'] = 'skeleton_after_header';

	// hook priorities
	$vcsidebars['abovecontent_priority'] = '5';
	$vcsidebars['belowcontent_priority'] = '5';
	$vcsidebars['loginsidebar_priority'] = '5';
	$vcsidebars['membersidebar_priority'] = '5';

	// fallback switches
	$vcsidebars['abovecontent_fallback'] = '';
	$vcsidebars['belowcontent_fallback'] = '';
	$vcsidebars['loginsidebar_fallback'] = 'fallback';
	$vcsidebars['membersidebar_mode'] = 'fallback';

	// post types
	$vcsidebars['abovecontent_sidebar_cpts'] = 'page';
	$vcsidebars['belowcontent_sidebar_cpts'] = 'post';
	$vcsidebars['login_sidebar_cpts'] = 'post,page';
	$vcsidebars['member_sidebar_cpts'] = 'post,page';
	$vcsidebars['inpost_sidebars_cpts'] = 'article';

	// 1.4.5: added page contexts
	$vcsidebars['abovecontent_sidebar_pages'] = '';
	$vcsidebars['belowcontent_sidebar_pages'] = '';
	$vcsidebars['login_sidebar_pages'] = '';
	$vcsidebars['member_sidebar_pages'] = '';

	// 1.4.5: added archive contexts
	$vcsidebars['abovecontent_sidebar_archives'] = '';
	$vcsidebars['belowcontent_sidebar_archives'] = '';
	$vcsidebars['login_sidebar_archives'] = '';
	$vcsidebars['member_sidebar_archives'] = '';

	// disablers
	$vcsidebars['loginsidebar_disable'] = '';
	$vcsidebars['membersidebar_disable'] = '';
	$vcsidebars['abovecontent_disable'] = '';
	$vcsidebars['belowcontent_disable'] = '';
	$vcsidebars['shortcode1_disable'] = '';
	$vcsidebars['shortcode2_disable'] = '';
	$vcsidebars['shortcode3_disable'] = '';

	// inpost sidebars
	$vcsidebars['inpost1_disable'] = 'yes';
	$vcsidebars['inpost2_disable'] = 'yes';
	$vcsidebars['inpost3_disable'] = 'yes';
	$vcsidebars['inpost_marker'] = '</p>';
	$vcsidebars['inpost_positiona'] = '4';
	$vcsidebars['inpost_positionb'] = '8';
	$vcsidebars['inpost_positionc'] = '12';
	$vcsidebars['inpost1_float'] = 'right';
	$vcsidebars['inpost2_float'] = 'left';
	$vcsidebars['inpost3_float'] = 'right';
	$vcsidebars['inpost_priority'] = '100';

	// shortcode options
	$vcsidebars['widget_text_shortcodes'] = 'yes';
	$vcsidebars['widget_title_shortcodes'] = '';
	$vcsidebars['excerpt_shortcodes'] = '';
	$vcsidebars['sidebars_in_excerpts'] = '';

	// css options
	$vdefaultcss = file_get_contents(dirname(__FILE__).'/content-sidebars.css');
	$vcsidebars['css_mode'] = 'default';
	$vcsidebars['dynamic_css'] = $vdefaultcss;
	$vcsidebars['last_saved'] = time();

	return $vcsidebars;
}

// Reset Options
// -------------
// 1.3.5: added reset options function
if ( (isset($_GET['contentsidebars'])) && ($_GET['contentsidebars'] == 'reset') ) {add_action('init','csidebars_reset_options',0);}
function csidebars_reset_options() {
	if (current_user_can('manage_options')) {delete_option('content_sidebars'); csidebars_add_options();}
}

// Update Options Trigger
// ----------------------
if ( (isset($_POST['fcs_update_options'])) && ($_POST['fcs_update_options'] == 'yes') ) {add_action('init','csidebars_update_options');}

// Update Options
// --------------
// 1.3.5 update to use global options array
function csidebars_update_options() {

	if (!current_user_can('manage_options')) {return;}

	// 1.5.0: verify nonce field
	check_admin_referer('content_sidebars');

	global $vcsidebars;

	// 1.5.5: added option data types for saving
	// 1.5.8: remove direct dynamic PHP CSS method
	// update all option keys here except the CPT ones
	$voptionkeys = array('abovebelow_method' => 'hooks/filter',
		'abovecontent_hook' => 'alphanumeric', 'belowcontent_hook' => 'alphanumeric',
		'loginsidebar_hook' => 'alphanumeric', 'membersidebar_hook' => 'alphanumeric',
		'abovecontent_priority' => 'numeric', 'belowcontent_priority' => 'numeric',
		'loginsidebar_priority' => 'numeric', 'membersidebar_priority' => 'numeric',
		'abovecontent_fallback' => 'checkbox', 'belowcontent_fallback' => 'checkbox',
		'loginsidebar_fallback' => 'checkbox', 'membersidebar_mode' => 'fallback/standalone/both',
		'membersidebar_disable' => 'checkbox', 'loginsidebar_disable' => 'checkbox',
		'abovecontent_disable' => 'checkbox', 'belowcontent_disable' => 'checkbox',
		'widget_text_shortcodes' => 'checkbox', 'widget_title_shortcodes' => 'checkbox',
		'excerpt_shortcodes' => 'checkbox', 'sidebars_in_excerpts' => 'checkbox',
		'shortcode1_disable' => 'checkbox', 'shortcode2_disable' => 'checkbox', 'shortcode3_disable' => 'checkbox',
		'inpost1_disable' => 'checkbox', 'inpost2_disable' => 'checkbox', 'inpost3_disable' => 'checkbox',
		'inpost_marker' => 'textarea', 'inpost_priority' => 'numeric',
		'inpost_positiona' => 'numeric', 'inpost_positionb' => 'numeric', 'inpost_positionc' => 'numeric',
		'inpost1_float' => '/none/left/right', 'inpost2_float' => '/none/left/right', 'inpost3_float' => '/none/left/right',
		'css_mode' => 'default/adminajax/write', 'dynamic_css' => 'textarea'
	);

	// 1.5.5: validate option values before saving
	foreach ($voptionkeys as $vkey => $vtype) {
		if (isset($_POST['fcs_'.$vkey])) {$vposted = $_POST['fcs_'.$vkey];} else {$vposted = '';}
		if (strstr($vtype,'/')) {
			$vvalid = explode('/',$vtype);
			if (in_array($vposted,$vvalid)) {$vcsidebars[$vkey] = $vposted;}
			else {$vcsidebars[$vkey] = $vvalid[0];}
		} elseif ($vtype == 'checkbox') {
			if ( ($vposted == '') || ($vposted == 'yes') ) {$vcsidebars[$vkey] = $vposted;}
		} elseif ($vtype == 'numeric') {
			$vposted = absint($vposted);
			if (is_numeric($vposted)) {$vcsidebars[$vkey] = $vposted;}
		} elseif ($vtype == 'alphanumeric') {
			// TODO: improve this?
			$vcheckposted = preg_match('/^[a-zA-Z0-9_]+$/',$vposted);
			if ($vcheckposted) {$vcsidebars[$vkey] = $vposted;}
		} elseif ($vtype == 'textarea') {
			$vposted = stripslashes($vposted);
			$vcsidebars[$vkey] = $vposted;
		}
	}

	// get all the post types
	$vcpts[0] = 'post'; $vcpts[1] = 'page';
	$vargs = array('public'=>true, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vcpts = array_merge($vcpts,$vcptlist);

	// 1.4.5: loop all sidebar post types (but not for shortcodes)
	$vsidebars = array('abovecontent','belowcontent','login','member','inpost');
	foreach ($vsidebars as $vsidebar) {
		$vi = 0; $vnewcpts = array();
		foreach ($vcpts as $vcpt) {
			$vpostkey = 'fcs_'.$vsidebar.'_posttype_'.$vcpt;
			if ( (isset($_POST[$vpostkey])) && ($_POST[$vpostkey] == 'yes') ) {$vnewcpts[$vi] = $vcpt; $vi++;}
		}
		$vcptoptions = implode(',',$vnewcpts);
		if ($vsidebar == 'inpost') {$s = 's';} else {$s = '';}
		$vcsidebars[$vsidebar.'_sidebar'.$s.'_cpts'] = $vcptoptions;
	}

	// added page and archive contexts
	$vsidebars = array('abovecontent','belowcontent','login','member');
	$vcontexts = array('frontpage','home','404','search');
	// 1.5.5: add missing taxonomy key
	$varchives = array('archive','tag','category','taxonomy','author','date');
	foreach ($vsidebars as $vsidebar) {
		$vi = 0; $vnewpages = array();
		foreach ($vcontexts as $vcontext) {
			$vpostkey = 'fcs_'.$vsidebar.'_pagetype_'.$vcontext;
			if ( (isset($_POST[$vpostkey])) && ($_POST[$vpostkey] == 'yes') ) {$vnewpages[$vi] = $vcontext; $vi++;}
		}
		$vpageoptions = implode(',',$vnewpages);
		$vcsidebars[$vsidebar.'_sidebar_pages'] = $vpageoptions;

		$vi = 0; $vnewarchives = array();
		foreach ($varchives as $varchive) {
			$vpostkey = 'fcs_'.$vsidebar.'_archive_'.$varchive;
			if ( (isset($_POST[$vpostkey])) && ($_POST[$vpostkey] == 'yes') ) {$vnewarchives[$vi] = $varchive; $vi++;}
		}
		$varchiveoptions = implode(',',$vnewarchives);
		$vcsidebars[$vsidebar.'_sidebar_archives'] = $varchiveoptions;
	}

	$vcsidebars['last_saved'] = time();

	// for debugging save values
	// ob_start(); echo "POSTED: "; print_r($_POST); echo PHP_EOL."OPTIONS: "; print_r($vcsidebars);
	// $posted = ob_get_contents(); ob_end_clean();
	// $fh = fopen(dirname(__FILE__).'/debug-save.txt','w'); fwrite($fh,$posted); fclose($fh);

	update_option('content_sidebars',$vcsidebars);
}

// Options Page
// ------------
function csidebars_options_page() {

	global $vcsidebarsversion, $vcsidebarsslug;

	// global $vcsidebars; echo "<!-- "; print_r($vcsidebars);} echo " -->";

	echo "<script language='javascript' type='text/javascript'>
	function loaddefaultcss() {document.getElementById('dynamiccss').value = document.getElementById('defaultcss').value;}
	function loadcssfile() {document.getElementById('dynamiccss').value = document.getElementById('cssfile').value;}
	function loadsavedcss() {document.getElementById('dynamiccss').value = document.getElementById('savedcss').value;}</script>";

	echo "<style>.small {font-size:9pt;} .wp-admin select.select {height:24px; line-height:22px; margin-top:-5px;</style>";

	echo '<div id="pagewrap" class="wrap" style="width:100%;margin-right:0px !important;">';

	// Call Plugin Sidebar
	// -------------------
	// $vargs = array('fcs','content-sidebars','free','content-sidebars','','Content Sidebars',$vcsidebarsversion);
	$vargs = array('content-sidebars','yes'); // trimmed settings
	if (function_exists('wqhelper_sidebar_floatbox')) {
		wqhelper_sidebar_floatbox($vargs);

		// 1.5.5: replace floatbox with stickykit
		echo wqhelper_sidebar_stickykitscript();
		echo '<style>#floatdiv {float:right;}</style>';
		echo '<script>jQuery("#floatdiv").stick_in_parent();
		wrapwidth = jQuery("#pagewrap").width(); sidebarwidth = jQuery("#floatdiv").width();
		newwidth = wrapwidth - sidebarwidth;
		jQuery("#wrapbox").css("width",newwidth+"px");
		jQuery("#adminnoticebox").css("width",newwidth+"px");
		</script>';

		// echo wqhelper_sidebar_floatmenuscript();
		// echo '<script language="javascript" type="text/javascript">
		// floatingMenu.add("floatdiv", {targetRight: 10, targetTop: 20, centerX: false, centerY: false});
		// function move_upper_right() {
		// 	floatingArray[0].targetTop=20;
		//	floatingArray[0].targetBottom=undefined;
		//	floatingArray[0].targetLeft=undefined;
		//	floatingArray[0].targetRight=10;
		//	floatingArray[0].centerX=undefined;
		//	floatingArray[0].centerY=undefined;
		// }
		// move_upper_right();
		// </script>

		// echo '</div>';
	}

	// Admin Notices Boxer
	// -------------------
	if (function_exists('wqhelper_admin_notice_boxer')) {wqhelper_admin_notice_boxer();} else {echo "<h2> </h2>";}

	echo "<div id='wrapbox' class='postbox' style='width:680px;line-height:2em;'><div class='inner' style='padding-left:20px;'>";

	// Plugin Page Title
	// -----------------
	$viconurl = plugins_url("images/content-sidebars.png",__FILE__);
	echo "<table><tr><td><img src='".$viconurl."'></td>";
	echo "<td width='20'></td><td>";
		echo "<table><tr><td><h2>".__('Content Sidebars','csidebars')."</h2></td>";
		echo "<td width='20'></td>";
		echo "<td><h3>v".$vcsidebarsversion."</h3></td></tr>";
		echo "<tr><td colspan='3' align='center'>".__('by','csidebars');
		echo " <a href='http://wordquest.org/' style='text-decoration:none;' target=_blank><b>WordQuest Alliance</b></a>";
		echo "</td></tr></table>";
	echo "</td><td width='50'></td>";
	// 1.5.7: added welcome message
	if ( (isset($_REQUEST['welcome'])) && ($_REQUEST['welcome'] == 'true') ) {
		echo "<td><table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
		echo "<tr><td><div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
		echo __('Welcome! For usage see','csidebars')." <i>readme.txt</i> FAQ</font></div></td></tr></table></td>";
	}
	if ( (isset($_REQUEST['updated'])) && ($_REQUEST['updated'] == 'yes') ) {
		echo "<td><table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
		echo "<tr><td><div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
		echo __('Settings Updated.','csidebars')."</font></div></td></tr></table></td>";
	}
	echo "</tr></table><br>";

	$vfallbackoptions = array(
		'output' => __('Output','csidebars'), 'hidden' => __('Hide','bioship'),
		'fallback' => __('Fallback','csidebars'), 'nooutput' => __('No Output','bioship')
	);

	// get post types
	$vcpts[0] = 'post'; $vcpts[1] = 'page';
	$vargs = array('public'=>true, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vcpts = array_merge($vcpts,$vcptlist);

	// 1.4.5: add page context options
	$vcontexts = array('frontpage' => __('Front Page','csidebars'), 'home' => __('Blog Page','csidebars'),
		'404' => __('404 Pages','csidebars'), 'search' => __('Search Pages','csidebars') );
	$varchives = array('archive' => __('ALL','csidebars'), 'tag' => __('Tag','csidebars'),
		'category' => __('Category','csidebars'), 'taxonomy' => __('Taxonomy','csidebars'),
		'author' => __('Author','csidebars'), 'date' => __('Date','csidebars') );

	echo "<h3>".__('Extra Sidebars','csidebars')."</h3>";
	echo "<form action='admin.php?page=".$vcsidebarsslug."&updated=yes' method='post'>";
	// 1.5.0: add nonce field
	wp_nonce_field('content_sidebars');
	echo "<input type='hidden' name='fcs_update_options' value='yes'>";

	echo "<table><tr><td><b>".__('Positioning Mode','csidebars')."</b></td><td></td>";
	echo "<td colspan='2'><input type='radio' name='fcs_abovebelow_method' value='hooks'";
	if (csidebars_get_option('abovebelow_method') == 'hooks') {echo " checked";}
	echo "> ".__('Use Template Action Hooks','csidebars')."</td>";
	echo "<td colspan='4'><input type='radio' name='fcs_abovebelow_method' value='filter'";
	if (csidebars_get_option('abovebelow_method') == 'filter') {echo " checked";}
	echo "> ".__('Use Content Filter','csidebars')."</td></tr>";
	echo "<tr><td colspan='10'>".__('Note: Content Filter mode cannot account for the post title which is (usually) above','csidebars')." the_content!<br>";
	echo __('So if you want a sidebar above the title you will need to use Template Hooks','csidebars')." (see readme.txt FAQ)</td></tr>";
	echo "<tr height='20'><td> </td></tr>";

	echo "<tr><td><b>".__('Above Content Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_abovecontent_hook' size='20' value='".csidebars_get_option('abovecontent_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_abovecontent_priority' size='2' style='width:35px;' value='".csidebars_get_option('abovecontent_priority')."'></td>";
	echo "<td class='small'>".__('Logged In','csidebars').": </td>";
	echo "<td><select name='fcs_abovecontent_fallback' class='select'>";
		$vfallback = csidebars_get_option('abovecontent_fallback');
		foreach ($vfallbackoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if ($vfallback == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr>";

	echo "<tr><td align='center' style='vertical-align:top;'>";
	echo "<div style='text-align:right;'>Output Sidebar for:</div>";
	echo "<table style='margin-top:20px;'><tr><td><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_abovecontent_disable' value='yes'";
	if (csidebars_get_option('abovecontent_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	echo "<td align='left' colspan='6' class='small'>";

		// post type selection for above content sidebars
		$vgetcpts = csidebars_get_option('abovecontent_sidebar_cpts');
		if (strstr($vgetcpts,',')) {$vabovecpts = explode(',',$vgetcpts);}
		else {$vabovecpts[0] = $vgetcpts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 10px 0 0;'>";
		echo __('Singular','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_abovecontent_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vabovecpts)) {echo " checked>";} else {echo ">";}
			echo strtoupper(substr($vcpt,0,1)).substr($vcpt,1,strlen($vcpt))."</li>";
		}
		echo "</ul>";

		// archive type selection for above content sidebar
		$vgetarchives = csidebars_get_option('abovecontent_sidebar_archives');
		if (strstr($vgetarchives,',')) {$varchivecontexts = explode(',',$vgetarchives);}
		else {$varchivecontexts[0] = $vgetarchives;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Archives','csidebars').": </li>";
		foreach ($varchives as $varchive => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_abovecontent_archive_".$varchive."' value='yes'";
			if (in_array($varchive,$varchivecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul>";

		// context type selection for above content sidebar
		$vgetcontexts = csidebars_get_option('abovecontent_sidebar_pages');
		if (strstr($vgetcontexts,',')) {$vpagecontexts = explode(',',$vgetcontexts);}
		else {$vpagecontexts[0] = $vgetcontexts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Special','csidebars').": </li>";
		foreach ($vcontexts as $vcontext => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_abovecontent_pagetype_".$vcontext."' value='yes'";
			if (in_array($vcontext,$vpagecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul><br>";

	echo "</td></tr>";

	echo "<tr><td><b>".__('Below Content Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_belowcontent_hook' size='20' value='".csidebars_get_option('belowcontent_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_belowcontent_priority' size='2' style='width:35px;' value='".csidebars_get_option('belowcontent_priority')."'></td>";
	echo "<td class='small'>".__('Logged In','csidebars').": </td>";
	echo "<td><select name='fcs_belowcontent_fallback' class='select'>";
		$vfallback = csidebars_get_option('belowcontent_fallback');
		foreach ($vfallbackoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if ($vfallback == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr>";

	echo "<tr><td align='center' style='vertical-align:top;'>";
	echo "<div style='text-align:right;'>Output Sidebar for:</div>";
	echo "<table style='margin-top:20px;'><tr><td><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_belowcontent_disable' value='yes'";
	if (csidebars_get_option('belowcontent_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	echo "<td align='left' colspan='6' class='small'>";

		// post type selection for below content sidebar
		$vgetcpts = csidebars_get_option('belowcontent_sidebar_cpts');
		if (strstr($vgetcpts,',')) {$vbelowcpts = explode(',',$vgetcpts);}
		else {$vbelowcpts[0] = $vgetcpts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 10px 0 0;'>";
		echo __('Singular','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_belowcontent_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vbelowcpts)) {echo " checked>";} else {echo ">";}
			echo strtoupper(substr($vcpt,0,1)).substr($vcpt,1,strlen($vcpt))."</li>";
		}
		echo "</ul>";

		// archive type selection for below content sidebar
		$vgetarchives = csidebars_get_option('belowcontent_sidebar_archives');
		if (strstr($vgetarchives,',')) {$varchivecontexts = explode(',',$vgetarchives);}
		else {$varchivecontexts[0] = $vgetarchives;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Archives','csidebars').": </li>";
		foreach ($varchives as $varchive => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_belowcontent_archive_".$varchive."' value='yes'";
			if (in_array($varchive,$varchivecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul>";

		// context type selection for below content sidebar
		$vgetcontexts = csidebars_get_option('belowcontent_sidebar_pages');
		if (strstr($vgetcontexts,',')) {$vpagecontexts = explode(',',$vgetcontexts);}
		else {$vpagecontexts[0] = $vgetcontexts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Special','csidebars').": </li>";
		foreach ($vcontexts as $vcontext => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_belowcontent_pagetype_".$vcontext."' value='yes'";
			if (in_array($vcontext,$vpagecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul><br>";

	echo "</td></tr>";

	// 1.4.5: removed unneeded output and hide options from login sidebar
	$vfallbackoptions = array('fallback' => __('Fallback','csidebars'), 'nooutput' => __('No Output','bioship'));

	echo "<tr><td><b>".__('Login Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_loginsidebar_hook' size='20' value='".csidebars_get_option('loginsidebar_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_loginsidebar_priority' size='2' style='width:35px;' value='".csidebars_get_option('loginsidebar_priority')."'></td>";
	echo "<td class='small'>".__('Logged In','csidebars').": </td>";
	echo "<td><select name='fcs_loginsidebar_fallback' class='select'>";
		$vfallback = csidebars_get_option('loginsidebar_fallback');
		foreach ($vfallbackoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if ($vfallback == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr>";

	echo "<tr><td align='center' style='vertical-align:top;'>";
	echo "<div style='text-align:right;'>Output Sidebar for:</div>";
	echo "<table style='margin-top:20px;'><tr><td><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_loginsidebar_disable' value='yes'";
	if (csidebars_get_option('loginsidebar_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	echo "<td align='left' colspan='6' class='small'>";

		// post type selection for login sidebar
		$vgetcpts = csidebars_get_option('login_sidebar_cpts');
		if (strstr($vgetcpts,',')) {$vlogincpts = explode(',',$vgetcpts);}
		else {$vlogincpts[0] = $vgetcpts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 10px 0 0;'>";
		echo __('Singular','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_login_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vlogincpts)) {echo " checked>";} else {echo ">";}
			echo strtoupper(substr($vcpt,0,1)).substr($vcpt,1,strlen($vcpt))."</li>";
		}
		echo "</ul>";

		// archive type selection for login sidebar
		$vgetarchives = csidebars_get_option('login_sidebar_archives');
		if (strstr($vgetarchives,',')) {$varchivecontexts = explode(',',$vgetarchives);}
		else {$varchivecontexts[0] = $vgetarchives;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Archives','csidebars').": </li>";
		foreach ($varchives as $varchive => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_login_archive_".$varchive."' value='yes'";
			if (in_array($varchive,$varchivecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul>";

		// context type selection for login sidebar
		$vgetcontexts = csidebars_get_option('login_sidebar_pages');
		if (strstr($vgetcontexts,',')) {$vpagecontexts = explode(',',$vgetcontexts);}
		else {$vpagecontexts[0] = $vgetcontexts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Special','csidebars').": </li>";
		foreach ($vcontexts as $vcontext => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_login_pagetype_".$vcontext."' value='yes'";
			if (in_array($vcontext,$vpagecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul><br>";

	echo "</td></tr>";

	echo "<tr><td><b>".__('Logged In Sidebar','csidebars')."</b></td><td width='10'></td>";
	echo "<td class='small'>".__('Hook','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_membersidebar_hook' size='20' value='".csidebars_get_option('membersidebar_hook')."'></td>";
	echo "<td class='small'>".__('Priority','csidebars').": </td>";
	echo "<td><input type='text' class='small' name='fcs_membersidebar_priority' size='2' style='width:35px;' value='".csidebars_get_option('membersidebar_priority')."'></td>";
	echo "<td>".__('Mode','csidebars').": </td>";
	// 1.4.5: added member sidebar mode selection
	echo "<td><select name='fcs_membersidebar_mode' class='select'>";
	$vfallback = csidebars_get_option('membersidebar_mode');
	$vfallbackoptions = array(
		'fallback' => __('Fallback','csidebars'), 'standalone' => __('Standalone','csidebars'), 'both' => __('Both','csidebars')
	);
	foreach ($vfallbackoptions as $vkey => $vlabel) {
		echo "<option value='".$vkey."'";
		if ($vfallback == $vkey) {echo " selected='selected'";}
		echo ">".$vlabel."</option>";
	}
	echo "</select></td></tr>";

	echo "<tr><td align='center' style='vertical-align:top;'>";
	echo "<div style='text-align:right;'>Output Sidebar for:</div>";
	echo "<table style='margin-top:20px;'><tr><td><td class='small'>".__('Disable','csidebars').": </td>";
	echo "<td><input type='checkbox' name='fcs_membersidebar_disable' value='yes'";
	if (csidebars_get_option('membersidebar_disable') == 'yes') {echo " checked";}
	echo "></td></tr></table></td><td width='10'></td>";

	echo "<td align='left' colspan='6' class='small'>";

		// post type selection for member sidebar
		$vgetcpts = csidebars_get_option('member_sidebar_cpts');
		if (strstr($vgetcpts,',')) {$vmembercpts = explode(',',$vgetcpts);}
		else {$vmembercpts[0] = $vgetcpts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 10px 0 0;'>";
		echo __('Singular','csidebars').": </li>";
		foreach ($vcpts as $vcpt) {
			echo "<li style='display:inline-block; margin:0 10px;'>";
			echo "<input type='checkbox' name='fcs_member_posttype_".$vcpt."' value='yes'";
			if (in_array($vcpt,$vmembercpts)) {echo " checked>";} else {echo ">";}
			echo strtoupper(substr($vcpt,0,1)).substr($vcpt,1,strlen($vcpt))."</li>";
		}
		echo "</ul>";

		// archive type selection for member sidebar
		$vgetarchives = csidebars_get_option('member_sidebar_archives');
		if (strstr($vgetarchives,',')) {$varchivecontexts = explode(',',$vgetarchives);}
		else {$varchivecontexts[0] = $vgetarchives;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Archives','csidebars').": </li>";
		foreach ($varchives as $varchive => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_member_archive_".$varchive."' value='yes'";
			if (in_array($varchive,$varchivecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul>";

		// context type selection for member sidebar
		$vgetcontexts = csidebars_get_option('member_sidebar_pages');
		if (strstr($vgetcontexts,',')) {$vpagecontexts = explode(',',$vgetcontexts);}
		else {$vpagecontexts[0] = $vgetcontexts;}
		echo "<ul style='margin:0px;'><li style='display:inline-block; margin:0 5px 0 0;'>";
		echo __('Special','csidebars').": </li>";
		foreach ($vcontexts as $vcontext => $vlabel) {
			echo "<li style='display:inline-block; margin:0 5px;'>";
			echo "<input type='checkbox' name='fcs_member_pagetype_".$vcontext."' value='yes'";
			if (in_array($vcontext,$vpagecontexts)) {echo " checked>";} else {echo ">";}
			echo $vlabel."</li>";
		}
		echo "</ul>";

	echo "</td></tr>";

	echo "</table>";
	echo "(".__('Sidebar with Fallbacks show Logged In Sidebar instead for Logged In Users, eg. Members Area Links.','csidebars').")<br><br>";

	// 1.3.5: add options for widget text/title shortcodes
	// 1.4.5: added option for shortcodes in excerpts
	echo "<h3>".__('Shortcode Processing','csidebars')."</h3>";
	echo "<table><tr><td><b>".__('Process Shortcodes in Widget Text','csidebars')."</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_widget_text_shortcodes' value='yes'";
	if (csidebars_get_option('widget_text_shortcodes') == 'yes') {echo " checked";}
	echo "></td><td width='30'></td>";
	echo "<td><b>".__('Process Shortcodes in Excerpts','csidebars')."</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_excerpt_shortcodes' value='yes'";
	if (csidebars_get_option('excerpt_shortcodes') == 'yes') {echo " checked";}
	echo "></td></tr>";
	echo "<tr><td><b>".__('Process Shortcodes in Widget Titles','csidebars')."</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_widget_title_shortcodes' value='yes'";
	if (csidebars_get_option('widget_title_shortcodes') == 'yes') {echo " checked";}
	echo "></td><td width='30'></td>";
	echo "<td><b>".__('Shortcode Sidebars in Excerpts','csidebars')."</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_sidebars_in_excerpts' value='yes'";
	if (csidebars_get_option('sidebars_in_excerpts') == 'yes') {echo " checked";}
	echo "></td></tr></table><br>";

	echo "<h3>".__('Shortcode Sidebars','csidebars')."</h3>";

	echo "<table><tr><td><b>".__('Sidebar','csidebars')." 1</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode1_disable' value='yes'";
	if (csidebars_get_option('shortcode1_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 2</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode2_disable' value='yes'";
	if (csidebars_get_option('shortcode2_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 3</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_shortcode3_disable' value='yes'";
	if (csidebars_get_option('shortcode3_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td></tr>";

	echo "<tr><td colspan='4'>[shortcode-sidebar-1]</td><td></td>";
	echo "<td colspan='4'>[shortcode-sidebar-2]</td><td></td>";
	echo "<td colspan='4'>[shortcode-sidebar-3]</td></tr></table><br>";

	echo "<h3>".__('InPost Sidebars','csidebars')."</h3>";

	echo "<table><tr><td><b>".__('Sidebar','csidebars')." 1</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost1_disable' value='yes'";
	if (csidebars_get_option('inpost1_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 2</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost2_disable' value='yes'";
	if (csidebars_get_option('inpost2_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td><td width='40'></td>";

	echo "<td><b>".__('Sidebar','csidebars')." 3</b></td><td width='10'></td>";
	echo "<td><input type='checkbox' name='fcs_inpost3_disable' value='yes'";
	if (csidebars_get_option('inpost3_disable') == 'yes') {echo " checked";}
	echo "></td><td width='10'>".__('Disable','csidebars')."</td></tr>";
	echo "</table>";

	$vcptoptions = csidebars_get_option('inpost_sidebars_cpts');
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
	echo "<td><input type='text' size='15' style='width:110px;' name='fcs_inpost_marker' value='".csidebars_get_option('inpost_marker')."'></td>";
	echo "<td width='40'></td>";
	echo "<td>the_content ".__('Filter Priority','csidebars').":</td><td width='10'></td>";
	echo "<td><input type='text' size='3' style='width:40px;' name='fcs_inpost_priority' value='".csidebars_get_option('inpost_priority')."'></td>";
	echo "</tr><tr><td colspan='3' align='center'>(".__('Used to count and split paragraphs.','csidebars').")</td>";
	echo "<td></td><td colspan='3' align='center'>(".__('Filter positioning method only.','csidebars').")</td>";
	echo "</tr></table>";

	echo "<table><tr><td style='vertical-align:top;'>";
		echo "<table>";
		echo "<tr height='30'><td>".__('Insert Sidebar','csidebars')." 1 ".__('After Paragraph','csidebars')." #</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positiona' value='".csidebars_get_option('inpost_positiona')."'></td></tr>";
		echo "<tr height='5'><td> </td></tr>";
		echo "<tr height='30'><td>".__('Insert Sidebar','csidebars')." 2 ".__('After Paragraph','csidebars')." #</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positionb' value='".csidebars_get_option('inpost_positionb')."'></td></tr>";
		echo "<tr height='5'><td> </td></tr>";
		echo "<tr height='30'><td>".__('Insert Sidebar','csidebars')." 3 ".__('After Paragraph','csidebars')." #</td><td width='30'></td>";
		echo "<td><input type='text' size='2' style='width:30px;' name='fcs_inpost_positionc' value='".csidebars_get_option('inpost_positionc')."'></td>";
		echo "</tr></table>";
	echo "</td><td width='20'></td><td style='vertical-align:top;'>";

	$vfloatoptions = array('' => __('Do Not Set','csidebars'), 'none' => __('None','csidebars'),
						'left' => __('Left','csidebars'), 'right' => __('Right','csidebars'));

	echo "<table><tr height='30'><td>".__('Float Sidebar','csidebars')." 1: </td><td width='10'></td>";
	echo "<td><select name='fcs_inpost1_float'>";
		foreach ($vfloatoptions as $vkey => $vlabel) {
		 	echo "<option value='".$vkey."'";
			if (csidebars_get_option('inpost1_float') == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr><tr height='5'><td> </td></tr>";
	echo "<tr height='30'><td>".__('Float Sidebar','csidebars')." 2: </td><td width='10'></td>";
	echo "<td><select name='fcs_inpost2_float'>";
		foreach ($vfloatoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if (csidebars_get_option('inpost2_float') == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr><tr height='5'><td> </td></tr>";
	echo "<tr height='30'><td>".__('Float Sidebar','csidebars')." 3: </td><td width='10'></td>";
	echo "<td><select name='fcs_inpost3_float'>";
		foreach ($vfloatoptions as $vkey => $vlabel) {
			echo "<option value='".$vkey."'";
			if (csidebars_get_option('inpost3_float') == $vkey) {echo " selected='selected'";}
			echo ">".$vlabel."</option>";
		}
	echo "</select></td></tr></table>";

	echo "</td></tr><tr height='20'><td></td></tr>";

	echo "<tr><td><h3>".__('CSS Styles','csidebars')."</h3></td></tr>";
	$vdefaultcss = file_get_contents(dirname(__FILE__).'/content-default.css');
	$vcssfile = file_get_contents(dirname(__FILE__).'/content-sidebars.css');
	$vsavedcss = csidebars_get_option('dynamic_css');
	// 1.5.0: added direct URL loading as new default
	// 1.5.6: added file write method (to content-sidebars.css)
	$vcssmode = csidebars_get_option('css_mode');
	// 1.5.8: remove direct dnamic PHP to CSS method
	if ( ($vcssmode == 'dynamic') || ($vcssmode == 'direct') ) {$vcssmode = 'write';}
	echo "<tr><td style='vertical-align:top;'><b>".__('CSS Mode','csidebars')."</b></td></tr>";
	echo "<tr><td colspan='3'><table>";
		echo "<td align='center'><input type='radio' name='fcs_css_mode' value='default'";
		if ($vcssmode == 'default') {echo " checked";}
		echo "> ".__('Default','csidebars')."<br>content-default.css</td><td width='20'></td>";
		echo "<td align='center'><input type='radio' name='fcs_css_mode' value='adminajax'";
		if ($vcssmode == 'adminajax') {echo " checked";}
		echo "> ".__('AJAX','csidebars')." <br>".__('via','csidebars')." admin-ajax.php</td><td width='20'></td>";
		// 1.5.8: remove direct dynamic PHP to CSS method
		// echo "<td align='center'><input type='radio' name='fcs_css_mode' value='direct'";
		// if ($vcssmode == 'direct') {echo " checked";}
		// echo "> ".__('Direct','csidebars')." <br>content-sidebars-css.php<td width='20'></td>";
		echo "<td align='center'><input type='radio' name='fcs_css_mode' value='write'";
		if ($vcssmode == 'write') {echo " checked";}
		echo "> ".__('Write','csidebars')." <br>".__('to','csidebars')." content-sidebars.css</tr></table><br>";
	echo "</td></tr>";

	echo "<tr><td colspan='3'><b>".__('Dynamic CSS','csidebars')."</b>:<br>";
	echo "<textarea rows='7' cols='70' style='width:100%;' id='dynamiccss' name='fcs_dynamic_css'>".esc_textarea($vsavedcss)."</textarea>";
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
	echo "<textarea id='savedcss' style='display:none'>".esc_textarea($vsavedcss)."</textarea>";

	echo "<br><h4>".__('CSS ID and Class Reference','csidebars').":</h4>";
	echo "<table cellpadding='5' cellspacing='5'>
	<tr><td><b>".__('Sidebar ID','csidebars')."</b></td><td><b>".__('Sidebar Class','csidebars')."</b></td>
	<td><b>".__('Widget Class','csidebars')."</b></td><td><b>".__('Widget Title Class','csidebars')."</b></td></tr>
	<tr><td>#abovecontentsidebar</td><td>.contentsidebar</td><td>.abovecontentwidget</td><td>.abovecontenttitle</td></tr>
	<tr><td>#belowcontentsidebar</td><td>.contentsidebar</td><td>.belowcontentwidget</td><td>.belowcontenttitle</td></tr>
	<tr><td>#loginsidebar</td><td>.contentsidebar</td><td>.loginwidget</td><td>.loginwidgettitle</td></tr>
	<tr><td>* .loggedinsidebar</td><td>.contentsidebar</td><td>.loggedinwidget</td><td>.loggedinwidgettitle</td></tr>
	<tr><td>#membersidebar</td><td>.contentsidebar</td><td>.loggedinwidget</td><td>.loggedinwidgettitle</td></tr>
	<tr><td>#shortcodesidebar1</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#shortcodesidebar2</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#shortcodesidebar3</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>
	<tr><td>#inpostsidebar1</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	<tr><td>#inpostsidebar2</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	<tr><td>#inpostsidebar3</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>
	</table>";

	echo "* ".__('if logged out, the','csidebars').' .loggedoutsidebar class '.__('is also added to all sidebars on that page.','csidebars').'<br>';
	echo __('The','csidebars').' .loggedinsidebar '.__('class is added to the Above, Below or Login sidebars on fallback,','csidebars').'<br>';

	// echo "Note: For individualized widget shortcodes you can use: <a href='http://wordpress.org/plugins/amr-shortcode-any-widget/' target=_blank>Shortcode Any Widget</a><br><br>";
	echo "</div></div>";

	echo "</div>";
}

// -------------------------
// Register Content Sidebars
// -------------------------

// 1.3.5: added register sidebar abstract helper
function csidebars_register_sidebar($vsettings) {
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
// add_action('wp_head','csidebars_register_dynamic_sidebars');
// add_action('admin_head','csidebars_register_dynamic_sidebars');
add_action('widgets_init','csidebars_register_active_sidebars',11);
add_action('widgets_init','csidebars_register_inactive_sidebars',13);
function csidebars_register_active_sidebars() {csidebars_register_dynamic_sidebars(true);}
function csidebars_register_inactive_sidebars() {csidebars_register_dynamic_sidebars(false);}

// 1.3.5: register all but split active and inactive sidebars
function csidebars_register_dynamic_sidebars($vactive=true) {

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
		if (csidebars_get_option('abovecontent_disable') == 'yes') {
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
		if (csidebars_get_option('belowcontent_disable') == 'yes') {
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
		if (csidebars_get_option('loginsidebar_disable') == 'yes') {
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
		if (csidebars_get_option('membersidebar_disable') == 'yes') {
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
		if (csidebars_get_option('shortcode1_disable') == 'yes') {
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
		if (csidebars_get_option('shortcode2_disable') == 'yes') {
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
		if (csidebars_get_option('shortcode3_disable') == 'yes') {
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
		if (csidebars_get_option('inpost1_disable') == 'yes') {
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
		if (csidebars_get_option('inpost2_disable') == 'yes') {
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
		if (csidebars_get_option('inpost3_disable') == 'yes') {
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
				csidebars_register_sidebar($vsidebar);
			}
		}
		if ( (!$vactive) && (count($vinactivesidebars) > 0) ) {
			foreach ($vinactivesidebars as $vsidebar) {
				// 1.4.0: add widget count to sidebar label
				if ( (is_admin()) && (is_active_sidebar($vsidebar['id'])) ) {
					$vwidgetcount = count($vallwidgets[strtolower($vsidebar['id'])]);
					$vsidebar['name'] .= ' ('.$vwidgetcount.')';
				}
				csidebars_register_sidebar($vsidebar);
			}
		}

	}
}

// Shortcode Filters
// -----------------
// 1.3.5: added these widget shortcode filter options
add_action('init','csidebars_process_shortcodes');
function csidebars_process_shortcodes() {
	// widget text shortcodes
	if (csidebars_get_option('widget_text_shortcodes',true)) {
		if (!has_filter('widget_text','do_shortcode')) {add_filter('widget_text','do_shortcode');}
	}
	// widget title shortcodes
	if (csidebars_get_option('widget_title_shortcodes',true)) {
		if (!has_filter('widget_title','do_shortcode')) {add_filter('widget_title','do_shortcode');}
	}
	// shortcodes in excerpts
	if (csidebars_get_option('excerpt_shortcodes',false)) {
		// add_filter('wp_trim_excerpt','csidebars_excerpt_with_shortcodes');
		if (has_filter('get_the_excerpt','wp_trim_excerpt')) {
			remove_filter('get_the_excerpt','wp_trim_excerpt');
			add_filter('get_the_excerpt','csidebars_excerpt_with_shortcodes');
		}
		// 1.5.9: fix to old function prefix
		add_shortcode('testexcerptshortcode','csidebars_test_excerpts');
		function csidebars_test_excerpts() {return 'This shortcode will display in excerpts now.';}
	}
}

// Excerpts with Shortcodes
// ------------------------
// 1.4.5: copy of wp_trim_excerpt but with shortcodes kept
// note: formatting is still stripped but shortcode text remains
function csidebars_excerpt_with_shortcodes($text) {
	// for use in shortcodes to provide alternative output
	global $doingexcerpt; $doingexcerpt = true;

	$text = get_the_content('');
	// $text = strip_shortcodes( $text ); // modification
	$text = apply_filters('the_content', $text);
	$text = str_replace(']]>', ']]&gt;', $text);
	$excerpt_length = apply_filters('excerpt_length', 55);
	$excerpt_more = apply_filters('excerpt_more', ' ' . '[&hellip;]');
	$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
	$doingexcerpt = false; return $text;
}

// Register Discreet Text Widget
// -----------------------------
// 1.3.5: added this super-handy widget type
// ref: https://wordpress.org/plugins/hackadelic-discreet-text-widget/
// add_shortcode('test-shortcode', 'fcs_test_shortcode');
// function fcs_test_shortcode() {return '';}

add_action('widgets_init', 'csidebars_discreet_text_widget', 11);
function csidebars_discreet_text_widget() {
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

// Login Sidebar Setup
// -------------------
// 1.3.5: just enqueue and perform checks within action
add_action('init','csidebars_login_sidebar_setup');
function csidebars_login_sidebar_setup() {
	$vloginsidebarhook = csidebars_get_option('loginsidebar_hook',true);
	$vloginsidebarpriority = csidebars_get_option('loginsidebar_priority',true);
	add_action($vloginsidebarhook,'csidebars_login_sidebar_output',$vloginsidebarpriority);
}
function csidebars_login_sidebar_output() {echo csidebars_login_sidebar();}

// Login Sidebar
// -------------
function csidebars_login_sidebar() {

	global $vcsidebarsoverrides, $vcsidebarsstate;
	$vdisable = csidebars_get_option('loginsidebar_disable');

	// 1.4.5: check new page contexts
	$vdisable = csidebars_check_context($vdisable,'login');
	// 1.5.5: make this a separate override filter
	$vdisable = apply_filters('csidebars_loginsidebar_override',$vdisable);

	// 1.3.0: fix for option typo
	$vfallback = csidebars_get_option('loginsidebar_fallback',true);
	if ( ($vfallback == 'nooutput') && ($vcsidebarsstate == 'loggedin') ) {return '';}
	if ($vfallback == 'fallback') {
		if ( ($vdisable != 'yes') && ($vcsidebarsstate == 'loggedin') ) {
			// 1.4.5: check mode and call to member sidebar function
			$vmode = csidebars_get_option('membersidebar_mode','fallback');
			if ($vmode == 'standalone') {return '';}
			$vsidebar = PHP_EOL.'<div id="loginsidebar" class="contentsidebar loggedinsidebar">';
			$vsidebar .= csidebars_member_sidebar();
			$vsidebar .= '</div>'.PHP_EOL.PHP_EOL;
			// 1.5.5: apply backward compatible and new filter prefix
			$vsidebar = apply_filters('fcs_login_sidebar_loggedin',$vsidebar);
			$vsidebar = apply_filters('csidebars_login_sidebar_loggedin',$vsidebar);
			return $vsidebar;
		}
	}

	// if (get_post_meta($vpostid,'_disableloginsidebar',true) == 'yes') {$vdisable = 'yes';}
	if (isset($vcsidebarsoverrides['login'])) {
		if ($vcsidebarsoverrides['login'] == 'enable') {$vdisable = '';}
		if ($vcsidebarsoverrides['login'] == 'disable') {$vdisable = 'yes';}
	}

	if ($vdisable != 'yes') {
		if (is_active_sidebar('LoginSidebar')) {
			if ($vfallback == 'hidden') {$vhidden = ' style="display:none;"';} else {$vhidden = '';}
			$vsidebar = PHP_EOL.'<div id="loginsidebar" class="contentsidebar loggedoutsidebar"'.$vhidden.'>';
			$vsidebar .= csidebars_get_sidebar('LoginSidebar');
			$vsidebar .= '</div>'.PHP_EOL.PHP_EOL;
		} else {$vsidebar = '';}

		// 1.5.5: apply backward compatible and new filter prefix
		$vsidebar = apply_filters('fcs_login_sidebar',$vsidebar);
		$vsidebar = apply_filters('csidebars_login_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_login_sidebar_loggedout',$vsidebar);
		$vsidebar = apply_filters('csidebars_login_sidebar_loggedout',$vsidebar);
		return $vsidebar;
	}
	return '';
}

// --------------
// Member Sidebar
// --------------

// Member Sidebar Setup
// --------------------
// 1.4.5: added member sidebar mode options
add_action('init','csidebars_member_sidebar_setup');
function csidebars_member_sidebar_setup() {
	$vmembersidebarmode = csidebars_get_option('membersidebar_mode','fallback');
	if ( ($vmembersidebarmode == 'standalone') || ($vmembersidebarmode == 'both') ) {
		$vmembersidebarhook = csidebars_get_option('membersidebar_hook',true);
		$vmembersidebarpriority = csidebars_get_option('membersidebar_priority',true);
		add_action($vmembersidebarhook,'csidebars_member_sidebar_output',$vmembersidebarpriority);
	}
}
function csidebars_member_sidebar_output() {echo csidebars_member_sidebar(true);}

// Member Sidebar
// --------------
// 1.4.5: added standalone member sidebar function
function csidebars_member_sidebar($vstandalone=false) {

	global $vcsidebarsoverrides, $vcsidebarsstate;
	$vdisable = csidebars_get_option('membersidebar_disable');

	// 1.4.5: check new page contexts
	$vdisable = csidebars_check_context($vdisable,'member');
	// 1.5.5: made this a separate override filter
	$vdisable = apply_filters('csidebars_membersidebar_override',$vdisable);

	// if (get_post_meta($vpostid,'_disablemembersidebar',true) == 'yes') {$vdisable = 'yes';}
	if (isset($vcsidebarsoverrides['member'])) {
		if ($vcsidebarsoverrides['member'] == 'enable') {$vdisable = '';}
		if ($vcsidebarsoverrides['member'] == 'disable') {$vdisable = 'yes';}
	}

	if ($vdisable != 'yes') {
		if (is_active_sidebar('LoggedInSidebar')) {
			if ($vstandalone) {
				// 1.3.0: fix for logged in sidebar name
				$vsidebar = PHP_EOL.'<div id="membersidebar" class="contentsidebar loggedinsidebar">';
				$vsidebar .= csidebars_get_sidebar('LoggedInSidebar');
				$vsidebar .= '</div>'.PHP_EOL.PHP_EOL;
			} else {$vsidebar = csidebars_get_sidebar('LoggedInSidebar');}
		} else {$vsidebar = '';}

		// 1.5.5: apply backward compatible and new filter prefix
		$vsidebar = apply_filters('fcs_member_sidebar',$vsidebar);
		$vsidebar = apply_filters('csidebars_member_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_member_sidebar_loggedin',$vsidebar);
		$vsidebar = apply_filters('csidebars_member_sidebar_loggedin',$vsidebar);
		return $vsidebar;
	} else {return '';}
}


// Above/Below Method Actions
// --------------------------
// 1.3.5: just enqueue and check disable within actions
// 1.3.5: added filters to hooks and priorities
// 1.4.5: change to use output function wrappers
add_action('init','csidebars_content_sidebars_setup');
function csidebars_content_sidebars_setup() {
	$vmethod = csidebars_get_option('abovebelow_method',true);
	if ($vmethod == 'hooks') {
		// add to above content hook
		$vhook = csidebars_get_option('abovecontent_hook',true);
		$vpriority = csidebars_get_option('abovecontent_priority',true);
		add_action($vhook,'csidebars_abovecontent_sidebar_output',$vpriority);

		// add to below content hook
		$vhook = csidebars_get_option('belowcontent_hook',true);
		$vpriority = csidebars_get_option('belowcontent_priority',true);
		add_action($vhook,'csidebars_belowcontent_sidebar_output',$vpriority);
	}
	elseif ($vmethod == 'filter') {
		add_filter('the_content','csidebars_add_content_sidebars',999);
	}
}

// Above Content Sidebar
// ---------------------
function csidebars_abovecontent_sidebar_output() {echo csidebars_abovecontent_sidebar();}
function csidebars_abovecontent_sidebar() {

	global $vcsidebarsoverrides, $vcsidebarsstate;
	$vdisable = csidebars_get_option('abovecontent_disable');
	// 1.6.1: set empty sidebar to avoid warning
	$vsidebar = '';

	// 1.4.5: check new page contexts
	$vdisable = csidebars_check_context($vdisable,'abovecontent');
	// 1.5.5: made this a separate override filter
	$vdisable = apply_filters('csidebars_abovecontent_override',$vdisable);

	// check if logged in and fallback
	$vfallback = csidebars_get_option('abovecontent_fallback',true);
	if ( ($vfallback == 'nooutput') && ($vcsidebarsstate == 'loggedin') ) {return '';}
	if ($vfallback == 'fallback') {
		if ( ($vdisable != 'yes') && ($vcsidebarsstate == 'loggedin') ) {
			// 1.4.5: check mode and call to member sidebar function
			$vmode = csidebars_get_option('membersidebar_mode','fallback');
			if ($vmode == 'standalone') {return '';}
			$vsidebar = '<div id="abovecontentsidebar" class="contentsidebar loggedinsidebar">';
			$vsidebar .= csidebars_member_sidebar();
			$vsidebar .= "</div>";
			// 1.5.5: apply backward compatible and new filter prefix
			$vsidebar = apply_filters('fcs_abovecontent_sidebar_loggedin',$vsidebar);
			$vsidebar = apply_filters('csidebars_abovecontent_sidebar_loggedin',$vsidebar);
			return $vsidebar;
		}
	}

	// otherwise, fall forward haha
	// if (get_post_meta($vpostid,'_disableabovecontentsidebar',true) == 'yes') {$vdisable = 'yes';}
	if (isset($vcsidebarsoverrides['abovecontent'])) {
		if ($vcsidebarsoverrides['abovecontent'] == 'disable') {$vdisable = 'yes';}
		if ($vcsidebarsoverrides['abovecontent'] == 'enable') {$vdisable = '';}
	}
	if ($vdisable != 'yes') {
		if (is_active_sidebar('AboveContent')) {
			if ($vfallback == 'hidden') {$vhidden = ' style="display:none;"';} else {$vhidden = '';}
			// 1.4.5: replaced loggedout with login state variable class
			$vsidebar = PHP_EOL.'<div id="abovecontentsidebar" class="contentsidebar '.$vcsidebarsstate.'sidebar"'.$vhidden.'>';
			$vsidebar .= csidebars_get_sidebar('AboveContent');
			$vsidebar .= '</div>'.PHP_EOL;
		}

		// 1.5.5: apply backward compatible and new filter prefix
		$vsidebar = apply_filters('fcs_abovecontent_sidebar',$vsidebar);
		$vsidebar = apply_filters('csidebars_abovecontent_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_abovecontent_sidebar_'.$vcsidebarsstate,$vsidebar);
		$vsidebar = apply_filters('csidebars_abovecontent_sidebar_'.$vcsidebarsstate,$vsidebar);
		return $vsidebar;
	}
	return '';
}

// Below Content Sidebar
// ---------------------
function csidebars_belowcontent_sidebar_output() {echo csidebars_belowcontent_sidebar();}
function csidebars_belowcontent_sidebar() {

	global $vcsidebarsoverrides, $vcsidebarsstate;
	$vdisable = csidebars_get_option('belowcontent_disable');
	// 1.6.1: set empty sidebar to avoid warning
	$vsidebar = '';

	// 1.4.5: check new page contexts
	$vdisable = csidebars_check_context($vdisable,'belowcontent');
	// 1.5.5: made this a separate override filter
	$vdisable = apply_filters('csidebars_belowcontent_override',$vdisable);

	// check if logged in and fall back
	$vfallback = csidebars_get_option('belowcontent_fallback',true);
	if ( ($vfallback == 'nooutput') && ($vcsidebarsstate == 'loggedin') ) {return '';}
	if ($vfallback == 'fallback') {
		if ( ($vdisable != 'yes') && ($vcsidebarsstate == 'loggedin') ) {
			// 1.4.5: check mode and call to member sidebar function
			$vmode = csidebars_get_option('membersidebar_mode','fallback');
			if ($vmode == 'standalone') {return '';}
			$vsidebar = PHP_EOL.'<div id="belowcontentsidebar" class="contentsidebar loggedinsidebar">';
			$vsidebar .= csidebars_member_sidebar();
			$vsidebar .= '</div>'.PHP_EOL;
			// 1.5.5: apply backward compatible and new filter prefix
			$vsidebar = apply_filters('fcs_belowcontent_sidebar_loggedin',$vsidebar);
			$vsidebar = apply_filters('csidebars_belowcontent_sidebar_loggedin',$vsidebar);
			return $vsidebar;
		 }
	}

	// otherwise, fall sideways :-]
	// if (get_post_meta($vpostid,'_disablebelowcontentsidebar',true) == 'yes') {$vdisable = 'yes';}
	if (isset($vcsidebarsoverrides['belowcontent'])) {
		if ($vcsidebarsoverrides['belowcontent'] == 'disable') {$vdisable = 'yes';}
		if ($vcsidebarsoverrides['belowcontent'] == 'enable') {$vdisable = '';}
	}
	if ($vdisable != 'yes') {
		if (is_active_sidebar('BelowContent')) {
			// 1.4.5: replaced loggedout with login state variable class
			$vsidebar = PHP_EOL.'<div id="belowcontentsidebar" class="contentsidebar '.$vcsidebarsstate.'sidebar">';
			$vsidebar .= csidebars_get_sidebar('BelowContent');
			$vsidebar .= '</div>'.PHP_EOL;
		} else {$vsidebar = '';}

		// 1.5.5: apply backward compatible and new filter prefix
		$vsidebar = apply_filters('fcs_belowcontent_sidebar',$vsidebar);
		$vsidebar = apply_filters('csidebars_belowcontent_sidebar',$vsidebar);
		$vsidebar = apply_filters('fcs_belowcontent_sidebar_'.$vcsidebarsstate,$vsidebar);
		$vsidebar = apply_filters('csidebars_belowcontent_sidebar_'.$vcsidebarsstate,$vsidebar);
		return $vsidebar;
	}
	return '';
}

// -----------------------------------
// Above/Below Content - Filter Method
// -----------------------------------
// 1.3.5: removed code duplication (now just use above functions)
function csidebars_add_content_sidebars($vcontent) {
	// 1.4.5: bug out if excerpting
	global $vcsidebarsexcerpt; if ($vcsidebarsexcerpt) {return $vcontent;}

	// above content sidebar
	// 1.4.5: use return value not output buffering
	// 1.5.9: fix to old function name
	$vtopsidebar = csidebars_abovecontent_sidebar();

	// below content sidebar
	// 1.4.5: use return value not output buffering
	// 1.5.9: fix to old function name
	$vbottomsidebar = csidebars_belowcontent_sidebar();

	$vcontent = $vtopsidebar.$vcontent.$vbottomsidebar;
	return $vcontent;
}

// ------------------
// Shortcode Sidebars
// ------------------
// 1.3.5: just add and check disable/overrides within shortcodes
add_action('init','csidebars_sidebar_shortcodes');
function csidebars_sidebar_shortcodes() {
	if (!is_admin()) {
		add_shortcode('shortcode-sidebar-1','csidebars_shortcode_sidebar1');
		add_shortcode('shortcode-sidebar-2','csidebars_shortcode_sidebar2');
		add_shortcode('shortcode-sidebar-3','csidebars_shortcode_sidebar3');
	}
}
// 1.3.5: replaced individual shortcodes with abstract calls
function csidebars_shortcode_sidebar1() {return csidebars_shortcode_sidebar('1');}
function csidebars_shortcode_sidebar2() {return csidebars_shortcode_sidebar('2');}
function csidebars_shortcode_sidebar3() {return csidebars_shortcode_sidebar('3');}

// Shortcode Sidebar Abstract
// --------------------------
// 1.3.5: replace individual functions with abstracted function
function csidebars_shortcode_sidebar($vid) {
	global $post, $vcsidebarsoverrides, $vcsidebarsstate, $vcsidebarsexcerpt;

	// 1.6.1: set empty sidebar to avoid warning
	$vsidebar = '';

	// 1.4.5: bug out if excerpting
	if ($vcsidebarsexcerpt) {
		// normally we do not actually want to output shortcode sidebars in excerpts,
		// but for flexibility in usage let us give the user the option to do so
		$vprocess = csidebars_get_option('sidebars_in_excerpts',true);
		// 1.5.5: add prefix to this filter for specific shortcode sidebar in excerpts
		$vprocess = apply_filters('csidebars_shortcode_sidebar'.$vid.'_in_excerpts',$vprocess);
		if (!$vprocess) {return '';}
	}

	// check if sidebar is disabled
	$vdisable = csidebars_get_option('shortcode'.$vid.'_disable',true);
	if (is_object($post)) {
		$vpostid = $post->ID;
		// 1.5.5: removed old post meta key check
		// if (get_post_meta($vpostid,'_disableshortcodesidebar'.$vid,true) == 'yes') {$vdisable = 'yes';}
		if (isset($vcsidebarsoverrides['shortcodesidebar'.$vid])) {
			if ($vcsidebarsoverrides['shortcodesidebar'.$vid] == 'disable') {$vdisable = 'yes';}
			elseif ($vcsidebarsoverrides['shortcodesidebar'.$vid] == 'enable') {$vdisable = '';}
		}
	}
	if ($vdisable == 'yes') {return '';}

	// check if sidebar has widgets
	if (is_active_sidebar('ShortcodeSidebar'.$vid)) {
		$vsidebar = PHP_EOL.'<div id="shortcodesidebar'.$vid.'" class="shortcodesidebar '.$vcsidebarsstate.'sidebar">';
		$vsidebar .= csidebars_get_sidebar('ShortcodeSidebar'.$vid);
		$vsidebar .= '</div>'.PHP_EOL;
	}

	// apply sidebar output filters
	// 1.5.5: apply backward compatible and new filter prefix
	$vsidebar = apply_filters('fcs_shortcode_sidebar'.$vid,$vsidebar);
	$vsidebar = apply_filters('csidebars_shortcode_sidebar'.$vid,$vsidebar);
	$vsidebar = apply_filters('fcs_shortcode_sidebar'.$vid.'_'.$vcsidebarsstate,$vsidebar);
	$vsidebar = apply_filters('csidebars_shortcode_sidebar'.$vid.'_'.$vcsidebarsstate,$vsidebar);
	return $vsidebar;
}

// ---------------
// InPost Sidebars
// ---------------

add_action('init','csidebars_inpost_sidebars');
function csidebars_inpost_sidebars() {
	if (!is_admin()) {
		// 1.3.5: just add filter and check states within function
		$vinpostpriority = csidebars_get_option('inpost_priority',true);
		add_filter('the_content', 'csidebars_do_inpost_sidebars', $vinpostpriority);
	}
}

// Do InPost Sidebars
// ------------------
function csidebars_do_inpost_sidebars($vpostcontent) {

	global $post, $vcsidebarsoverrides, $vcsidebarsexcerpt, $vcsidebarsstate;

	// 1.4.5: bug out if excerpting or empty post
	if ($vcsidebarsexcerpt) {return $vpostcontent;}
	if (!is_object($post)) {return $vpostcontent;}

	// check for Content Marker (case insensitive)
	$vcontentmarker = csidebars_get_option('inpost_marker',true);
	if (!stristr($vpostcontent,$vcontentmarker)) {return $vpostcontent;}

	// get general disable options (filtered)
	$vinpostdisable1 = csidebars_get_option('inpost1_disable',true);
	$vinpostdisable2 = csidebars_get_option('inpost2_disable',true);
	$vinpostdisable3 = csidebars_get_option('inpost3_disable',true);

	// check InPost disable options
	$vpostid = $post->ID;
	$vcptoptions = csidebars_get_option('inpost_sidebars_cpts',true);
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
	// 1.5.5: make these into disable override filters
	$vinpostdisable1 = apply_filters('csidebars_inpost1_override',$vinpostdisable1);
	$vinpostdisable2 = apply_filters('csidebars_inpost2_override',$vinpostdisable2);
	$vinpostdisable3 = apply_filters('csidebars_inpost3_override',$vinpostdisable3);

	// 1.3.5: check meta overrides here
	if (isset($vcsidebarsoverrides['inpost1'])) {
		if ($vcsidebarsoverrides['inpost1'] == 'disable') {$vinpostdisable1 = 'yes';}
		if ($vcsidebarsoverrides['inpost1'] == 'enable') {$vinpostdisable1 = '';}
	}
	if (isset($vcsidebarsoverrides['inpost2'])) {
		if ($vcsidebarsoverrides['inpost2'] == 'disable') {$vinpostdisable2 = 'yes';}
		if ($vcsidebarsoverrides['inpost2'] == 'enable') {$vinpostdisable2 = '';}
	}
	if (isset($vcsidebarsoverrides['inpost3'])) {
		if ($vcsidebarsoverrides['inpost3'] == 'disable') {$vinpostdisable3 = 'yes';}
		if ($vcsidebarsoverrides['inpost3'] == 'enable') {$vinpostdisable3 = '';}
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

	// get inpost content positions (filtered)
	$vpositiona = csidebars_get_option('inpost_positiona',true);
	$vpositionb = csidebars_get_option('inpost_positionb',true);
	$vpositionc = csidebars_get_option('inpost_positionc',true);
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
				$vsidebar = PHP_EOL.'<div id="inpostsidebar1" class="inpostsidebar"';
				// 1.4.0: added float style option
				$vfloat = csidebars_get_option('inpost1_float',true);
				if ($vfloat != '') {
					$vsidebar .= ' style="float:'.$vfloat.';';
					if ($vfloat == 'left') {$vsidebar .= 'margin-right:30px;"';}
					elseif ($vfloat == 'right') {$vsidebar .= 'margin-left:30px;"';}
					else {$vsidebar .= '"';}
				}
				$vsidebar .= '>';
				$vsidebar .= csidebars_get_sidebar('InPost1');
				$vsidebar .= '</div>'.PHP_EOL.PHP_EOL;
			} else {$vsidebar = '';}
			// 1.5.5: apply backwards compatible and new filter prefix
			$vsidebar = apply_filters('csidebars_inpost_sidebar',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar1',$vsidebar);
			$vsidebar = apply_filters('csidebars_inpost_sidebar1',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar1_'.$vcsidebarsstate,$vsidebar);
			$vsidebar = apply_filters('csidebars_inpost_sidebar1_'.$vcsidebarsstate,$vsidebar);
			$vcontent .= $vsidebar;
		}
		elseif ( ($vcount == $vpositionb) && ($vinpostdisable2 != 'yes') ) {
			if (is_active_sidebar('InPost2')) {
				$vsidebar = 'PHP_EOL.<div id="inpostsidebar2" class="inpostsidebar"';
				// 1.4.0: added float style option
				$vfloat = csidebars_get_option('inpost2_float',true);
				if ($vfloat != '') {
					$vsidebar .= ' style="float:'.$vfloat.';';
					if ($vfloat == 'left') {$vsidebar .= 'margin-right:30px;"';}
					elseif ($vfloat == 'right') {$vsidebar .= 'margin-left:30px;"';}
					else {$vsidebar .= '"';}
				}
				$vsidebar .= '>';
				$vsidebar .= csidebars_get_sidebar('InPost2');
				$vsidebar .= '</div>'.PHP_EOL.PHP_EOL;
			} else {$vsidebar = '';}
			// 1.5.5: apply backwards compatible and new filter prefix
			$vsidebar = apply_filters('csidebars_inpost_sidebar',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar2',$vsidebar);
			$vsidebar = apply_filters('csidebars_inpost_sidebar2',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar2_'.$vcsidebarsstate,$vsidebar);
			$vsidebar = apply_filters('csidebars_inpost_sidebar2_'.$vcsidebarsstate,$vsidebar);
			$vcontent .= $vsidebar;
		}
		elseif ( ($vcount == $vpositionc) && ($vinpostdisable3 != 'yes') ) {
			if (is_active_sidebar('InPost3')) {
				$vsidebar = PHP_EOL.'<div id="inpostsidebar3" class="inpostsidebar"';
				// 1.4.0: added float style option
				$vfloat = csidebars_get_option('inpost3_float',true);
				if ($vfloat != '') {
					$vsidebar .= ' style="float:'.$vfloat.';';
					if ($vfloat == 'left') {$vsidebar .= 'margin-right:30px;"';}
					elseif ($vfloat == 'right') {$vsidebar .= 'margin-left:30px;"';}
					else {$vsidebar .= '"';}
				}
				$vsidebar .= '>';
				$vsidebar .= csidebars_get_sidebar('InPost3');
				$vsidebar .= '</div>'.PHP_EOL.PHP_EOL;
			} else {$vsidebar = '';}
			// 1.5.5: apply backwards compatible and new filter prefix
			$vsidebar = apply_filters('csidebars_inpost_sidebar',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar3',$vsidebar);
			$vsidebar = apply_filters('csidebars_inpost_sidebar3',$vsidebar);
			$vsidebar = apply_filters('fcs_inpost_sidebar3_'.$vcsidebarsstate,$vsidebar);
			$vsidebar = apply_filters('csidebars_inpost_sidebar3_'.$vcsidebarsstate,$vsidebar);
			$vcontent .= $vsidebar;
		}
		$vcount++;
	}
	return $vcontent;
}

// -----------------
// Metabox Overrides
// -----------------

add_action('add_meta_boxes','csidebars_add_perpage_metabox');
function csidebars_add_perpage_metabox() {

	$vcpts[0] = 'post'; $vcpts[1] = 'page';
	$vargs = array('public'=>true, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vcpts = array_merge($vcpts,$vcptlist);

	// you can use this filter to conditionally adjust the post types for which the metabox is shown
	// 1.3.5: changed this filter name to match purpose
	// 1.5.5: apply backwards compatible and new filter prefix
	$vcpts = apply_filters('fcs_metabox_cpts',$vcpts);
	$vcpts = apply_filters('csidebars_metabox_cpts',$vcpts);

	// 1.3.5: fix to variable typo here
	if (count($vcpts) > 0) {
		foreach ($vcpts as $vcpt) {
			add_meta_box('csidebars_perpage_metabox', 'Content Sidebars', 'csidebars_perpage_metabox', $vcpt, 'normal', 'low');
		}
	}
}

// Content Sidebars Metabox
// ------------------------
function csidebars_perpage_metabox() {

	global $post, $vcsidebarsoverrides;
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
	echo " ".$vposttypedisplay." (";
	echo __('indicated in bold','csidebars')."):<br>";

	// Above/Below, Login/LoggedIn
	echo "<table><tr>";
	echo "<td>".__('Above Content','csidebars')."</td>";
	csidebars_output_setting_cell('abovecontent');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('Below Content','csidebars')."</td>";
	csidebars_output_setting_cell('belowcontent');
	echo "</tr>";
	echo "<tr><td>".__('Login','csidebars')."</td>";
	csidebars_output_setting_cell('login');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td><span class='fcs-small'>".__('Logged In (fallback)','csidebars')."</span></td>";
	csidebars_output_setting_cell('member');
	echo "</tr>";

	// Shortcode and InPost Sidebars
	echo "<tr><td>".__('Shortcode','csidebars').' 1'."</td>";
	csidebars_output_setting_cell('shortcode1');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('InPost','csidebars')." 1</td>";
	csidebars_output_setting_cell('inpost1');
	echo "</tr>";
	echo "<tr><td>".__('Shortcode','csidebars')." 2</td>";
	csidebars_output_setting_cell('shortcode2');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('InPost','csidebars')." 2</td>";
	csidebars_output_setting_cell('inpost2');
	echo "</tr>";
	echo "<tr><td>".__('Shortcode','csidebars')." 3</td>";
	csidebars_output_setting_cell('shortcode3');
	echo "<td width='20'>&nbsp;</td>";
	echo "<td>".__('InPost','csidebars')." 3</td>";
	csidebars_output_setting_cell('inpost3');
	echo "</tr>";

	echo "</table>";
}

// Output Setting Cell
// -------------------
function csidebars_output_setting_cell($vid) {
	global $vcsidebarsoverrides, $post;

	// check sidebar state
	$vdisable = csidebars_get_option($vid.'_disable');
	if ($vdisable == 'yes') {$vstate = 'off';}
	else {
		$vstate = 'on';
		// check output for this post type
		if ( (is_object($post)) && (!strstr($vid,'shortcode')) ) {
			if (strstr($vid,'inpost')) {$vsidebarcpts = csidebars_get_option('inpost_sidebars_cpts');}
			else {$vsidebarcpts = csidebars_get_option($vid.'_sidebar_cpts');}
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
	if ( ($vid == 'login') || ($vid == 'member') ) {$vfilter = 'csidebars_'.$vid.'sidebar_disable';}
											  else {$vfilter = 'csidebars_'.$vid.'_disable';}

	$vfiltered = apply_filters($vfilter,$vstate);
	if ($vfiltered != $vstate) {
		if ($vfiltered == 'yes') {$von = __('On','csidebars'); $voff = "<b>".__('Off','csidebars')."</b>*";}
		elseif ($vfiltered == '') {$voff = __('Off','csidebars'); $von = "<b>".__('On','csidebars')."</b>*";}
	}

	echo "<td> <input type='radio' name='fcs_".$vid."' value=''";
	if ($vcsidebarsoverrides[$vid] == '') {echo " checked";}
	echo "> <span class='fcs-small'>".__('Current','csidebars')."</span></td><td width='5'></td>";
	echo "<td> <input type='radio' name='fcs_".$vid."' value='enable'";
	if ($vcsidebarsoverrides[$vid] == 'enable') {echo " checked";}
	echo "> ".$von."</td><td width='5'></td>";
	// 1.5.5: fix to missing disable value
	echo "<td> <input type='radio' name='fcs_".$vid."' value='disable'";
	if ($vcsidebarsoverrides[$vid] == 'disable') {echo " checked";}
	echo "> ".$voff."</td>";
}

// Update Meta Values on Save
// --------------------------
add_action('publish_post','csidebars_perpage_updates');
add_action('save_post','csidebars_perpage_updates');

// 1.3.5: efficient save using single postmeta value
function csidebars_perpage_updates() {

	// 1.3.5: return if post object is empty
	global $post, $vcsidebarsoverrides;
	if (!is_object($post)) {return;}
	$vpostid = $post->ID;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {return $vpostid;}
	if (!current_user_can('edit_posts')) {return $vpostid;}
	if (!current_user_can('edit_post',$vpostid)) {return $vpostid;}

	$voptionkeys = array(
		'abovecontent','belowcontent','login','member','shortcode1','shortcode2','shortcode3','inpost1','inpost2','inpost3'
	);

	$vcsidebarsoverrides = array();
	foreach ($voptionkeys as $voptionkey) {
		if (isset($_POST['fcs_'.$voptionkey])) {
			$vposted = $_POST['fcs_'.$voptionkey];
			// 1.5.5: validate metabox save options
			if ( ($vposted == '') || ($vposted == 'enable') || ($vposted == 'disable') ) {
				$vcsidebarsoverrides[$voptionkey] = $vposted;
			} else {$vcsidebarsoverrides[$voptionkey] = '';}
		} else {$vcsidebarsoverrides[$voptionkey] = '';}
	}
	delete_post_meta($vpostid,'content_sidebars');
	add_post_meta($vpostid,'content_sidebars',$vcsidebarsoverrides);

}

?>