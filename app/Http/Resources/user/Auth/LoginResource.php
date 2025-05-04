<?php

namespace App\Http\Resources\User\Auth;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * The API token issued at login.
     *
     * @var string
     */
    protected string $token;

    /**
     * @param  \App\Models\User  $resource
     * @param  string           $token
     */
    public function __construct($resource, string $token)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    public function toArray($request): array
    {
        return [
            'status' => true,
            'token'  => $this->token,
            'user'   => new UserResource($this->resource),
        ];
    }
}
