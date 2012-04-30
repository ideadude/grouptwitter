=== GroupTwitter ===
Contributors: strangerstudios
Tags: twitter, cache, widget, multiple
Requires at least: 3.3
Tested up to: 3.3.2
Stable tag: .2.1

Cache tweets from multiple Twitter accounts on your site.

== Description ==

This plugin is unsupported, but you may find it useful. You can add multiple Twitter accounts through the admin, then schedule a script to cache the most recent tweets from each account.

There is a simple widget, and more can be done through the various functions in the plugin. Look through the code.

== Installation ==

1. Upload the `grouptwitter` directory to the `/wp-content/plugins/` 
directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add your Twitter accounts through the settings page.
1. Update the cache for the Twitter accounts.

== Frequently Asked Questions ==

No one has asked yet.

== Changelog ==

= .2.1 =
* Added readme.
* Using WP HTTP API instead of CURL to get Twitter XML.
* Renamed Twitter class to GT_Twitter to avoid conflicts.

= .2 =
* Added widget.