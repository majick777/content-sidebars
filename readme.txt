=== Content Sidebars ===
Contributors: majick
Donate link: http://wordquest.org/contribute/?plugin=content-sidebars
Tags: sidebars, widgets, widget areas, content area, content sidebar, content widgets, widgets in posts, widgets on pages, adsense
Author URI: http://dreamjester.net
Plugin URI: http://wordquest.org/plugins/content-sidebars/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.0.0
Tested up to: 4.7
Stable tag: trunk

Give an instant boost to your Layout and Call-to-Action options. Auto-add Sidebars to your Post Content Display, inside and out!

== Description ==

Use **Content Sidebars** to give an instant boost to your Layout and Call-to-Action options. 
Automatically add Widget Sidebars to your Content display area, inside and out! 

Improve Subscriber Conversions, Call-to-Actions, Visitor Experience, AdSense Revenue,
Design Layout flexibility and sex drive all in one plugin (wait no, forget that last one.)

1. **Subscribe or Call-to-Action Widget Areas** - Top of Post and Bottom of Post Content
1. **Visitor Targeting**  - Login Visitor and Logged-In User Sidebars for Offers and Navigation
1. **Easy Widgets in Posts** - Shortcodes for using Widgets within your Post Content
1. **Boost Ads and AdSense Revenue and CTRs** - InPost Sidebars for Contextual Advertising

You can safely activate this plugin and create additional Sidebars and thus Widget Areas
to use instantly - regardless of your existing theme. See the Frequently Asked Questions
for usage notes on better integration with your Theme. 

Additional Sidebars (Widget Areas) available through **Content Sidebars**:

1. *Above Post Content* --- ie. above the_content() output (or other hook position)
1. *Below Post Content* --- ie. below the_content() output (or other hook position)
1. *Login Sidebar* -------- a Login Sidebar shown only to Visitors (Logged Out Users)
1. *Fallback Sidebar* ----- a fallback Sidebar shown to Return Visitors (Logged In Users)
1. *3 Shortcode Sidebars* - use Sidebars/Widgets on posts/pages via simple shortcodes
1. *3 InPost Sidebars* ---- for automatic contextual advertising, eg. Google Adsense

All Sidebars persist between Theme activations/deactivations, making theme conversions 
easier too - no need to add all the Widgets again or even move them. 

There are also PerPost overrides available via the Content Sidebars metabox for each post
type on the post writing/editing screen to enable/disable any of these sidebars for that page.

Filters and Widget plugins are available for advanced display and output logic for all 
sidebars, giving even further flexible options to when and which sidebars are displayed.

