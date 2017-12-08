<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCustomer\Model\Type\Handler;

use Magento\GraphQl\Model\EntityAttributeList;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Define Customer GraphQL type
 */
class Customer implements HandlerInterface
{
    const CUSTOMER_TYPE_NAME = 'Customer';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var EntityAttributeList
     */
    private $entityAttributeList;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeGenerator $typeGenerator
     * @param EntityAttributeList $entityAttributeList
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        TypeGenerator $typeGenerator,
        EntityAttributeList $entityAttributeList,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->entityAttributeList = $entityAttributeList;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->typeFactory->createObject(
            [
                'name' => self::CUSTOMER_TYPE_NAME,
                'fields' => $this->getFields(self::CUSTOMER_TYPE_NAME),
            ]
        );
    }

    /**
     * Retrieve Product base fields
     *
     * @param string $typeName
     * @return array
     * @throws \LogicException Schema failed to generate from service contract type name
     */
    private function getFields(string $typeName)
    {
        $result = [];
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(\Magento\Customer\Model\Customer::ENTITY);
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = 'string';
        }

        $staticAttributes = $this->typeGenerator->getTypeData('CustomerDataCustomerInterface');
        $result = array_merge($result, $staticAttributes);

        unset($result['extension_attribute']);

        $resolvedTypes = $this->typeGenerator->generate($typeName, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
