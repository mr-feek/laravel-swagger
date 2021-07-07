<?php

namespace Mtrajano\LaravelSwagger\Parsers;

use Illuminate\Http\Resources\Json\JsonResource;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;

class JsonResourceResponseParser
{
    /**
     * @return array<string, string>
     */
    public function parse(ReflectionClass $class): array
    {
        if (!$class->isSubclassOf(JsonResource::class)) {
            throw new \InvalidArgumentException($class . ' must be a subclass of ' . JsonResource::class);
        }

        $resourceClass = $class->getName();

        $resourceClassInfo = (new BetterReflection())
            ->classReflector()
            ->reflect($resourceClass);

        $resourceMethodInfo = $resourceClassInfo->getMethod('toArray');

        $ast = $resourceMethodInfo->getReturnStatementsAst();

        $nodeFinder = new \PhpParser\NodeFinder();

        /** @var \PhpParser\Node\Stmt\Return_ $returnNode */
        $returnNode = $nodeFinder->findFirstInstanceOf($ast, \PhpParser\Node\Stmt\Return_::class);

        /** @var \PhpParser\Node\Expr\Array_ $returnExpression */
        $returnExpression = $returnNode->expr;

        return array_map(function (ArrayItem $item) {
            /** @var \PhpParser\Node\Expr\ArrayItem $item */
            $key = $item->key;
            $value = $item->value;

            $resolvedKey = 'parser-unknown';
            $resolvedValue = 'parser-unknown';
            if ($key instanceof \PhpParser\Node\Scalar\String_) {
                $resolvedKey = $key->value;
            }

            if ($value instanceof ArrayDimFetch) {
                $resolvedValue = $value->dim->getType();
            }

            return [
                $resolvedKey => $resolvedValue,
            ];
        }, $returnExpression->items)[0];
    }
}
