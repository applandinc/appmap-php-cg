<?php

namespace CG\Generator;

class BuiltinType
{
    private static $builtinTypes = ['self', 'array', 'callable', 'bool', 'float', 'int', 'string', 'void'];
    
    public static function isBuiltin($type)
    {
        return in_array($type, static::$builtinTypes);
    }
}
    
    
 
