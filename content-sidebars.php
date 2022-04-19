<?php

/*
Plugin Name: Content Sidebars
Plugin URI: http://wordquest.org/plugins/content-sidebars/
Author: Tony Hayes
Description: Adds Flexible Dynamic Sidebars to your Content Areas without editing your theme.
Version: 1.6.8
Author URI: http://wordquest.org
GitHub Plugin URI: majick777/content-sidebars
@fs_premium_only pro-functions.php
*/

/* csidebars_ "Do you like seaside bars? I like seaside bars." */

if ( !function_exists( 'add_action' ) ) {
	exit;
}

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

// === Plugin Loader ===
// - Plugin Options
// - Plugin Settings
// - Start Plugin Loader
// === Plugin Setup ===
// - Add to Appearance Menu
// - Appearance Item Redirect
// - Load Sidebar Styles
// - AJAX Dynamic CSS Output
// - Widget Page Styles
// - Widget Page Message
// - CSS Hero Integration
// === Plugin Settings ===
// - Transfer Old Settings
// - Process Special Settings
// - Plugin Settings Page
// === Plugin Helpers ===
// - Set Excerpt Page
// - Set Login State
// - Set Pageload Context
// - Get Sidebar Overrides
// - Get Sidebar Helper
// - Check Context Helper
// === Register Sidebars ===
// - Register Sidebar Helper
// - Register Content Sidebars
// - Register Discreet Text Widget
// === Shortcodes ===
// - Shortcode Filters
// - Excerpts with Shortcodes
// === Login Sidebar ===
// - Add Login Sidebar
// - Login Sidebar Output
// === Member Sidebar ===
// - Add Member Sidebar
// - Member Sidebar Output
// === Above / Below Sidebars ===
// - Above / Below Sidebar Method Actions
// - Above Content Sidebar Output
// - Below Content Sidebar Output
// - Above / Below Content Filter Method
// === Shortcode Sidebars ===
// - Add Shortcode Sidebars 
// - Shortcode Sidebar Output
// === InPost Sidebars ===
// - Add InPost Sidebars
// - InPost Sidebar Output
// === Metabox Overrides ===
// - Add Metaboxes for Post Types
// - Content Sidebars Metabox
// - Output Metabox Setting Cell
// - Update Meta Values on Save

// -----------------
// Development TODOs
// -----------------
// - content sidebar hook definitions for layout manager


// -----------------------
// === WordQuest Menus ===
// -----------------------
// note: these actions must be added before loader is initiated

// ---------------------
// Add WordQuest Submenu
// ---------------------
add_filter( 'csidebars_admin_menu_added', 'csidebars_add_admin_menu', 10, 2 );
function csidebars_add_admin_menu( $added, $args ) {

	// --- filter menu capability early ---
	$capability = apply_filters( 'wordquest_menu_capability', 'manage_options' );

	// --- maybe add Wordquest top level menu ---
	global $admin_page_hooks;
	if ( empty( $admin_page_hooks['wordquest'] ) ) {
		$icon = plugins_url( 'images/wordquest-icon.png', $args['file'] );
		$position = apply_filters( 'wordquest_menu_position', '3' );
		add_menu_page( 'WordQuest Alliance', 'WordQuest', $capability, 'wordquest', 'wqhelper_admin_page', $icon, $position );
	}

	// --- check if using parent menu ---
	// (and parent menu capability)
	if ( isset( $args['parentmenu']) && ( $args['parentmenu'] == 'wordquest' ) && current_user_can( $capability ) ) {

		// --- add WordQuest Plugin Submenu ---
		add_submenu_page( 'wordquest', $args['pagetitle'], $args['menutitle'], $args['capability'], $args['slug'], $args['namespace'] . '_settings_page' );

		// --- add icons and styling fix to the plugin submenu :-) ---
		add_action( 'admin_footer', 'csidebars_wordquest_submenu_fix' );

		return true;
	}

	return false;
}

// --------------------------
// WordQuest Submenu Icon Fix
// --------------------------
function csidebars_wordquest_submenu_fix() {
	$args = csidebars_loader_instance()->args;
	$icon_url = plugins_url( 'images/icon.png', $args['file'] );
	if ( isset( $_REQUEST['page'] ) && ( $_REQUEST['page'] == $args['slug'] ) ) {$current = '1';} else {$current = '0';}
	echo "<script>jQuery(document).ready(function() {if (typeof wordquestsubmenufix == 'function') {
	wordquestsubmenufix('" . esc_js( $args['slug'] ) . "', '" . esc_url( $icon_url ) . "', '" . esc_js( $current ) . "');} });</script>";
}

// ------------------------------
// Add WordQuest Sidebar Settings
// ------------------------------
add_action( 'csidebars_add_settings', 'csidebars_add_settings' , 10, 1 );
function csidebars_add_settings( $args ) {
	if ( isset( $args['settings'] ) ) {
		$adsboxoff = 'checked';
		if ( file_exists($args['dir'] . '/updatechecker.php' ) ) {
			$adsboxoff = '';
		}
		$sidebaroptions = array(
			'installdate'		=> date( 'Y-m-d' ),
			'donationboxoff'	=> '',
			'subscribeboxoff'	=> '',
			'reportboxoff' 		=> '',
			'adsboxoff'		=> $adsboxoff,
		);
		add_option( $args['settings'] . '_sidebar_options', $sidebaroptions );
	}
}

// ---------------------------
// Load WordQuest Admin Helper
// ---------------------------
add_action( 'csidebars_loader_helpers', 'csidebars_load_wordquest_helper', 10, 1 );
function csidebars_load_wordquest_helper( $args ) {
	if ( is_admin() && ( version_compare( PHP_VERSION, '5.3.0') >= 0 ) ) {
		$wqhelper = dirname( __FILE__ ) . '/wordquest.php';
		if ( file_exists( $wqhelper ) ) {
			include( $wqhelper );
			global $wordquestplugins; $slug = $args['slug'];
			$wordquestplugins[$slug] = $args;
		}
	}
}


// ====================
// --- Plugin Setup ---
// ====================
// 1.6.5: updated options to use plugin loader

// --------------
// Plugin Options
// --------------
$defaultcss = file_get_contents( dirname( __FILE__ ) . '/content-sidebars.css' );
$options = array(

	// --- above/below sidebars method ---
	'abovebelow_method'		=> array( 'type' => 'hooks/filter', 'default' => 'hooks'),

	// --- template hooks ---
	// 1.6.8: change to text type and add options key
	// 1.6.8: remove skeleton_ from Bioship hook names
	'abovecontent_hook'		=> array( 'type' => 'text', 'options' => 'ALPHANUMERIC', 'default' => 'bioship_before_loop' ),
	'belowcontent_hook'		=> array( 'type' => 'text', 'options' => 'ALPHANUMERIC', 'default' => 'bioship_after_loop' ),
	'loginsidebar_hook'		=> array( 'type' => 'text', 'options' => 'ALPHANUMERIC', 'default' => 'bioship_before_header' ),
	'membersidebar_hook'		=> array( 'type' => 'text', 'options' => 'ALPHANUMERIC', 'default' => 'bioship_after_header' ),

	// --- hook priorities ---
	'abovecontent_priority'		=> array( 'type' => 'numeric', 'default' => '5' ),
	'belowcontent_priority'		=> array( 'type' => 'numeric', 'default' => '5' ),
	'loginsidebar_priority'		=> array( 'type' => 'numeric', 'default' => '5' ),
	'membersidebar_priority'	=> array( 'type' => 'numeric', 'default' => '5' ),

	// --- fallback switches ---
	'abovecontent_fallback'		=> array( 'type' => 'output/hide/fallback/nooutput', 'default' => 'output' ),
	'belowcontent_fallback'		=> array( 'type' => 'output/hide/fallback/nooutput', 'default' => 'output' ),
	'loginsidebar_fallback'		=> array( 'type' => 'fallback/nooutput', 'default' => 'fallback' ),
	'membersidebar_mode'		=> array( 'type' => 'fallback/standalone/both', 'default' => 'fallback' ),

	// --- post types ---
	'abovecontent_sidebar_cpts'	=> array( 'type' => 'special', 'default' => 'page' ),
	'belowcontent_sidebar_cpts'	=> array( 'type' => 'special', 'default' => 'post' ),
	'login_sidebar_cpts'		=> array( 'type' => 'special', 'default' => 'post,page' ),
	'member_sidebar_cpts'		=> array( 'type' => 'special', 'default' => 'post,page' ),
	'inpost_sidebars_cpts'		=> array( 'type' => 'special', 'default' => 'article' ),

	// --- page contexts ---
	// 1.4.5: added page contexts
	'abovecontent_sidebar_pages'	=> array( 'type' => '', 'default' => '' ),
	'belowcontent_sidebar_pages'	=> array( 'type' => '', 'default' => '' ),
	'login_sidebar_pages'		=> array( 'type' => '', 'default' => '' ),
	'member_sidebar_pages'		=> array( 'type' => '', 'default' => '' ),

	// --- archive contexts ---
	// 1.4.5: added archive contexts
	'abovecontent_sidebar_archives'	=> array( 'type' => '', 'default' => '' ),
	'belowcontent_sidebar_archives'	=> array( 'type' => '', 'default' => '' ),
	'login_sidebar_archives'	=> array( 'type' => '', 'default' => '' ),
	'member_sidebar_archives'	=> array( 'type' => '', 'default' => '' ),

	// --- sidebar disablers ---
	'loginsidebar_disable'		=> array( 'type' => 'checkbox', 'default' => '' ),
	'membersidebar_disable'		=> array( 'type' => 'checkbox', 'default' => '' ),
	'abovecontent_disable'		=> array( 'type' => 'checkbox', 'default' => '' ),
	'belowcontent_disable'		=> array( 'type' => 'checkbox', 'default' => '' ),
	'shortcode1_disable'		=> array( 'type' => 'checkbox', 'default' => '' ),
	'shortcode2_disable'		=> array( 'type' => 'checkbox', 'default' => '' ),
	'shortcode3_disable'		=> array( 'type' => 'checkbox', 'default' => '' ),

	// --- inpost sidebars ---
	'inpost1_disable'		=> array( 'type' => 'checkbox', 'default' => 'yes' ),
	'inpost2_disable'		=> array( 'type' => 'checkbox', 'default' => 'yes' ),
	'inpost3_disable'		=> array( 'type' => 'checkbox', 'default' => 'yes' ),
	'inpost_marker'			=> array( 'type' => 'textarea', 'default' => '</p>' ),
	'inpost_priority'		=> array( 'type' => 'numeric', 'default' => '100' ),
	'inpost_positiona'		=> array( 'type' => 'numeric', 'default' => '4' ),
	'inpost_positionb'		=> array( 'type' => 'numeric', 'default' => '8' ),
	'inpost_positionc'		=> array( 'type' => 'numeric', 'default' => '12' ),
	// note: the options below are correct values (so that first value = '')
	'inpost1_float'			=> array( 'type' => '/none/left/right', 'default' => 'right' ),
	'inpost2_float'			=> array( 'type' => '/none/left/right', 'default' => 'left' ),
	'inpost3_float'			=> array( 'type' => '/none/left/right', 'default' => 'right' ),

	// --- shortcode options ---
	'widget_text_shortcodes'	=> array( 'type' => 'checkbox', 'default' => 'yes' ),
	'widget_title_shortcodes'	=> array( 'type' => 'checkbox', 'default' => '' ),
	'excerpt_shortcodes'		=> array( 'type' => 'checkbox', 'default' => '' ),
	'sidebars_in_excerpts'		=> array( 'type' => 'checkbox', 'default' => '' ),

	// --- CSS options ---
	'css_mode'				=> array( 'type' => 'default/adminajax/right', 'default' => 'default' ),
	'dynamic_css'			=> array( 'type' => 'textarea', 'default' => $defaultcss ),
	'last_saved'			=> array( 'type' => 'special', 'default' => time() ),

);

// ---------------
// Plugin Settings
// ---------------
// 1.6.5: updated settings to use plugin loader
$slug = 'content-sidebars';
$args = array(
	// --- Plugin Info ---
	'slug'			=> $slug,
	'file'			=> __FILE__,
	'version'		=> '0.0.1',

	// --- Menus and Links ---
	'title'			=> 'Content Sidebars',
	'parentmenu'		=> 'wordquest',
	'home'			=> 'http://wordquest.org/plugins/' . $slug . '/',
	'support'		=> 'http://wordquest.org/quest-category/' . $slug . '/',
	// 'share'		=> 'http://wordquest.org/plugins/' . $slug . '/#share',
	'donate'		=> 'https://wordquest.org/contribute/?plugin=' . $slug,
	'donatetext'		=> __( 'Support Content Sidebars' ),
	'welcome'		=> '',	// TODO

	// --- Options ---
	'namespace'		=> 'csidebars',
	'option'		=> 'content_sidebars',
	'options'		=> $options,
	'settings'		=> 'fcs',

	// --- WordPress.Org ---
	'wporgslug'		=> 'content-sidebars',
	'textdomain'		=> 'csidebars',
	'wporg'			=> false,

	// --- Freemius ---
	'freemius_id'		=> '163',
	'freemius_key'		=> 'pk_386ac55ea05fcdcd4daf27798b46c',
	'hasplans'		=> false,
	'hasaddons'		=> false,
	'plan'			=> 'free',
);

// ----------------------------
// Start Plugin Loader Instance
// ----------------------------
require dirname( __FILE__ ) . '/loader.php';
new csidebars_loader( $args );

// ----------------------
// Add to Appearance Menu
// ----------------------
// (as relevant to plugin)
add_action( 'admin_menu','csidebars_theme_options_menu' );
function csidebars_theme_options_menu() {
	add_theme_page( __( 'Content Sidebars', 'csidebars' ) , __( 'Content Sidebars', 'csidebars' ), 'manage_options', 'flexi-content-sidebars', 'csidebars_theme_options_dummy' );
	function csidebars_theme_options_dummy() {} // dummy menu item function
}

// -----------------------------
// Appearance Menu Item Redirect
// -----------------------------
// 1.6.5: check redirect trigger internally
add_action( 'init', 'csidebars_theme_options_page' );
function csidebars_theme_options_page() {
	global $csidebars;
	// --- check for redirect trigger to real admin menu item ---
	if ( !strstr( $_SERVER['REQUEST_URI'], '/themes.php' ) ) {
		return;
	}
	if ( !isset( $_REQUEST['page'] ) || ( 'flexi-content-sidebars' != $_REQUEST['page'] ) ) {
		return;
	}
	wp_redirect( admin_url( 'admin.php' ) . '?page=' . $csidebars['slug'] );
}

// -------------------
// Load Sidebar Styles
// -------------------
// 1.3.5: changed to wp_enqueue_scripts hook
add_action( 'wp_enqueue_scripts', 'csidebars_enqueue_styles' );
function csidebars_enqueue_styles() {

	$cssmode = csidebars_get_setting('css_mode', true);

	if ( ( 'direct' == $cssmode ) || ( 'dynamic' == $cssmode ) ) {
	 	// 1.4.5: added direct URL load option as new default
		// 1.5.6: check/write exact ABSPATH for safe wp-load
		// 1.5.7: only write single require line to wp-loader.php
		// 1.5.8: remove direct dynamic PHP to CSS mode
		$cssmode = 'write';
	}

	if ( 'default' == $cssmode ) {
		// 1.5.8: added check for default style file just in case
		$cssfile = dirname( __FILE__ ) . '/content-default.css';
		if ( !file_exists( $cssfile ) ) {
			$cssmode = 'adminajax';
		} else {
			$cssurl = plugins_url( 'content-default.css', __FILE__ );
			// 1.6.1: remove doubled up css suffix
			wp_enqueue_style( 'content-sidebars', $cssurl );
		}
	}

	if ( 'write' == $cssmode ) {
		// 1.5.6: added write/check method
		$cssfile = dirname( __FILE__ ) . '/content-sidebars.css';
		$cssurl = plugins_url( 'content-sidebars.css', __FILE__ );
		$css = csidebars_get_setting( 'dynamic_css', true );
		// 1.6.5: added check if file exists before checking contents
		if ( file_exists( $cssfile ) ) {
			if ( file_get_contents( $cssfile ) != $css ) {
				// --- rewrite the file as the CSS has changed ---
				// 1.5.7: check the WP Filesystem before writing
				$checkmethod = get_filesystem_method( array(), $dirpath, false );
				if ( 'direct' !== $checkmethod ) {
					$cssmode = 'adminajax';
				} else {
					$fh = fopen( $cssfile, 'w' );
					fwrite( $fh, $css );
					fclose( $fh );
				}
			}
		} else {
			$cssmode = 'adminajax';
		}
		if ( 'adminajax' != $cssmode ) {
			$version = csidebars_get_setting( 'last_saved' );
			// 1.6.1: remove doubled up css suffix
			wp_enqueue_style( 'content-sidebars', $cssurl, array(), $version );
		}
	}

	// --- AJAX method ---
	// 1.5.7: use AJAX mode as fallback
	if ( 'adminajax' == $cssmode ) {
		$version = csidebars_get_setting( 'last_saved' );
		$ajaxurl = admin_url( 'admin-ajax.php' ) . '?action=csidebars_dynamic_css';
		// 1.6.1: remove doubled css suffix
		wp_enqueue_style( 'content-sidebars', $ajaxurl, array(), $version );
	}
}

