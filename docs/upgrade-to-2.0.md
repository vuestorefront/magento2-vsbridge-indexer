1. Vsbridge_indexer.xml was replaced with vsbridge.xml
- Vsbridge.xml keep information about type/entity and mapping.
- Datapvoriderds were moved to di.xml
Example:

Before:
```
<indices xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="urn:magento:module:Divante_VsbridgeIndexerCore:etc/vsbridge_indices.xsd">

    <index identifier="vue_storefront_catalog">
        <type name="taxrule" mapping="Divante\VsbridgeIndexerTax\Index\Mapping\Tax">
            <data_providers>
                <data_provider name="tax_classes">Divante\VsbridgeIndexerTax\Model\Indexer\DataProvider\TaxClasses</data_provider>
                <data_provider name="tax_rates">Divante\VsbridgeIndexerTax\Model\Indexer\DataProvider\TaxRates</data_provider>
            </data_providers>
        </type>
    </index>
</indices>
``` 

After:
**vsbridge:xml**
```
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Divante_VsbridgeIndexerCore:etc/vsbridge.xsd">
    <type identifier="taxrule" mapping="Divante\VsbridgeIndexerTax\Index\Mapping\Tax"/>
</config>
```

**di.xml**
```
  <type name="Divante\VsbridgeIndexerCore\Index\DataProviderResolver">
        <arguments>
            <argument name="dataProviders" xsi:type="array">
                <item name="taxrule" xsi:type="array">
                    <item name="tax_classes" xsi:type="object">Divante\VsbridgeIndexerTax\Model\Indexer\DataProvider\TaxClasses</item>
                    <item name="tax_rates" xsi:type="object">Divante\VsbridgeIndexerTax\Model\Indexer\DataProvider\TaxRates</item>
                </item>
            </argument>
        </arguments>
    </type>
```