[Content Sidebars Home] (http://wordquest.org/plugins/content-sidebars/)
[Support Forum] (http://wordquest.org/quest/quest-category/plugin-support/content-sidebars/)


**Above/Below Content Sidebars** - *for Subscribe / Join / Call-to-Action Offers*

Adding these sidebars creates an excellent space to encourage visitor registration or
mailing list subscriptions or other call-to-action offers. Using registration or 
subscriber widgets in these places can have better results than places them in your
standard sidebar locations. To take a relevent parallel, split tests show that the 
headline and the PS sections of salespages are the most often looked at, so using 
these spaces wisely on any webpage is a must.

The Above/Below Sidebars work either via Template Action Hooks or Content Filters methods.
Either method can be used with any theme. The Template Action Hook defaults are preconfigured 
for immediate use with [BioShip Child Theme Framework] (http://bioship.space)


**Login Sidebar** - *for Easy Return Visitor Login*

Allowing returning visitors to login from any page on your site rather than having to
go to a specific login page increases your site's usability and accessibility. And of
course having this sidebar automatically not show for already logged in users, or show
different content in it's place (see next) are further good ideas.


**Logged In Sidebar** - *for User Fallback and Member Offers*

This was created so you can have different content for Logged In Users (eg. your Members)
so that you can display for example a Join Us / Signup / Register Widget for new visitors
and after they have signed up and logged in, show Members Area links or offers in their
place instead. A very handy professional touch.


**Shortcode Sidebars** - *for Easy Widgets in Pages or Posts*

The three Shortcode Sidebars can be used via Wordpress shortcodes in your post content, 
or by adding a shortcode sidebar call to your theme template files or plugins.
eg. `[shortcode-sidebar-1]` will call any Widgets dropped in Shortcode Sidebar 1.


**InPost Sidebars** - *for Contextual Advertising Magic*

InPost Sidebars are spaced evenly throughout your paragraph text at whichever positions
you like, for whichever Post Types you choose. For example: Drop an AdSense Widget or 
Advertising Widget or other HTML advertising block in each Sidebar (3 available) to show 
these sidebars periodically throughout your post content. Awesome.


**Discreet Text Widget** - *for better Shortcode output in Sidebars*

Using shortcode in text widgets has the advantage of being able to easily use custom code 
in your Sidebars. However, in different conditions you may want this shortcode to display 
nothing at all. If you were using a normal text widget it would still output the empty widget
(with title etc.) - but with a Discreet Text Widget, if the shortcode returns empty the widget
does not output at all. So this useful type of widget as been included with Content Sidebars.
Credit: [Hackadelic Discreet Text Widget] (https://wordpress.org/plugins/hackadelic-discreet-text-widget/)

[Content Sidebars Home] (http://wordquest.org/plugins/content-sidebars/)
[Support Forum] (http://wordquest.org/quest/quest-category/plugin-support/content-sidebars/)


== Installation ==

1. Upload `content-sidebars.zip` via the Plugins upload page.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the Appearance -> Content Sidebars menu to configure the plugin.
1. Refer to Frequently Asked Questions for configurations notes.

== Frequently Asked Questions ==

= How do I use this plugin? =

1. Once activated your will have additional Widget Areas available via Appearance -> Widgets.
1. You can change your Content Sidebar Settings via Appearance -> Content Sidebars.
1. There are other options relating to the different types of Sidebars explained below.
1. You can change PerPost Sidebar Display options via the post writing/editing screen Metabox.
1. You can use filters or plugins to change the Sidebar output conditions (see last question.)


= What are the Shortcodes for the Shortcode Sidebars? =

The shortcode Sidebars are inserted using the relevent shortcode for that Sidebar.
`eg. [shortcode-sidebar-1] or [shortcode-sidebar-2] or [shortcode-sidebar-3]`
They can be inserted anywhere in your posts using these shortcodes.
If you want to insert them in a template or code, you can use, for example: 
`<?php echo do_shortcode['shortcode-sidebar-1']; ?>` 


= How do I style the resulting Sidebars? =

You can change the CSS for any of the Content Sidebars on the settings page.
There is some default CSS already to help with some general styles and fixes.

The relevent CSS IDs and classes for the different Sidebar types and widgets are:
(also in a table on the Content Sidebars settings page for easy reference.)

`| Sidebar ID --------| Sidebar Class ----| Widget Class -------| Widget Title Class`
`---------------------.-------------------.---------------------.-------------------`
`#abovecontentsidebar | .contentsidebar - | .abovecontentwidget | .abovecontenttitle`
`#belowcontentsidebar | .contentsidebar - | .belowcontentwidget | .belowcontenttitle`
`#loginsidebar -------| .contentsidebar - | .loginwidget -------| .loginwidgettitle`
`* .loggedinsidebar --| .contentsidebar - | .loggedinwidget ----| .loggedinwidgettitle`
`#membersidebar ------| .contentsidebar - | .memberwidget ------| .memberwidgettitle`
`#shortcodesidebar1 --| .shortcodesidebar | .shortcodewidget ---| .shortcodewidgettitle`
`#shortcodesidebar2 --| .shortcodesidebar | .shortcodewidget ---| .shortcodewidgettitle`
`#shortcodesidebar3 --| .shortcodesidebar | .shortcodewidget ---| .shortcodewidgettitle`
`#inpostsidebar1 -----| .inpostsidebar ---| .inpostwidget ------| .inpostwidgettitle`
`#inpostsidebar2 -----| .inpostsidebar ---| .inpostwidget ------| .inpostwidgettitle`
`#inpostsidebar1 -----| .inpostsidebar ---| .inpostwidget ------| .inpostwidgettitle`

* if logged out, the .loggedoutsidebar is also added to all sidebars on that page.
the .loggedinsidebar class is added to the Above, Below or Login sidebars on fallback,


= How do I set up the 'Above Content' and 'Below Content' Sidebar positioning? =

The main Content Sidebars can be positioned by one of two methods of your choice. 
Either via Template Action Hooks or via the Content Filter. The Content Filter method is 
easier and faster to setup, but less flexible (read on for why.)

The Content Filter method works for ANY theme, as it adds the sidebars to the_content(). 
You can adjust the priorities so they can 'fit' in with other end-of-post features such as
Related Posts. The default is for absolute before (first) and absolute end (last.)

The limitation of the Content Filter method is that for the 'Above Content' Sidebar it 
cannot account for the Post Title (and sometimes subtitle and post meta display depending 
on the Theme) - for that is typically *above* the_content - so filtering the content does 
not change that. So if you want the *Above Content Sidebar* to be completely above the Post 
Title you will need to use Template Action Hooks (which are preferable anyway.)

Template Action Hooks are a better option because you do not rely on applying filters to
the_content(), but you may need to find existing hooks in your Theme (preferable) - or if 
needs be to add them yourself into your theme templates. The default Template Layout Hooks 
used for the *Above Content*, *Below Content* and *Login* Sidebars are for [BioShip Framework].

You can add these Sidebars to others Themes either by finding or adding a hook to your Theme 
page templates (see the WordPress Codex for more information on the page template hierarchy.).
Simply look for: `do_action('my_themes_action_hook');` or add it where desired. Then change
the corresponding hook name for that Sidebar on the Content Sidebars Settings Page, so that
when the action hook is fired, the sidebar function is called, outputting the content sidebar.

Alternatively, you can call the sidebars directly in code with the function fcs_get_sidebar(), 
eg. *Above Content Sidebar*
`<?php if (function_exists('fcs_get_sidebar')) {echo fcs_get_sidebar('AboveContent');} ?>`
*Below Content Sidebar*
`<?php if (function_exists('fcs_get_sidebar')) {echo fcs_get_sidebar('BelowContent');} ?>`
(Note: Do NOT forget the function_exist wrappers - they prevent your layout from breaking
if you need to disable the Content Sidebars plugin at any time.)


= How do I use the 'Login' Sidebar? =

The primary purpose of the 'Login' Sidebar is to allow users to login of course. As such,
it will not show for a Logged In User in any case. If your site accepts user registrations 
in any capacity, having somewhere obvious to login can be a good idea. You can alays use a 
conditional Login Widget in your main Sidebar of course, but this way the Login Widget can
be handled separately and positioned differently (eg. across the page, above/below navbar.)

Just drop in a Login Widget into the Login Sidebar (such as a Theme My Login Widget) on your
Appearance - Widgets page and then position the resulting Sidebar wherever you like, by using 
a  Template Action Hook or function call (it will not be auto-added by the Content Filter 
positioning method, as that relates to the Above and Below Content Sidebars only.) You can
place it below your main navigation bar, or above or within your header area for example.

You can change the default hook from 'skeleton_before_header' to a hook used in your Theme 
page template, eg. header.php (again the default hook name used is for [BioShip Framework].)
Again, look in your Theme template for: 
`do_action('my_themes_action_hook');`
or for a function call you can do:
`<?php if (function_exists('fcs_get_sidebar')) {echo fcs_get_sidebar('LoginSidebar');} ?>`


= How do I use the 'Logged In' Sidebar? =

The 'Logged In' or 'Members' fallback Sidebar is available for showing different content 
to Logged In Users. This means you can either hide Join / Signup / Register Widgets for 
registered users if they are logged in, or show the alternative contents of this Sidebar.

This Sidebar can be used as a fallback for either the Above Content, Below Content and/or 
Login Sidebars. In other words, it will show *instead* of any of those sidebars (if you 
have 'Fallback' ticked next to that Sidebar in your settings) - for any Logged In User of 
any role (Wordpress user logged into your site that is!)

This is pretty useful for showing Members Area links, or other user navigation and/or offers.
Or even a role-based menu, for example, using the [Theme My Login Plugin] (User Links Module.)
It has a drap-and-drop interface for all roles from administrator to subscriber. And of 
course, other Members plugins may have their own Widgets that you can use for this.


= How do I use the 'InPost' Sidebars? =

The InPost Sidebars are inserted automatically into the Post Types that you choose on the 
Settings page. You can choose 1 to 3 Sidebars and the paragraph positions where the Sidebars 
will display, and Widgets in these Sidebars will automatically display after these positions.
Posts which do not have enough paragraphs for that sidebar position will not output it.
(These Sidebars are disabled by default so don't forget to uncheck the disable checkboxes.)

You can also set a Content Marker to split paragraphs with (default is `</p>`) and the
priority of sidebar content filter is available for fine-tuning if you need to integrate 
with other content filters. Once you have decided which paragraph positions are suitable, 
go and drop some Widgets in the Sidebars on the Appearance - Widgets page. eg. Advertising 
or AdSense blocks. (Remember there is a limit of 3 AdSense blocks per page, so take note 
if you have them anywhere else on the page.) 

You could also use shortcodes in text widgets for more fine-grained custom control 
(see next question on setting up those.) Now check the display of a relevent post type 
(of sufficient length) to see the output of the InPost Sidebars. You will probably need 
to tweak the CSS for these Sidebars depending on where and how you want them to display, 
eg. `float:left` or `float:right`, plus some matching margins and/or padding etc... 
And that's it, you now have some magical in-context advertising space. Use wisely. :-)


= How do I use Shortcodes within Text Widgets? =

A very flexible way of adding custom content to your Sidebars is through using a Shortcode
within a Text Widget. In order to do so, you need to make sure the Wordpress shortcode 
filter is applied to the widget_text content. Here is what this code looks like:
`<?php if (!has_filter('widget_text','do_shortcode')) {add_filter('widget_text','do_shortcode');} ?>`
You can activate this code from the Settings page for widget text (and optionally widget titles.)
You can also place this code in a PHP file in your /wp-content/mu-plugins/ folder.

Once you know that is in place, you can add any shortcode to a Text Widget and it will be
used to display the return value of the executed shortcode, eg. for [my-custom-shortcode]
`<?php add_shortcode('my-custom-shortcode','my_custom_shortcode_function');`
`function my_custom_shortcode_function() {return 'Welcome!';} ?>`

You may also want to use the Discreet Text Widget for this case, so that if the shortcode 
returns nothing (eg. for an empty shopping cart) then the Text Widget does not show at 
all (ie. the Widget title is not displayed either if there is no Widget text output) 


= How do I use filter individual Widgets for different conditions? =

The easy way to do this is in combination with a *conditional widget plugin*, so you can 
drop those Widgets in the Sidebars of your choice to display a Widget conditionally in 
any of these new Sidebar areas for different purposes. ie. posts or pages or CPTs or
other conditions. eg. [Widget Logic Plugin], [Display Widgets Plugin], etc...


= How do I use filter Sidebars for different conditions? =

For a more advanced or custom setup you can modify any of the Plugin Settings via Filters.
For consistency these filters have the same key as the plugin settings. The full list of
settings (and thus filters) can be seen in the plugin setting initialization list.

For example, you can disable a sidebar according to conditions using these filters:
`fcs_abovecontent_disable, fcs_belowcontent_disable, fcs_login_disable, fcs_member_disable`
`fcs_shortcode_sidebar1, fcs_shortcode_sidebar2, fcs_shortcode_sidebar3`
`fcs_inpost_sidebar1, fcs_inpost_sidebar2, fcs_inpost_sidebar3`

eg. To remove the above content sidebar from the site Front Page only:
`add_filter('fcs_abovecontent_disable','my_custom_sidebar_filter1');`
`function my_custom_sidebar_filter1() {if (is_front_page()) {return 'yes';} }`
(for disbable filters, returning yes indicates the sidebar is to be *disabled*.)

You can also filter the entire sidebar output with the following filters:
`fcs_abovecontent_sidebar, fcs_belowcontent_sidebar, fcs_loginsidebar, fcs_member_sidebar`
`fcs_shortcode_sidebar1, fcs_shortcode_sidebar2, fcs_shortcode_sidebar3`
`fcs_inpost_sidebar1, fcs_inpost_sidebar2, fcs_inpost_sidebar3`

eg. To replace the output of the Above Content Sidebar for the site Front Page only:
`add_filter('fcs_abovecontent_sidebar','my_custom_sidebar_filter2');`
`function my_custom_sidebar_filter2() {if (is_front_page()) {return 'Welcome!';} }`

`*` additionally, `*_loggedout` and `*_loggedin` filters can be used for any sidebar.
eg. To remove the Above Content Sidebar from the Front Page for logged in users:
`add_filter('fcs_abovecontent_sidebar_loggedin','my_custom_sidebar_filter3');`
`function my_custom_sidebar_filter3() {if (is_front_page()) {return '';} }`

Using the filters makes when, where and what your sidebars display for different page
conditions extremely flexible... we leave it up to your unlimited imagination!



== Screenshots ==

1. A screenshot example of the default Content Sidebar Layout Positions.
1. A screenshot example of an InPost paragraphs Content area.
1. A screenshot of the post Sidebar Override Metabox.


== Changelog ==

= 1.5.0 =
* Added nonce checks for settings updates
* Changed function prefix to fcsb

= 1.4.5 = 
* Added Archive and Page Context Sidebar Display Options
* Added optional Excerpt Shortcode Processing
* Added Member Sidebar standalone mode option

= 1.4.0 =
* Improved Overall Plugin Logic
* Improved Fallback Selection Options
* Added Plugin Settings Auto-Filter
* Added InPost Float Options
* Updated Dynamic CSS loader with cachebusting
* Split active/inactive Widget Page display
* Revamped: Metabox Override System
* Made Translation Ready

= 1.3.5 =
* First Public Release Version
* Compact options to single option array
* Added Discreet Text Widget registration
* Added Widget Shortcode Processing option
* Update Freemius to 1.2.1
* Update WordQuest Helper to 1.6.0

= 1.3.0 =
* Added WordQuest integration (1.5.5)
* Added Freemius integration (1.0.5)

= 1.2.0 = 
* Beta Public Version
* Improved LoggedIn Fallback Sidebar Logic
* Added Post Type selection Above/Below Content Sidebars
* Added Post Type selection to InPost Sidebars
* Added CSS Reference Table and completed classes
* Added loggedout and loggedin classes to sidebars
* Added advanced output filters to every Sidebar
* Added _loggedout and _loggedin filters to sidebars
* Change to default hook priority settings
* Improved Helper text on the Settings page
* Fix to plugins.php page Settings link
* Defaulted InPost Sidebars to Disabled

= 1.1.0 =
* Beta Client Version 

= 1.0.0 =
* Development Version


== Upgrade Notice ==


== Other Notes ==

[Content Sidebars Home] (http://wordquest.org/plugins/content-sidebars/)

Like this plugin? Check out more of our free plugins here: 
[WordQuest Alliance Plugins] (http://wordquest.org/plugins/)

Looking for an awesome theme? Check out my child theme framework:
[BioShip Child Theme Framework] (http://bioship.space/)

= Support = 
For support or if you have an idea to improve this plugin:
[Content Sidebars Support Quests] (http://wordquest.org/support/content-sidebars/)

= Donate = 
To make a donation for a feature request, or to activate a supporter subscription:
[Content Sidebars Donation] (http://wordquest.org/contribute/?plugin=content-sidebars)

= Development =
To contribute directly to development, fork on Github and do a pull request:
[Content Sidebars on Github] (http://github.com/majick777/content-sidebars/)

= Planned Updates/Features =
* Subcriber Recognition code via Cookie detection option
* Smart Filters to hide Subscription Widgets from Subscribed Visitors

