=== Plugin Name ===
Contributors: Templarian
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=JAJEKK28BB6EQ&lc=US&item_name=Templarian&item_number=bbcode&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: bbcode, editor, bbcode editor, formatting, posts, plugin, format, organize
Requires at least: 2.0.2
Tested up to: 2.7.0
Stable tag: 1.0.1

BBcode for your Wordpress Blog, but with an easy to use editor.

== Description ==

The plugin is pretty simple in nature and allows you to fully create BBcode to use in the content of your wordpress blog.

I would highly recommend that you view through the FAQ section of this page (although most should be able to use it in a matter of seconds with no problems).

I'm busy, but if you donate I'll find time to convert my current CMS's formatter to Wordpress (imagine this plugin supporting Textile/Markdown/BBcode with a very simple editor).

== Installation ==

1. Upload `bbcode-w-editor.php` and `bbcode.xml` to the `/wp-content/plugins/bbcode-w-editor` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

NOTE: When version 1.1+ comes out users will have to rename `default_bbcode.xml` to `bbcode.xml` or Wordpress will produce errors. This is for the simple fact I do not want to overwrite the bbcode.xml with the auto update.

== Frequently Asked Questions ==

= How do I add my own BBcode? =

Please look under the `Settings` &gt; `BBcode`.

= How do I get the blogs URL into a tag? =

`[~URL]`

Please ask and I will add any predefined variables like above in.

= Are there BBcode Examples? =

Below are a few simple ones:

= URL Type 1 =
`Tag (input):
[url]<1>[/url]
HTML (output):
<a href="[1]">[1]</a>`

= URL Type 2 =

`Tag (input):
[url=<1>]<2>[/url]
HTML (output):
<a href="[1]">[2]</a>`

= Bold =

`Tag (input):
[b]<1>[/b]
HTML (output):
<strong>[1]</strong>`

= Youtube (SWFobject might be best, but meh) =

`Tag (input):
[youtube=<1> width=<2> height=<3>]
HTML (output):
<object width="[2]" height="[3]">
<param name="movie" value="http://www.youtube.com/v/[1]&hl=en"></param><param name="wmode" value="transparent"></param>
<embed src="http://www.youtube.com/v/[1]&hl=en" type="application/x-shockwave-flash" wmode="transparent" width="[2]" height="[3]"></embed>
</object>`

I'll be sure to edit this later, but once you get the general idea you can basically just add your own.

== Screenshots ==

1. A list of all BBcode you've made (these are the default ones included)