<?php

namespace CG\Generator;

class BuiltinType
{
    private static $builtinTypes = ['self', 'array', 'callable', 'bool', 'float', 'int', 'string', 'void', 'iterable', 'object'];
    
    public static function isBuiltin(string $type): bool
    {
        return in_array($type, static::$builtinTypes, true);
    }
}
    
    
 
