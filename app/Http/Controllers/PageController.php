<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('Home-Pages.about');
    }

    public function contact(): View
    {
        return view('Home-Pages.contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Mail::to(config('mail.from.address'))->send(new ContactMessage($validated));

        return redirect()
            ->route('contact')
            ->with('success', 'Thank you for reaching out. We will get back to you soon.');
    }
}
