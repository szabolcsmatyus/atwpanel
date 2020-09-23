<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Databases;

use Pterodactyl\Models\Permission;
use Pterodactyl\Contracts\Http\ClientPermissionsRequest;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class StoreDatabaseRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    /**
     * @return string
     */
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_CREATE;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'database' => 'required|alpha_dash|min:1|max:100',
            'remote' => 'required|string|regex:/^[0-9%.]{1,15}$/',
        ];
    }
}
