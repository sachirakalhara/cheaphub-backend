# CheapHub Project Memory

> **If this file is missing** (workspace resets wipe it — 4 times so far), restore it from `cheaphub-backend` → `docs/CLAUDE-memory.md` on branch `claude/fix-ecommerce-bug-1Vx1d`. Keep both copies in sync when updating.

E-commerce (digital accounts/subscriptions). Owner Sachira, Sri Lanka. Prod https://cheaphub.io. **User has no coding knowledge** — Claude writes all code; user runs server commands and pastes output. Claude has no server access and can't reach cheaphub.io from the workspace. Explain things in plain language.

## Repos, branch, workspace
- `cheaphub-backend` (Laravel 10), `cheaphub-front-end` (CRA React/TS, Tailwind), `cheaphubadminpanel` (CRA4 + **craco**, Vuexy/reactstrap), `cheaphubadmin` (unused).
- ALL work on `claude/fix-ecommerce-bug-1Vx1d`. Never create PRs unless asked. Commit attribution lines per system reminder.
- **Workspace resets:** local clones in /home/user get reset to stale `master`/`main` heads and `node_modules` is wiped. **Never push those heads.** Resetting them with `git reset --hard` is blocked by the permission system. Work in clean worktrees instead:
  `git -C /home/user/<repo> fetch origin <branch> && git -C /home/user/<repo> worktree add --detach <scratchpad>/wt/<repo> origin/<branch>`, commit there, push with `git push origin HEAD:<branch>`. Read remote state with `git show origin/<branch>:<path>`.
- Stop-hook "unpushed commit" warnings caused by those stale heads: user said ignore.
- Rebuild deps to verify builds:
  - frontend: `npm install --legacy-peer-deps --no-package-lock` then `npm install ajv@8 --no-save --no-package-lock --legacy-peer-deps`; `CI=false NODE_OPTIONS=--max-old-space-size=4096 npm run build`
  - admin: `npm ci --legacy-peer-deps --ignore-scripts && rm -rf node_modules/node-sass`; build **`npx craco build`** (NOT react-scripts — @styles aliases) with `NODE_OPTIONS="--max-old-space-size=4096 --openssl-legacy-provider"`
  - backend: no vendor in workspace → `php -l` + logic checks; real test on dev.
  - Never commit lockfile changes; `git add` specific files only.

## Server & deploy (always dev first, then prod)
- Prod backend `/var/www/cheaphub-backend`: `git fetch origin <br> && git merge origin/<br> && php artisan migrate && php artisan config:cache` (merge may open nano — run alone). Add `php artisan route:clear` when routes change. Dev backend `/var/www/dev-cheaphub-backend`: `git pull origin <br>`.
- Customer site: shared source `/var/www/cheaphub-frontend-src`; flag `src/contains/contants.ts` `const IS_DEV`. Prod: sed to false → `npm run build` → copy to `/var/www/cheaphub/build` → **sed back to true + grep verify**. Dev build → `/var/www/dev-cheaphub/build`.
- Admin: dev builds in place `/var/www/dev-cheaphubadminpanel`. Prod: backup to `/var/backups/cheaphub-admin/`, scratch-build in `/var/www/cheaphub-admin-scratch` with `src/services/apiConfig.js` IS_DEV=false, then `rm -rf build && mkdir build && cp -r scratch/build/. build/`; verify `grep -rl --include="*.js" "dev-api.cheaphub.io" build/static/js/` finds nothing (only .js — .map files gave false alarms) and `find build -name "*.map" | wc -l` = 0.
- Always `rm -rf node_modules/.cache` before builds. User hard-refreshes (no CF purge).

## Server facts
- Contabo VPS, nginx, PHP-FPM (two PHP versions installed). **Cloudflare in front** of api.cheaphub.io (and likely the sites).
- `TrustProxies::$proxies` unset → `$request->ip()` returns a **Cloudflare IP**, not the customer's. Any IP-based limit is shared across customers (generic `api` limiter = 60/min by IP). Fix (trust Cloudflare ranges, X-Forwarded-For only) was proposed; user declined for now.
- Prod `APP_DEBUG=false` (set 2026-09-24, backup `.env.bak-20260924`).
- PHP `upload_max_filesize=2M`, `post_max_size=8M` — user decision, do not raise. nginx `client_max_body_size 3M;` in http{} of nginx.conf (backup `nginx.conf.bak-20260923`).
- nginx sites in `/etc/nginx/sites-enabled/` (admin, api, cheaphub.io, dev-admin, dev-api, dev). Pre-existing harmless warnings: duplicate server_name api/dev/www.dev on :80 (dev-api file contains a stray `server_name api.cheaphub.io`).
- **Dev + admin hidden from search:** `/etc/nginx/conf.d/noindex-dev.conf` (map $host → `X-Robots-Tag: noindex, nofollow` for dev, dev-admin, dev-api, admin). Undo: delete file + `nginx -t && systemctl reload nginx`. Static-asset blocks with their own `add_header Cache-Control` don't inherit it (fine).
- Server changes pattern: backup → edit → `nginx -t && systemctl reload nginx`.

## Source maps OFF (don't re-enable)
- Admin: `GENERATE_SOURCEMAP=false` in tracked admin `.env` (9270923).
- Customer: in `package.json` build script `GENERATE_SOURCEMAP=false react-scripts build` (7fa061c) — customer `.env*` is gitignored.

