<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\Criteria\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Ergnuor\DomainModel\Criteria\ExpressionMapper\ExpressionMapperInterface;
use Ergnuor\DomainModel\Criteria\OrderMapper\OrderMapperInterface;

class Config
{
    /** @var ArrayCollection<string, Field> */
    private ArrayCollection $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    public function addField(Field $field): void
    {
        $this->fields->set($field->getFieldName(), $field);
    }

    public function configureExpressionMapper(ExpressionMapperInterface $expressionMapper): void
    {
        foreach ($this->fields as $fieldName => $field) {
            $filter = $field->getFilter();
            if ($filter === null) {
                continue;
            }

            $expressionMapper->addField(
                $fieldName,
                $filter->getValueSource(),
                $filter->getFieldExpressionMapper()
            );
        }
    }

    public function configureOrderMapper(OrderMapperInterface $orderMapper): void
    {
        foreach ($this->fields as $fieldName => $field) {
            $sorting = $field->getSorting();
            if ($sorting === null) {
                continue;
            }

            $orderMapper->addField($fieldName, $sorting->getValueSource());
        }
    }
}