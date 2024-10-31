=== [Propeople] Google News Sitemap ===
Contributors: brO_0keN
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DG37YP52Z7SC4
Tags: sitemap, google news, xml, post, google, plugin
Requires at least: 3.2.1
Tested up to: 3.7.1
Stable tag: 1.0.1
License: MIT
License URI: http://plugins.svn.wordpress.org/propeople-google-news-sitemap/trunk/LICENCE

Basic XML sitemap generator for submission to Google News.

== Description ==
Plugin needed for generating XML sitemap for sending it to Google News. It analyzes the publication by date and shows web-form with next options if it is appropriate:

* add this post to sitemap or not;
* access type for reading;
* genres of publication;
* language, in which the article is written;
* stock tickers;
* geolocation data.

Also, plugin having a small tutorial, in which described the information on how to fill the options fields.

In addition, this plugin have the defaults settings, which will be applyed for all new publications.

== Installation ==

= WordPress instalation =
1. Upload `propeople-google-news-sitemap` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Choose needed setting which will be applied as defaults for each new publication.

= Requirements for backend and frontend =
* PHP 5.3 or higher
* jQuery 1.0.0 or higher

== Frequently Asked Questions ==
= How to fill the fields, which plugin provides? =
When you adding the new publication, in web-form of plugin, there is a link `How to fill this fields?`, pressing on it, you will see the modal window with the tutorial.

== Screenshots ==
1. Form and modal window with the tutorial on "add new" or "edit" post page.
2. Plugin settings by default on `Plugins` page.
3. Publication has been added more than two days ago and can't be included to sitemap.

== Changelog ==
= 1.0.1, November 12, 2013 =
* Bug fix: [major] plugin isn't work for multisite.

= 1.0.0, November 11, 2013 =
* Initial release.

== Upgrade Notice ==
= 1.0.1 =
* If you want to use the plugin in multisite mode, that you must update it to version 1.0.1.