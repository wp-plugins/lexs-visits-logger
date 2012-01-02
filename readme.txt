=== Lex's Visits Logger ===
Contributors: Lex--
Donate link: 
Tags: statistics, stats, visits, logs
Requires at least: 3.3
Tested up to: 3.3
Stable tag: trunk

A plugin to log information about your visitors.
Logs are stored in files on your server for later exploitation.


== Description ==

There are several ways to track visitors on your blog.  
If you have a blog hosted on [Wordpress.com](http://wordpress.com "Wordpress.com"), you already have everything you need.  
If your blog is hosted somewhere else, several solutions are available.

* If you want to link your blog with Wordpress.com, you can use [JetPack](http://wordpress.org/extend/plugins/stats/ "JetPack").
* As an alternative, you can also use web solutions, like Google Analytics.
* If you do not trust anyone, you may prefer to install your own software for web analytics. [Piwik](http://piwik.org/ "Piwik") is such an option.
* Eventually, if you find these self-hosted solutions too greedy, you will probably need another solution.

This plugin goes in the last category.  
It provides a way to store information about your visitors in files that will be saved on your own server. 

The main advantage is that you do not need a(nother) database to track your visitors.  
Visit logs can rotate monthly or on a daily basis. This plugin only stores information. Analysis will have to be done in a distinct step, either with a log analyzer, or with reporting tools like JasperSoft, Eclipse BIRT or Pentaho. Notice that these logs can also be read without tools.

= Sample Log =

Here is a sample of such a log:

> visit-time = 1325351804  
> incoming-page = /galerie/  
> referrer = http://www.google.fr/search?q=fanny+veyrac&hl=fr&amp;client=ms-android-samsung&amp;gl=fr&source=android-launcher-widget&amp;etc...  
> ip-address = 82.240.138.xxx  

As you can see, up to 4 fields can be stored per visit.  
The **referrer** is kept only when it is available. It indicates the page that led to the **incoming page**.  
The **visit time** is a timestamp which indicates the visit time (server time).  
And the **IP address** is the one of the visitor. It can be used to determine the country of the visitor.

More generally, this plugin is useful to know the trend in your visits.  
It is in particular useful to know the websites that have links towards your blog.

= Options =

This plugin provides a menu in the administration panel.  
It allows to configure:

* The **logging directory**, i.e. the directory in which the logs will be saved. This directory will be created if it does not exist. It will have to be protected explicitely, e.g. with a *.htaccess* file. It must be given as a path from the web server root (e.g. "/my/stats/").
* The **tracking strategy**, i.e. which visits you want to log. Currently, there are 3 strategies, listed below.
* The third and last option is about the **logs rotation**. Basically, it is about whether you want to have one file per month (monthly rotation) or one log per day (daily rotation).

The 3 tracking strategies are the following ones:

* **Daily:** a cookie is stored by the visitor. There will be 1 log entry per cookie and per day. If a user comes twice a day, there will be only 1 log entry for him.
* **All:** every visits is logged. With our previous example, there will be 2 log entries for our user.
* **None:** this is to disable the logging (meant to be used temporarily).

Note that other strategies could be implemented.


== Installation ==

1. Unzip the plugin archive on your local disk
1. Upload the `lex-visits-logger` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go into `Settings > Visits Logger` and define the **logging directory**

== Frequently Asked Questions ==

Nothing for the moment.

== Screenshots ==

1. The plugin settings in the administration panel.

== Changelog ==

= 1.0 =
* Initial version.

== Upgrade Notice ==

Nothing for now.
