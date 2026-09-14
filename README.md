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
- WooCommerce (must be installed and active)

## Install

1. Copy the `public-shop-floor` folder into `wp-content/plugins/`.
2. Activate **WooCommerce**, then **Public Shop Floor**.
3. Edit a product and check **Made on the floor**.
4. Paid orders in Processing (or On hold / Completed) open jobs at the first station.
5. Open `/shop-floor/` on the storefront. Merchant view: **WooCommerce → Shop floor**.

Flush permalinks once after activation if `/shop-floor/` 404s (**Settings → Permalinks → Save**).

## Stations

Default line: Mill → Joinery → Finish → Packed.

Change them under **WooCommerce → Shop floor → Stations**. Each line is `Label | key | verb` (the verb is the “being …” copy on the ticket). Existing jobs keep the station key they already have.

## License

GPL-2.0-or-later
# shop-floor
