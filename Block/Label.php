<?php

namespace LucasArt\StockStatusLabel\Block;

class Label extends \Magento\Framework\View\Element\Template {

    const XML_PATH_ENABLE_STOCK_STATUS_LABEL = 'cataloginventory/stock_status_label/enable_stock_status_label';

    const XML_PATH_ENABLE_CRITICAL_LABEL = 'cataloginventory/stock_status_label/enable_critical_label';
    const XML_PATH_ENABLE_WARNING_LABEL = 'cataloginventory/stock_status_label/enable_warning_label';
    const XML_PATH_ENABLE_SECURE_LABEL = 'cataloginventory/stock_status_label/enable_secure_label';

    const XML_PATH_CRITICAL_LABEL_QTY = 'cataloginventory/stock_status_label/critical_label_qty';
    const XML_PATH_WARNING_LABEL_QTY = 'cataloginventory/stock_status_label/warning_label_qty';
    const XML_PATH_SECURE_LABEL_QTY = 'cataloginventory/stock_status_label/secure_label_qty';

    const XML_PATH_CRITICAL_LABEL_TEXT = 'cataloginventory/stock_status_label/critical_label_text';
    const XML_PATH_WARNING_LABEL_TEXT = 'cataloginventory/stock_status_label/warning_label_text';
    const XML_PATH_SECURE_LABEL_TEXT = 'cataloginventory/stock_status_label/secure_label_text';

    const XML_PATH_CRITICAL_LABEL_COLOR = 'cataloginventory/stock_status_label/critical_label_color';
    const XML_PATH_WARNING_LABEL_COLOR = 'cataloginventory/stock_status_label/warning_label_color';
    const XML_PATH_SECURE_LABEL_COLOR = 'cataloginventory/stock_status_label/secure_label_color';


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;



    /**
     *
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context);
    }



    /**
     *
     */
     public function isStockStatusLabelEnabled() {

         return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_STOCK_STATUS_LABEL);

     }



    /**
     *
    */
    public function getProductData() {


        // simple
        if ( $this->getLayout()->hasElement('product.info.simple') ) {

            /** @var \Magento\Catalog\Block\Product\View\Type\Simple $block */
            $block = $this->getLayout()->getBlock('product.info.simple');
            $product = $block->getProduct();
            $stockItem = $this->stockRegistry->getStockItem($product->getId());


            $this->productData['type'] = 'simple';
            $this->productData['sku'] = $product->getSku();
            $this->productData['stock_status'] = $this->getStockStatusLabel($stockItem->getQty());


            // var_dump($product->getId());
            // var_dump($stockItem->getQty());
            // die;

            // echo $this->scopeConfig->getValue(self::XML_PATH_ENABLE_CRITICAL_LABEL);
            // echo '<br>';
            // echo $product->getId() . ' - ' . $stockItem->getQty();

        }


        // configurable
        if ( $this->getLayout()->hasElement('product.info.configurable') ) {

            $this->productData['type'] = 'configurable';

            /** @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $block */
            $block = $this->getLayout()->getBlock('product.info.configurable');
            $product = $block->getProduct();

            // retrieve childs
            $childIds = $product->getTypeInstance()->getUsedProductIds($product);
            foreach ($childIds as $childId){

                $stockItem = $this->stockRegistry->getStockItem($childId);
                $this->productData['childs'][$childId] = $this->getStockStatusLabel($stockItem->getQty());

            }

            // echo '<pre>';
            // var_dump($product->getId());
            // var_dump($stockItem->getQty());
            // var_dump($block->getJsonConfig());
            // die;

        }

        return json_encode($this->productData);

    }



    /**
     *
     */
    public function isConfigurableProduct() {

        return $this->getLayout()->hasElement('product.info.configurable') ? true : false;

    }



    /**
     *
     */
    public function getSwatchOptions() {

        if ( !$this->isConfigurableProduct() ) {

            // if simple return an empty json object
            return "{}";

        }

        /** @var \Magento\Swatches\Block\Product\Renderer\Configurable $block */
        $block = $this->getLayout()->getBlock('product.info.options.swatches');
        $swatchOptions = $block->getJsonConfig();
        return $swatchOptions;

    }



    /**
     *
     */
    public function getStockStatusLabel($qty) {

        // STOCK STATUS CRITICAL
        if (
            $this->scopeConfig->getValue(self::XML_PATH_ENABLE_CRITICAL_LABEL)
            && $qty <= $this->scopeConfig->getValue(self::XML_PATH_CRITICAL_LABEL_QTY)
        ) {

            return 'critical';

        }


        // STOCK STATUS WARNING
        if (
            $this->scopeConfig->getValue(self::XML_PATH_ENABLE_WARNING_LABEL)
            && $qty <= $this->scopeConfig->getValue(self::XML_PATH_WARNING_LABEL_QTY)
        ) {

            return 'warning';

        }


        // STOCK STATUS SECURE
        if (
            $this->scopeConfig->getValue(self::XML_PATH_ENABLE_SECURE_LABEL)
            && $qty <= $this->scopeConfig->getValue(self::XML_PATH_SECURE_LABEL_QTY)
        ) {

            return 'secure';

        }

    }



    /**
     *
     */
    public function getLabelTexts() {

        return json_encode([
            'critical' => $this->scopeConfig->getValue(self::XML_PATH_CRITICAL_LABEL_TEXT),
            'warning'  => $this->scopeConfig->getValue(self::XML_PATH_WARNING_LABEL_TEXT),
            'secure'   => $this->scopeConfig->getValue(self::XML_PATH_SECURE_LABEL_TEXT)
        ]);

    }



    /**
     *
     */
    public function getStyle() {

        $color_critical = $this->scopeConfig->getValue(self::XML_PATH_CRITICAL_LABEL_COLOR);
        $color_warning = $this->scopeConfig->getValue(self::XML_PATH_WARNING_LABEL_COLOR);
        $color_secure = $this->scopeConfig->getValue(self::XML_PATH_SECURE_LABEL_COLOR);

        return '<style>

        #stock_status_label.critical { color: #' . $color_critical. '; }
        #stock_status_label.warning { color: #' . $color_warning . '; }
        #stock_status_label.secure { color: #' . $color_secure . '; }

        </style>';

    }



    /**
     *
     */
    public function test() {

        if ( $this->getLayout()->hasElement('product.info.simple') ) {

            /** @var \Magento\Catalog\Block\Product\View\Type\Simple $block */
            $block = $this->getLayout()->getBlock('product.info.simple');

            // echo '<pre>';
            // var_dump(get_class($block));
            // var_dump(get_class($this->stockRegistry));


            $product = $block->getProduct();
            $stockItem = $this->stockRegistry->getStockItem($product->getId());

            // var_dump($product->getId());
            // var_dump($stockItem->getQty());
            // die;

            echo $this->scopeConfig->getValue(self::XML_PATH_ENABLE_CRITICAL_LABEL);
            echo '<br>';
            echo $product->getId() . ' - ' . $stockItem->getQty();


        }

        if ( $this->getLayout()->hasElement('product.info.configurable') ) {

            /** @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $block */
            $block = $this->getLayout()->getBlock('product.info.configurable');

            $product = $block->getProduct();
            $stockItem = $this->stockRegistry->getStockItem($product->getId());

            // echo '<pre>';
            // var_dump($product->getId());
            // var_dump($stockItem->getQty());
            // var_dump($block->getJsonConfig());
            // die;

        }

    }

}