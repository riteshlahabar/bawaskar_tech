<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiRouteProtectionTest extends TestCase
{
    /**
     * Endpoints that are deliberately public: the storefront catalog the apps
     * read before anyone signs in, plus the auth handshake itself.
     */
    private const PUBLIC_PREFIXES = ['health', 'auth/', 'catalog/', 'translations'];

    public function test_every_non_public_api_route_is_behind_the_token_middleware(): void
    {
        $unprotected = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            $path = preg_replace('#^api/(v1/)?#', '', $uri);

            foreach (self::PUBLIC_PREFIXES as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    continue 2;
                }
            }

            $guarded = collect($route->gatherMiddleware())
                ->contains(fn (mixed $middleware): bool => is_string($middleware) && str_starts_with($middleware, 'api.auth'));

            if (! $guarded) {
                $unprotected[] = implode('|', $route->methods()).' '.$uri;
            }
        }

        $this->assertSame([], $unprotected, "These API routes have no api.auth middleware:\n".implode("\n", $unprotected));
    }

    /**
     * Putting the token middleware on the login endpoints would lock everyone
     * out: you cannot present a token before you have one.
     */
    public function test_login_endpoints_are_not_behind_the_token_middleware(): void
    {
        $locked = [];

        foreach (Route::getRoutes() as $route) {
            if (! str_contains($route->uri(), 'auth/')) {
                continue;
            }

            $guarded = collect($route->gatherMiddleware())
                ->contains(fn (mixed $middleware): bool => is_string($middleware) && str_starts_with($middleware, 'api.auth'));

            if ($guarded) {
                $locked[] = $route->uri();
            }
        }

        $this->assertSame([], $locked, "These auth routes are unreachable:\n".implode("\n", $locked));
    }

    public function test_admin_and_storefront_logins_are_rate_limited(): void
    {
        foreach (['admin.login.store', 'store.auth.login', 'store.auth.register'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Route {$name} is missing.");
            $this->assertContains(
                'throttle:login',
                $route->gatherMiddleware(),
                "Route {$name} can be brute forced without a throttle."
            );
        }
    }
}
