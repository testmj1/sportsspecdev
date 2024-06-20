=== Advanced Ads Pro ===
Requires at least: WP 4.9
Tested up to: 6.1
Requires PHP: 7.2
Stable tag: 2.26.1

Advanced Ads Pro is for those who want to perform magic on their ads.

== Distribution ==

The distribution of the software might be limited by copyright and trademark laws.
Copyright and trademark holder: Advanced Ads GmbH.
Please see also https://wpadvancedads.com/terms/.

== Description ==

Advanced Ads Pro extends the free version of Advanced Ads with additional features that help to increase revenue from ads.

Features:

* check delivered ads within the admin bar in the frontend
* Cache Busting
* test placements against each other
* option to limit an ad to be displayed only once per page
* refresh ads without reloading the page
* select ad-related user role for users
* inject ads into any content that uses a filter hook
* Click Fraud Protection
* alternative ads for ad-block users
* Lazy Loading
* place custom code after an ad
* disable all ads by post type
* serve ads on other websites

Placements:

* use display and visitor conditions in placements
* pick any position for the ad in your frontend
* inject ads between posts on posts lists, e.g., home, archive, category
* inject ads based on images, tables, containers, quotes, and any headline level in the content
* ads on random positions in posts (fighting ad blindness)
* ads above the main post headline
* ads in the middle of a post
* background/skin ads
* parallax ads
* set a minimum content length before content injections are happening
* set a minimum amount of words between ads injected into the content
* dedicated placements for bbPress, BuddyPress, and BuddyBoss
* show ads from another blog in a multisite
* repeat content placement injections
* allow Post List placement in any loop on static pages
* ad server to embed ads on other websites

Display and Visitor conditions:

* display ads based on the geolocation
* display ads based on where the user comes from (referrer)
* display ads based on the user agent (browser)
* display ads based on URL parameters (request URI)
* display ads based on user capability
* display ads based on the browser language
* display ads based on browser width
* display ads based on the number of previous page impressions
* display ads based on the number of ad impressions per period
* display ads to new or recurring visitors only
* display ads based on a set cookie
* display ads based on the page template
* display ads based on post metadata
* display ads based on post parent
* display ads based on the day of the week
* display ads based on the language of the page set with WPML
* display ads based on GamiPress points, ranks, and achievements
* display ads based on the BuddyPress profile information
* display ads based on the BuddyBoss profile information and BuddyBoss groups

== Installation ==

Advanced Ads Pro is based on the free Advanced Ads plugin, a simple and powerful ad management solution for WordPress.
You can use Advanced Ads along with any other ad management plugin and don’t need to switch completely.

== Changelog ==

= 2.26.1 (March 13, 2024) =

- Improvement: update German, German (Austria), German (Switzerland) and German (formal) translations
- Improvement: show Ad Health notice when ads are disabled for selected post type

= 2.26.0 (January 31, 2024) =

- Improvement: update German and German (formal) translations
- Improvement: display required modules for adblocker fallback item
- Improvement: display required modules for Lazy Loading
- Improvement: allow use of existing MaxMind database files via filters
- Fix: allow "parent page" and "post meta" display conditions to work with AJAX Cache Busting
- Fix: allow Gravity Forms shortcode to work with AJAX Cache Busting
- Fix: avoid storing browser width when `ADVANCED_ADS_RESPONSIVE_DISABLE_BROWSER_WIDTH` is defined

Build: 2024-03-2063e3cf