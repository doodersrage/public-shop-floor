#!/usr/bin/env bash
# Build a WordPress.org / Marketplace-ready zip with the correct plugin folder name.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SLUG="public-shop-floor"
VERSION="$(grep -E "^\s*\*\s*Version:" "$ROOT/public-shop-floor.php" | head -1 | sed -E 's/.*Version:\s*//')"
OUT_DIR="$ROOT/dist"
STAGE="$OUT_DIR/stage"
ZIP_NAME="${SLUG}-${VERSION}.zip"

mkdir -p "$OUT_DIR"
rm -rf "$STAGE"
mkdir -p "$STAGE/$SLUG"

# Prefer rsync with .distignore; fall back to a simple copy exclude list.
if command -v rsync >/dev/null 2>&1; then
	rsync -a --delete \
		--exclude-from="$ROOT/.distignore" \
		--exclude dist \
		"$ROOT/" "$STAGE/$SLUG/"
else
	# Minimal fallback when rsync is unavailable.
	cp -a "$ROOT/public-shop-floor.php" "$ROOT/uninstall.php" "$ROOT/LICENSE" "$ROOT/readme.txt" "$STAGE/$SLUG/"
	cp -a "$ROOT/includes" "$ROOT/templates" "$ROOT/assets" "$STAGE/$SLUG/"
	mkdir -p "$STAGE/$SLUG/languages"
	touch "$STAGE/$SLUG/languages/.gitkeep"
fi

# Ensure empty languages dir ships for Domain Path.
mkdir -p "$STAGE/$SLUG/languages"
touch "$STAGE/$SLUG/languages/.gitkeep"

(
	cd "$STAGE"
	rm -f "$OUT_DIR/$ZIP_NAME"
	zip -rq "$OUT_DIR/$ZIP_NAME" "$SLUG"
)

rm -rf "$STAGE"
echo "Built $OUT_DIR/$ZIP_NAME"
