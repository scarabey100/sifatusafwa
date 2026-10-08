# Product feature value loss: root cause and verification

## Confirmed root cause

This repository is a deployment overlay (modules, overrides and theme); it does
not contain the PrestaShop core or a local database. The destructive operation
is in `FFC::deleteCombinationsFeatures()`, from the custom
`featuresforcombinations` module version 1.5.2.

On a product save containing `ffc_submitted`, `hookActionProductSave()` rebuilds
the combination-feature rows. For every product combination it calls
`deleteCombinationsFeatures()` before inserting the submitted rows. Previously,
that method immediately deleted every referenced `feature_value` with
`custom = 1`, followed by its `feature_value_lang` rows. It did not check
`feature_product`, and only afterwards removed the scoped
`featuresforcombinations` row.

The database row reported as still present is the association in
`feature_product`; it is not proof that its referenced custom value and language
text still exist. The two feature mechanisms share PrestaShop's `feature_value`
table. Therefore,
when an FFC row and a standard product feature referred to the same custom value,
saving the FFC form deleted the shared parent and translations but deliberately
did not touch `feature_product`. This exactly produces the reported orphan. It
also explains why predefined values (`custom = 0`) survive and why the trigger
correlates with creating, changing, or re-saving products with combinations.
The standard attributes themselves are not involved.

## Reproduction on staging

1. Create a product and assign predefined Format plus custom Title, Author and
   Pages standard product features.
2. Add a combination and make its FFC data refer to one of those custom value
   IDs (the historical form permits the shared ID).
3. Save once with `ffc_submitted=1`, then save the product/combination again.
4. Before the patch, the second save enters `hookActionProductSave()`, calls the
   destructive cleanup, and removes the shared custom parent and translations.
   The standard `feature_product` row remains.
5. With the patch, FFC removes only the scoped association it owns. It never
   deletes shared `feature_value` or `feature_value_lang` data.

There was a second destructive interpretation in the same save handler: once
`ffc_submitted` existed, combinations absent from a partial request were treated
as empty and cleared. The handler now rebuilds only combination IDs explicitly
present in `ffc`; absence means "not submitted". An explicitly submitted empty
array continues to mean "remove the FFC associations for this combination".

Repeat with price-only and name-only changes, simple-to-combinations conversion,
combination edits, and product duplication. If those requests do not submit the
FFC form they do not enter this workflow; if they do, the module now protects
standard feature values by never deleting them. Confirm that Format, Title,
Author and Pages, the copied
product, attributes, and remaining FFC rows are unchanged.

## Read-only integrity checks

Use the actual installation prefix in place of `sf_` when necessary:

```sql
SELECT fp.id_product, fp.id_feature, fp.id_feature_value
FROM sf_feature_product fp
LEFT JOIN sf_feature_value fv
  ON fv.id_feature_value = fp.id_feature_value
WHERE fv.id_feature_value IS NULL
ORDER BY fp.id_product, fp.id_feature;

SELECT COUNT(DISTINCT fp.id_product) AS affected_products,
       COUNT(DISTINCT fp.id_feature_value) AS missing_values
FROM sf_feature_product fp
LEFT JOIN sf_feature_value fv
  ON fv.id_feature_value = fp.id_feature_value
WHERE fv.id_feature_value IS NULL;

-- Existing parent, but a missing/empty language value (a different failure mode)
SELECT fp.id_product, fp.id_feature, fp.id_feature_value, l.id_lang,
       fvl.value
FROM sf_feature_product fp
JOIN sf_feature_value fv
  ON fv.id_feature_value = fp.id_feature_value AND fv.custom = 1
CROSS JOIN sf_lang l
LEFT JOIN sf_feature_value_lang fvl
  ON fvl.id_feature_value = fp.id_feature_value
 AND fvl.id_lang = l.id_lang
WHERE l.active = 1
  AND (fvl.id_feature_value IS NULL OR fvl.value IS NULL OR fvl.value = '')
ORDER BY fp.id_product, fp.id_feature, l.id_lang;
```

Run these before and after the staging matrix. The second result must not grow.
The third query distinguishes a physically present custom parent from missing or
empty localized text. The queries are diagnostic only and never delete or repair
data.

## Safety, risks, and recovery

The patch is limited to FFC cleanup. It does not change the standard feature
save handler, attributes, combinations, shops, languages, or existing orphaned
rows. It prevents duplicates by retaining a referenced ID rather than creating
a replacement. FFC no longer performs feature-value garbage collection at all:
this module cannot reliably prove ownership of a row in a shared core table.
Potential unused custom values are safer than irreversible loss and can be
reported and reviewed separately.

There is no safe way to infer lost Title, Author, or Pages text from an orphaned
ID. Export the read-only report, restore exact parent and all language rows from
the newest trustworthy backups/import source, and validate IDs and feature IDs
on staging. Keep unresolved rows for manual recovery; do not create empty values
or delete their `feature_product` links.
