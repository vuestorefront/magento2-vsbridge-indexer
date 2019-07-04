# Changelog

## [Unreleased]

### Fixed
- Fix bug causing empty product URL paths  - [@indiebytes](https://github.com/indiebytes) ([#63](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/63))
- Changed ElasticSearch password to be obscured, encrypted, and considered sensitive (will dump to `env.php` instead of `config.php`) - [@rain2o](https://github.com/rain2o) ([#69](https://github.com/DivanteLtd/magento2-vsbridge-indexer/issues/69))
- Fix exporting values for multiselect option arrays as integers (instead of strings)
- Change "Use Short Catalog Urls" setting option to "Use Catalog Url Keys" 

### Added
- Adding support for video data. Small change will be needed in VSF #19
- Add support for "product_count" in category. When products are reassign to category, category data is not updated automatically in ES.
There is not need really, VSF only has to know if `product_count > 0`, so if you already had products assign to categories (before running ` bin/magento indexer:reindex vsbridge_category_indexer`) 
category will be visible in menu sidebar.
- Add support for reviews. Reviews are exported without ratings (VSF does not support ratings for now)  
- Add support for custom options. 
- Add option to enable/disable exporting data to ES.
- Add ProductCategory indexer to partially update product data in ES (category, category_ids fields). Trigger after changing products positions in category.
- Fix getting stock_status value for products
- Remove dependency from catalog_product_price and cataloginventory_stock indexer. On product save multiple request has been send   
- Add new command ```php bin/magento vsbridge:reindex``` which will run all Magento indices which names start with "vsbridge_".
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
