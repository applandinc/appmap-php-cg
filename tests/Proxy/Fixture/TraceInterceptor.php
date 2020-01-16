<?php

namespace CG\Tests\Proxy\Fixture;

use CG\Proxy\MethodInvocation;
use CG\Proxy\MethodInterceptorInterface;

class TraceInterceptor implements MethodInterceptorInterface
{
    private array $log;

    public function getLog(): array
    {
        return $this->log;
    }

    public function intercept(MethodInvocation $method)
    {
        $message = sprintf('%s::%s(', $method->reflection->class, $method->reflection->name);

        $logArgs = [];
        foreach ($method->arguments as $arg) {
            $logArgs[] = var_export($arg, true);
        }
        $this->log[] = $message.implode(', ', $logArgs).')';

        return $method->proceed();
    }
}