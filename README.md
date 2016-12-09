# Magento2 module
Stock status label module allow to show a custom availability info label to product page.

## Installation
Run command below from your magento2 root.
```
composer require lucasart/stock-status-label:~1.1.0
bin/magento module:enable LucasArt_StockStatusLabel
bin/magento setup:upgrade
```
## Usage
Enable and configure the module in admin
```
Stores -> Configuration -> Catalog -> Inventory -> Stock Status Label
```
That's all! Clean magento2 cache and go to product page in frontend. You will see the stock status label.
