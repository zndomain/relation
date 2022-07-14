<?php

namespace ZnDomain\Relation\Libs\Types;

use ZnCore\Collection\Interfaces\Enumerable;
use ZnDomain\Relation\Interfaces\RelationInterface;

class VoidRelation extends BaseRelation implements RelationInterface
{

    protected function loadRelation(Enumerable $collection)
    {

    }
}
