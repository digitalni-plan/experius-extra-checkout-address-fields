<?php
/**
 * Copyright © Experius B.V. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Experius\ExtraCheckoutAddressFields\Helper;

use Exception;
use Magento\Framework\DataObject\Copy\Config;
use Psr\Log\LoggerInterface;

class Data
{
    public const IS_DEBUG = FALSE;

    public function __construct(
        protected Config $fieldsetConfig,
        protected LoggerInterface $logger
    ) {

    }

    /**
     * @param string $fieldset
     * @param string $root
     * @return array
     */
    public function getExtraCheckoutAddressFields($fieldset='extra_checkout_billing_address_fields', $root='global'): array
    {
        $fields = $this->fieldsetConfig->getFieldset($fieldset, $root);

        $extraCheckoutFields = [];

        if (is_array($fields)) {
            foreach ($fields as $field => $fieldInfo) {
                $extraCheckoutFields[] = $field;
            }
        }

        return $extraCheckoutFields;
    }

    /**
     * @param $fromObject
     * @param $toObject
     * @param string $fieldset
     * @return mixed
     */
    public function transportFieldsFromExtensionAttributesToObject(
        $fromObject,
        $toObject,
        $fieldset='extra_checkout_billing_address_fields'
    ) {

        $debugCategory = 'Experius_ExtraCheckoutAddressFields  - ';
        if (self::IS_DEBUG) {
            $this->logger->info(__($debugCategory . 'fieldset: %s', $fieldset));
            $this->logger->info(__($debugCategory . 'fromObject class: %s', get_class($fromObject)));
        }

        foreach ($this->getExtraCheckoutAddressFields($fieldset) as $extraField) {

            if (self::IS_DEBUG) {
                $this->logger->info(__($debugCategory . 'extraField: %s', $extraField));
            }

            $set = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $extraField)));
            $get = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $extraField)));

            try {
                if (
                    $fieldset == 'extra_checkout_billing_address_fields'
                    && method_exists($fromObject, 'getQuote')
                    && $fromObject->getQuote()
                    && $fromObject->getQuote()->getShippingAddress()
                ) {
                    $shippingSameAsBilling = $fromObject->getQuote()->getShippingAddress()->getSameAsBilling();
                    if ($shippingSameAsBilling) {
                        $fromObject = $fromObject->getQuote()->getShippingAddress();
                    }
                }
            } catch (Exception $e) {

            }

            $value = $fromObject->$get();

            if (self::IS_DEBUG) {
                $this->logger->info($debugCategory . 'value set: %s', $value);
            }

            try {
                $toObject->$set($value);
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $toObject;
    }
}
