<?php

namespace Divante\VsbridgeIndexerCore\Model\Adminhtml\System\Config\Source;

/**
 * All registered elasticsearch versions
 *
 * @api
 */
class Version implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Engines list
     *
     * @var array
     */
    private $engines;

    /**
     * Construct
     *
     * @param array $engines
     */
    public function __construct(array $engines)
    {
        $this->engines = $engines;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->engines as $key => $label) {
            $options[] = [
                'value' => $key,
                'label' => $label,
            ];
        }

        return $options;
    }
}
