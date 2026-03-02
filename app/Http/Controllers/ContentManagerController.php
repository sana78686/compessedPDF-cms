<?php

namespace App\Http\Controllers;

use App\Models\ContentManagerSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentManagerController extends Controller
{
    public const KEY_HOME_PAGE_CONTENT = 'home_page_content';
    public const KEY_CONTACT_EMAIL = 'contact_email';
    public const KEY_CONTACT_PHONE = 'contact_phone';
    public const KEY_CONTACT_ADDRESS = 'contact_address';

    public function index(): Response
    {
        return Inertia::render('ContentManager/Index', [
            'homePageContent' => ContentManagerSetting::get(self::KEY_HOME_PAGE_CONTENT, ''),
            'contactEmail' => ContentManagerSetting::get(self::KEY_CONTACT_EMAIL, ''),
            'contactPhone' => ContentManagerSetting::get(self::KEY_CONTACT_PHONE, ''),
            'contactAddress' => ContentManagerSetting::get(self::KEY_CONTACT_ADDRESS, ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'home_page_content' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:64',
            'contact_address' => 'nullable|string|max:500',
        ]);

        if (array_key_exists('home_page_content', $validated)) {
            ContentManagerSetting::set(self::KEY_HOME_PAGE_CONTENT, $validated['home_page_content'] ?? '');
        }
        if (array_key_exists('contact_email', $validated)) {
            ContentManagerSetting::set(self::KEY_CONTACT_EMAIL, $validated['contact_email'] ?? '');
        }
        if (array_key_exists('contact_phone', $validated)) {
            ContentManagerSetting::set(self::KEY_CONTACT_PHONE, $validated['contact_phone'] ?? '');
        }
        if (array_key_exists('contact_address', $validated)) {
            ContentManagerSetting::set(self::KEY_CONTACT_ADDRESS, $validated['contact_address'] ?? '');
        }

        return back()->with('success', 'Content manager settings saved.');
    }
}
