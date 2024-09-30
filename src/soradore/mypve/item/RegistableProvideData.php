<?php

namespace soradore\mypve\item;

class RegistableProvideData
{
    public function __construct(protected string $itemTypeName, protected string $namespace, protected bool $addCreativeInventory)
    {
        //
    }

    public function getItemTypeName()
    {
        return $this->itemTypeName;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getAddCreativeInventory()
    {
        return $this->addCreativeInventory;
    }
}
