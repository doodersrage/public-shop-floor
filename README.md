# Public Shop Floor

A WooCommerce plugin for **made-to-order work**. When a flagged product sells, a job appears on a public shop floor and moves station by station when a person at the bench says it is done.

This is not shipment tracking. There is no carrier. The board is the mill, joinery, finish, and packed benches.

## What you get

- Product checkbox: **Made on the floor**
- A job ticket (`PSF-1047`) with a private link
- Public board at `/shop-floor/` — job numbers, station, place in line, no customer names
- Merchant kanban under **WooCommerce → Shop floor** (advance, send back, hold, resume, leave floor)
- Configurable stations (`Label | key | verb`)

Held jobs stay on the board but are out of line until someone resumes them. Packed jobs remain until you send them off the floor.

## Requirements

- WordPress 6.4+
- PHP 8.0+
- WooCommerce 8.2+ (must be installed and active)

Compatible with HPOS and Cart & Checkout blocks.

## Install

1. Copy the `public-shop-floor` folder into `wp-content/plugins/` (or upload the release zip).
2. Activate **WooCommerce**, then **Public Shop Floor**.
3. Edit a product and check **Made on the floor**.
4. Paid orders in Processing (or On hold / Completed) open jobs at the first station.
5. Open `/shop-floor/` on the storefront. Merchant view: **WooCommerce → Shop floor**.

Flush permalinks once after activation if `/shop-floor/` 404s (**Settings → Permalinks → Save**).

## Stations

Default line: Mill → Joinery → Finish → Packed.

Change them under **WooCommerce → Shop floor → Floor settings**. Each line is `Label | key | verb` (the verb is the “being …” copy on the ticket). Existing jobs keep the station key they already have.

## Packaging for WordPress.org / Woo Marketplace

```bash
./bin/build-zip.sh
```

This produces `dist/public-shop-floor-1.0.0.zip` with the correct plugin folder name.

Directory assets (icon, banner, screenshots) for the WordPress.org listing live in `.wordpress-org/` after you add them — see that folder’s README.

## License

GPL-2.0-or-later
