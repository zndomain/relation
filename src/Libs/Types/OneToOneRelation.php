<?php

namespace ZnDomain\Relation\Libs\Types;

use yii\di\Container;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnDomain\Entity\Factories\PropertyAccess;
use ZnDomain\Entity\Helpers\CollectionHelper;
use ZnDomain\Relation\Interfaces\RelationInterface;
use ZnDomain\Relations\interfaces\CrudRepositoryInterface;

class OneToOneRelation extends BaseRelation implements RelationInterface
{

    /** Связующее поле */
    public $relationAttribute;

    //public $foreignPrimaryKey = 'id';
    //public $foreignAttribute = 'id';

    protected function loadRelation(Enumerable $collection)
    {
        $ids = CollectionHelper::getColumn($collection, $this->relationAttribute);
        $ids = array_unique($ids);
        $foreignCollection = $this->loadRelationByIds($ids);
        $foreignCollection = CollectionHelper::indexing($foreignCollection, $this->foreignAttribute);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($collection as $entity) {
            $relationIndex = $propertyAccessor->getValue($entity, $this->relationAttribute);
            if (!empty($relationIndex)) {
                try {
                    if (isset($foreignCollection[$relationIndex])) {
                        $value = $foreignCollection[$relationIndex];
                        if ($this->matchCondition($value)) {
                            $value = $this->getValueFromPath($value);
                            $propertyAccessor->setValue($entity, $this->relationEntityAttribute, $value);
                        }
                    }
                } catch (\Throwable $e) {
                }
            }
        }
    }

    protected function matchCondition($row)
    {
        if (empty($this->condition)) {
            return true;
        }
        foreach ($this->condition as $key => $value) {
            if (empty($row[$key])) {
                return false;
            }
            if ($row[$key] !== $this->condition[$key]) {
                return false;
            }
        }
        return true;
    }
}
