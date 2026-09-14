=== Public Shop Floor ===
Contributors: doodlersrage
Donate link:
Tags: woocommerce, shop floor, made to order, job board, manufacturing
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Made-to-order jobs on a real floor. Customers see station and place in line — not a fake tracking page.

== Description ==

Public Shop Floor is a WooCommerce extension for **made-to-order work**. When a flagged product sells, a job appears on a public shop floor and moves station by station when a person at the bench says it is done.

This is not shipment tracking. There is no carrier. The board is the mill, joinery, finish, and packed benches.

= What you get =

* Product checkbox: **Made on the floor**
* A job ticket (e.g. `PSF-1047`) with a private link
* Public board at `/shop-floor/` — job numbers, station, place in line, no customer names
* Merchant kanban under **WooCommerce → Shop floor** (advance, send back, hold, resume, leave floor)
* Configurable stations (`Label | key | verb`)

Held jobs stay on the board but are out of line until someone resumes them. Packed jobs remain until you send them off the floor.

= Requirements =

* WordPress 6.4+
* PHP 8.0+
* WooCommerce 8.2+ (must be installed and active)

Compatible with WooCommerce High-Performance Order Storage (HPOS) and Cart & Checkout blocks.

== Installation ==

1. Upload the `public-shop-floor` folder to `/wp-content/plugins/`, or install the zip via **Plugins → Add New → Upload Plugin**.
2. Activate **WooCommerce**, then **Public Shop Floor**.
3. Edit a product and check **Made on the floor**.
4. Paid orders in Processing (or On hold / Completed) open jobs at the first station.
5. Open `/shop-floor/` on the storefront. Merchant view: **WooCommerce → Shop floor**.

Flush permalinks once after activation if `/shop-floor/` 404s (**Settings → Permalinks → Save**).

== Frequently Asked Questions ==

= Does this track shipments? =

No. Jobs move when someone on the floor advances them. There is no carrier integration.

= Are customer names shown on the public board? =

No. The public board shows job numbers, station, place in line, and optionally product titles.

= Can I change the stations? =

Yes. Under **WooCommerce → Shop floor → Floor settings**, edit the stations list. Each line is `Label | key | verb`. Existing jobs keep the station key they already have.

= What happens to held jobs? =

They stay visible on the board but are out of line until someone resumes them.

== Screenshots ==

1. Public shop floor board with jobs queued by station.
2. Merchant kanban under WooCommerce → Shop floor.
3. Product checkbox: Made on the floor.
4. Customer job ticket with station pipeline.

== Privacy ==

Public Shop Floor does not send data to remote servers. It stores job rows in a custom database table and product/settings options on your site. The public board shows job numbers and station status only — not customer names or addresses. On uninstall, the jobs table and plugin options are removed.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