// -----------------------------
// Check File System Credentials
// -----------------------------
// function csidebars_filesystem_check_creds($url, $method, $context, $extrafields) {
//	global $wp_filesystem;
//	if (empty($wp_filesystem)) {
//		$filefunctions = ABSPATH.'/wp-admin/includes/file.php';
//		if (!file_exists($filefunctions)) {return false;}
//		else {require_once($filefunctions); WP_Filesystem();}
//	}
//	$credentials = request_filesystem_credentials($url, $method, false, $context, $extrafields);
//	if ($credentials === false) {return false;}
//	if (!WP_Filesystem($credentials)) {return false;}
//	return true;
// }

// -----------------------
// AJAX Dynamic CSS Output
// -----------------------
add_action( 'wp_ajax_csidebars_dynamic_css', 'csidebars_dynamic_css' );
add_action( 'wp_ajax_nopriv_csidebars_dynamic_css', 'csidebars_dynamic_css' );
function csidebars_dynamic_css() {
	// 1.6.5: fix for AJAX method to load CSS setting
	// require(dirname(__FILE__).'/content-sidebars-css.php');
	$styles = csidebar_get_setting( 'dynamic_css' );
	header( 'Content-type: text/css; charset: UTF-8' );
	echo $styles;
	exit;
}

// ------------------
// Widget Page Styles
// ------------------
// 1.4.0: style the sidebar on widget page
// 1.6.5: check/fix to widget styles page conditions
add_action( 'admin_head', 'csidebars_widget_page_styles' );
function csidebars_widget_page_styles() {
	global $pagenow;
	if ( 'widgets.php' != $pagenow ) {
		return;
	}
	echo "<style>.sidebar-content-on {background-color:#E9F0FF;} .sidebar-content-on h2 {font-size: 12pt;}
	.sidebar-content-off {background-color:#EFF3FF;} .sidebar-content-off h2 {font-weight: normal; font-size: 10pt;}</style>";
}

// -------------------
// Widget Page Message
// -------------------
add_action( 'widgets_admin_page', 'csidebars_widget_page_message', 11 );
function csidebars_widget_page_message() {
	$message = '<b>' . esc_html( __( 'Note', 'csidebars' ) ) . '</b>: ';
	$message .= __( 'Inactive Content Sidebars are listed with lowercase titles.', 'csidebars' );
	$message .= __( 'Activate them via the Content Sidebars settings page.', 'csidebars');
	echo '<div class="message">' . esc_html( $message ) . '</div>';
}

// --------------------
// CSS Hero Integration
// --------------------
// 1.3.0: added CSS Hero script workaround
// TODO: retest in combination with theme CSS Hero integration ?
// add_action('wp_loaded', 'csidebars_csshero_script_dir', 1);
function csidebars_csshero_script_dir() {

	// 1.6.5: moved trigger check internally
	if ( !isset( $_GET['csshero_action'] ) || ( 'edit_page' != $_GET['csshero_action'] ) ) {
		return;
	}

	add_filter( 'stylesheet_directory_uri', 'csidebars_csshero_script_url', 11, 3 );

	function csidebars_csshero_script_url( $stylesheet_dir_url, $stylesheet, $theme_root_uri ) {
		$csshero = dirname( __FILE__ );
		if ( file_exists( $csshero . '/csshero.js' ) ) {
			$cssherourl = plugins_url( '', __FILE__ );
			// --- workaround to add additional script URL ---
			$stylesheet_dir_url .= "/csshero.js'><script type='text/javascript' src='" . $cssherourl;
		}
		return $stylesheet_dir_url;
	}
}


// -----------------------
// === Plugin Settings ===
// -----------------------
// 1.6.5: use common settings functions in plugin loader

// ---------------------
// Transfer Old Settings
// ---------------------
// 1.3.5: compact old settings into global array
// 1.6.5: set function for usage via plugin loader
function csidebars_transfer_settings() {
	if ( get_option( 'fcs_abovebelow_method' ) && !get_option( 'content_sidebars' ) ) {

		// --- set option keys ---
		$optionkeys = array(
			'abovebelow_method', 'abovecontent_hook', 'belowcontent_hook', 'loginsidebar_hook', 'membersidebar_hook',
			'abovecontent_priority', 'belowcontent_priority', 'loginsidebar_priority', 'membersidebar_priority',
			'abovecontent_fallback', 'belowcontent_fallback', 'loginsidebar_fallback', 'membersidebar_mode',
			'abovecontent_sidebar_cpts', 'belowcontent_sidebar_cpts', 'inpost_sidebars_cpts', 'member_sidebar_cpts',
			'abovecontent_sidebar_pages', 'belowcontent_sidebar_pages', 'login_sidebar_pages', 'member_sidebar_pages',
			'abovecontent_sidebar_archives', 'belowcontent_sidebar_archives', 'login_sidebar_archives', 'member_sidebar_archives',
			'loginsidebar_disable', 'membersidebar_disable', 'abovecontent_disable', 'belowcontent_disable',
			'widget_text_shortcodes', 'widget_title_shortcodes', 'excerpt_shortcodes', 'sidebars_in_excerpts',
			'shortcode1_disable', 'shortcode2_disable', 'shortcode3_disable',
			'inpost1_disable', 'inpost2_disable', 'inpost3_disable', 'inpost_marker', 'inpost_priority',
			'inpost_positiona', 'inpost_positionb', 'inpost_positionc', 'inpost1_float', 'inpost2_float', 'inpost3_float',
			'css_mode', 'dynamic_css');

		// --- loop and transfer settings ---
		foreach ( $optionkeys as $key ) {
			$csidebars[$key] = get_option('fcs_' . $key);
			// 1.4.0: convert old fallback value
			if ( strstr( $key, '_fallback' ) && ( 'yes' == $csidebars[$key] ) ) {
				$csidebars[$key] = 'fallback';
			}
		}
		$csidebars['last_saved'] = time();

		add_option( 'content_sidebars', $csidebars );
	}
}


// --------------------------------
// Process Special Settings Updates
// --------------------------------
// 1.3.5 update to use global options array
// 1.5.5: added option data types for saving
// 1.5.8: remove direct dynamic PHP CSS method option
// 1.6.5: use plugin loader to save main options
function csidebars_process_special( $settings ) {

	// --- get all the post types ---
	$cpts = array( 'post', 'page' );
	$args = array( 'public' => true, '_builtin' => false );
	$cptlist = get_post_types( $args, 'names', 'and' );
	$cpts = array_merge( $cpts, $cptlist );

	// --- loop sidebars for CPTs ---
	// 1.4.5: loop all sidebar post types except shortcodes
	$sidebars = array( 'abovecontent', 'belowcontent', 'login', 'member', 'inpost' );
	foreach ( $sidebars as $sidebar ) {
		$i = 0;
		$newcpts = array();
		foreach ( $cpts as $cpt ) {
			$postkey = 'fcs_' . $sidebar . '_posttype_' . $cpt;
			if ( isset( $_POST[$postkey] ) && ( 'yes' == $_POST[$postkey] ) ) {
				$newcpts[$i] = $cpt;
				$i++;
			}
		}
		$cptsettings = implode( ',', $newcpts );
		if ( 'inpost' == $sidebar ) {
			$s = 's';
		} else {
			$s = '';
		}
		$settings[$sidebar . '_sidebar' . $s . '_cpts'] = $cptsettings;
	}

	// --- loop sidebars for page and archive contexts ---
	$sidebars = array( 'abovecontent', 'belowcontent', 'login', 'member' );
	$contexts = array( 'frontpage', 'home', '404', 'search' );
	// 1.5.5: add missing taxonomy key
	$archives = array( 'archive', 'tag', 'category', 'taxonomy', 'author', 'date' );
	foreach ( $sidebars as $sidebar ) {

		// --- page contexts ---
		$i = 0;
		$newpages = array();
		foreach ( $contexts as $context ) {
			$postkey = 'fcs_' . $sidebar . '_pagetype_' . $context;
			if ( isset( $_POST[$postkey] ) && ( 'yes' == $_POST[$postkey] ) ) {
				$newpages[$i] = $context;
				$i++;
			}
		}
		$pageoptions = implode( ',', $newpages );
		$settings[$sidebar . '_sidebar_pages'] = $pageoptions;

		// --- archive contexts ---
		$i = 0;
		$newarchives = array();
		foreach ( $archives as $archive ) {
			$postkey = 'fcs_' . $sidebar . '_archive_' . $archive;
			if ( isset( $_POST[$postkey] ) && ( 'yes' == $_POST[$postkey] ) ) {
				$newarchives[$i] = $archive;
				$i++;
			}
		}
		$archiveoptions = implode( ',', $newarchives );
		$settings[$sidebar . '_sidebar_archives'] = $archiveoptions;
	}

	// 1.6.5: update last saved time
	$settings['last_saved'] = time();

	return $settings;
}

// --------------------
// Plugin Settings Page
// --------------------
function csidebars_settings_page() {

	global $csidebars;

	// --- open pagewrap div ---
	echo '<div id="pagewrap" class="wrap" style="width:100%;margin-right:0px !important;">';

	// Sidebar Floatbox
	// ----------------
	$args = array( 'content-sidebars', 'yes' ); // trimmed settings
	if ( function_exists( 'wqhelper_sidebar_floatbox' ) ) {
		wqhelper_sidebar_floatbox( $args );

		echo wqhelper_sidebar_stickykitscript();
		echo '<style>#floatdiv {float:right;}</style>';
		echo '<script>jQuery("#floatdiv").stick_in_parent();
		wrapwidth = jQuery("#pagewrap").width(); sidebarwidth = jQuery("#floatdiv").width();
		newwidth = wrapwidth - sidebarwidth;
		jQuery("#wrapbox").css("width",newwidth+"px");
		jQuery("#adminnoticebox").css("width",newwidth+"px");
		</script>';
	}

	// Admin Notices Boxer
	// -------------------
	if ( function_exists( 'wqhelper_admin_notice_boxer' ) ) {
		wqhelper_admin_notice_boxer();
	}

	// Plugin Admin Settings Header
	// ----------------------------
	csidebars_settings_header();

	// --- Plugin Page Scripts ---
	// 1.6.5: added reset to defaults function
	$reset_confirm = __( 'Are you sure you want to reset this plugin to default settings?', 'csidebars' );
	echo "<script>
	function loaddefaultcss() {document.getElementById('dynamiccss').value = document.getElementById('defaultcss').value;}
	function loadcssfile() {document.getElementById('dynamiccss').value = document.getElementById('cssfile').value;}
	function loadsavedcss() {document.getElementById('dynamiccss').value = document.getElementById('savedcss').value;}
	function resettodefaults() {
		message = '" . esc_js( $reset_confirm ) . "';
		agree = confirm(message); if (!agree) {return false;}
		document.getElementById('csidebars-update-action').value = 'reset';
		document.getElementById('csidebars-update-form').submit();
	}</script>";

	// --- plugin page style fixes ---
	echo "<style>.small {font-size:9pt;} .wp-admin select.select {height:24px; line-height:22px; margin-top:-5px;</style>";

	// --- set fallback options ---
	$fallbackoptions = array(
		'output'	=> __( 'Output', 'csidebars' ),
		'hidden'	=> __( 'Hide','bioship' ),
		'fallback'	=> __( 'Fallback', 'csidebars' ),
		'nooutput'	=> __( 'No Output', 'bioship' ),
	);

	// --- get post types ---
	$cpts = array( 'post', 'page' );
	$args = array( 'public' => true, '_builtin' => false );
	$cptlist = get_post_types( $args, 'names', 'and' );
	$cpts = array_merge( $cpts, $cptlist );

	// 1.4.5: add page context options
	$contexts = array(
		'frontpage' => __('Front Page','csidebars'), 
		'home' => __('Blog Page','csidebars'),
		'404' => __('404 Pages','csidebars'), 
		'search' => __('Search Pages','csidebars')
	);
	$archives = array(
		'archive' => __('ALL','csidebars'), 
		'tag' => __('Tag','csidebars'),
		'category' => __('Category','csidebars'), 
		'taxonomy' => __('Taxonomy','csidebars'),
		'author' => __('Author','csidebars'), 
		'date' => __('Date','csidebars') 
	);

	// Settings Update Form
	// --------------------
	// 1.5.0: add nonce field
	echo '<h3>' . esc_html( __( 'Extra Sidebars', 'csidebars' ) ) . '</h3>';
	echo '<form method="post" id="csidebars-update-form">';
	// 1.6.8: fix to update settings and nonce
	// echo '<input type="hidden" name="fcs_update_options" id="csidebars-update-action" value="yes">';
	echo '<input type="hidden" name="csidebars_update_settings" id="csidebars-update-action" value="yes">';
	wp_nonce_field( 'content-sidebars_update_settings' );

	// --- Above/Below Method ---
	echo '<table><tr><td>';
		echo '<b>' . esc_html( __( 'Positioning Mode', 'csidebars' ) ) . '</b>';
	echo '</td><td></td><td colspan="2">';
		echo '<input type="radio" name="fcs_abovebelow_method" value="hooks"';
		if ( 'hooks' == csidebars_get_setting( 'abovebelow_method', false ) ) {
			echo ' checked="checked"';
		}
		echo '> ' . esc_html( __( 'Use Template Action Hooks', 'csidebars' ) );
	echo '</td><td colspan="4">';
		echo '<input type="radio" name="fcs_abovebelow_method" value="filter"';
		if ( 'filter' == csidebars_get_setting( 'abovebelow_method', false ) ) {
			echo ' checked="checked"';
		}
		echo '> ' . esc_html( __( 'Use Content Filter', 'csidebars' ) );
	echo '</td></tr><tr><td colspan="10">';
		echo esc_html( __( 'Note: Content Filter mode cannot account for the post title which is (usually) above', 'csidebars' ) );
		echo ' the_content!<br>';
		echo esc_html( __( 'So if you want a sidebar above the title you will need to use Template Hooks', 'csidebars' ) );
		echo ' (see readme.txt FAQ)';
	echo '</td></tr><tr height="20"><td> </td></tr>';

	// --- Above Content Sidebar ---
	echo '<tr><td>';
		echo '<b>' . esc_html( __( 'Above Content Sidebar', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td class="small">';
		echo esc_html( __( 'Hook', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_abovecontent_hook" size="20" value="' . esc_attr( csidebars_get_setting( 'abovecontent_hook', false ) ) . '">';
	echo '</td><td class="small">';
		echo esc_html(  __('Priority', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_abovecontent_priority" size="2" style="width:35px;" value="' . esc_attr( csidebars_get_setting( 'abovecontent_priority', false ) ) . '">';
	echo '</td><td class="small">';
		echo esc_html(  __( 'Logged In', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<select name="fcs_abovecontent_fallback" class="select">';
		$fallback = csidebars_get_setting( 'abovecontent_fallback', false );
		foreach ( $fallbackoptions as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( $fallback == $key ) {
				echo ' selected="selected"';
			}
			echo '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	echo '</td></tr>';

	// --- disable above content sidebar ---
	// 1.6.2: added missing translation wrapper
	echo '<tr><td align="center" style="vertical-align:top;">';
		echo '<div style="text-align:right;">';
			echo esc_html( __( 'Output Sidebar for', 'csidebars' ) ) . ': ';
		echo '</div>';
		// 1.6.7: fix to remove orphaned table cell
		echo '<table style="margin-top:20px;"><tr><td class="small">';
			echo esc_html(  __('Disable', 'csidebars' ) ) . ': ';
		echo '</td><td>';
			echo '<input type="checkbox" name="fcs_abovecontent_disable" value="yes"';
			if ( 'yes' == csidebars_get_setting( 'abovecontent_disable', false ) ) {
				echo ' checked="checked"';
			}
			echo '>';
		echo'</td></tr></table>';
	echo '</td><td width="10"></td>';

	echo '<td align="left" colspan="6" class="small">';

		// --- post type selection for above content sidebars ---
		$getcpts = csidebars_get_setting( 'abovecontent_sidebar_cpts', false );
		if ( strstr( $getcpts, ',' ) ) {
			$abovecpts = explode( ',', $getcpts );
		} else {
			$abovecpts = array( $getcpts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 10px 0 0;">';
				echo esc_html( __( 'Singular', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ($cpts as $cpt) {
				// 1.6.2: use post type object label
				$posttypeobject = get_post_type_object($cpt);
				$posttypedisplay = $posttypeobject->labels->singular_name;
				echo '<li style="display:inline-block; margin:0 10px;">';
					echo '<input type="checkbox" name="fcs_abovecontent_posttype_' . esc_attr( $cpt ) . '" value="yes"';
					if ( in_array( $cpt, $abovecpts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $posttypedisplay );
				echo '</li>';
			}
		echo '</ul>';

		// --- archive type selection for above content sidebar ---
		$getarchives = csidebars_get_setting( 'abovecontent_sidebar_archives', false );
		if ( strstr( $getarchives, ',' ) ) {
			$archivecontexts = explode( ',', $getarchives );
		} else {
			$archivecontexts = array( $getarchives );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
				echo esc_html( __( 'Archives', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $archives as $archive => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_abovecontent_archive_' . esc_attr( $archive ) . '" value="yes"';
					if ( in_array( $archive, $archivecontexts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $label );
			echo '</li>';
		}
		echo '</ul>';

		// --- context type selection for above content sidebar ---
		$getcontexts = csidebars_get_setting( 'abovecontent_sidebar_pages', false );
		if ( strstr( $getcontexts, ',' ) ) {
			$pagecontexts = explode( ',', $getcontexts );
		} else {
			$pagecontexts = array( $getcontexts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
			echo esc_html( __( 'Special', 'csidebars' ) ) . ': </li>';
			foreach ( $contexts as $context => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_abovecontent_pagetype_' . esc_attr( $context ) . '" value="yes"';
					if ( in_array( $context, $pagecontexts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $label );
				echo '</li>';
			}
		echo '</ul><br>';

	echo '</td></tr>';

	// --- Below Content Sidebar ---
	echo '<tr><td>';
		echo '<b>' . esc_html( __( 'Below Content Sidebar', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td class="small">';
		echo esc_html( __( 'Hook', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_belowcontent_hook" size="20" value="' . esc_attr( csidebars_get_setting( 'belowcontent_hook', false ) ) . '">';
	echo '</td><td class="small">';
		echo esc_html( __( 'Priority', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_belowcontent_priority" size="2" style="width:35px;" value="' . esc_attr( csidebars_get_setting('belowcontent_priority', false ) ) . '">';
	echo '</td><td class="small">';
		echo esc_html( __( 'Logged In', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<select name="fcs_belowcontent_fallback" class="select">';
		$fallback = csidebars_get_setting( 'belowcontent_fallback', false );
		foreach ( $fallbackoptions as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( $fallback == $key ) {
				echo ' selected="selected"';
			}
			echo '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	echo '</td></tr>';

	// --- disable below content sidebar ---
	// 1.6.2: added missing translation wrapper
	echo '<tr><td align="center" style="vertical-align:top;">';
		echo '<div style="text-align:right;">';
			echo esc_html( __( 'Output Sidebar for', 'csidebars' ) ) . ':';
		echo '</div>';
		echo '<table style="margin-top:20px;"><tr>';
			// 1.6.7: fix to remove orphaned table cell
			echo '<td class="small">';
				echo esc_html( __( 'Disable', 'csidebars' ) ) . ': ';
			echo '</td><td>';
				echo '<input type="checkbox" name="fcs_belowcontent_disable" value="yes"';
				if ( 'yes' == csidebars_get_setting( 'belowcontent_disable', false ) ) {
					echo ' checked="checked"';
				}
			echo '>';
		echo '</td></tr></table>';
	echo '</td><td width="10"></td>';

	echo '<td align="left" colspan="6" class="small">';

		// --- post type selection for below content sidebar ---
		$getcpts = csidebars_get_setting( 'belowcontent_sidebar_cpts', false );
		if ( strstr( $getcpts, ',' ) ) {
			$belowcpts = explode( ',', $getcpts );
		} else {
			$belowcpts = array( $getcpts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 10px 0 0;">';
				echo esc_html( __( 'Singular', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $cpts as $cpt ) {
				// 1.6.2: use post type object label
				$posttypeobject = get_post_type_object( $cpt );
				$posttypedisplay = $posttypeobject->labels->singular_name;
				echo '<li style="display:inline-block; margin:0 10px;">';
					echo '<input type="checkbox" name="fcs_belowcontent_posttype_' . esc_attr( $cpt ) . '" value="yes"';
					if ( in_array( $cpt, $belowcpts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $posttypedisplay );
				echo '</li>';
			}
		echo '</ul>';

		// --- archive type selection for below content sidebar ---
		$getarchives = csidebars_get_setting( 'belowcontent_sidebar_archives', false );
		if ( strstr( $getarchives, ',' ) ) {
			$archivecontexts = explode( ',', $getarchives );
		} else {
			$archivecontexts = array( $getarchives );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
				echo esc_html( __( 'Archives', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $archives as $archive => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_belowcontent_archive_' . esc_attr( $archive ) . '" value="yes"';
					if ( in_array( $archive, $archivecontexts ) ) {
						echo ' checked="checked"';
					}
				echo '>' . esc_html( $label ) . '</li>';
			}
		echo '</ul>';

		// --- context type selection for below content sidebar ---
		$getcontexts = csidebars_get_setting( 'belowcontent_sidebar_pages', false );
		if ( strstr( $getcontexts, ',' ) ) {
			$pagecontexts = explode( ',', $getcontexts );
		} else {
			$pagecontexts = array( $getcontexts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
			echo esc_html( __( 'Special', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $contexts as $context => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_belowcontent_pagetype_' . esc_attr( $context ) . '" value="yes"';
					if ( in_array( $context, $pagecontexts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $label );
				echo '</li>';
			}
		echo '</ul><br>';

	echo '</td></tr>';

	// --- Login Sidebar ---
	// 1.4.5: removed unneeded output and hide options for login sidebar
	// 1.6.7: fix to incorrect translation text domain
	$fallbackoptions = array(
		'fallback' => __( 'Fallback','csidebars' ),
		'nooutput' => __( 'No Output','csidebars' ),
	);
	echo '<tr><td>';
		echo '<b>' . esc_html( __( 'Login Sidebar', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td class="small">';
		echo esc_html( __( 'Hook', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_loginsidebar_hook" size="20" value="' . esc_attr( csidebars_get_setting( 'loginsidebar_hook', false ) ) . '">';
	echo '</td><td class="small">';
		echo esc_html( __( 'Priority', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_loginsidebar_priority" size="2" style="width:35px;" value="' . esc_attr( csidebars_get_setting('loginsidebar_priority', false ) ) . '">';
	echo '</td><td class="small">';
		echo esc_html( __( 'Logged In', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<select name="fcs_loginsidebar_fallback" class="select">';
		$fallback = csidebars_get_setting( 'loginsidebar_fallback', false );
		foreach ( $fallbackoptions as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( $fallback == $key ) {
				echo ' selected="selected"';
			}
			echo '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	echo '</td></tr>';

	// --- disable login sidebar ---
	// 1.6.2: added missing translation wrapper
	echo '<tr><td align="center" style="vertical-align:top;">';
		echo '<div style="text-align:right;">';
			echo esc_html( __( 'Output Sidebar for', 'csidebars' ) ) . ':';
		echo '</div>';
		echo '<table style="margin-top:20px;"><tr><td><td class="small">';
			echo esc_html( __( 'Disable', 'csidebars' ) ) . ': ';
		echo '</td><td>';
			echo '<input type="checkbox" name="fcs_loginsidebar_disable" value="yes"';
			if ( 'yes' == csidebars_get_setting( 'loginsidebar_disable', false ) ) {
				echo ' checked="checked"';
			}
			echo '>';
		echo '</td></tr></table>';
	echo '</td><td width="10"></td>';

	echo '<td align="left" colspan="6" class="small">';

		// --- post type selection for login sidebar ---
		$getcpts = csidebars_get_setting( 'login_sidebar_cpts', false );
		if ( strstr( $getcpts, ',' ) ) {
			$logincpts = explode( ',', $getcpts );
		} else {
			$logincpts = array( $getcpts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 10px 0 0;">';
				echo esc_html( __( 'Singular', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ($cpts as $cpt) {
				// 1.6.2: use post type object label
				$posttypeobject = get_post_type_object( $cpt );
				$posttypedisplay = $posttypeobject->labels->singular_name;
				echo '<li style="display:inline-block; margin:0 10px;">';
					echo '<input type="checkbox" name="fcs_login_posttype_' . esc_attr( $cpt ) . '" value="yes"';
					if ( in_array( $cpt, $logincpts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $posttypedisplay );
				echo '</li>';
		}
		echo '</ul>';

		// --- archive type selection for login sidebar ---
		$getarchives = csidebars_get_setting( 'login_sidebar_archives', false );
		if ( strstr( $getarchives, ',' ) ) {
			$archivecontexts = explode( ',', $getarchives );
		} else {
			$archivecontexts = array( $getarchives );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
				echo esc_html( __( 'Archives', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $archives as $archive => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_login_archive_' . esc_attr( $archive ) . '" value="yes"';
					if ( in_array( $archive, $archivecontexts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $label );
				echo '</li>';
			}
		echo '</ul>';

		// --- context type selection for login sidebar ---
		$getcontexts = csidebars_get_setting( 'login_sidebar_pages', false );
		if ( strstr( $getcontexts, ',' ) ) {
			$pagecontexts = explode( ',', $getcontexts );
		} else {
			$pagecontexts = array( $getcontexts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
				echo esc_html( __( 'Special', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $contexts as $context => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_login_pagetype_' . esc_attr( $context ) . '" value="yes"';
					if ( in_array( $context, $pagecontexts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $label );
				echo '</li>';
			}
		echo '</ul><br>';

	echo '</td></tr>';

	// --- Member/Logged In Sidebar ---
	echo '<tr><td>';
		echo '<b>' . esc_html( __( 'Logged In Sidebar', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td class="small">';
		echo esc_html( __( 'Hook', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_membersidebar_hook" size="20" value="' . esc_attr( csidebars_get_setting( 'membersidebar_hook', false ) ) . '">';
	echo '</td><td class="small">';
		echo esc_html( __( 'Priority', 'csidebars' ) ) . ': ';
	echo '</td><td>';
		echo '<input type="text" class="small" name="fcs_membersidebar_priority" size="2" style="width:35px;" value="' . esc_attr( csidebars_get_setting( 'membersidebar_priority', false ) ) . '">';
	echo '</td><td>';
		echo esc_html( __( 'Mode', 'csidebars' ) ) . ': ';
	echo '</td><td>';
	// 1.4.5: added member sidebar mode selection
		echo '<select name="fcs_membersidebar_mode" class="select">';
		$fallback = csidebars_get_setting( 'membersidebar_mode', false );
		$fallbackoptions = array(
			'fallback'		=> __( 'Fallback', 'csidebars' ),
			'standalone'	=> __( 'Standalone', 'csidebars' ),
			'both'			=> __( 'Both', 'csidebars' ),
		);
		foreach ( $fallbackoptions as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( $fallback == $key ) {
				echo ' selected="selected"';
			}
			echo '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	echo '</td></tr>';

	// --- disable logged in sidebar ---
	// 1.6.2: added missing translation wrapper
	echo '<tr><td align="center" style="vertical-align:top;">';
		echo '<div style="text-align:right;">';
			echo esc_html(  __( 'Output Sidebar for', 'csidebars' ) ) . ':';
		echo '</div>';
		// 1.6.7: fix to remove orphaned table call
		echo '<table style="margin-top:20px;"><tr><td class="small">';
			echo esc_html( __( 'Disable', 'csidebars' ) ) . ': ';
			echo '</td><td>';
			echo '<input type="checkbox" name="fcs_membersidebar_disable" value="yes"';
			if ( 'yes' == csidebars_get_setting( 'membersidebar_disable', false ) ) {
				echo ' checked="checked"';
			}
			echo '>';
		echo '</td></tr></table>';
	echo '</td><td width="10"></td>';

	echo '<td align="left" colspan="6" class="small">';

		// --- post type selection for member sidebar ---
		$getcpts = csidebars_get_setting( 'member_sidebar_cpts', false );
		if ( strstr( $getcpts, ',' ) ) {
			$membercpts = explode( ',', $getcpts );
		} else {
			$membercpts = array( $getcpts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 10px 0 0;">';
				echo esc_html( __( 'Singular', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $cpts as $cpt ) {
				// 1.6.2: use post type object label
				$posttypeobject = get_post_type_object( $cpt );
				$posttypedisplay = $posttypeobject->labels->singular_name;
				echo '<li style="display:inline-block; margin:0 10px;">';
					echo '<input type="checkbox" name="fcs_member_posttype_' . esc_attr( $cpt ) . '" value="yes"';
					if ( in_array( $cpt, $membercpts ) ) {
						echo ' checked="checked"';
					}
				echo '>'. esc_html( $posttypedisplay );
				echo '</li>';
			}
		echo '</ul>';

		// --- archive type selection for member sidebar ---
		$getarchives = csidebars_get_setting( 'member_sidebar_archives', false );
		if ( strstr( $getarchives, ',' ) ) {
			$archivecontexts = explode( ',', $getarchives );
		} else {
			$archivecontexts = array( $getarchives );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
				echo esc_html( __( 'Archives', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $archives as $archive => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_member_archive_' . esc_attr( $archive ) . '" value="yes"';
					if ( in_array( $archive, $archivecontexts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $label );
				echo '</li>';
			}
		echo '</ul>';

		// --- context type selection for member sidebar ---
		$getcontexts = csidebars_get_setting( 'member_sidebar_pages', false );
		if ( strstr( $getcontexts, ',' ) ) {
			$pagecontexts = explode( ',', $getcontexts );
		} else {
			$pagecontexts = array( $getcontexts );
		}
		echo '<ul style="margin:5px 0;">';
			echo '<li style="display:inline-block; margin:0 5px 0 0;">';
				echo esc_html( __( 'Special', 'csidebars' ) ) . ': ';
			echo '</li>';
			foreach ( $contexts as $context => $label ) {
				echo '<li style="display:inline-block; margin:0 5px;">';
					echo '<input type="checkbox" name="fcs_member_pagetype_' . esc_attr( $context ) . '" value="yes"';
					if ( in_array( $context, $pagecontexts ) ) {
						echo ' checked="checked"';
					}
				echo '>' . esc_html( $label );
			echo '</li>';
		}
		echo '</ul>';

	echo '</td></tr>';

	echo '</table>';
	echo '(' . esc_html( __( 'Sidebar with Fallbacks show Logged In Sidebar instead for Logged In Users, eg. Members Area Links.', 'csidebars' ) ) . ')<br><br>';

	// --- Shortcode Options ---
	// 1.3.5: add options for widget text/title shortcodes
	// 1.4.5: added option for shortcodes in excerpts
	echo '<h3>' . esc_html( __( 'Shortcode Processing', 'csidebars' ) ) . '</h3>';
	echo '<table><tr><td>';
		echo '<b>' . esc_html( __( 'Process Shortcodes in Widget Text', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td>';
		echo '<input type="checkbox" name="fcs_widget_text_shortcodes" value="yes"';
		if ( 'yes' == csidebars_get_setting( 'widget_text_shortcodes', false ) ) {
			echo ' checked="checked"';
		}
		echo '>';
	echo '</td><td width="30"></td><td>';
		echo '<b>' . esc_html( __( 'Process Shortcodes in Excerpts', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td>';
		echo '<input type="checkbox" name="fcs_excerpt_shortcodes" value="yes"';
		if ( 'yes' == csidebars_get_setting( 'excerpt_shortcodes', false ) ) {
			echo ' checked="checked"';
		}
		echo '>';
	echo '</td></tr><tr><td>';
		echo '<b>' . esc_html( __( 'Process Shortcodes in Widget Titles', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td>';
	echo '<input type="checkbox" name="fcs_widget_title_shortcodes" value="yes"';
		if ( 'yes' == csidebars_get_setting( 'widget_title_shortcodes', false ) ) {
			echo ' checked="checked"';
		}
		echo '>';
	echo '</td><td width="30"></td><td>';
		echo '<b>' . esc_html( __( 'Shortcode Sidebars in Excerpts', 'csidebars' ) ) . '</b>';
	echo '</td><td width="10"></td><td>';
		echo '<input type="checkbox" name="fcs_sidebars_in_excerpts" value="yes"';
		if ( 'yes' == csidebars_get_setting( 'sidebars_in_excerpts', false ) ) {
			echo ' checked="checked"';
		}
		echo '>';
	echo '</td></tr></table><br>';

	// --- Shortcode Sidebars ---
	echo '<h3>' . esc_html( __( 'Shortcode Sidebars', 'csidebars' ) ) . '</h3>';

	echo '<table><tr><td>';
		echo '<b>' . esc_html( __( 'Sidebar', 'csidebars' ) ) . ' 1</b>';
	echo '</td><td width="10"></td><td>';
		echo '<input type="checkbox" name="fcs_shortcode1_disable" value="yes"';
		if ( 'yes' == csidebars_get_setting( 'shortcode1_disable', false ) ) {
			echo ' checked="checked"';
		}
		echo '>';
	echo '</td><td width="10">';
		echo esc_html( __( 'Disable', 'csidebars' ) );
	echo '</td><td width="40"></td><td>';
		echo '<b>' . esc_html( __( 'Sidebar', 'csidebars' ) ) . ' 2</b>';
	echo '</td><td width="10"></td><td>';
		echo '<input type="checkbox" name="fcs_shortcode2_disable" value="yes"';
		if ( 'yes' == csidebars_get_setting( 'shortcode2_disable', false ) ) {
			echo ' checked="checked"';
		}
		echo '>';
	echo '</td><td width="10">';
		echo esc_html( __( 'Disable', 'csidebars' ) );
	echo '</td><td width="40"></td><td>';
		echo '<b>' . esc_html( __( 'Sidebar', 'csidebars' ) ) . ' 3</b>';
	echo '</td><td width="10"></td><td>';
		echo '<input type="checkbox" name="fcs_shortcode3_disable" value="yes"';
		if ( 'yes' == csidebars_get_setting( 'shortcode3_disable', false ) ) {
			echo ' checked="checked"';
		}
		echo '>';
	echo '</td><td width="10">';
		echo esc_html( __( 'Disable', 'csidebars' ) );
	echo '</td></tr>';

	echo '<tr><td colspan="4">';
		echo '[shortcode-sidebar-1]';
	echo '</td><td></td><td colspan="4">';
		echo '[shortcode-sidebar-2]';
	echo '</td><td></td><td colspan="4">';
		echo '[shortcode-sidebar-3]';
	echo '</td></tr></table><br>';

	// --- InPost Sidebars ---
	echo '<h3>' . esc_html( __( 'InPost Sidebars', 'csidebars' ) ) . '</h3>';

	echo '<table>';
		echo '<tr><td>';
			echo '<b>' . esc_html( __( 'Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 1 ) . '</b>';
		echo '</td><td width="10"></td><td>';
			echo '<input type="checkbox" name="fcs_inpost1_disable" value="yes"';
			if ( 'yes' == csidebars_get_setting( 'inpost1_disable', false ) ) {
				echo ' checked="checked"';
			}
			echo '>';
		echo '</td><td width="10">';
			echo esc_html( __( 'Disable', 'csidebars' ) );
		echo '</td><td width="40"></td><td>';
			echo '<b>' . esc_html( __( 'Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 2 ) . '</b>';
		echo '</td><td width="10"></td><td>';
			echo '<input type="checkbox" name="fcs_inpost2_disable" value="yes"';
			if ( 'yes' == csidebars_get_setting( 'inpost2_disable', false ) ) {
				echo ' checked="checked"';
			}
			echo '>';
		echo '</td><td width="10">';
			echo esc_html( __( 'Disable', 'csidebars' ) );
		echo '</td><td width="40"></td><td>';
			echo '<b>' . esc_html( __( 'Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 3 ) . '</b>';
		echo '</td><td width="10"></td><td>';
			echo '<input type="checkbox" name="fcs_inpost3_disable" value="yes"';
			if ( 'yes' == csidebars_get_setting( 'inpost3_disable', false ) ) {
				echo ' checked="checked"';
			}
			echo '>';
		echo '</td><td width="10">';
			echo esc_html( __( 'Disable', 'csidebars' ) );
		echo '</td></tr>';
	echo '</table>';

	// --- get inpost CPT settings ---
	$cptsettings = csidebars_get_setting( 'inpost_sidebars_cpts', false );
	if ( strstr( $cptsettings, ',' ) ) {
		$inpostcpts = explode( ',', $cptsettings );
	} else {
		$inpostcpts = array( $cptsettings );
	}

	// --- post types for inpost sidebars ---
	echo '<table>';
		echo '<tr><td>';
			echo esc_html( __( 'Activate for Post Types', 'csidebars' ) ) . ':';
		echo '</td><td width="10"></td><td colspan="5">';
		if ( count( $cpts ) > 0 ) {
			echo '<ul>';
			foreach ( $cpts as $cpt ) {
				// 1.6.2: use post type object label
				$posttypeobject = get_post_type_object( $cpt );
				$posttypedisplay = $posttypeobject->labels->singular_name;
				echo '<li style="display:inline-block; margin:0 10px;">';
					echo '<input type="checkbox" name="fcs_inpost_posttype_' . esc_attr( $cpt ) . '" value="yes"';
					if ( in_array( $cpt, $inpostcpts ) ) {
						echo ' checked="checked"';
					}
					echo '>' . esc_html( $posttypedisplay );
				echo '</li>';
			}
			echo '</ul>';
		}
		echo '</td></tr>';

		// --- paragraph marker and priority ---
		echo '<tr><td>';
			echo esc_html( __( 'Paragraph Split Marker', 'csidebars' ) ) . ':';
		echo '</td><td width="10"></td><td>';
			echo '<input type="text" size="15" style="width:110px;" name="fcs_inpost_marker" value="' . esc_attr( csidebars_get_setting( 'inpost_marker', false ) ) . '">';
		echo '</td><td colspan="3">';
			echo '(' . esc_html( __( 'Used to count and split paragraphs.', 'csidebars' ) ) . ')';
		echo '</td></tr><tr><td>';
			echo 'the_content ' . esc_html( __( 'Filter Priority', 'csidebars' ) ) . ':';
		echo '<td width="10"></td><td>';
			echo '<input type="text" size="3" style="width:40px;" name="fcs_inpost_priority" value="' . esc_attr( csidebars_get_setting( 'inpost_priority', false ) ) . '">';
		echo '</td><td colspan="3">';
			echo '(' . esc_html( __( 'Filter positioning method only.', 'csidebars' ) ) . ')';
		echo '</td></tr>';
	echo '</table>';

	// --- paragraph count settings ---
	echo '<table><tr><td style="vertical-align:top;">';
		echo '<table><tr height="30"><td>';
			echo esc_html( __( 'Insert Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 1 ) . ' ' . esc_html( __( 'After Paragraph', 'csidebars' ) ) . ' #';
		echo '</td><td width="30"></td><td>';
			echo '<input type="text" size="2" style="width:30px;" name="fcs_inpost_positiona" value="' . esc_attr( csidebars_get_setting( 'inpost_positiona', false ) ) . '">';
		echo '</td></tr><tr height="5"><td> </td></tr><tr height="30"><td>';
			echo esc_html( __( 'Insert Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 2 ) . ' ' . esc_html( __( 'After Paragraph', 'csidebars' ) ) . ' #';
		echo '</td><td width="30"></td><td>';
			echo '<input type="text" size="2" style="width:30px;" name="fcs_inpost_positionb" value="' . esc_attr( csidebars_get_setting( 'inpost_positionb', false ) ) . '">';
		echo '</td></tr><tr height="5"><td> </td></tr><tr height="30"><td>';
			echo esc_html( __( 'Insert Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 3 ) . ' ' . esc_html( __( 'After Paragraph', 'csidebars' ) ) . ' #';
		echo '</td><td width="30"></td><td>';
			echo '<td><input type="text" size="2" style="width:30px;" name="fcs_inpost_positionc" value="' . esc_attr( csidebars_get_setting( 'inpost_positionc', false ) ) . '">';
		echo '</td></tr></table>';
	echo '</td><td width="20"></td><td style="vertical-align:top;">';

	$floatoptions = array(
		''		=> __( 'Do Not Set', 'csidebars' ),
		'none'	=> __( 'None', 'csidebars' ),
		'left'	=> __( 'Left', 'csidebars' ),
		'right'	=> __( 'Right', 'csidebars' ),
	);

	echo '<table><tr height="30"><td>';
		echo esc_html( __( 'Float Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 1 ) . ': ';
	echo '</td><td width="10"></td><td>';
		echo '<select name="fcs_inpost1_float">';
			$float = csidebars_get_setting( 'inpost1_float', false );
			foreach ( $floatoptions as $key => $label ) {
			 	echo '<option value="' . esc_attr( $key ) . '"';
				if ( $float == $key ) {
					echo ' selected="selected"';
				}
				echo '>' . esc_html( $label ) . '</option>';
			}
		echo '</select>';
	echo '</td></tr><tr height="5"><td> </td></tr><tr height="30"><td>';
		echo esc_html( __( 'Float Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 2 ) . ': ';
	echo '</td><td width="10"></td><td>';
		echo '<select name="fcs_inpost2_float">';
		$float = csidebars_get_setting( 'inpost2_float', false );
		foreach ( $floatoptions as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( $float == $key ) {
				echo ' selected="selected"';
			}
			echo '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	echo '</td></tr><tr height="5"><td> </td></tr><tr height="30"><td>';
		echo esc_html( __( 'Float Sidebar', 'csidebars' ) ) . ' ' . number_format_i18n( 3 ) . ': ';
	echo '</td><td width="10"></td><td>';
		echo '<select name="fcs_inpost3_float">';
		$float = csidebars_get_setting( 'inpost3_float', false );
		foreach ( $floatoptions as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( $float == $key ) {
				echo ' selected="selected"';
			}
			echo '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	echo '</td></tr></table>';

	echo '</td></tr><tr height="20"><td></td></tr>';

	// --- CSS Styles ---
	// 1.5.0: added direct URL loading as new default
	// 1.5.6: added file write method (to content-sidebars.css)
	echo '<tr><td>';
		echo '<h3>' . esc_html( __( 'CSS Styles', 'csidebars' ) ) . '</h3>';
	echo '</td></tr>';
	$defaultcss = file_get_contents( dirname( __FILE__ ) . '/content-default.css');
	$cssfile = file_get_contents( dirname( __FILE__ ) . '/content-sidebars.css');
	$savedcss = csidebars_get_setting( 'dynamic_css', false );
	$cssmode = csidebars_get_setting( 'css_mode', false );

	// 1.5.8: remove direct dynamic PHP to CSS method
	if ( ( 'dynamic' == $cssmode ) || ( 'direct' == $cssmode ) ) {
		$cssmode = 'write';
	}
	echo '<tr><td style="vertical-align:top;">';
		echo '<b>' . esc_html( __( 'CSS Mode', 'csidebars' ) ) . '</b>';
	echo '</td></tr><tr><td colspan="3">';
		echo '<table>';
			echo '<td align="center">';
				echo '<input type="radio" name="fcs_css_mode" value="default"';
				if ( 'default' == $cssmode ) {
					echo ' checked="checked"';
				}
				echo '> ' . esc_html( __( 'Default', 'csidebars' ) ) . '<br>content-default.css';
			echo '</td><td width="20"></td><td align="center">';
				echo '<input type="radio" name="fcs_css_mode" value="adminajax"';
				if ( 'adminajax' == $cssmode ) {
					echo ' checked="checked"';
				}
				echo '> ' . esc_html( __( 'AJAX', 'csidebars' ) ) . ' <br>';
				echo esc_html( __( 'via', 'csidebars' ) ) . ' admin-ajax.php';
			echo '</td><td width="20"></td>';
				// 1.5.8: remove direct dynamic PHP to CSS method
				// echo "<td align='center'><input type='radio' name='fcs_css_mode' value='direct'";
				// if ($cssmode == 'direct') {echo " checked";}
				// echo "> ".__('Direct','csidebars')." <br>content-sidebars-css.php<td width='20'></td>";
			echo '<td align="center">';
				echo '<input type="radio" name="fcs_css_mode" value="write"';
				if ( 'write' == $cssmode ) {
					echo ' checked="checked"';
				}
				echo '> ' . esc_html( __( 'Write', 'csidebars' ) ) . ' <br>';
				echo esc_html( __( 'to', 'csidebars' ) ) . ' content-sidebars.css';
			echo '</td></tr>';
		echo '</table><br>';
	echo '</td></tr>';

	// --- Dynamic CSS Area ---
	echo '<tr><td colspan="3">';
		echo '<b>' . esc_html( __( 'Dynamic CSS', 'csidebars' ) ) . '</b>:<br>';
		echo '<textarea rows="7" cols="70" style="width:100%;" id="dynamiccss" name="fcs_dynamic_css">' . esc_textarea( $savedcss ) . '</textarea>';
	echo '</td></tr>';

	// --- Load Default / File / Saved CSS ---
	echo '<tr><td colspan="3">';
		echo '<table style="width:100%;">';
			echo '<tr><td align="left" style="width:33%;">';
				echo '<input type="button" class="button-secondary" style="font-size:9pt;" onclick="loaddefaultcss();" value="' . esc_attr( __( 'Load Default CSS', 'csidebars' ) ) . '">';
			echo '</td><td align="center" style="width:33%;">';
				echo '<input type="button" class="button-secondary" style="font-size:9pt;" onclick="loadcssfile();" value="' . esc_attr( __( 'Load CSS File', 'csidebars' ) ) . '">';
			echo '</td><td align="right" style="width:33%;">';
				echo '<input type="button" class="button-secondary" style="font-size:9pt;" onclick="loadsavedcss();" value="' . esc_attr( __( 'Reload Saved CSS', 'csidebars' ) ) . '">';
			echo '</td></tr>';
		echo '</table>';
	echo '</td></tr>';

	// --- reset and save settings buttons ---
	// 1.6.5: add reset to default settings button
	echo '<tr height="15"><td> </td></tr>';
	echo '<tr><td align="center">';
		echo '<input type="button" class="button-secondary" id="" value="' . esc_attr( __( 'Reset', 'csidebars' ) ) . '" onclick="resettodefaults();">';
	echo '</td><td></td><td align="center">';
		echo '<input type="submit" class="button-primary" id="plugin-settings-save" value="' . esc_attr( __( 'Save Settings', 'csidebars' ) ) . '">';
	echo '</td></tr>';

	echo '</table><br></form>';

	// --- Hidden CSS Textareas ---
	echo "<textarea id='defaultcss' style='display:none'>" . $defaultcss . "</textarea>";
	echo "<textarea id='cssfile' style='display:none'>" . $cssfile . "</textarea>";
	echo "<textarea id='savedcss' style='display:none'>" . esc_textarea( $savedcss ) . "</textarea>";

	echo "<br><h4>" . esc_html( __( 'CSS ID and Class Reference', 'csidebars' ) ) . ':</h4>';
	echo '<table cellpadding="5" cellspacing="5">';
		echo '<tr><td>';
			echo '<b>' . esc_html( __( 'Sidebar ID', 'csidebars' ) ) . '</b>';
		echo '</td><td>';
			echo '<b>' . esc_html( __( 'Sidebar Class', 'csidebars' ) ) . '</b>';
		echo '</td><td>';
			echo '<b>' . esc_html( __( 'Widget Class', 'csidebars' ) ) . '</b>';
		echo '</td><td>';
			echo '<b>' . esc_html( __( 'Widget Title Class', 'csidebars' ) ) . '</b>';
		echo '</td></tr>';
		echo '<tr><td>#abovecontentsidebar</td><td>.contentsidebar</td><td>.abovecontentwidget</td><td>.abovecontenttitle</td></tr>';
		echo '<tr><td>#belowcontentsidebar</td><td>.contentsidebar</td><td>.belowcontentwidget</td><td>.belowcontenttitle</td></tr>';
		echo '<tr><td>#loginsidebar</td><td>.contentsidebar</td><td>.loginwidget</td><td>.loginwidgettitle</td></tr>';
		echo '<tr><td>* .loggedinsidebar</td><td>.contentsidebar</td><td>.loggedinwidget</td><td>.loggedinwidgettitle</td></tr>';
		echo '<tr><td>#membersidebar</td><td>.contentsidebar</td><td>.loggedinwidget</td><td>.loggedinwidgettitle</td></tr>';
		echo '<tr><td>#shortcodesidebar1</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>';
		echo '<tr><td>#shortcodesidebar2</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>';
		echo '<tr><td>#shortcodesidebar3</td><td>.shortcodesidebar</td><td>.shortcodewidget</td><td>.shortcodewidgettitle</td></tr>';
		echo '<tr><td>#inpostsidebar1</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>';
		echo '<tr><td>#inpostsidebar2</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>';
		echo '<tr><td>#inpostsidebar3</td><td>.inpostsidebar</td><td>.inpostwidget</td><td>.inpostwidgettitle</td></tr>';
	echo '</table>';

	echo '* ' . esc_html( __( 'if logged out, the class', 'csidebars' ) ) . ' .loggedoutsidebar ';
	echo esc_html( __( 'is also added to all sidebars on that page.', 'csidebars' ) ) . '<br>';
	echo '.loggedinsidebar ' . esc_html( __( 'class is added to the Above, Below or Login sidebars on fallback.', 'csidebars' ) ) . '<br>';

	// echo "Note: For individualized widget shortcodes you can use: <a href='http://wordpress.org/plugins/amr-shortcode-any-widget/' target=_blank>Shortcode Any Widget</a><br><br>";

	echo '</div></div>';

	echo '</div>';
}


// ----------------------
// === Plugin Helpers ===
// ----------------------
// 1.3.5: get global plugin options
// 1.6.5: removed global plugin settings here as handled by plugin loader
// global $csidebars; $csidebars = get_option('content_sidebars');


// -----------------
// Set Excerpt State
// -----------------
// 1.4.5: added to better handle excerpt output
global $csidebarsexcerpt;
$csidebarsexcerpt = false;
add_filter( 'get_the_excerpt', 'csidebars_doing_excerpt_on', 0 );
add_filter( 'get_the_excerpt', 'csidebars_doing_excerpt_off', 999 );
function csidebars_doing_excerpt_on( $excerpt ) {
	global $csidebarsexcerpt;
	$csidebarsexcerpt = true;
	return $excerpt;
}
function csidebars_doing_excerpt_off( $excerpt ) {
	global $csidebarsexcerpt;
	$csidebarsexcerpt = false;
	return $excerpt;
}

// ---------------
// Set Login State
// ---------------
// 1.3.5: set login state once for efficiency
global $csidebarsstate;
add_action( 'init', 'csidebars_set_login_state' );
function csidebars_set_login_state() {
	global $csidebarsstate;
	// 1.6.7: simplify to use is_user_logged_in
	// $current_user = wp_get_current_user();
	// if ( $current_user->exists() ) { 
	if ( is_user_logged_in() ) {
		$csidebarsstate = 'loggedin';
	} else {
		$csidebarsstate = 'loggedout';
	}
}

// --------------------
// Set Pageload Context
// --------------------
// 1.4.5: added this once-off context checker
add_action( 'wp', 'csidebars_set_page_context' );
function csidebars_set_page_context() {
	global $csidebarscontext, $csidebarsarchive;
	$csidebarscontext = $csidebarsarchive = '';
	if ( is_front_page() ) {
		$csidebarscontext = 'frontpage';
	} elseif (is_home() ) {
		$csidebarscontext = 'home';
	} elseif ( is_404() ) {
		$csidebarscontext = '404';
	} elseif ( is_search() ) {
		$csidebarscontext = 'search';
	} elseif ( is_singular() ) {
		$csidebarscontext = 'singular';
	} elseif ( is_archive() ) {
		$csidebarscontext = 'archive';
		if ( is_tag() ) {
			$csidebarsarchive = 'tag';
		} elseif ( is_category() ) {
			$csidebarsarchive = 'category';
		} elseif ( is_tax() ) {
			$csidebarsarchive = 'taxonomy';
		} elseif ( is_author() ) {
			$csidebarsarchive = 'author';
		} elseif ( is_date() ) {
			$csidebarsarchive = 'date';
		}
	}
}

// ---------------------
// Get Sidebar Overrides
// ---------------------
add_action( 'init', 'csidebars_get_overrides' );
function csidebars_get_overrides() {
	global $post, $csidebarsoverrides;
	if ( is_object( $post ) ) {
		$postid = $post->ID;
		$csidebarsoverrides = get_post_meta( $postid, 'content_sidebars', true );

		// maybe set new key value, checking for existing disable metakeys
		if ( !$csidebarsoverrides ) {
			$optionkeys = array(
				'abovecontentsidebar',
				'belowcontentsidebar',
				'loginsidebar',
				'membersidebar',
				'shortcodesidebar1',
				'shortcodesidebar2',
				'shortcodesidebar3',
				'inpostsidebar1',
				'inpostsidebar2',
				'inpostsidebar3'
			);
			foreach ( $optionkeys as $optionkey ) {
				$newkey = str_replace( 'sidebar', '', $optionkey );
				if ( 'yes' == get_post_meta( $postid, '_disable' . $optionkey, true ) ) {
					$csidebarsoverrides[$newkey] = 'disable';
				} else {
					$csidebarsoverrides[$newkey] = '';
				}
			}
			add_post_meta( $postid, 'content_sidebars', $csidebarsoverrides, true );
		}
	}
}

// ------------------
// Get Sidebar Helper
// ------------------
function csidebars_get_sidebar( $sidebar ) {
	ob_start();
	dynamic_sidebar( $sidebar );
	$sidebarcontents = ob_get_contents();
	ob_end_clean();
	return $sidebarcontents;
}

// --------------------
// Check Context Helper
// --------------------
function csidebars_check_context( $disable, $sidebar ) {
	global $csidebarscontext, $csidebarsarchive;

	$disablein = $disable;
	if ( 'singular' == $csidebarscontext ) {
		// --- maybe disable if sidebar not active for this CPT ---
		global $post;
		$postid = $post->ID;
		$posttype = get_post_type($postid);
		$cptsettings = csidebars_get_setting( $sidebar . '_sidebar_cpts', true );
		if ( strstr( $cptsettings, ',' ) ) {
			$activecpts = explode(',', $cptsettings);
		} else {
			$activecpts = array( $cptsettings );
		}
	 	if ( !in_array( $posttype, $activecpts ) ) {
	 		$disable = 'yes';
	 	}
	 	$debug = 'Post Type: ' . $posttype . ' in ' . $cptsettings;
	} elseif ( 'archive' == $csidebarscontext ) {
		// --- maybe disable if sidebar not active for this archive ---
		$archiveoptions = csidebars_get_setting( $sidebar . '_sidebar_archives' );
		if ( strstr( $archiveoptions, ',' ) ) {
			$archives = explode( ',', $archiveoptions );
		} else {
			$archives = array( $archiveoptions );
		}
		if ( !in_array('archive', $archives ) ) {
			if ( !in_array( $csidebarsarchive, $archives ) ) {
				$disable = 'yes';
			}
		}
		$debug = 'Archive: ' . $csidebarsarchive . ' in ' . $archiveoptions;
	} elseif ( '' != $csidebarscontext ) {
		// --- maybe disable if sidebar not active for this context ---
		$pageoptions = csidebars_get_setting( $sidebar . '_sidebar_pages' );
		if (strstr($pageoptions, ',')) {
			$contexts = explode( ',', $pageoptions );
		} else {
			$contexts = array( $pageoptions );
		}
		if ( !in_array( $csidebarscontext, $contexts ) ) {
			$disable = 'yes';
		}
		$debug = 'Page Context: ' . $csidebarscontext . ' in ' . $pageoptions;
	} else {
		$disable = 'yes';
	}

	// --- debug point for disable change ---
	// if ( $disablein != $disable ) {
	//	echo "<!-- ".$sidebar." sidebar disabled (".$debug.") -->";
	// }

	return $disable;
}


// -------------------------
// === Register Sidebars ===
// -------------------------

// ------------------------
// Register Sidebars Helper
// ------------------------
// 1.3.5: added register sidebar abstract helper
function csidebars_register_sidebar( $settings ) {
	register_sidebar( array(
		'name' 			=> $settings['name'],
		'id'			=> sanitize_title( $settings['id'] ),
		'description'	=> $settings['description'],
		'class'			=> 'content-' . $settings['class'],
		'before_widget'	=> $settings['before_widget'],
		'after_widget'	=> $settings['after_widget'],
		'before_title'	=> $settings['before_title'],
		'after_title'	=> $settings['after_title'],
	) );
}

// -------------------------
// Register Content Sidebars
// -------------------------
// 1.3.5: use widgets_init action hook instead
// 1.4.0: declare active and inactive with different priorities
add_action( 'widgets_init', 'csidebars_register_active_sidebars', 11 );
add_action( 'widgets_init', 'csidebars_register_inactive_sidebars', 13 );
function csidebars_register_active_sidebars() {
	csidebars_register_dynamic_sidebars( true );
}
function csidebars_register_inactive_sidebars() {
	csidebars_register_dynamic_sidebars( false );
}

// 1.3.5: register all but split active and inactive sidebars
function csidebars_register_dynamic_sidebars( $active = true ) {

	$activesidebars = $inactivesidebars = array();

	if ( function_exists( 'register_sidebar' ) ) {

		// --- Above Content Sidebar ---
		$sidebar = array(
			'name'		=> __( 'Above Content','csidebars' ),
			'id'		=> 'AboveContent',
			'class'		=> 'on',
			'description'	=> __( 'Above Post Content','csidebars' ),
			'before_widget'	=> '<div class="abovecontentwidget"><li>',
			'after_widget'	=> '</li></div>',
			'before_title'	=> '<div class="abovecontenttitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'abovecontent_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- Below Content Sidebar ---
		$sidebar = array(
			'name'		=> __( 'Below Content', 'csidebars' ),
			'id'		=> 'BelowContent',
			'class'		=> 'on',
			'description'	=> __( 'Below Post Content', 'csidebars' ),
			'before_widget'	=> '<div class="belowcontentwidget"><li>',
			'after_widget'	=> '</li></div>',
			'before_title'	=> '<div class="belowcontenttitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'belowcontent_disable' ) ) {
			$sidebar['name'] = strtolower($sidebar['name']);
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- Login Sidebar ---
		$sidebar = array(
			'name'		=> __( 'Login Sidebar', 'csidebars' ),
			'id'		=> 'LoginSidebar',
			'class'		=> 'on',
			'description'	=> __( 'Shows to Logged Out Users', 'csidebars' ),
			'before_widget'	=> '<div class="loginwidget"><li>',
			'after_widget'	=> '</li></div>',
			'before_title'	=> '<div class="loginwidgettitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'loginsidebar_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- Member/Logged In Sidebar ---
		$sidebar = array(
			'name'		=> __( 'Logged In Sidebar', 'csidebars' ),
			'id'		=> 'LoggedInSidebar',
			'class'		=> 'on',
			'description'	=> __( 'Fallback Sidebar for Logged In Users', 'csidebars' ),
			'before_widget'	=> '<div class="loggedinwidget"><li>',
			'after_widget'	=> '</li></div>',
			'before_title'	=> '<div class="loggedinwidgettitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'membersidebar_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- Shortcode Sidebar 1 ---
		$sidebar = array(
			'name'		=> __( 'Shortcode Sidebar', 'csidebars' ) . ' 1',
			'id'		=> 'ShortcodeSidebar1',
			'class'		=> 'on',
			'description'	=> __( 'Display with', 'csidebars' ) . ' [shortcode-sidebar-1]',
			'before_widget'	=> '<div class="shortcodewidget"><li>',
			'after_widget'	=> '</li></div>',
			'before_title'	=> '<div class="shortcodewidgettitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'shortcode1_disable' ) ) {
			$sidebar['name'] = strtolower($sidebar['name']);
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- Shortcode Sidebar 2 ---
		$sidebar = array(
			'name'		=> __( 'Shortcode Sidebar', 'csidebars' ) . ' 2',
			'id'		=> 'ShortcodeSidebar2',
			'class'		=> 'on',
			'description'	=> __( 'Display with', 'csidebars' ) . ' [shortcode-sidebar-2]',
			'before_widget'	=> '<div class="shortcodewidget"><li>',
			'after_widget'	=> '</li></div>',
			'before_title'	=> '<div class="shortcodewidgetitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'shortcode2_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- Shortcode Sidebar 3 ---
		$sidebar = array(
			'name'		=> __( 'Shortcode Sidebar', 'csidebars' ) . ' 3',
			'id'		=> 'ShortcodeSidebar3',
			'class'		=> 'on',
			'description'	=> __( 'Display with', 'csidebars' ) . ' [shortcode-sidebar-3]',
			'before_widget'	=> '<div class="shortcodewidget"><li>',
			'after_widget'	=> '</li></div>',
			'before_title'	=> '<div class="shortcodewidgettitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'shortcode3_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- InPost Sidebar 1 ---
		$sidebar = array(
			'name'		=> __( 'InPost', 'csidebars' ) . ' 1',
			'id'		=> 'InPost1',
			'class'		=> 'on',
			'description'	=> __( 'Auto-spaced Contextual Sidebar', 'csidebars' ),
			'before_widget'	=> '<div class="inpostwidget">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<div class="inpostwidgettitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'inpost1_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- InPost Sidebar 2 ---
		$sidebar = array(
			'name'		=> __('InPost','csidebars').' 2',
			'id'		=> 'InPost2',
			'class'		=> 'on',
			'description'	=> __( 'Auto-spaced Contextual Sidebar', 'csidebars' ),
			'before_widget'	=> '<div class="inpostwidget">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<div class="inpostwidgettitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'inpost2_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- InPost Sidebar 3 ---
		$sidebar = array(
			'name'		=> __( 'InPost', 'csidebars' ) . ' 3',
			'id'		=> 'InPost3',
			'class'		=> 'on',
			'description'	=> __( 'Auto-spaced Contextual Sidebar', 'csidebars' ),
			'before_widget'	=> '<div class="inpostwidget">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<div class="inpostwidgettitle">',
			'after_title'	=> '</div>',
		);
		if ( 'yes' == csidebars_get_setting( 'inpost3_disable' ) ) {
			$sidebar['name'] = strtolower( $sidebar['name'] );
			$sidebar['class'] = 'off';
			$inactivesidebars[] = $sidebar;
		} else {
			$activesidebars[] = $sidebar;
		}

		// --- get all widgets ---
		// 1.6.5: only count sidebar widgets on widgets page
		global $pagenow;
		if ( is_admin() && ( 'widgets.php' == $pagenow ) ) {
			$allwidgets = wp_get_sidebars_widgets();
		}
		// print_r($allwidgets);

		// --- register active sidebars ---
		// 1.3.5: register active then inactive sidebars
		// 1.4.0: register with different priorities
		if ( $active && ( count($activesidebars) > 0 ) ) {
			foreach ( $activesidebars as $sidebar ) {
				// 1.4.0: add widget count to sidebar label
				// 1.6.5: only add widget count on widget.php page
				if ( is_admin() && ( 'widgets.php' == $pagenow ) && is_active_sidebar( $sidebar['id'] ) ) {
					$widgetcount = count( $allwidgets[strtolower( $sidebar['id'] )] );
					$sidebar['name'] .= ' (' . $widgetcount . ')';
				}
				csidebars_register_sidebar( $sidebar );
			}
		}

		// --- register inactive sidebars ---
		if ( !$active && ( count( $inactivesidebars) > 0 ) ) {
			foreach ( $inactivesidebars as $sidebar ) {
				// 1.4.0: add widget count to sidebar label
				// 1.6.5: only add widget count on widget.php page
				if ( is_admin() && ( 'widgets.php' == $pagenow ) && is_active_sidebar( $sidebar['id'] ) ) {
					$widgetcount = count( $allwidgets[strtolower( $sidebar['id'] )] );
					$sidebar['name'] .= ' (' . $widgetcount . ')';
				}
				csidebars_register_sidebar( $sidebar );
			}
		}

	}
}

// -----------------------------
// Register Discreet Text Widget
// -----------------------------
// 1.3.5: added this super-handy widget type
// ref: https://wordpress.org/plugins/hackadelic-discreet-text-widget/
// add_shortcode('test-shortcode', 'fcs_test_shortcode');
// function fcs_test_shortcode() {return '';}
add_action( 'widgets_init', 'csidebars_discreet_text_widget', 11 );
function csidebars_discreet_text_widget() {
	if ( !class_exists( 'DiscreetTextWidget' ) ) {
		class DiscreetTextWidget extends WP_Widget_Text {
			function __construct() {
				$widgetops = array(
					'classname'	=> 'discreet_text_widget',
					'description'	=> __( 'Arbitrary text or HTML, only shown if not empty.', 'csidebars' ),
				);
				$controlops = array( 'width' => 400, 'height' => 350 );
				// 1.4.0: fix to deprecated class construction method
				call_user_func( array( get_parent_class( get_parent_class( $this ) ), '__construct'), 'discrete_text', __( 'Discreet Text', 'csidebars' ), $widgetops, $controlops );
				// parent::__construct('discrete_text', __('Discreet Text','csidebars'), $widgetops, $controlops);
				// $this->WP_Widget('discrete_text', 'Discreet Text', $widgetops, $controlops);
			}
			function widget( $args, $instance ) {
				
				$text = apply_filters( 'widget_text', $instance['text'], $instance, $this );
				if ( empty( $text ) ) {return;}

				echo $args['before_widget'];
				$title = empty( $instance['title'] ) ? '' : $instance['title'];
				$title = apply_filters( 'widget_title', $title, $instance );
				if ( !empty( $title ) ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}
				echo '<div class="textwidget">';
					echo $instance['filter'] ? wpautop( $text ) : $text;
				echo '</div>';
				echo $args['after_widget'];
			}
		}
		return register_widget( 'DiscreetTextWidget' );
	}
}


// ------------------
// === Shortcodes ===
// ------------------

// -----------------
// Shortcode Filters
// -----------------
// 1.3.5: added these widget shortcode filter options
add_action( 'init','csidebars_process_shortcodes' );
function csidebars_process_shortcodes() {

	// --- widget text shortcodes ---
	// note: this may be unnecessary now
	// ref: https://core.trac.wordpress.org/changeset/41361
	if ( csidebars_get_setting( 'widget_text_shortcodes' ) ) {
		if ( !has_filter( 'widget_text', 'do_shortcode' )) {
			add_filter( 'widget_text', 'do_shortcode' );
		}
	}
	// --- widget title shortcodes ---
	if ( csidebars_get_setting( 'widget_title_shortcodes' ) ) {
		if ( !has_filter( 'widget_title', 'do_shortcode' ) ) {
			add_filter('widget_title', 'do_shortcode');
		}
	}
	// --- shortcodes in excerpts ---
	if ( csidebars_get_setting( 'excerpt_shortcodes' ) ) {
		// add_filter('wp_trim_excerpt','csidebars_excerpt_with_shortcodes');
		if ( has_filter( 'get_the_excerpt', 'wp_trim_excerpt' ) ) {
			remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
			add_filter( 'get_the_excerpt', 'csidebars_excerpt_with_shortcodes', 10, 2 );
		}

		// --- excerpt shortcode tester ---
		// 1.5.9: fix to old function prefix
		add_shortcode( 'testexcerptshortcode', 'csidebars_test_excerpts' );
		function csidebars_test_excerpts() {
			return 'This shortcode will display in excerpts now.';
		}
	}
}

// ------------------------
// Excerpts with Shortcodes
// ------------------------
// 1.4.5: copy of wp_trim_excerpt but with shortcodes kept
// note: formatting is still stripped but shortcode text remains
function csidebars_excerpt_with_shortcodes( $text, $post ) {

	// for use in shortcodes to provide alternative output
	global $doingexcerpt;
	$doingexcerpt = true;

	$text = get_the_content( '', null, $post );
	// $text = strip_shortcodes( $text ); // modification
	$text = apply_filters( 'the_content', $text );
	$text = str_replace( ']]>', ']]&gt;', $text );
	$excerpt_length = apply_filters('excerpt_length', 55 );
	$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
	$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
	$doingexcerpt = false;

	return $text;
}


// ---------------------
// === Login Sidebar ===
// ---------------------

// -----------------
// Add Login Sidebar
// -----------------
// 1.3.5: just enqueue and perform checks within action
// 1.6.5: added check if priority is 0 or above
add_action( 'init', 'csidebars_login_sidebar_setup' );
function csidebars_login_sidebar_setup() {
	$hook = csidebars_get_setting( 'loginsidebar_hook' );
	$priority = csidebars_get_setting( 'loginsidebar_priority' );
	if ( $priority > -1 ) {
		add_action( $hook, 'csidebars_login_sidebar_output', $priority );
	}
}

// --------------------
// Login Sidebar Output
// --------------------
// 1.6.8: add member sidebar shortcode
add_shortcode( 'guest-sidebar', 'csidebars_login_sidebar' );
add_shortcode( 'login-sidebar', 'csidebars_login_sidebar' );
add_shortcode( 'logged-out-sidebar', 'csidebars_login_sidebar' );
function csidebars_login_sidebar_output() {
	echo csidebars_login_sidebar();
}
function csidebars_login_sidebar() {

	global $csidebarsoverrides, $csidebarsstate;

	// --- check if disabled in context ---
	// 1.4.5: check new page contexts
	// 1.5.5: added a separate override filter
	$disable = csidebars_get_setting( 'loginsidebar_disable' );
	$disable = csidebars_check_context( $disable, 'login' );
	$disable = apply_filters( 'csidebars_loginsidebar_override', $disable );

	// --- maybe fallback if logged in ---
	// 1.3.0: fix for option typo
	$fallback = csidebars_get_setting( 'loginsidebar_fallback', true );
	if ( ( 'nooutput' == $fallback ) && ( 'loggedin' == $csidebarsstate ) ) {
		return '';
	}
	if ( 'fallback' == $fallback ) {
		if ( ( 'yes' != $disable ) && ( 'loggedin' == $csidebarsstate ) ) {

			// --- check logged in sidebar mode ---
			// 1.4.5: check mode and call to member sidebar function
			$mode = csidebars_get_setting( 'membersidebar_mode', 'fallback' );
			if ( 'standalone' == $mode ) {
				return '';
			}

			// --- check logged in sidebar override ---
			// 1.6.5: added missing override check
			if ( isset( $csidebarsoverrides['member'] ) && ( 'disable' == $csidebarsoverrides['member'] ) ) {
				return '';
			}

			// --- get logged in sidebar ---
			$sidebar = '<div id="loginsidebar" class="contentsidebar loggedinsidebar">' . PHP_EOL;
			$sidebar .= csidebars_member_sidebar();
			$sidebar .= '</div>' . PHP_EOL;

			// --- filter logged in sidebar ---
			// 1.5.5: apply backward compatible and new filter prefix
			// 1.6.5: removed old fcs prefixed filter
			// $sidebar = apply_filters('fcs_login_sidebar_loggedin', $sidebar);
			$sidebar = apply_filters( 'csidebars_login_sidebar_loggedin', $sidebar );
			return $sidebar;
		}
	}

	// --- check login sidebar overrides ---
	// if (get_post_meta($postid,'_disableloginsidebar',true) == 'yes') {$disable = 'yes';}
	if ( isset( $csidebarsoverrides['login'] ) ) {
		if ( 'enable' == $csidebarsoverrides['login'] ) {
			$disable = '';
		} elseif ( 'disable' == $csidebarsoverrides['login'] ) {
			$disable = 'yes';
		}
	}

	// --- bug out if disabled ---
	if ( 'yes' == $disable ) {
		return '';
	}

	// --- get login sidebar ---
	if ( is_active_sidebar('LoginSidebar' ) ) {
		$hidden = '';
		if ( 'hidden' == $fallback ) {
			$hidden = ' style="display:none;"';
		}
		$sidebar = '<div id="loginsidebar" class="contentsidebar loggedoutsidebar"' . $hidden . '>' . PHP_EOL;
		$sidebar .= csidebars_get_sidebar( 'LoginSidebar' );
		$sidebar .= '</div>' . PHP_EOL;
	} else {
		$sidebar = '';
	}

	// --- filter and return login sidebar ---
	// 1.5.5: apply backward compatible and new filter prefix
	// 1.6.5: removed old fcs prefixed filters
	// $sidebar = apply_filters('fcs_login_sidebar', $sidebar);
	$sidebar = apply_filters( 'csidebars_login_sidebar', $sidebar );
	// $sidebar = apply_filters('fcs_login_sidebar_loggedout', $sidebar);
	$sidebar = apply_filters( 'csidebars_login_sidebar_loggedout', $sidebar );
	return $sidebar;
}


// ----------------------
// === Member Sidebar ===
// ----------------------
// (aka Logged In Sidebar)

// ------------------
// Add Member Sidebar
// ------------------
// 1.4.5: added member sidebar mode options
add_action( 'init', 'csidebars_member_sidebar_setup' );
function csidebars_member_sidebar_setup() {
	// 1.6.5: removed fallback argument from get_setting
	$mode = csidebars_get_setting( 'membersidebar_mode' );
	if ( ( 'standalone' == $mode ) || ( 'both' == $mode ) ) {
		$hook = csidebars_get_setting( 'membersidebar_hook' );
		$priority = csidebars_get_setting( 'membersidebar_priority' );
		if ( $priority > -1 ) {
			add_action( $hook, 'csidebars_member_sidebar_output', $priority );
		}
	}
}

// ---------------------
// Member Sidebar Output
// ---------------------
// 1.4.5: added standalone member sidebar function
// 1.6.8: add member sidebar shortcode
add_shortcode( 'member-sidebar', 'csidebars_member_sidebar' );
add_shortcode( 'logged-in-sidebar', 'csidebars_member_sidebar' );
function csidebars_member_sidebar_output() {
	echo csidebars_member_sidebar( true );
}
function csidebars_member_sidebar( $standalone = false ) {

	global $csidebarsoverrides, $csidebarsstate;

	// --- check if disabled for context ---
	// 1.4.5: check new page contexts
	// 1.5.5: added separate override filter
	$disable = csidebars_get_setting( 'membersidebar_disable' );
	$disable = csidebars_check_context( $disable, 'member' );
	$disable = apply_filters( 'csidebars_membersidebar_override', $disable );

	// --- check disable override ---
	// if (get_post_meta($postid,'_disablemembersidebar',true) == 'yes') {$disable = 'yes';}
	if ( isset( $csidebarsoverrides['member'] ) ) {
		if ( 'enable' == $csidebarsoverrides['member'] ) {
			$disable = '';
		}
		if ( 'disable' == $csidebarsoverrides['member'] ) {
			$disable = 'yes';
		}
	}

	// --- bug out if disabled ---
	// 1.6.7: fix single equals to double equals
	if ( 'yes' == $disable ) {
		return '';
	}

	// --- get member sidebar ---
	if ( is_active_sidebar( 'LoggedInSidebar' ) ) {
		if ( $standalone ) {
			// 1.3.0: fix for logged in sidebar name
			$sidebar = '<div id="membersidebar" class="contentsidebar loggedinsidebar">' . PHP_EOL;
			$sidebar .= csidebars_get_sidebar( 'LoggedInSidebar' );
			$sidebar .= '</div>' . PHP_EOL;
		} else {
			$sidebar = csidebars_get_sidebar( 'LoggedInSidebar' );
		}
	} else {
		$sidebar = '';
	}

	// --- filter and return member sidebar ---
	// 1.5.5: apply backward compatible and new filter prefix
	// 1.6.5: removed old fcs prefixed filters
	// $sidebar = apply_filters('fcs_member_sidebar', $sidebar);
	$sidebar = apply_filters( 'csidebars_member_sidebar', $sidebar );
	// $sidebar = apply_filters('fcs_member_sidebar_loggedin', $sidebar);
	$sidebar = apply_filters( 'csidebars_member_sidebar_loggedin', $sidebar );
	return $sidebar;
}


// ------------------------------
// === Above / Below Sidebars ===
// ------------------------------

// ------------------------------------
// Above / Below Sidebar Method Actions
// ------------------------------------
// 1.3.5: just enqueue and check disable within actions
// 1.3.5: added filters to hooks and priorities
// 1.4.5: change to use output function wrappers
add_action( 'init','csidebars_content_sidebars_setup' );
function csidebars_content_sidebars_setup() {
	$method = csidebars_get_setting( 'abovebelow_method' );
	if ( 'hooks' == $method ) {
		// --- add to above content hook ---
		$hook = csidebars_get_setting( 'abovecontent_hook' );
		$priority = csidebars_get_setting( 'abovecontent_priority' );
		if ( $priority > -1 ) {
			add_action( $hook, 'csidebars_abovecontent_sidebar_output', $priority );
		}

		// --- add to below content hook ---
		$hook = csidebars_get_setting( 'belowcontent_hook' );
		$priority = csidebars_get_setting( 'belowcontent_priority' );
		if ( $priority > -1 ) {
			add_action( $hook, 'csidebars_belowcontent_sidebar_output', $priority );
		}
	} elseif ( 'filter' == $method ) {
		// --- add via content filter ---
		// 1.6.7: filter content method priority
		$priority = apply_filters( 'csidebars_content_priority', 999 );
		if ( $priority > -1 ) {
			add_filter( 'the_content', 'csidebars_add_content_sidebars', $priority );
		}
	}
}

// ----------------------------
// Above Content Sidebar Output
// ----------------------------
// 1.6.8: add below content sidebar shortcode
add_shortcode( 'above-content-sidebar', 'csidebars_abovecontent_sidebar' );
function csidebars_abovecontent_sidebar_output() {
	echo csidebars_abovecontent_sidebar();
}
function csidebars_abovecontent_sidebar() {

	global $csidebarsoverrides, $csidebarsstate;

	// --- check if disabled for context ---
	// 1.4.5: check new page contexts
	// 1.5.5: added a separate override filter
	$disable = csidebars_get_setting ( 'abovecontent_disable' );
	$disable = csidebars_check_context( $disable, 'abovecontent' );
	$disable = apply_filters( 'csidebars_abovecontent_override', $disable );

	// --- check disable override ---
	// if (get_post_meta($postid,'_disableabovecontentsidebar', true) == 'yes') {$disable = 'yes';}
	if ( isset( $csidebarsoverrides['abovecontent'] ) ) {
		if ( 'disable' == $csidebarsoverrides['abovecontent'] ) {
			$disable = 'yes';
		}
		if ( 'enable' == $csidebarsoverrides['abovecontent'] ) {
			$disable = '';
		}
	}

	// --- maybe fallback to logged in sidebar ---
	$fallback = csidebars_get_setting( 'abovecontent_fallback' );
	if ( ( 'nooutput' == $fallback ) && ( 'loggedin' == $csidebarsstate ) ) {
		return '';
	}
	if ( 'fallback' == $fallback ) {
		if ( ( 'yes' != $disable ) && ( 'loggedin' == $csidebarsstate ) ) {

			// --- get members sidebar ---
			// 1.4.5: check mode and call to member sidebar function
			$mode = csidebars_get_setting( 'membersidebar_mode', 'fallback' );
			if ( 'standalone' == $mode ) {
				return '';
			}
			$sidebar = '<div id="abovecontentsidebar" class="contentsidebar loggedinsidebar">' . PHP_EOL;
			$sidebar .= csidebars_member_sidebar();
			$sidebar .= '</div>' . PHP_EOL;

			// --- filter sidebar and return ---
			// 1.5.5: apply backward compatible and new filter prefix
			// 1.6.5: removed old fcs prefixed filter
			// $sidebar = apply_filters('fcs_abovecontent_sidebar_loggedin', $sidebar);
			$sidebar = apply_filters( 'csidebars_abovecontent_sidebar_loggedin', $sidebar );
			return $sidebar;
		} else {
			return '';
		}
	} // otherwise, fall forward haha ---

	// --- bug out if disabled ---
	if ( 'yes' == $disable ) {
		return '';
	}

	// --- get above content sidebar ---
	// 1.6.7: maybe set empty sidebar variable
	if ( is_active_sidebar( 'AboveContent' ) ) {
		$hidden = '';
		if ( 'hidden' == $fallback ) {
			$hidden = ' style="display:none;"';
		}
		// 1.4.5: replaced loggedout with login state variable class
		$sidebar = '<div id="abovecontentsidebar" class="contentsidebar ' . $csidebarsstate . 'sidebar"' . $hidden . '>' . PHP_EOL;
		$sidebar .= csidebars_get_sidebar( 'AboveContent' );
		$sidebar .= '</div>' . PHP_EOL;
	} else {
		$sidebar = '';
	}

	// --- filter above content sidebar and return ---
	// 1.5.5: apply backward compatible and new filter prefix
	// 1.6.5: removed old fcs prefixed filters
	// $sidebar = apply_filters('fcs_abovecontent_sidebar', $sidebar);
	$sidebar = apply_filters( 'csidebars_abovecontent_sidebar', $sidebar );
	// $sidebar = apply_filters('fcs_abovecontent_sidebar_'.$csidebarsstate, $sidebar);
	$sidebar = apply_filters( 'csidebars_abovecontent_sidebar_'.$csidebarsstate, $sidebar );
	return $sidebar;
}

// ----------------------------
// Below Content Sidebar Output
// ----------------------------
// 1.6.8: add below content sidebar shortcode
add_shortcode( 'below-content-sidebar', 'csidebars_belowcontent_sidebar' );
function csidebars_belowcontent_sidebar_output() {
	echo csidebars_belowcontent_sidebar();
}
function csidebars_belowcontent_sidebar() {

	global $csidebarsoverrides, $csidebarsstate;

	// --- check if disabled for context ---
	// 1.4.5: check new page contexts
	// 1.5.5: added separate override filter
	$disable = csidebars_get_setting( 'belowcontent_disable' );
	$disable = csidebars_check_context( $disable, 'belowcontent' );
	$disable = apply_filters( 'csidebars_belowcontent_override', $disable );

	// --- check for logged in fallback ---
	$fallback = csidebars_get_setting( 'belowcontent_fallback', true );
	if ( ( 'nooutput' == $fallback ) && ( 'loggedin' == $csidebarsstate ) ) {
		return '';
	}
	if ( 'fallback' == $fallback ) {
		if ( ( 'yes' != $disable ) && ( 'loggedin' == $csidebarsstate ) ) {

			// --- get logged in sidebar fallback ---
			// 1.4.5: check mode and call to member sidebar function
			// 1.6.5: removed fallback argument for get setting
			$mode = csidebars_get_setting( 'membersidebar_mode' );
			if ( 'standalone' == $mode ) {
				return '';
			}
			$sidebar = '<div id="belowcontentsidebar" class="contentsidebar loggedinsidebar">' . PHP_EOL;
			$sidebar .= csidebars_member_sidebar();
			$sidebar .= '</div>' . PHP_EOL;

			// --- filter logged in sidebar fallback and return ---
			// 1.5.5: apply backward compatible and new filter prefix
			// 1.6.5: removed old fcs prefixed filters
			// $sidebar = apply_filters('fcs_belowcontent_sidebar_loggedin', $sidebar);
			$sidebar = apply_filters( 'csidebars_belowcontent_sidebar_loggedin', $sidebar );
			return $sidebar;
		 } else {
		 	return '';
		 }
	} // otherwise, fall sideways this time :-]

	// --- check for disable override ---
	// if (get_post_meta($postid, '_disablebelowcontentsidebar', true) == 'yes') {$disable = 'yes';}
	if ( isset( $csidebarsoverrides['belowcontent'] ) ) {
		if ( 'disable' == $csidebarsoverrides['belowcontent'] ) {
			$disable = 'yes';
		}
		if ( 'enable' == $csidebarsoverrides['belowcontent'] ) {
			$disable = '';
		}
	}

	// --- bug out if disabled ---
	if ( 'yes' == $disable ) {
		return '';
	}

	// --- get below content sidebar ---
	if ( is_active_sidebar( 'BelowContent' ) ) {
		// 1.4.5: replaced loggedout with login state variable class
		$sidebar = '<div id="belowcontentsidebar" class="contentsidebar ' . $csidebarsstate . 'sidebar">' . PHP_EOL;
		$sidebar .= csidebars_get_sidebar( 'BelowContent' );
		$sidebar .= '</div>' . PHP_EOL;
	} else {
		$sidebar = '';
	}

	// --- filter below content sidebar and return ---
	// 1.5.5: apply backward compatible and new filter prefix
	// 1.6.5: removed old fcs prefixed filters
	// $sidebar = apply_filters('fcs_belowcontent_sidebar', $sidebar);
	$sidebar = apply_filters( 'csidebars_belowcontent_sidebar', $sidebar );
	// $sidebar = apply_filters('fcs_belowcontent_sidebar_'.$csidebarsstate, $sidebar);
	$sidebar = apply_filters( 'csidebars_belowcontent_sidebar_'.$csidebarsstate, $sidebar );
	return $sidebar;
}

// ---------------------------------
// Above/Below Content Filter Method
// ---------------------------------
// 1.3.5: removed code duplication (now just use above functions)
function csidebars_add_content_sidebars( $content ) {

	// --- for content only, not excerpts ---
	// 1.4.5: bug out if excerpting
	global $csidebarsexcerpt;
	if ( $csidebarsexcerpt ) {
		return $content;
	}

	// --- get above content sidebar ---
	// 1.4.5: use return value not output buffering
	// 1.5.9: fix to old function name
	$topsidebar = csidebars_abovecontent_sidebar();

	// --- get below content sidebar ---
	// 1.4.5: use return value not output buffering
	// 1.5.9: fix to old function name
	$bottomsidebar = csidebars_belowcontent_sidebar();

	// --- wrap content with sidebars and return ---
	$content = $topsidebar . $content . $bottomsidebar;
	return $content;
}


// --------------------------
// === Shortcode Sidebars ===
// --------------------------

// ----------------------
// Add Shortcode Sidebars
// ----------------------
// 1.3.5: just add and check disable/overrides within shortcodes
add_action( 'init','csidebars_sidebar_shortcodes' );
function csidebars_sidebar_shortcodes() {
	// 1.6.5: removed unnecessary is_admin check
	add_shortcode( 'shortcode-sidebar-1', 'csidebars_shortcode_sidebar1' );
	add_shortcode( 'shortcode-sidebar-2', 'csidebars_shortcode_sidebar2' );
	add_shortcode( 'shortcode-sidebar-3', 'csidebars_shortcode_sidebar3' );
}
// 1.3.5: replaced individual shortcodes with abstract calls
function csidebars_shortcode_sidebar1() {
	return csidebars_shortcode_sidebar( '1' );
}
function csidebars_shortcode_sidebar2() {
	return csidebars_shortcode_sidebar( '2' );
}
function csidebars_shortcode_sidebar3() {
	return csidebars_shortcode_sidebar( '3' );
}

// ------------------------
// Shortcode Sidebar Output
// ------------------------
// 1.3.5: replace individual functions with abstracted function
function csidebars_shortcode_sidebar( $id ) {
	global $post, $csidebarsoverrides, $csidebarsstate, $csidebarsexcerpt;

	// --- check for excerpting ---
	// 1.4.5: bug out if excerpting
	if ( $csidebarsexcerpt ) {
		// normally we do not actually want to output shortcode sidebars in excerpts,
		// but for flexibility in usage let us give the user the option to do so
		$process = csidebars_get_setting( 'sidebars_in_excerpts' );
		// 1.5.5: add prefix to this filter for specific shortcode sidebar in excerpts
		// 1.6.5: added general name to allow filtering shortcode sidebars in excerpts
		$process = apply_filters( 'csidebars_shortcode_sidebars_in_excerpts', $process );
		$process = apply_filters( 'csidebars_shortcode_sidebar'.$id.'_in_excerpts', $process );
		if ( !$process ) {
			return '';
		}
	}

	// --- check if sidebar is disabled ---
	// 1.6.5: added separate shortcode disable filter
	$disable = csidebars_get_setting( 'shortcode' . $id . '_disable', true );
	$disable = apply_filters( 'csidebars_shortcode' . $id . '_override', $disable );

	// --- check disable overrides ---
	// 1.5.5: removed old post meta key check
	// if (get_post_meta($postid, '_disableshortcodesidebar'.$id, true) == 'yes') {$disable = 'yes';}
	if ( isset( $csidebarsoverrides['shortcodesidebar' . $id] ) ) {
		if ( 'disable' == $csidebarsoverrides['shortcodesidebar' . $id] ) {
			$disable = 'yes';
		} elseif ( 'enable' == $csidebarsoverrides['shortcodesidebar' . $id] ) {
			$disable = '';
		}
	}

	// --- bug out if disabled ---
	if ( 'yes' == $disable ) {
		return '';
	}

	// --- get shortcode sidebar ---
	if ( is_active_sidebar( 'ShortcodeSidebar' . $id ) ) {
		$sidebar = '<div id="shortcodesidebar' . $id . '" class="shortcodesidebar ' . $csidebarsstate . 'sidebar">' . PHP_EOL;
		$sidebar .= csidebars_get_sidebar( 'ShortcodeSidebar' . $id );
		$sidebar .= '</div>' . PHP_EOL;
	} else {
		$sidebar = '';
	}

	// --- apply shortcode sidebar filters and return ---
	// 1.5.5: apply backward compatible and new filter prefix
	// 1.6.5: removed old fcs prefixed filters
	// $sidebar = apply_filters('fcs_shortcode_sidebar'.$id, $sidebar);
	$sidebar = apply_filters( 'csidebars_shortcode_sidebar' . $id, $sidebar );
	// $sidebar = apply_filters('fcs_shortcode_sidebar'.$id.'_'.$csidebarsstate, $sidebar);
	$sidebar = apply_filters( 'csidebars_shortcode_sidebar' . $id . '_' . $csidebarsstate, $sidebar );
	return $sidebar;
}


// -----------------------
// === InPost Sidebars ===
// -----------------------

// -------------------
// Add InPost Sidebars
// -------------------
add_action( 'init', 'csidebars_inpost_sidebars' );
function csidebars_inpost_sidebars() {
	// 1.3.5: just add filter and check states within function
	// 1.6.5: removed unnecessary is_admin check
	$inpostpriority = csidebars_get_setting( 'inpost_priority', true );
	if ( $inpostpriority > -1 ) {
		add_filter( 'the_content', 'csidebars_do_inpost_sidebars', $inpostpriority );
	}
}

// ---------------------
// InPost Sidebar Output
// ---------------------
function csidebars_do_inpost_sidebars( $postcontent ) {

	global $post, $csidebarsoverrides, $csidebarsexcerpt, $csidebarsstate;

	// --- not for excerpting or if empty ---
	// 1.4.5: bug out if excerpting or empty post
	if ( $csidebarsexcerpt ) {
		return $postcontent;
	}
	if ( !is_object( $post ) ) {
		return $postcontent;
	}

	// --- check InPostContent Marker ---
	// note: marker is intentionally case insensitive
	// 1.6.2: hotfix - bug out if content marker is empty!
	// 1.6.5: do not trim marker in case space needed for matching
	$contentmarker = csidebars_get_setting( 'inpost_marker' );
	if ( '' == trim( $contentmarker ) ) {
		return $postcontent;
	}
	if ( !stristr( $postcontent, $contentmarker ) ) {
		return $postcontent;
	}

	// --- get inpost sidebar disable options ---
	$inpostdisable1 = csidebars_get_setting( 'inpost1_disable' );
	$inpostdisable2 = csidebars_get_setting( 'inpost2_disable' );
	$inpostdisable3 = csidebars_get_setting( 'inpost3_disable' );

	// --- check InPost sidebars for custom post types ---
	$postid = $post->ID;
	$cptsettings = csidebars_get_setting( 'inpost_sidebars_cpts' );
	if ( strstr( $cptsettings, ',' ) ) {
		$inpostcpts = explode( ',', $cptsettings );
	} else {
		$inpostcpts = array( $cptsettings );
	}
	// 1.6.7: remove old filter name
	// $inpostcpts = apply_filters( 'fcs_inpost_sidebars_cpts', $inpostcpts );
	// 1.6.5: added missing csidebars prefixed filter
	$inpostcpts = apply_filters( 'csidebars_inpost_sidebars_cpts', $inpostcpts );
	// 1.3.5: maybe disable for specified post types
	if ( is_array( $inpostcpts ) ) {
		$posttype = get_post_type( $postid );
		if ( !in_array( $posttype, $inpostcpts ) ) {
			$inpostdisable1 = $inpostdisable2 = $inpostdisable3 = 'yes';
		}
	}

	// --- filter disable states ---
	// 1.3.5: allow for disable option filtering
	// 1.5.5: make these into disable override filters
	$inpostdisable1 = apply_filters( 'csidebars_inpost1_override', $inpostdisable1 );
	$inpostdisable2 = apply_filters( 'csidebars_inpost2_override', $inpostdisable2 );
	$inpostdisable3 = apply_filters( 'csidebars_inpost3_override', $inpostdisable3 );

	// --- check disable overrides ---
	// 1.3.5: check meta overrides here
	if ( isset( $csidebarsoverrides['inpost1'] ) ) {
		if ( 'disable' == $csidebarsoverrides['inpost1'] ) {
			$inpostdisable1 = 'yes';
		} elseif ( 'enable' == $csidebarsoverrides['inpost1'] ) {
			$inpostdisable1 = '';
		}
	}
	if ( isset( $csidebarsoverrides['inpost2'] ) ) {
		if ( 'disable' == $csidebarsoverrides['inpost2'] ) {
			$inpostdisable2 = 'yes';
		} elseif ( 'enable' == $csidebarsoverrides['inpost2'] ) {
			$inpostdisable2 = '';
		}
	}
	if ( isset( $csidebarsoverrides['inpost3'] ) ) {
		if ( 'disable' == $csidebarsoverrides['inpost3'] ) {
			$inpostdisable3 = 'yes';
		} elseif ( 'enable' == $csidebarsoverrides['inpost3'] ) {
			$inpostdisable3 = '';
		}
	}

	// --- bug out now if all inpost sidebars disabled ---
	if ( ( 'yes' == $inpostdisable1 ) && ( 'yes' == $inpostdisable2 ) && ( 'yes' == $inpostdisable3 ) ) {
		return $postcontent;
	}

	// ---- convert marker case - 'just in case' ---
	// 1.6.5: removed to use exact matching case
	// if ($contentmarker == strtolower($contentmarker)) {
	//	if (strstr($postcontent, strtoupper($contentmarker))) {
	//		$postcontent = str_replace(strtoupper($contentmarker), $contentmaker, $postcontent);
	//	}
	// }
	// if ($contentmarker == strtoupper($contentmarker)) {
	//	if (strstr($postcontent, strtolower($contentmarker))) {
	//		$postcontent = str_replace(strtolower($contentmarker), $contentmaker, $postcontent);
	//	}
	// }

	// --- get inpost content positions (filtered) ---
	$positiona = csidebars_get_setting( 'inpost_positiona' );
	$positionb = csidebars_get_setting( 'inpost_positionb' );
	$positionc = csidebars_get_setting( 'inpost_positionc' );
	// 1.6.7: use absint instead of is_numeric check
	$positiona = absint( $positiona );
	$positionb = absint( $positionb );
	$positionc = absint( $positionc );

	// --- chunk the content using marker ---
	$chunks = explode( $contentmarker, $postcontent );

	// --- loop the split content chunks ----
	// 1.4.0: start count at 1 not 0
	$count = 1;
	$content = '';
	foreach ( $chunks as $chunk ) {

		$content .= $chunk;

		if ( ( $count == $positiona ) && ( 'yes' != $inpostdisable1 ) ) {

			// --- get inpost sidebar 1 ---
			if ( is_active_sidebar( 'InPost1' ) ) {
				$sidebar = '<div id="inpostsidebar1" class="inpostsidebar"';
				// 1.4.0: added float style option
				$float = csidebars_get_setting( 'inpost1_float' );
				if ( '' != $float ) {
					$sidebar .= ' style="float:' . $float . ';';
					if ( 'left' == $float ) {
						$sidebar .= 'margin-right:30px;"';
					} elseif ( 'right' == $float ) {
						$sidebar .= 'margin-left:30px;"';
					} else {
						$sidebar .= '"';
					}
				}
				$sidebar .= '>' . PHP_EOL;
				$sidebar .= csidebars_get_sidebar( 'InPost1' );
				$sidebar .= '</div>' . PHP_EOL;
			} else {
				$sidebar = '';
			}

			// --- filter inpost sidebar 1 ---
			// 1.5.5: apply backwards compatible and new filter prefix
			// 1.6.5: removed old fcs prefixed filters
			$sidebar = apply_filters( 'csidebars_inpost_sidebar', $sidebar );
			// $sidebar = apply_filters('fcs_inpost_sidebar1', $sidebar);
			$sidebar = apply_filters( 'csidebars_inpost_sidebar1', $sidebar );
			// $sidebar = apply_filters('fcs_inpost_sidebar1_'.$csidebarsstate, $sidebar);
			$sidebar = apply_filters( 'csidebars_inpost_sidebar1_' . $csidebarsstate, $sidebar );
			$content .= $sidebar;

		} elseif ( ( $count == $positionb ) && ( 'yes' != $inpostdisable2 ) ) {

			// --- get inpost sidebar 2 ---
			if ( is_active_sidebar( 'InPost2' ) ) {
				// 1.6.7: fix to quoting syntax error
				$sidebar = '<div id="inpostsidebar2" class="inpostsidebar"';
				// 1.4.0: added float style option
				$float = csidebars_get_setting( 'inpost2_float' );
				if ( '' != $float ) {
					$sidebar .= ' style="float:' . $float . ';';
					if ( 'left' == $float ) {
						$sidebar .= 'margin-right:30px;"';
					} elseif ( 'right' == $float ) {
						$sidebar .= 'margin-left:30px;"';
					} else {
						$sidebar .= '"';
					}
				}
				$sidebar .= '>' . PHP_EOL;
				$sidebar .= csidebars_get_sidebar( 'InPost2' );
				$sidebar .= '</div>' . PHP_EOL;
			} else {
				$sidebar = '';
			}

			// --- filter inpost sidebar 2 ---
			// 1.5.5: apply backwards compatible and new filter prefix
			// 1.6.5: removed old fcs prefixed filters
			$sidebar = apply_filters( 'csidebars_inpost_sidebar', $sidebar );
			// $sidebar = apply_filters('fcs_inpost_sidebar2', $sidebar);
			$sidebar = apply_filters( 'csidebars_inpost_sidebar2', $sidebar );
			// $sidebar = apply_filters('fcs_inpost_sidebar2_'.$csidebarsstate, $sidebar);
			$sidebar = apply_filters( 'csidebars_inpost_sidebar2_' . $csidebarsstate, $sidebar );
			$content .= $sidebar;

		} elseif ( ( $count == $positionc ) && ( 'yes' != $inpostdisable3 ) ) {

			// --- get inpost sidebar 3 ---
			if ( is_active_sidebar( 'InPost3' ) ) {
				$sidebar = '<div id="inpostsidebar3" class="inpostsidebar"';
				// 1.4.0: added float style option
				$float = csidebars_get_setting('inpost3_float',true);
				if ( '' != $float ) {
					$sidebar .= ' style="float:' . $float . ';';
					if ( 'left' == $float ) {
						$sidebar .= 'margin-right:30px;"';
					} elseif ( 'right' == $float ) {
						$sidebar .= 'margin-left:30px;"';
					} else {
						$sidebar .= '"';
					}
				}
				$sidebar .= '>' . PHP_EOL;
				$sidebar .= csidebars_get_sidebar( 'InPost3' );
				$sidebar .= '</div>' . PHP_EOL;
			} else {
				$sidebar = '';
			}

			// --- filter inpost sidebar 3 ---
			// 1.5.5: apply backwards compatible and new filter prefix
			$sidebar = apply_filters( 'csidebars_inpost_sidebar', $sidebar );
			// $sidebar = apply_filters('fcs_inpost_sidebar3', $sidebar);
			$sidebar = apply_filters( 'csidebars_inpost_sidebar3', $sidebar );
			// $sidebar = apply_filters('fcs_inpost_sidebar3_'.$csidebarsstate, $sidebar);
			$sidebar = apply_filters( 'csidebars_inpost_sidebar3_'.$csidebarsstate, $sidebar );
			$content .= $sidebar;
		}
		$count++;
	}
	return $content;
}


// -------------------------
// === Metabox Overrides ===
// -------------------------

// ----------------------------
// Add Metaboxes for Post Types
// ----------------------------
add_action( 'add_meta_boxes', 'csidebars_add_perpage_metabox' );
function csidebars_add_perpage_metabox() {

	// --- get post types ---
	$cpts = array( 'post', 'page' );
	$args = array( 'public' => true, '_builtin' => false );
	$cptlist = get_post_types( $args, 'names', 'and' );
	$cpts = array_merge( $cpts, $cptlist );

	// --- filter post types ---
	// (to adjust the post types for which the metabox is shown)
	// 1.3.5: changed this filter name to match purpose
	// 1.5.5: apply backwards compatible and new filter prefix
	// 1.6.5: removed old fcs prefixed filter
	// $cpts = apply_filters('fcs_metabox_cpts', $cpts);
	$cpts = apply_filters( 'csidebars_metabox_cpts', $cpts );

	// --- loop post types to add metabox ---
	// 1.3.5: fix to variable typo here
	if ( count( $cpts ) > 0 ) {
		foreach ( $cpts as $cpt ) {
			add_meta_box( 'csidebars_perpage_metabox', __( 'Content Sidebars', 'csidebars' ), 'csidebars_perpage_metabox', $cpt, 'normal', 'low' );
		}
	}
}

// ------------------------
// Content Sidebars Metabox
// ------------------------
function csidebars_perpage_metabox() {

	global $post, $csidebarsoverrides;

	if ( is_object( $post ) ) {
		$postid = $post->ID;
		$posttype = get_post_type( $postid );
		$posttypeobject = get_post_type_object( $posttype );
		$posttypedisplay = $posttypeobject->labels->singular_name;
	} else {
		$posttypedisplay = __( 'Post', 'csidebars' );
	}

	echo "<style>.fcs-small {font-size:8pt;}</style>";

	// ### MARKER
	// TODO: check this plugin page link
	echo esc_html( __( 'Override','csidebars' ) );
	echo ' <a href="/admin.php?page=content-sidebars"><b>';
	echo esc_html( __( 'current settings', 'csidebars' ) );
	echo '</b></a> ';
	echo esc_html( __( 'for Content Sidebar Output on this', 'csidebars' ) );
	echo ' '.$posttypedisplay . ' (';
	echo esc_html( __( 'indicated in bold',' csidebars' ) ) . '):<br>';

	// --- Above/Below, Login/LoggedIn ---
	echo '<table><tr>';
		echo '<td>' . esc_html( __( 'Above Content', 'csidebars' ) ) . '</td>';
			csidebars_output_setting_cell( 'abovecontent' );
		echo '<td width="20">&nbsp;</td>';
		echo '<td>' . esc_html( __( 'Below Content', 'csidebars' ) ) . '</td>';
		csidebars_output_setting_cell( 'belowcontent' );
	echo '</tr><tr>';
		echo '<td>' . esc_html( __( 'Login', 'csidebars' ) ) . '</td>';
		csidebars_output_setting_cell( 'login' );
		echo '<td width="20">&nbsp;</td>';
		echo '<td><span class="fcs-small">' . esc_html( __( 'Logged In (fallback)', 'csidebars' ) ) . '</span></td>';
		csidebars_output_setting_cell( 'member' );
	echo '</tr>';

	// --- Shortcode and InPost Sidebars ---
	// 1.6.7: add number label translation functions
	echo '<tr>';
		echo '<td>' . esc_html( __( 'Shortcode', 'csidebars' ) ) . ' ' . number_format_i18n( 1 ) . '</td>';
		csidebars_output_setting_cell( 'shortcode1' );
		echo '<td width="20">&nbsp;</td>';
		echo '<td>' . esc_html( __( 'InPost', 'csidebars' ) ) . ' ' . number_format_i18n( 1 ) . '</td>';
		csidebars_output_setting_cell( 'inpost1' );
	echo '</tr><tr>';
		echo '<td>' . esc_html( __( 'Shortcode', 'csidebars' ) ) . ' ' . number_format_i18n( 2 ) . '</td>';
		csidebars_output_setting_cell( 'shortcode2' );
		echo '<td width="20">&nbsp;</td>';
		echo '<td>' . esc_html( __( 'InPost', 'csidebars' ) ) . ' ' . number_format_i18n( 2 ) . '</td>';
		csidebars_output_setting_cell( 'inpost2' );
	echo '</tr><tr>';
		echo '<td>' . esc_html( __( 'Shortcode', 'csidebars' ) ) . ' ' . number_format_i18n( 3 ) . '</td>';
		csidebars_output_setting_cell( 'shortcode3' );
		echo '<td width="20">&nbsp;</td>';
		echo '<td>' . esc_html( __( 'InPost', 'csidebars' ) ) . ' ' . number_format_i18n( 3 ) . '</td>';
		csidebars_output_setting_cell( 'inpost3' );
	echo '</tr>';

	echo '</table>';
}

// ---------------------------
// Output Metabox Setting Cell
// ---------------------------
function csidebars_output_setting_cell( $id ) {
	global $csidebarsoverrides, $post;

	// --- check sidebar state---
	$disable = csidebars_get_setting( $id . '_disable', false );

	if ( 'yes' == $disable ) {
		$state = 'off';
	} else {
		// --- set default state ---
		$state = 'on';

		// --- check default state for this post type ---
		if ( is_object( $post ) && !strstr( $id, 'shortcode' ) ) {
			if ( strstr( $id, 'inpost' ) ) {
				$sidebarcpts = csidebars_get_setting( 'inpost_sidebars_cpts' );
			} else {
				$sidebarcpts = csidebars_get_setting( $id . '_sidebar_cpts' );
			}
			if ( strstr( $sidebarcpts, ',' ) ) {
				$cpts = explode( ',', $sidebarcpts );
			} else {
				$cpts = array( $sidebarcpts );
			}
			$posttype = get_post_type( $post->ID );
			// echo $id.'--'.$posttype; print_r($cpts); echo "<br>";
			if ( !in_array( $posttype, $cpts ) ) {
				$state = 'off';
			}
		}
	}

	// --- set sidebar state labels ---
	if ( 'on' == $state ) {
		$off = __( 'Off', 'csidebars' );
		$on = '<b>' . __( 'On', 'csidebars' ) . '</b>';
	} elseif ( 'off' == $state ) {
		$on = __( 'On', 'csidebars' );
		$off = '<b>' . __( 'Off', 'csidebars' ) . '</b>';
	}

	// --- get filter disable name ---
	if ( ( 'login' == $id ) || ( 'member' == $id ) ) {
		$filter = 'csidebars_' . $id . 'sidebar_disable';
	} else {
		$filter = 'csidebars_' . $id . '_disable';
	}

	// --- check disabled filter ---
	$filtered = apply_filters( $filter, $state );
	if ( $filtered != $state ) {
		if ( 'yes' == $filtered ) {
			$on = __( 'On','csidebars' );
			$off = '<b>' . __( 'Off', 'csidebars' ) . '</b>*';
		} elseif ( '' == $filtered ) {
			$off = __('Off','csidebars');
			$on = '<b>' . __( 'On', 'csidebars' ) . '</b>*';
		}
	}

	// --- output setting cells ---
	echo '<td>';
		echo ' <input type="radio" name="fcs_' . $id . '" value=""';
		if ( '' == $csidebarsoverrides[$id] ) {
			echo ' checked="checked"';
		}
		echo '> <span class="fcs-small">' . esc_html( __( 'Current', 'csidebars' ) ) . '</span>';
	echo '</td><td width="5"></td><td>';
		echo '<input type="radio" name="fcs_' . $id . '" value="enable"';
		if ( 'enable' == $csidebarsoverrides[$id] ) {
			echo ' checked="checked"';
		}
		echo '> ' . $on;
	echo '</td><td width="5"></td><td>';
	// 1.5.5: fix to missing disable value
		echo ' <input type="radio" name="fcs_' . $id . '" value="disable"';
		if ( 'disable' == $csidebarsoverrides[$id] ) {
			echo ' checked="checked"';
		}
		echo '> ' . $off;
	echo '</td>';
}

// --------------------------
// Update Meta Values on Save
// --------------------------
add_action( 'publish_post', 'csidebars_perpage_updates' );
add_action( 'save_post', 'csidebars_perpage_updates' );

// 1.3.5: efficient save using single postmeta value
function csidebars_perpage_updates() {

	// 1.3.5: return if post object is empty
	global $post, $csidebarsoverrides;
	if ( !is_object( $post ) ) {
		return;
	}
	$postid = $post->ID;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $postid;
	}
	if ( !current_user_can( 'edit_posts' ) ) {
		return $postid;
	}
	if ( !current_user_can( 'edit_post', $postid ) ) {
		return $postid;
	}

	$optionkeys = array(
		'abovecontent',
		'belowcontent',
		'login',
		'member',
		'shortcode1',
		'shortcode2',
		'shortcode3',
		'inpost1',
		'inpost2',
		'inpost3'
	);


	$csidebarsoverrides = array();
	foreach ( $optionkeys as $optionkey ) {
		if ( isset( $_POST['fcs_' . $optionkey] ) ) {
			$posted = $_POST['fcs_' . $optionkey];
			// 1.5.5: validate metabox save options
			if ( ( '' == $posted ) || ( 'enable' == $posted ) || ( 'disable' == $posted ) ) {
				$csidebarsoverrides[$optionkey] = $posted;
			} else {
				$csidebarsoverrides[$optionkey] = '';
			}
		} else {
			$csidebarsoverrides[$optionkey] = '';
		}
	}

	// 1.6.5: use update_post_meta instead of delete and add
	update_post_meta( $postid, 'content_sidebars', $csidebarsoverrides );

}
