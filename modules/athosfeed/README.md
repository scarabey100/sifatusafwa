# Athos Integration for PrestaShop

Production-oriented product/combination feed foundation for Athos. The module is isolated from `mncklevu`: it does not alter its configuration, overrides, templates, JavaScript, search routes, category pages or recommendations. Athos frontend code is disabled by default and can only load in explicit `test` mode.

## Verified repository context

This checkout is a deployment-oriented partial PrestaShop tree: core `config/`, `classes/`, `app/` and root `composer.json` are not committed. Consequently the exact installed PrestaShop version, enabled shops/languages/currencies and production PHP version cannot be derived from this repository alone. The existing module declares compatibility from PrestaShop 1.7.6, while the requested target is PrestaShop 8.2.8/PHP 8.1. Local checks use the container PHP shown in the delivery report. No PrestaShop-only API introduced in version 8 is intentionally used.

The initial `athosfeed` commit provided a provider, normalizer, in-memory batch builder, JSONL serializer, basic logger, fixture and CLI script. Review found that it did **not** provide atomic publication, file locking, validation, endpoint authentication, BO configuration, explicit pricing context, category paths, centralized schema, required Athos variant fields, status/metrics or an isolated frontend adapter. Its CLI also wrote one batch rather than a production full feed.

## Current Klevu feature matrix

| Current feature | Existing implementation | Required for Athos | Planned/implemented approach | Status |
|---|---|---:|---|---|
| Shop-scoped settings | `MNCKLEVU_*` adapter | yes | independent `ATHOS_FEED_*` settings | implemented |
| Full sync | token front controller, language loop, offset batches, valid markers | yes | keyset full export to atomic NDJSON file | implemented locally |
| Partial product update/delete | product CRUD hooks | yes | future idempotent queue abstraction | prepared in architecture, not registered |
| Batch size | BO setting | yes | 1–1000 BO/CLI setting | implemented |
| Parent/variants | presenter records, `p`/`v`, optional `itemGroupId` | yes | sequential product then variants; Athos `__parent_*` fields | implemented; parent contract must be confirmed |
| Prices/currencies | presenter regular/final plus enabled currency conversion | yes | configured shop/currency/country/unidentified group; no visitor cart | implemented; contract test blocked |
| Stock/back-order | presenter quantity, `allow_oosp`, order availability | yes | quantity, `available_for_order`, `__in_stock`, configured OOS inclusion | implemented |
| Category facets | hierarchy and category page custom attributes | yes | IDs, names and `Parent>Child` paths | implemented |
| Features/attributes | configured Klevu `other` facets and extension hooks | yes | maps plus flattened `feature_*` / `attribute_*` fields | implemented |
| Images | presenter cover image | yes | cover/additional/combination image fallback | implemented |
| Autocomplete | Klevu quick-search JS and theme template | yes | Snap integration point | SDK verification blocked |
| Search results/facets/sorting | Klevu landing JS and large custom template | yes | isolated Snap adapter; Core API only if spike finds gaps | adapter prepared, parity blocked |
| Category listing | controller overrides and Klevu category template | yes | feature-flagged future mount; standard newest order retained otherwise | integration point prepared |
| Product recommendations | `displayFooterProduct` plus Klevu content | yes | reusable zone renderer | prepared, disabled |
| Cart recommendations | custom recommendation/theming path | yes | reusable zone renderer | prepared, disabled |
| Product card/sliders | server theming and custom Slick templates | yes | validate Snap templating/events before enabling | blocked on SDK/account |

`mncklevu` remains unchanged.

## Architecture

* `Provider/PrestaShopProductDataProvider` reads one deterministic product batch for an explicit shop/language and obtains products, combinations, prices, stock, images, categories and localized facets.
* `Schema/FeedSchema` owns required fields and deterministic facet-field names.
* `Normalizer/ProductNormalizer` creates transport-neutral records and Athos-reserved variant fields.
* `Feed/ProductFeedBuilder` keeps each parent's variants adjacent and isolates product/combination failures.
* `Contract/CatalogChangeQueueInterface` defines the idempotent seam for later upsert/deactivate/delete hooks; no catalog mutation hooks are registered before Athos confirms its incremental contract.
* `Serializer/JsonLinesSerializer` serializes one record at a time as UTF-8 NDJSON.
* `Validation/FeedValidator` streams the candidate file and rejects malformed/unsafe feeds.
* `Export/ExportManager` loops through keyset batches, writes a same-directory temporary file, holds an exclusive per-shop/language lock, validates, and atomically renames. Failure removes only the candidate, preserving the previous feed.
* `controllers/front/feed.php` only streams the previously generated fixed-path file after token and optional IP authorization. It never builds a catalog in the request.
* `controllers/front/cron.php` runs a protected export. CLI remains preferred for observability.
* `views/js/adapter.js` and the shared zone template are a deliberately small SDK boundary; they are inert unless BO mode is `test`, frontend is enabled and an HTTPS SDK URL is configured.

