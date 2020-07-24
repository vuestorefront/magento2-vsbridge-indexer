# Changelog

## Unreleased
- Add optimization settings. You can now enable checking ES health before indexing and setting number_of_replicas=0
and refresh_interval=-1 during indexing ([#308](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/308))

## [1.19.0] (2020.06.23)

### Added
- Added new command `vsbridge:reset`. Resets vsbridge indexer status to invalid ([303](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/303))
- Export 'created_at' field for products ([299](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/299))

### Fixed
- Trigger does not work in cascade deleting ([296](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/296))


## [1.18.0] (2020.06.09)

### Added
- Add support for agreements ([#286](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/286))
- Added events to reindex command ([#280](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/280))

###
- Fix module installation on fresh Magento.

## [1.17.0] (2020.05.20)

### Added
- Add ability to use a text swatch attribute ([#278](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/278))
```
{
  "options": [
    {
      "swatch": {
        "type": 0,
        "value": "XL"
      },
      "label": "Extra large",
      "value": "5749",
      "sort_order": 1
    }
  ]
}
```

### Fixed
- Fixed expression to select subcategories   ([#271](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/271))
- Fix parsing cms page content ([#276](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/276))

## [1.16.1] (2020.04.14)
- Fix excluding attributes from category.  ([#267](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/267))

### [1.16.0] (2020.20.24)

### Added
- Add option to export `parent_sku` for products ([#84](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/84))
- Add `extension_attributes.downloadable_product_links` for downloadable products ([#142](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/142))

## [1.15.1] (2020.04.14)

### Fixed
- Fix `vsbridge:reindex --store` option: check if store is allowed to reindex, show correct message.

## [1.15.0] (2020.04.06)

### Added
 Manage exported attributes for categories. Customize indexed attributes for `category` and `category.child_data`.

## [1.14.1] (2020.04.06)

### Fixed
- Fix BulkLogger
- Fix `vsbridge:reindex --all` option: reindex only allowed stores.

## [1.14.0] (2020.04.02)

### Added
- Add option to set up configurable children batch size.

## [1.13.0] (2020.04.02)

### Added
- Export category,product url suffixes base on Magento configuration.

### Fixed
- Fix exporting positions for linked products ([#242](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/242))

## [1.12.0] (2020.03.27)

### Added
- Add option to export data for store views to different ES instances

## [1.11.2] (2020.03.18)

### Fixed
- Resolve issue ([#184](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/184)), hide 'vsbridge_product_category' indexer from magento commands: `indexer:reindex`, `indexer:info`

## [1.11.1]

- Fix cleaning cache after reindex. Related to: ([#183] (https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/183))

## [1.11.0] (2020.02.24)

### Fixed
- Fix exporting products for Magento EE ([#226](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/226))
- Hide vsbridge_product_category indexer in Admin Panel: ([#184](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/184))
- Too long invalidate redis cache url for mass actions or large amount of products in a shop ([#183] (https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/183))

### Added
- Add option to export "attributes_metadata" for products. Only visible attributes are exported by default.
Sample: `docs/sample/attributes-metadata.json`
Options values are collected from exported attributes - so make sure attribute is exported to ES (check option `Product attributes to export`).

- Manage exported attributes for products. Customize indexed attributes for product and product.configurable_children.

~~Some attributes are always exported:
- price
- sku
- name
- visibility
- status
- url_path
- url_key~~


** By default only part of attributes will be exported, like: description, image, small_image, thumbnail,
media_gallery, media_title, meta_description, special_price, special_from_date, special_to_date, tax_class_id.

```json
    "type_id": "simple",
    "sku": "24-MB04",
    "id": 2,
    "media_gallery": [
      {
        "typ": "image",
        "image": "/m/b/mb04-black-0.jpg",
        "lab": "Image",
        "pos": 1
      },
      {
        "typ": "image",
        "image": "/m/b/mb04-black-0_alt1.jpg",
        "lab": "Image",
        "pos": 2
      }
    ],
    "description": "<p>Convenience is next to nothing when your day is crammed with action. So whether you're heading to class, gym, or the unbeaten path, make sure you've got your Strive Shoulder Pack stuffed with all your essentials, and extras as well.</p>\n<ul>\n<li>Zippered main compartment.</li>\n<li>Front zippered pocket.</li>\n<li>Side mesh pocket.</li>\n<li>Cell phone pocket on strap.</li>\n<li>Adjustable shoulder strap and top carry handle.</li>\n</ul>",
    "name": "Strive Shoulder Pack",
    "image": "/m/b/mb04-black-0.jpg",
    "small_image": "/m/b/mb04-black-0.jpg",
    "thumbnail": "/m/b/mb04-black-0.jpg",
    "url_key": "strive-shoulder-pack-2",
    "price": 32,
    "special_price": 32,
    "status": 1,
    "visibility": 4,
    "tax_class_id": 2,
    "special_from_date": "2019-11-27 13:25:55",
    "slug": "strive-shoulder-pack-2",
    "url_path": "strive-shoulder-pack",
```


** For configurable_children attributes used to create configurable products are also always exported
plus attributes set in configuration (by default: image, small_image, thumbnail, media_gallery, special_price, special_from_date, special_to_date)

** You can customize attributes exported for product and product.configurable_children.

```json
    "configurable_children": [
      {
        "sku": "MH01-XS-Black",
        "final_price": 52,
        "regular_price": 52,
        "color": 49,
        "status": 1,
        "visibility": 1,
        "size": 167,
        "name": "Chaz Kangeroo Hoodie-XS-Black",
        "image": "/m/h/mh01-black_main.jpg",
        "small_image": "/m/h/mh01-black_main.jpg",
        "thumbnail": "/m/h/mh01-black_main.jpg",
        "price": 52,
        "media_gallery": [
          {
            "typ": "image",
            "image": "/m/h/mh01-black_main.jpg",
            "lab": "",
            "pos": 1
          }
        ],
      },
```

## [1.10.0] (2020.02.05)

### Fixed
- Fixed `max_sale_qty` getting wrong value when set per-product. [@rain2o](https://github.com/rain2o) ([#201](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/201))
- Fixed exporting products for Magento Commerce: Bundle products and Linked products([#202](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/202))

## [1.9.0] (2020.01.28)

### Fixed
- Emulate store when rebuilding cms block and pages. ([#179](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/179))
- Fix modules sequence declaration
- Fixes for clean magento install with sample data modules.
- Collecting attributes values when there is no default value ([#160](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/160))

### Added
- Product indexer - add dependencies, add dynamic catalogrule_product_price dependencies for Magento <= 2.2.4 ([#176](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/176))
- Reindexing products which have beed modified by mass action on product grid when indexers are 'On Save ([#157](https://github.com/DivanteLtd/magento2-vsbridge-indexer/pull/157))

## [1.8.3] (2019.12.19)

### Fixes
- support table prefix with review_entity
- fix exporting final_price and price for configurable products when `final_price` equals `0`

## [1.8.2] (2019.11.29)

### Fixes
- fix notice undefined final_price

## [1.8.1] (2019.11.28)

### Fixes
- export catalog rule prices for Magento <= 2.2.4
- export update_at field for products

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
