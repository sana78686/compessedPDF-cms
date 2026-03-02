<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContentManagerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seo\IndexingController;
use App\Http\Controllers\Seo\MetaManagerController;
use App\Http\Controllers\Seo\SitemapManagerController;
use App\Http\Controllers\Seo\RobotsManagerController;
use App\Http\Controllers\Seo\SchemaMarkupController;
use App\Http\Controllers\Seo\SocialSharingController;
use App\Http\Controllers\Seo\ImageSeoController;
use App\Http\Controllers\Seo\AnalyticsController;
use App\Http\Controllers\Seo\BrokenLinksController;
use App\Http\Controllers\Seo\ContentOptimizationController;
use App\Http\Controllers\Seo\PerformanceController;
use App\Http\Controllers\Seo\UrlRedirectsController;
use App\Http\Controllers\RobotsTxtController;
use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsTxtController::class)->name('robots');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('content-manager')->name('content-manager.')->group(function () {
    Route::get('/', [ContentManagerController::class, 'index'])->name('index');
    Route::put('/', [ContentManagerController::class, 'update'])->name('update');
});

Route::get('/media', function () {
    return Inertia::render('Media/Index');
})->middleware(['auth', 'verified'])->name('media.index');

Route::middleware(['auth', 'verified'])->prefix('seo')->name('seo.')->group(function () {
    Route::get('/meta-manager', [MetaManagerController::class, 'index'])->name('meta-manager');
    Route::get('/meta-manager/create', [MetaManagerController::class, 'create'])->name('meta-manager.create');
    Route::get('/url-redirects', [UrlRedirectsController::class, 'index'])->name('url-redirects');
    Route::get('/indexing', [IndexingController::class, 'index'])->name('indexing');
    Route::get('/sitemap', [SitemapManagerController::class, 'index'])->name('sitemap');
    Route::get('/robots', [RobotsManagerController::class, 'index'])->name('robots');
    Route::put('/robots', [RobotsManagerController::class, 'update'])->name('robots.update');
    Route::get('/schema-markup', [SchemaMarkupController::class, 'index'])->name('schema-markup');
    Route::get('/social-sharing', [SocialSharingController::class, 'index'])->name('social-sharing');
    Route::get('/social-sharing/edit', [SocialSharingController::class, 'edit'])->name('social-sharing.edit');
    Route::put('/social-sharing', [SocialSharingController::class, 'update'])->name('social-sharing.update');
    Route::get('/image-seo', [ImageSeoController::class, 'index'])->name('image-seo');
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance');
    Route::put('/performance', [PerformanceController::class, 'update'])->name('performance.update');
    Route::get('/broken-links', [BrokenLinksController::class, 'index'])->name('broken-links');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::put('/analytics', [AnalyticsController::class, 'update'])->name('analytics.update');
    Route::get('/content-optimization', [ContentOptimizationController::class, 'index'])->name('content-optimization');

    $seoModules = [
        'sitemap' => ['name' => 'Sitemap Manager', 'purpose' => 'Auto-generate XML sitemap and update when content changes.'],
        'robots' => ['name' => 'Robots.txt Manager', 'purpose' => 'Edit robots.txt rules and submit sitemap link.'],
        'social-sharing' => ['name' => 'Social Sharing (Open Graph)', 'purpose' => 'Manage OG title, image & Twitter cards for better preview.'],
        'schema-markup' => ['name' => 'Schema Markup Manager', 'purpose' => 'Structured data: Article, FAQ, Product, Breadcrumb.'],
        'image-seo' => ['name' => 'Image SEO Manager', 'purpose' => 'ALT text, compress images, convert to WebP.'],
        'performance' => ['name' => 'Performance & Speed', 'purpose' => 'Caching, lazy loading, minification & CDN.'],
        'indexing' => ['name' => 'Indexing Controls', 'purpose' => 'noindex, nofollow, exclude from sitemap.'],
        'analytics' => ['name' => 'SEO Analytics & Reports', 'purpose' => 'Clicks, impressions & ranking via Search Console & Analytics.'],
        'content-optimization' => ['name' => 'Content Optimization Tools', 'purpose' => 'Keyword suggestions, readability & heading checks.'],
        'broken-links' => ['name' => 'Broken Link & Error Monitor', 'purpose' => 'Detect 404s and suggest redirects.'],
    ];
    foreach ($seoModules as $slug => $info) {
        if (in_array($slug, ['url-redirects', 'indexing', 'sitemap', 'robots', 'schema-markup', 'social-sharing', 'image-seo', 'performance', 'broken-links', 'analytics', 'content-optimization'], true)) {
            continue;
        }
        Route::get('/'.$slug, fn () => Inertia::render('Seo/Placeholder', [
            'moduleName' => $info['name'],
            'purpose' => $info['purpose'],
        ]))->name($slug);
    }
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['verified', 'permission:users.view'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', fn () => Inertia::render('Users/Index'))->name('index');
        Route::get('/create', fn () => Inertia::render('Users/Create'))->name('create')->middleware('permission:users.create');
        Route::get('/{user}/edit', fn ($user) => Inertia::render('Users/Edit', ['userId' => is_object($user) ? $user->id : (int) $user]))->name('edit')->middleware('permission:users.edit');
    });

    Route::middleware(['verified', 'permission:roles.view'])->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', fn () => Inertia::render('Roles/Index'))->name('index');
        Route::get('/create', fn () => Inertia::render('Roles/Create'))->name('create')->middleware('permission:roles.create');
        Route::get('/{role}/edit', fn ($role) => Inertia::render('Roles/Edit', ['roleId' => $role]))->name('edit')->middleware('permission:roles.edit');
    });

    Route::middleware('verified')->prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index');
        Route::get('/create', [PageController::class, 'create'])->name('create');
        Route::get('/{page}/seo', fn ($page) => redirect()->route('seo.meta-manager.create', ['page_id' => is_object($page) ? $page->id : $page]))->name('seo');
        Route::get('/{page}/edit', [PageController::class, 'edit'])->name('edit');
    });

    Route::middleware('verified')->prefix('blogs')->name('blogs.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::get('/{blog}/edit', [BlogController::class, 'edit'])->name('edit');
    });
});

require __DIR__.'/auth.php';
