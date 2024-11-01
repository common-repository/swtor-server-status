=== SWTOR Server Status ===
Tags: widget,swtor,status,server
Requires at least: 2.9.2
Tested up to: 3.3.1
Stable tag: 0.3.1

Shows the population, queue size and type (PvP, PvE, RP) of a server from the MMORPG SWTOR (Star Wars - The Old Republic).

== Description ==
This addon shows the status of a server of the MMORPG SWTOR (Star Wars - The Old Republic). It shows if the server is online, population, server type, language and so on.

I'm currently unsure how to "stlye" the information, for now most text is encapsulated with "spans" to make it possible to change the attributes via css. An example is included and can be deactivated in the settings. You can then copy the css into your own stylesheet and adapt it without the fear that they get overwritten the next update. If you create something cool, I would like to include it into this plugin, if it is not too site specific. Just contact me on the wordpress page.

The SWTOR homepage has no official feed for the data, therefore I have to parse the status page. I'm not sure if they will block us, but I would reccomend high cache times to stay friendly!

== Installation ==
Nothing fancy, just like any wordpress addon. After the installation make sure the cache directory (`wp-content/plugins/swtor_server_status/cache`) is writable by wordpress.

If you don't use the automatic installer in the wordpress backend, try the following:

1. Upload and unzip the plugin to the `/wp-content/plugins/` directory
1. Make sure the cache directory (`wp-content/plugins/swtor_server_status/cache`) is writable by wordpress.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Optionally configure the plugin in the settings tab


== Frequently Asked Questions ==
= Can I use this addon without the widget? =

Well, you can add something like the following in your themes code:
`<div class="swtorss">
<?php
$instance = array();
$instance['shard'] = "yourServerName";
$instance['region'] = "eu"; // or "us" or "ap" (for Asia Pacific)
$instance['cache_time'] = 300; // in seconds
$instance['show_last_update'] = true; // or false...
echo(swtor_server_status_html($instance));
?>
</div>`

== Screenshots ==
1. This is how the plugin looks like in my blog


== Changelog ==
= 0.3.1 =
* Fixed a small, but important typo, plugin was showing wrong data
= 0.3 =
* Added support for Asia Pacific region
* fixed parser for the changes in the status page that went live with patch 1.1.5
* removed language for region us since it isn't provided
* added a css "theme", can be activated in the options, thanks to shiftey (http://www.baraans-corner.de/wordpress-plugins/swtor-server-status/#comment-2773)
= 0.2 =
* small typo in code and css fixed
= 0.1 =
* First public release

== Upgrade Notice ==
= 0.3.1
v0.3 updated the parser to Biowares changes. 0.3.1 fixes a typo introduced in 0.3, that made the plugin show data from another server.
= 0.3 =
Please upgrade to this version: The parser had to be updated to biowares changes.

== Restrictions ==
No restrictions so far, that I'm aware of. If you find out SWTOR Server Status doesn't work with another addon, please contact me.
