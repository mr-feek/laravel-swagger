<?php


namespace Mtrajano\LaravelSwagger\Tests\Stubs\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @var array<string, string>
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'first_name' => $this->resource['first_name'],
        ];
    }
}
