# Marketplace submission

This package is ready to upload to the **WooCommerce.com Marketplace** (and optionally WordPress.org). Automated vendor approval and QIT runs happen on Woo’s side after you submit.

## What is already done in this repo

- GPL-2.0-or-later license + `uninstall.php`
- `readme.txt` (WordPress.org format)
- Full i18n text domain `public-shop-floor`
- HPOS + Cart/Checkout blocks compatibility declarations
- Sanitization, escaping, capability checks, nonces
- Listing assets in `.wordpress-org/` (icons, banners, screenshots)
- Release zip via `./bin/build-zip.sh`
- PHPCS / WPCS: `composer install && composer phpcs`

## Build the upload zip

```bash
./bin/build-zip.sh
# → dist/public-shop-floor-1.0.0.zip
```

Do **not** upload the git repo or `.wordpress-org/` assets inside the product zip (the build script excludes them).

---

## WooCommerce.com Marketplace

### 1. Vendor account (required — you must do this)

1. Apply: https://woocommerce.com/partners/
2. Wait for approval, then open **Vendor Dashboard → Submissions → Submit Product**
3. Product type: **Extension**

### 2. Suggested listing copy (paste into the form)

**Product name:** Public Shop Floor

**Short description:**  
Made-to-order jobs on a live shop floor. Customers see station and place in line — not fake shipment tracking.

**Long description:**  
Public Shop Floor turns paid WooCommerce orders into job tickets on a real production board. Flag a product as **Made on the floor**, and when the order is paid a job appears at your first station. Staff advance, send back, hold, or clear jobs from **WooCommerce → Shop floor**. Customers (and anyone you allow) watch progress on `/shop-floor/` — job numbers and stations only, no customer names.

Default stations: Mill → Joinery → Finish → Packed. Fully configurable.

Compatible with High-Performance Order Storage (HPOS) and Cart & Checkout blocks. Does not modify checkout.

**Category suggestions:** Store management / Order management / Manufacturing

**Pricing:** Set your own (one-time or subscription). Must match or beat any price you charge elsewhere. Revenue share applies per Woo Marketplace terms.

### 3. Testing instructions (paste for reviewers)

Environment

- WordPress 7.1+, WooCommerce 11.1+, PHP 8.0+ (8.3 recommended)
- Pretty permalinks enabled
- HPOS enabled (default)

Steps

1. Activate WooCommerce, then Public Shop Floor.
2. If `/shop-floor/` 404s, visit **Settings → Permalinks → Save**.
3. Edit any simple product → check **Made on the floor** → update.
4. Place an order for that product and set status to **Processing**.
5. Confirm a job appears under **WooCommerce → Shop floor** at the first station.
6. Open `/shop-floor/` — job number visible, no customer name.
7. Open the job’s public link — station verb and place in line shown.
8. On the merchant board: **Advance**, **Send back**, **Hold**, **Resume**, and on the last station **Leave the floor**.
9. Confirm held jobs stay visible but out of line; completed jobs leave the active board.
10. Check order admin panel and customer order view for job links; place a test order email and confirm the shop-floor link.

Negative checks

- Product without the checkbox must not create jobs.
- With **Public board** unchecked in Floor settings, `/shop-floor/` returns 403.

### 4. QIT

After upload, Woo runs Activation, Security, PHPCompatibility, Malware, Validation, API, and E2E. Fix any failures in the vendor dashboard discussion thread and re-upload the zip.

### 5. Docs / support URL

Point support to your preferred channel (email or docs site). Until you have one, use the GitHub repo issues: https://github.com/doodersrage/shop-floor/issues

---

## WordPress.org (optional free listing)

1. Create a wordpress.org account and [submit a plugin](https://wordpress.org/plugins/developers/add/).
2. Upload `dist/public-shop-floor-1.0.0.zip`.
3. After approval, commit `.wordpress-org/` assets to the plugin SVN `assets/` directory (or use a deploy action).
4. Update `Contributors:` in `readme.txt` to your wordpress.org username before or right after approval.

---

## Manual checklist before you click Submit

- [ ] Vendor account approved (Woo) **or** wordpress.org account ready
- [ ] `./bin/build-zip.sh` run on this commit
- [ ] Smoke-tested on a staging site with WC 11.x + HPOS
- [ ] Support / docs URL decided
- [ ] Price and refund policy decided (Woo only)
- [ ] `Contributors:` / Author URI updated if you have permanent URLs
