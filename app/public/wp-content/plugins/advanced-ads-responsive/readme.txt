=== Advanced Ads – AMP Ads ===
Requires at least: 3.5
Tested up to: 6.3
Requires PHP: 7.2
Stable tag: 1.12.1

Ready your ads for AMP power!

== Distribution ==

The distribution of the software might be limited by copyright and trademark laws.
Copyright and trademark holder: Advanced Ads GmbH.
Please see also https://wpadvancedads.com/terms/.

== Description ==

This add-on ensures your ads effortlessly integrate with Accelerated Mobile Pages. Experience superior ad engagement, higher click rates, and amplified revenue with ease.

**Features**

* convert AdSense automatically into AMP format
* support AMP versions of every ad network that offers them
* provide a display condition to show or hide specific ads on AMP pages
* set custom sizes for AdSense responsive ads
* set default AdSense sizes to rectangle, vertical, or horizontal
* customize AdSense Multiplex ad units for desktop and mobile

Tested with the following AMP plugins

* AMP (by AMP Project Contributors)
* AMP for WP

**external libraries**

* Mobile Detect Library v2.8.22, https://github.com/serbanghita/Mobile-Detect/blob/master/Mobile_Detect.php

== Installation ==

AMP Ads is based on the free Advanced Ads plugin, a simple and powerful ad management solution for WordPress. Before using this plugin download, install, and activate Advanced Ads for free from http://wordpress.org/plugins/advanced-ads/.
You can use Advanced Ads along with any other ad management plugin and don’t need to switch completely.

== Changelog ==

= 1.12.1 =
- Fix: prevent notification from showing infinitely

= 1.12.0 =

- Feature: rebrand add-on as “Advanced Ads AMP Ads”
- Feature: move the Browser Width condition to Advanced Ads Pro
- Feature: move feature to reload ads on screen size change to Advanced Ads Pro
- Feature: move feature to enforce responsive image ads for themes without support to Advanced Ads Pro
- Feature: remove the size assistant feature that helped determine the size of ads in the frontend

= 1.11.0 =

- Improvement: update AdSense code script tags to align with their latest version
- Improvement: remove an old version of the browser width condition that was deprecated in 2015
- Improvement: deprecate the Responsive Ads list. See the removal schedule on https://wpadvancedads.com/manual/deprecated-features/.
- Improvement: update UI of an AMP related notice on the ad edit page
- Improvement: update Arabic, German, Slovenian translations
- Fix: pass empty string instead of null to `add_submenu_page()`

= 1.10.6 =

- Improvement: add Slovenian translations
- Improvement: update Danish translations
- Fix: prevent undefined index notice

= 1.10.5 =

- Fix: remove workflow directory from plugin

= 1.10.4 =

- Improvement: add German (Austria, Switzerland), Greek translations
- Fix: prevent removing advanced default size of responsive AdSense ads when re-saving ad

= 1.10.3 =

- Improvement: add Arabic translations
- Improvement: update Polish translations
- Fix: prevent PHP 8 deprecation notices

= 1.10.2 =

- fix flash of unstyled content (FOUC) in firefox

= 1.10.1 =

- updated Vietnamese and Danish translations

= 1.10.0 =

- moved "Enable AMP Auto ads" option to Advanced Ads 1.24.0

= 1.9.2 =

- compatibility with the official AMP plugin version 2.0

= 1.9.1 =

- adjusted AdSense AMP codes to work with responsive widths after AdSense seems to have either changed something or introduce a bug – so this fix might just be temporary
- moved AMP Auto ads option to existing Auto ads option. Needs Advanced Ads 1.17.12
- prepared AMP tracking method for Tracking 2.0

= 1.9 =

- all AdSense ad units have an appropriate AMP version now
- removed fallback option for AdSense ads without an AMP version since it is no longer needed
- fixed issue with AMP Auto ads and the AMP for WP plugin
- fixed issue with AMP Auto ads in Reader mode of the official AMP plugin

= 1.8.10 =

* added compatibility with Advanced Ads Pro to use conditions in placements

= 1.8.9 =

* added compatibility to work with Advanced Ads Pro 2.5 on AMP pages

= 1.8.8 =

* convert responsive full-width AdSense ads to AMP if possible

= 1.8.7 =

* fixed issue on plugin activation caused by changes in 1.8.6

= 1.8.6 =

* compatibility with new versions of the AMP Plugin for WordPress: made AMP Auto ads for AdSense work, prevented validation errors
* added Polish translations
* updated Italian translations
* updated German translations

= 1.8.5 =

* added German translations
* added compatibility with latest versions of "Accelerated Mobile Pages for WP" plugin

= 1.8.4 =

* fixed AMP option name that broke the AdSense background option in the basic plugin

= 1.8.3 =

