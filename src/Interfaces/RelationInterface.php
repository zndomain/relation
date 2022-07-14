<?php

namespace ZnDomain\Relation\Interfaces;

use ZnCore\Collection\Interfaces\Enumerable;

interface RelationInterface
{

    public function run(Enumerable $collection);

}
