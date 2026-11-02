<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\WithWishlistIds;
use App\Models\Banner;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Product;
use App\Models\Recipe;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
    use WithWishlistIds;

    public function index()
    {
        $banners = \Illuminate\Support\Facades\Cache::remember('storefront_home_banners', 600, function () {
            return Banner::where('status', 1)
                ->orderBy('id', 'desc')
                ->take(3)
                ->get();
        });

        $categories = \Illuminate\Support\Facades\Cache::remember('storefront_home_categories', 600, function () {
            return Category::where('status', true)
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();
        });

        $products = \Illuminate\Support\Facades\Cache::remember('storefront_home_products', 300, function () {
            return Product::where('status', 1)
                ->with(['thumbnail', 'primaryVariant'])
                ->withCount('reviews')
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();
        });

        $wishlistIds = $this->getWishlistIds();

        return view('themes.xylo.home', compact('banners', 'categories', 'products', 'wishlistIds'));
    }

    /**
     * Canonical page key => accepted slugs. The seeder and admin-created pages
     * use short slugs (about, contact) while storefront links use the long ones.
     */
    private const PAGE_ALIASES = [
        'about-us' => ['about-us', 'about'],
        'our-services' => ['our-services', 'services'],
        'contact-us' => ['contact-us', 'contact'],
        'blog' => ['blog'],
    ];

    public function showPage($slug)
    {
        $pageKey = collect(self::PAGE_ALIASES)
            ->search(fn ($slugs) => in_array($slug, $slugs, true)) ?: $slug;
        $slugs = self::PAGE_ALIASES[$pageKey] ?? [$slug];

        $page = Page::whereIn('slug', $slugs)
            ->where('status', true)
            ->firstOrFail();

        $slug = $pageKey;

        // The blog page lists the latest published articles (recipes) under its CMS intro.
        $posts = $slug === 'blog'
            ? Recipe::where('is_active', true)->latest()->take(9)->get()
            : collect();

        return view('themes.xylo.page', compact('page', 'posts', 'pageKey'));
    }

    public function submitContact(Request $request, TelegramService $telegram)
    {
        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return back()->with('success', 'Thanks! Your message has been sent.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        ContactMessage::create($data + ['ip_address' => $request->ip()]);

        try {
            $telegram->sendMessage(
                "<b>New contact message</b>\n"
                .'From: '.e($data['name']).' ('.e($data['email']).")\n"
                .'Subject: '.e($data['subject'] ?? '-')."\n\n"
                .e($data['message'])
            );
        } catch (\Throwable $e) {
            Log::warning('Contact Telegram notification failed: '.$e->getMessage());
        }

        return back()->with('success', 'Thanks! Your message has been sent. We will reply soon.');
    }
}
