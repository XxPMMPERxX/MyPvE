<?php

namespace soradore\mypve\utils;

use Closure;
use pocketmine\utils\SingletonTrait;
use soradore\mypve\Main;

class ClassFinder
{
    use SingletonTrait;

    public static $base_namespace;

    public function __construct()
    {
        self::$base_namespace = str_replace('utils', '', __NAMESPACE__);
    }

    public function getClassesFromNamespace(?string $namespace = null, ?Closure $filter = null)
    {
        $namespace ??= self::$base_namespace;

        $dir = Main::$baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        $iterator = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($iterator);
        $list = [];
        foreach ($iterator as $fileinfo) { // $fileinfoはSplFiIeInfoオブジェクト
            if ($fileinfo->isFile()) {
                $list[] = $fileinfo->getPathname();
            }
        }

        $namespace = str_replace('\\', '\/', $namespace);
        $preClassList = array_map(function ($filePath) use ($namespace) {
            $pattern = '/(' . $namespace . '.*)\.php/';
            preg_match($pattern, $filePath, $matches);

            return $matches[1] ?? null;
        }, $list);

        $classList = array_filter($preClassList);
        $classList = array_map(fn ($class) => str_replace('/', '\\', $class), $classList);

        if (!is_null($filter)) {
            $classList = array_filter($classList, $filter);
        }

        return $classList;
    }
}