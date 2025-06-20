<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    #[\Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth.user' => fn () => $request->user() ? $request->user()->only('id', 'name', 'email') : null,
            'flash' => [
                'message' => fn () => $request->session()->get('flash_message'),
                'type' => fn () => $request->session()->get('flash_type'),
            ],
        ];
    }
}
