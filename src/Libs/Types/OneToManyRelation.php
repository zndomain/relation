<?php

namespace ZnDomain\Relation\Libs\Types;

use yii\di\Container;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\Collection\Libs\Collection;
use ZnDomain\Domain\Interfaces\FindAllInterface;
use ZnCore\Code\Factories\PropertyAccess;
use ZnCore\Collection\Helpers\CollectionHelper;
use ZnDomain\Query\Entities\Query;
use ZnDomain\Relation\Interfaces\RelationInterface;
use ZnDomain\Relations\interfaces\CrudRepositoryInterface;

class OneToManyRelation extends BaseRelation implements RelationInterface
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
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($collection as $entity) {
            $relationIndex = $propertyAccessor->getValue($entity, $this->relationAttribute);
            if (!empty($relationIndex)) {
                $relCollection = [];
                foreach ($foreignCollection as $foreignEntity) {
                    $foreignValue = $propertyAccessor->getValue($foreignEntity, $this->foreignAttribute);
                    if ($foreignValue == $relationIndex) {
                        $relCollection[] = $foreignEntity;
                    }
                }
                $value = $relCollection;
                $value = $this->getValueFromPath($value);
                $propertyAccessor->setValue($entity, $this->relationEntityAttribute, new Collection($value));
            }
        }
    }

    protected function loadCollection(FindAllInterface $foreignRepositoryInstance, array $ids, Query $query): Enumerable
    {
        //$query->limit(count($ids));
        $collection = $foreignRepositoryInstance->findAll($query);
        return $collection;
    }
}
