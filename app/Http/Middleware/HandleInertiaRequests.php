<?php

namespace App\Http\Middleware;

use App\Enums\ServiceInterest;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Honeypot\Honeypot;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'flash' => [
                'status' => fn (): ?string => $request->session()->get('status'),
            ],
            // The contact form appears on every marketing page, both as the
            // /contact page and as a modal, so its honeypot and select options
            // are shared rather than passed per page. Filament is Livewire, not
            // Inertia, so the admin panel is unaffected.
            'honeypot' => fn (): array => app(Honeypot::class)->toArray(),
            'serviceInterests' => fn (): array => ServiceInterest::options(),
        ];
    }
}
