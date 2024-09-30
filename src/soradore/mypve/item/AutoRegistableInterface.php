<?php

namespace soradore\mypve\item;

interface AutoRegistableInterface
{
    public static function provide(): RegistableProvideData;
}