Generated feeds and locks live in `modules/athosfeed/var/`, are denied direct web access and ignored by Git.

## Feed schema and mapping

Field names are centralized where derivation is required. Athos names remain subject to sandbox contract verification.

| PrestaShop source | Feed/Athos candidate field |
|---|---|
| synthetic product/combination key | `id` |
| `product.id_product` | `product_id` |
| `product_attribute.id_product_attribute` | `combination_id` |
| product/combination reference | `sku` |
| product/combination barcode | `ean13`, `upc` |
| `product_lang` | `name`, `description_short`, `description` |
| `Link::getProductLink` | `url` |
| cover/combination image | `thumbnail_url` |
| other product images | `additional_images` |
| price with reduction/specific price | `price` |
| price without reduction | `retail_price` |
| derived | `discount_amount`, `on_sale` |
| explicit currency | `currency` |
| `StockAvailable` | `quantity`, `__in_stock`, `__in_stock_pct` |
| product shop flags | `active`, `available_for_order` |
| manufacturer | `brand` |
| all product categories | `category_ids`, `category_names`, `category_paths` |
| default category | `default_category_id`, `default_category` |
| reference/barcodes/name | `searchable_keywords` |
| localized features | `features`, individual `feature_{slug}` fields |
| combination attributes | `attributes`, individual `attribute_{slug}` fields |
| combination relation | `__parent_id`, `__parent_title`, `__parent_image` |
| combination ordering/options | `__variant_position`, `__standard_options`, `__selected_options` |
| color group metadata | `__swatch_options` |
| product timestamps | `date_created`, `date_updated` |
| explicit context | `language`, `language_id`, `shop_id` |

Required candidate fields are `id`, `sku`, `name`, `url`, final `price` and `thumbnail_url`. A record missing one is logged and skipped. The minimum valid record count is configurable: keep the production default of 10, but use 1 for a deliberately small development catalog.

## Variants

The current candidate contract emits a physical parent record followed immediately by all variants sorted by combination ID. A variant falls back to parent SKU/EAN/UPC and cover image. This preserves grouping but whether Athos wants the physical parent in addition to children must be confirmed in the real Data Source. If parent ingestion causes duplicate search hits, parent emission must become a configuration mode before production indexing.

## Pricing context

Before loading products, export creates an explicit PrestaShop context from configured `shop`, `language`, `currency`, `country` and customer group IDs, clears cart/customer identity, and calls `Product::getPriceStatic` with and without active reductions for each product/combination. Defaults are the shop's default currency/country and unidentified visitor group. Thus export is not based on an arbitrary browser session. Customer-specific pricing is intentionally excluded. If multiple group prices must be searchable, add a pricing strategy/Live Pricing adapter only after Athos confirms its contract.

## Install and Back Office

Install the existing `athosfeed` module normally. It creates no database tables and registers only passive header/product/cart display hooks. Installation generates random feed and cron tokens; neither is rendered in BO. Uninstall removes module configuration and generated artifacts, not Klevu data.

BO supports mode, batch size, explicit pricing IDs, inactive/OOS inclusion, optional IP allowlist, Snap public settings, search/category flags, recommendation zones and token rotation. It shows a secret-free endpoint and the last export metrics. A BO save never runs a long export.

`production` mode does **not** activate the frontend adapter. Frontend loading is intentionally restricted to `test` mode plus its separate flag.

## CLI, cron and endpoint

```bash
# Diagnose the selected shop/language and effective export settings
php modules/athosfeed/bin/console.php diagnose --shop=1 --language=1

# Generate and atomically publish the complete feed
php modules/athosfeed/bin/console.php export --shop=1 --language=1 --batch-size=100

# Small development catalog; production default remains 10
php modules/athosfeed/bin/console.php export --shop=1 --language=1 --minimum-records=1

# Validate an already published feed
php modules/athosfeed/bin/console.php validate --shop=1 --language=1

# Inspect saved execution statistics
php modules/athosfeed/bin/console.php status --shop=1
```

CLI returns non-zero on failure and takes credentials only from PrestaShop configuration. Protected cron:

If the Back Office total is much larger than the exported feed, run `diagnose` first. `products_all_shops` is the global catalog count, while `active_products_in_shop` is the actual default export scope. The command also reports missing product references and cover images, both of which can make records fail required-field validation. Its `module_version` and `minimum_records` values confirm that the CLI container is running the expected module files and settings.

