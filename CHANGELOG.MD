# Changelog

## 1.1.0


## 1.0.0 (2019.04.03)
First version

## March 2019

### Added
- Adding 'url_path' for products. 
You have to delete existing index from Elasticsearch and run full reindexation.
Need to get correct mapping for new product field. 
- Add support for "available_sort_by" and "default_sort_by" default values.

### Fixed
- fix creating mapping for category EAV attributes.
 You have to delete existing index from Elasticsearch and run full reindexation.
- fix exporting final_price and price for configurable products created with option "Apply unique prices by attribute to each SKU"
