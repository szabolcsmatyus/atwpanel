<?php

namespace Pterodactyl\Http\Middleware\Admin\Servers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pterodactyl\Models\Server;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServerInstalled
{
    /**
     * Checks that the server is installed before allowing access through the route.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \Pterodactyl\Models\Server|null $server */
        $server = $request->route()->parameter('server');

        if (! $server instanceof Server) {
            throw new NotFoundHttpException(
                'No server resource was located in the request parameters.'
            );
        }

        if ($server->installed !== 1) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN, 'Access to this resource is not allowed due to the current installation state.'
            );
        }

        return $next($request);
    }
}