* fixed resize assistant causing responsive ads and AdSense not to show up

= 1.8.2 =

* removed comment that caused a false positive warning from WordFence – there was never Malware here

= 1.8.1 =

* added option for fallback width
* compatibility with SmartMag theme
* added Italian translation
* added French translation

= 1.8 =

* implemented AMP Auto ads for Adsense
* added posibility to set fallback browser width

= 1.7.3 =

* set `ADVANCED_ADS_RESPONSIVE_DISABLE_BROWSER_WIDTH` constant to disable Browser Width condition (and not save any cookies)

= 1.7.2 =

* added possibility to customize Adsense Responsive Matched Content unit

= 1.7.1 =

* fixed an issue caused by the "AMP for WP" plugin with advanced Responsive Adsense ads

= 1.7 =

* automatically convert all AdSense ads into an AMP format, see https://wpadvancedads.com/manual/amp-adsense-wordpress/
* updated German translation

= 1.6.2 =

* compatibility with handling responsive image ads in Advanced Ads version 1.8.21

= 1.6.1 =

* don’t track AMP impressions locally in Tracking add-on if Analytics method is selected
* updated Spanish translation
* secured backward compatibility with older versions of the basic plugin

= 1.6 =

* allow all ad types on AMP pages since the major AMP plugins filter invalid code automatically
* please reach out to us if you experience any issues with this new default behavior

= 1.5.2 =

* hotfix to save browser width cookie correctly

= 1.5.1 =

* removed old overview widget logic
* do not reload ads on iOS when the 'Reload ads on resize' option is enabled and the user scrolls the page
* allow image ad type on AMP pages also outside of 'the_content'

= 1.5 =

* moved AMP check function to basic plugin
* moved AMP warning for AdSense to new ad notices section
* minor fixes to textdomains and labels

= 1.4.5 =

* reload ads on screen resize is now optional and off by default, since it caused issues with some kinds of ads
* updated pot file and German translation

= 1.4.4 =

* use cookie functions from basic plugin
* trigger event when screen is resized to reload ads when cache-busting is enabled
* fixed dependency with basic plugin

= 1.4.3 =

* made compatible with AdSense Matched Content
* fixed possible issue when jQuery is not yet loaded

= 1.4.2 =

* show warning on AdSense types that don’t support AMP
* fixed positioning and label size of AMP ads

= 1.4.1 =

* added support for WP AMP plugin

= 1.4 =

* added automatic AMP support for AdSense
* added AMP Display Condition
* added AMP ad type to allow any ad network’s AMP format

= 1.3.2 =

* fix to keep additional sizes for advanced responsive AdSense ads

= 1.3.1 =

* fix to run Responsive add-on along with WP Mobile Detect plugin

= 1.3 =

* added option to force responsive image ads if not supported by the theme
* added tablet detection
* set default AdSense sizes to rectangle, vertical or horizontal

= 1.2.9 =

* fixed link to responsive overview page on blogs in subdirectories
* added Spanish and German localization

= 1.2.8 =

* updated user rights check
* fix for displaying AdSense ads as non-superadmin
* fix frontend helper showing an empty width in Chrome browser
* fix frontend helper working with cache busting in Pro
* fix for saving current browser width if tooltip is enabled

= 1.2.7 =

* fix for manually sized responsive ads

= 1.2.6 =

* fixed manual sizing for cache-busted ads
* fixed saved width on iPhone

= 1.2.5 =

* prevent ads from covering the frontend assistant

= 1.2.4 =

* show warning if Advanced Ads is not installed
* changed class of active buttons to align with main plugin css
* updated plugin link
* added plugin link to license page

= 1.2.3 =

* moved licensing code to main plugin
* added adsbygoogle library reference

= 1.2.2 =

* the list of responsive ads now only includes the new visitor conditions type

= 1.2.1 =

* moved browser width conditions to new visitor conditions api
* updated all class names from "Advads_" to "Advanced_Ads_"
* fixed minor error message in dashboard

= 1.2.0 =

* added frontend assistant to display ad, container, and window size for admins
* added license input and auto updates

= 1.1.4 =

* fix installation error
* changed widget on overview page

= 1.1.3 =

* change of color in responsive ad list
* minor bugfix in ad list
* link to plugin page changed

= 1.1.2 =

* minor fixes to the responsive ads list layout
* added legend below responsive ads list

= 1.1.1 =

* added list of ads by their responsive settings on the dashboard

= 1.1.0 =

* added support for AdSense manual responsive ad sizes
* hide ad settings, if option not enabled
* fixed issue when base plugin is not loaded before the add-on

= 1.0.2 =

* renaming the plugin

= 1.0.1 =

* bugfix - desktop fallback was not saved

= 1.0 =
* first plugin version

Build: 2023-11-58554a24