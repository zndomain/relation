<?php

namespace ZnDomain\Relation\Libs\Types;

use Psr\Container\ContainerInterface;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnDomain\Domain\Interfaces\FindAllInterface;
use ZnDomain\Entity\Factories\PropertyAccess;
use ZnDomain\Query\Entities\Query;
use ZnDomain\Query\Entities\Where;
use ZnDomain\Relation\Interfaces\RelationInterface;
use ZnDomain\Relations\interfaces\CrudRepositoryInterface;

abstract class BaseRelation implements RelationInterface
{

    /** @var string Имя связи, указываемое в методе with.
     * Если пустое, то берется из атрибута relationEntityAttribute
     */
    public $name;

    /** @var string Имя поля, в которое записывать вложенную сущность */
    public $relationEntityAttribute;

    /** @var string Имя первичного ключа связной таблицы */
    public $foreignAttribute = 'id';

    /** @var string Имя класса связного репозитория */
    public $foreignRepositoryClass;

    /** @var array Условие для присваивания связи, иногда нужно для полиморических связей */
    public $condition = [];

    /** @var callable Callback-метод для пост-обработки коллекции из связной таблицы */
    public $prepareCollection;

    /** @var Query Объект запроса для связного репозитория */
    public $query;
    protected $container;
    //private $cache = [];

    public $fromPath = null;

    abstract protected function loadRelation(Enumerable $collection);

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run(Enumerable $collection)
    {
        $this->loadRelation($collection);
        $collection = $this->prepareCollection($collection);
    }

    protected function getValueFromPath($value)
    {
        if ($this->fromPath) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($value, $this->fromPath);
        }
        return $value;
    }

    protected function prepareCollection(Enumerable $collection)
    {
        if ($this->prepareCollection) {
            call_user_func($this->prepareCollection, $collection);
        }
    }

    protected function loadRelationByIds(array $ids)
    {
        $foreignRepositoryInstance = $this->getRepositoryInstance();
        //$primaryKey = $foreignRepositoryInstance->primaryKey()[0];
        $query = $this->getQuery();
        $query->whereNew(new Where($this->foreignAttribute, $ids));
        //$query->andWhere(['in', ]);
        return $this->loadCollection($foreignRepositoryInstance, $ids, $query);
    }

    protected function loadCollection(FindAllInterface $foreignRepositoryInstance, array $ids, Query $query): Enumerable
    {
        // todo: костыль, надо проверить наверняка
        /*if (get_called_class() != OneToManyRelation::class) {
            $query->limit(count($ids));
        }*/
        $query->limit(count($ids));
        $collection = $foreignRepositoryInstance->findAll($query);
        return $collection;
    }

    protected function getQuery(): Query
    {
        return $this->query ? $this->query : new Query;
    }

    protected function getRepositoryInstance()/*: CrudRepositoryInterface*/
    {
        return $this->container->get($this->foreignRepositoryClass);
    }
}