## AI search / SEO (Ceyentra GEO report, Oct 2026)
- Step 1 LIVE: backend `GET /api/v1/sitemap-products.xml` (`SitemapController@products`, `visibility='open'` only — **unlisted (reseller links) and onHold must never appear**; cached 1h; base URL `config('app.storefront_url')`/`STOREFRONT_URL`). Prod: 45 products = 45 links.
- Customer `public/robots.txt` (private + template pages disallowed, explicit AI bot groups, two `Sitemap:` lines), `public/sitemap.xml` (real public pages only), `public/llms.txt` (summary from FAQ/refund policy; email admin@cheaphub.shop). Both sitemaps submitted in Google Search Console → Success.
- Listed products open in a modal; only per-product URLs are `/bulk/:id/:slug` and `/contribution/:id/:slug`.
- Step 2 (pre-render pages for no-JS AI crawlers) NOT started. Risk discussed: more visibility for brand-name reselling (ToS/takedown/payment-provider). Step 3 (agent checkout) not recommended.

## Customer site content
- Contact email **admin@cheaphub.shop**. Contact page (`/contact`) is NOT linked in menus — user's choice, keep. Its fake form (never sent anything) was replaced with ticket button + mailto. Real socials: Facebook, Telegram t.me/cheaphubio, WhatsApp wa.me/94724000012.
- Terms: filler text removed, "Cheaphub.shop" → CheapHub, refund link fixed (65813b4).
- **Card (Marx)** option: `MARX_ENABLED` in `contants.ts` filters Card out of checkout + top-up; currently **true** (paused 713cfd8, re-enabled 8c5a3a0).
- **Wallet top-up minimum $3.50:** backend `config('app.min_topup_amount')`/`MIN_TOPUP_AMOUNT` checked in Marx makePaymentV4 + Heleket createInvoice only when is_wallet; frontend `MIN_TOPUP_AMOUNT` in contants.ts + balance-modal. Keep both in sync.

## Ticket chat image attachments (LIVE)
- `comments` table + nullable `attachment_url/type/size`. `POST v1/ticket/comment` optional `attachment` (image jpg/jpeg/png/webp/gif, max 2MB). Image-only stores `message=''`.
- Upload to s3 BEFORE creating comment; disk `'throw'=>false` so `put()` false is checked. Filename `ticket/attachment/Str::random(40).<ext>` (not uniqid — screenshots may hold payment data).
- Customer `app/tickets/chat-box/Page.tsx`; admin `views/tickets/chat/Chat.js` + `index.js`. Text-only path untouched (separate multipart fn `createTicketCommentWithAttachment`).

## Admin panel
- Order details and customer profile load from URL (`/order/<order_id>?id=<id>`, `/customer/<name>?id=<id>`) as well as router state → can open in new tab/refresh.
- Ticket sidebar: Order Number / Customer Name open in new tab, email mailto, bulk price uses `bulk_product.price`. Ticket list: Order Number column; status/page/search kept in URL (history.replace).
- Chat avatars: admin = CheapHub logo, customer = initials.
- Ticket subscription status (a32adb4 admin, f8dddc6 backend): `utility/orderSubscriptionStatus.js` mirrors customer `utils.ts checkOrderValidStatus` + `orderCard.tsx getStatusBadge` — **change both together**. `chat/LinkedOrderStatus.js` box in sidebar + mobile; list badge uses `order.items.package:id,expiry_duration` eager load in TicketRepository::all.
- Ticket sidebar description still shows raw HTML for some products — **user said leave it**.

## Coupons
- Use `Coupon::isExpired()` only — `expiry_date` accessor returns d-m-Y STRING. Never change that accessor. Picker `dateFormat: 'd-m-Y'`.
- `ProcessCouponSchedules` re-activates inactive coupons with past `scheduled_start`; manual switch-off clears it. Tinker deactivation doesn't stick. Admin has Active toggle.
- is_active + expiry checked in cart apply/load and all 3 payment flows; `getCart` drops stale coupon. `DeleteExpiredCoupons` dead — leave.

## Security / payments
- onHold products blocked at add-to-cart + Marx + Heleket + Wallet. `unlisted` MUST stay purchasable (reseller links). Admin uses public `contribution/product/{id}`.
- Marx key in `.env` `MARX_MERCHANT_KEY`. `is_wallet` overloaded: gateway flows = top-up; WalletRepository = paid-with-wallet.
- Payment flows highest-risk: test on dev first.
- **Login rate limits** were built and reverted before deploy (6b7308c → e5d7ddf) — user declined ("don't want too many fixes"). Don't redo without asking.

## Open items (user decides)
1. **PHP security update** (Contabo email CVE-2026-109618/109619, RCE) — deferred by user. First step read-only: `php -v; ls /etc/php/; apt-get update -qq && apt list --upgradable | grep -i php`. Plan: snapshot → upgrade PHP packages only → restart php-fpm.
2. Backend dependency vulnerabilities (~120, 3 critical) + QUEUE_CONNECTION=sync.
3. AI search Step 2.
4. Optional: rotate AWS/DB keys (debug was on in prod for a while); clean nginx duplicate server_name.
5. Template bloat in customer site (unused demo pages).

## Users / testing
- DEV: sachira.kalhara.2@gmail.com is a CUSTOMER (admin = +admin alias). PROD: it IS super_admin.
- Tinker: single-quote `--execute='...'`; use `empty($x)` not `!$x`.
- Verify behaviour by executing logic, not just reading. `php -l` every changed PHP file.
