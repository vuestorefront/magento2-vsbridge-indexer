# Changelog

## Unreleased

### Changed/Improved
- Vsbridge Category Indexer - Save Mode: reindex subcategories if url_key has change in category ([#122](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/122)).
- Sort configurable options by option sort order. 

## [1.3.0] (2019.09.18)

### Fixes
- Fixed Backward compatibility with 2.2.x (< 2.2.6)  ([#53](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/53))

### Changed/Improved
- Skip exporting configurable products without "configurable_options".

### Added
- Export ratings for reviews. 

## [1.2.0] (2019.08.05)

### Fixed
- Uncaught TypeError in CMS indexer  - [@indiebytes](https://github.com/indiebytes) ([#85](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/85))
- Multiselect attribute values check if they are numeric before casting to `int` type to avoid casting strings to `int` and therefore causing the value to be `0` always. - [@rain2o](https://github.com/rain2o)
- Fix running `bin/magento vsbridge:reindex --store` per store. Only data for given store will be updated in ES. ([#91](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/91))

### Changed/Improved
- Provide more verbose feedback in console when running `bin/magento vsbridge:reindex`.  Removed `int` type cast from `--store` value to allow for store code or store ID since StoreManagerInterface allows both types. - [@rain2o](https://github.com/rain2o)

### Added
- New optional parameter for `bin/magento vsbridge:reindex` command `--all` to allow reindexing all stores. - [@rain2o](https://github.com/rain2o)


## [1.1.0] (2019.07.10)

### Fixed
- Fix bug causing empty product URL paths  - [@indiebytes](https://github.com/indiebytes) ([#63](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/63))
- Changed ElasticSearch password to be obscured, encrypted, and considered sensitive (will dump to `env.php` instead of `config.php`) - [@rain2o](https://github.com/rain2o) ([#69](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/69))
- Fix exporting values for multiselect option arrays as integers (instead of strings)
- Fix getting stock_status value for products
- Magento Commerce - fix getting configurable_children 

### Changed/Improved
- Change mapping for "category.name" field in product type
- Remove dependency from catalog_product_price and cataloginventory_stock indexer. On product save multiple request has been send to ES
- Change "Use Short Catalog Urls" setting option to "Use Catalog Url Keys" 
- Add option to generate product/category slug base on Magento Url Key and ID. By default slug (and url_key) field is generated base on NAME and ID.

### Added
- Adding support for video data. Small change will be needed in VSF #19
- Add support for "product_count" in category. When products are reassign to category, category data is not updated automatically in ES.
There is not need really, VSF only has to know if `product_count > 0`, so if you already had products assign to categories (before running ` bin/magento indexer:reindex vsbridge_category_indexer`) 
category will be visible in menu sidebar.
- Add support for reviews. Reviews are exported without ratings (VSF does not support ratings for now)  
- Add support for custom options. 
- Add option to enable/disable exporting data to ES.
- Add ProductCategory indexer to partially update product data in ES (category, category_ids fields). Trigger after changing products positions in category.
- Add the ability to choose between Store ID and Store Code to be used at the end of index names.
- Add label for configurable option value.  **Note:** When You modify any configurable attribute label or option label in Magento You should reindex all products manually.
```json
{
  "attribute_id": 93,
  "attribute_code": "color",
  "label": "Color",
  "values": [
    {
      "value_index": 61,
      "label": "Gray"
    },
    {
      "value_index": 66,
      "label": "Purple"
    },
    {
      "value_index": 69,
      "label": "Yellow"
    }
  ]
}
```
- Add new command ```php bin/magento vsbridge:reindex``` which will run all Magento indices which names start with "vsbridge_" (experimental).
```php
Description:
  Rebuild indexer in ES.

Usage:
  vsbridge:reindex [options]

Options:
      --store=STORE     Store ID
      --delete-index    Delete previous index and create new one (with new mapping)
```


## [1.0.0] (2019.04.03)
First version

## March 2019

### Added
- Adding 'url_path' for products. 
You have to delete existing index from Elasticsearch and run full reindexation.
You need to get correct mapping for new product field.
- Add support for "available_sort_by" and "default_sort_by" default values.

### Fixed
- fix creating mapping for category EAV attributes.
 You have to delete existing index from Elasticsearch and run full reindexation.
- fix exporting final_price and price for configurable products created with option "Apply unique prices by attribute to each SKU"
