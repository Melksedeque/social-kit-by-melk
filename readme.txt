=== Social Kit by Melk ===
Contributors: melksedeque
Tags: social media, x, twitter, content generation, gutenberg
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Automatically generates a title, text and caption to promote your posts on social media, with a character counter and an editor panel.

== Description ==

Social Kit by Melk generates, when you save a post, everything you need to promote it on social media: a label and a title for the card, a supporting text and the full caption with the link already included. It starts with X (Twitter), with an architecture ready for other networks in upcoming versions.

Nothing is locked into fixed text: the labels, CTAs, hashtags and stopwords used during generation are configurable through filters, so any WordPress site can adapt them to its own terms.

**Main features**

* Automatic generation when saving or publishing the post (card title, card text, caption and hashtags)
* Dedicated panel in the editor (Gutenberg) with a character counter, copy buttons and an "Open in X" button
* Manually editing a field locks the automatic regeneration for that post, so your adjustments are never overwritten (you can unlock it at any time)
* Optional integration with URL Shortener by Melk (uses the short link automatically if the other plugin is active); without it, the regular post link is used
* Choose which post types (posts, pages, custom post types) have the feature enabled
* Character counter that follows the X rules (links count as 23, emojis as 2), so the caption fits before you post
* Translated into Brazilian Portuguese (pt_BR)

**Privacy**

Social Kit does not use external services, does not track visitors or users and does not send any data anywhere. Everything is generated on your own server by fixed rules, with no AI and no API calls. The "Open in X" button only opens a new browser tab on x.com with the caption pre-filled, and only when you click it; nothing is sent before that.

**Roadmap**

* Support for more networks (Instagram, Threads, Bluesky, LinkedIn)
* Account linking and automatic publishing through the API
* Canva integration to generate the card artwork automatically

== Installation ==

1. Upload the `social-kit-by-melk` folder to `/wp-content/plugins/`, or install it from the WordPress plugin directory.
2. Activate the plugin in **Plugins**.
3. Go to **Settings > Social Kit** and choose the post types where the feature should be active.
4. Edit or publish a post of the chosen type: the "Social Kit" panel appears in the editor sidebar with the fields already generated.

== Frequently Asked Questions ==

= Do I need URL Shortener by Melk to use this plugin? =

No. The short link is used automatically only if URL Shortener by Melk is installed and active; without it, Social Kit uses the regular post link.

= Does the plugin post to social media automatically? =

Not yet. This version generates the texts and provides an "Open in X" button that pre-fills the caption; automatic publishing through the API is on the roadmap.

= Can I customize the labels, CTAs and hashtags? =

Yes, through the `skbm_label_map`, `skbm_cta_map`, `skbm_cta_trim_list`, `skbm_stopwords`, `skbm_networks` and `skbm_resolve_url` filters.

= Do I need an SEO plugin (Yoast, Rank Math...) to use this plugin? =

No. Without an active SEO plugin, the first hashtag uses the first tag of the post as an approximation. If you already use Yoast SEO, Rank Math or another plugin that defines the focus keyword of a post, Social Kit reuses that keyword automatically, with no extra setup and without requiring any of them.

= What happens to my data if I delete the plugin? =

Nothing, by default: your settings and the fields saved in your posts are kept, so reinstalling the plugin picks up where you left off. To remove everything when the plugin is deleted, check "Delete all plugin data" in the Danger Zone of Settings > Social Kit before deleting it.

== Screenshots ==

1. "Social Kit" panel in the editor, with the generated fields and the character counter.
2. Settings screen, with the enabled post types.

== Changelog ==

= 1.0.0 =
* First release.
* Rule-based generation of the card label, title, text and X caption when saving a post (no AI, no external services).
* Editor panel with a character counter that follows the X rules, copy buttons and an "Open in X" button.
* Manual edits lock the automatic regeneration for the post.
* Optional integration with URL Shortener by Melk.
* Settings screen to choose the enabled post types, with an opt-in "Delete all plugin data" option for when the plugin is deleted.
* Brazilian Portuguese (pt_BR) translation included.

== Upgrade Notice ==

= 1.0.0 =
First public release. If you also use URL Shortener by Melk, no extra setup is needed: the short link is picked up automatically.
