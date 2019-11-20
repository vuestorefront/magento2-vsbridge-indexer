# Changelog

## Unreleased

## [1.8.0] (2019.11.20)

### Added
- Add "final_price" to "configurable_children" ([#151](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/151))

## [1.7.0] (2019.11.15)

### Changed/Improved
- Speed up exporting for product/category data. Class `ConvertDataTypes` was removed, converting value to int/float is executed when loading product/category attributes.

## [1.6.0] (2019.11.07)

### Added
- Add support for Visual Swatches. ([#15](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/15))
Attribute options
```json
"options": [
    {
      "swatch": {
        "type": 1,
        "value": "#ffd500"
      },
      "value": "60",
      "label": "Yellow",
      "sort_order": 12
    },
    {
      "swatch": {
        "type": 3,
        "value": null
      },
      "value": "105",
      "label": "purple",
      "sort_order": 13
    },
    {
      "swatch": {
        "type": 2,
        "value": "/4/2/4245592.png"
      },
      "value": "106",
      "label": "File",
      "sort_order": 14
    }
]
```

Product configurable option values
```json
"values": [
    {
      "value_index": "50",
      "label": "blue defaul",
      "swatch": {
        "type": 1,
        "value": "#1857f7"
      }
    },
    {
      "value_index": "49",
      "label": "Black",
      "swatch": {
        "type": 1,
        "value": "#000000"
      }
    }
  ]
``` 
- Add new command `vsbridge:index` to run indexer for one product, cms block, etc. 
`bin/magento vsbridge:index vsbridge_product_indexer default 1`
vsbridge_product_indexer -> indexer code
default -> store code
1 -> entity id (in this particular example - product id)
Useful for testing. 

## [1.5.2] (2019.10.24)

### Fixes
 - Run `vsbridge:reindex` command only if all indices has valid status.

## [1.5.1] (2019.10.23)

### Fixes
-  Fix Notice: Undefined index: value in ConfigurableData.php on line 254 ([#139](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/139))
-  Switch indices when all types will be reindex ([#138](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/138))
-  Change method name `AbstractEavAttributes:canReindex` to '`AbstractEavAttributes:canIndexAttribute`

## [1.5.0] (2019.10.18)

### Changed/Improved
-  Change method `AbstractEavAttributes:canReindex` to public ([#136](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/136)) 

### Added
- Add support for aliases ([#3](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/3)).

## [1.4.0] (2019.10.15)

### Fixes
- Fix `vsbridge:reindex` command, fix initialize right areacode, loading di.xml files for adminhtml areacode. ([#127](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/127))
- Fix `vsbridge:reindex --all` command, data will be reindex store by store. ([#130](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/130))

### Changed/Improved
- Rework abstract mapper to allow configure static type map in di.xml ([#125](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/125))
- Minor refactoring for product resource model ([#126](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/126/)).
- Vsbridge Category Indexer - Save Mode: reindex subcategories if url_key has change in category ([#122](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/122)).
- Sort configurable options by option sort order. ([#129](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/129))

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