```text
/module/athosfeed/cron?shop=1&language=1&token=<cron-secret>
```

Stable feed endpoint:

```text
/module/athosfeed/feed?shop=1&language=1&token=<feed-secret>
```

Authentication accepts a preferred `Authorization: Bearer <feed-secret>` header and a token query parameter for schedulers that cannot send headers; both use `hash_equals`. Optional source IPs are an additional restriction, never the sole credential. File names derive only from integer shop/language IDs, so user input cannot select arbitrary paths. The response uses `application/x-ndjson`, `Content-Length`, `nosniff` and chunked file reads. Tokens are not logged or displayed in HTML. Confirm the supported authentication method with Athos before production.

## Validation and operational safety

Validation checks: minimum record count, required fields, unique IDs, URL/image URL syntax, non-negative numeric price, variant parent, valid JSON, empty lines, grouping sequence, and likely secrets/filesystem paths. JSON encoding rejects invalid UTF-8. Critical open/write/serialize/validation/rename errors abort publication. Per-record source errors are logged with execution/shop/language context and skipped.

The exporter records duration, file size, products, variants, skipped/errors, cursor and peak PHP memory. Keyset pagination (`id_product > cursor ORDER BY id_product`) avoids offset drift; only one bounded batch is resident. There remain unavoidable model-level N+1 calls for localized category paths, combinations and images; production profiling is required before query consolidation.

## Self-Snap technical spike

The supplied Athos support, developer and Snap documentation URLs could not be fetched in the current environment (HTTP proxy 403 / browsing service 401), and no Athos account/public SDK configuration is present. Therefore SDK globals, component names, callbacks, autocomplete, facets/order, search/category rendering and multiple merchandising zones **cannot honestly be marked verified**.

The repository-side spike establishes only a safe boundary: configurable HTTPS script, public JSON config, no fake responses, a reusable zone mount, product/cart hooks and strict disabled/test flags. `adapter.js` currently calls the explicit boundary `AthosSnap.renderZone`; this must be adjusted to the documented SDK API during the credentialed spike. Search/category flags reserve configuration but deliberately do not claim or mutate Klevu DOM.

Questions for the real spike: SDK package/loading method; autocomplete and search/category components; product-card templating; facet ordering; sorting/no-result hooks; events/callbacks; multiple independent zones; banners; SSR/accessibility; and which gaps require Core API.

## Athos dashboard checklist

1. Create the product-only Data Source and configure the authenticated feed URL.
2. Confirm NDJSON content type, minimum polling interval and parent-record contract.
3. Run initial test index and inspect rejected rows.
4. Map required, searchable and returned fields.
5. Configure `feature_*`, `attribute_*`, brand, stock and category facets in live order.
6. Configure relevance sorting plus newest sorting on `date_created`.
7. Verify price/specific-price/tax/currency and out-of-stock behavior.
8. Verify variant grouping, positions, selected options and swatches.
9. Test Search Preview across languages/shops.
10. Configure and record product/cart recommendation zone IDs.
11. Compare autocomplete, results, cards, filters, sorting, no-results and category behavior with live Klevu.
12. Document every dashboard-only change outside this repository.

Reference material supplied for the follow-up: [Data Sources Guide](https://support-search.athoscommerce.com/hc/en-us/articles/46892986390811-Data-Sources-Guide), [Filtering](https://support-search.athoscommerce.com/hc/en-us/articles/41498194645531-Filtering), [Sorting](https://support-search.athoscommerce.com/hc/en-us/articles/41499248296987-Sorting), [Search Preview](https://support-search.athoscommerce.com/hc/en-us/articles/41708123319707-Search-Preview), [developer docs](https://docs.athoscommerce.com/docs/getting-started-welcome), and [Snap docs](https://athoscommerce.github.io/snap). Supplemental feeds are intentionally out of scope.

## Rollback and limitations

Disable `athosfeed` frontend or set mode to `disabled`; Klevu remains the live implementation throughout. The previous valid feed remains available when a new export fails. No deployment, production Athos calls, dashboard changes or Klevu switch are performed here.

External blockers: missing full PrestaShop runtime/database in this checkout, unknown production shops/languages/currencies/PHP, unavailable Athos credentials/specification/Sandbox and inaccessible docs from this environment. Consequently module install/uninstall, real price parity, HTTP controllers, catalog-scale performance and Athos indexing require staging tests. Incremental queue/hooks are intentionally not registered until the Athos update/delete contract is known.
