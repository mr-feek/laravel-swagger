<?php

namespace Mtrajano\LaravelSwagger\Parsers;

use Illuminate\Http\Resources\Json\JsonResource;
use PhpParser\Node\Scalar;
use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\Reflection\ReflectionMethod;

class ResponseParser
{
    /**
     * @return array<int, array<string, mixed>
     */
    public function parseResponses(ReflectionMethod $controllerMethod): array
    {
        $controllerReturnType = $controllerMethod->getReturnType();
        $parsedResponse = null;

        if ($controllerReturnType instanceof \PHPStan\BetterReflection\Reflection\ReflectionNamedType) {
            $reflection = (new BetterReflection())->classReflector()->reflect($controllerReturnType->getName());

            if ($reflection->isSubclassOf(JsonResource::class)) {
                $parsedResponse = (new JsonResourceResponseParser())->parse($reflection);
            }
        }

        return [
            '200' => [
                'description' => 'OK',
                'example' => $parsedResponse ? $this->mapParsedTypesToOpenApiTypes($parsedResponse) : 'parser-unknown',
            ],
        ];
    }

    private function mapParsedTypesToOpenApiTypes(array $parsedResponse): array
    {
        return array_map(function (string $parsedType) {
            switch ($parsedType) {
                case 'Scalar_String': return 'string';
                default: return $parsedType;
            }
        }, $parsedResponse);
    }
}
