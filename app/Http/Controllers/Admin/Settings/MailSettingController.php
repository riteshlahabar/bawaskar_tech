<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Mail\MailSenderContract;
use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\MailSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class MailSettingController extends Controller
{
    public function __construct(private readonly MailSenderContract $mail) {}

    public function edit(): View
    {
        return view('admin.settings.mail', [
            'setting' => MailSetting::current(),
            'pageTitle' => 'Mail Settings',
            'breadcrumbs' => ['Admin', 'Settings', 'Mail Settings'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), [], ['from_address' => 'from address']);

        $setting = MailSetting::current();

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['invoice_email_enabled'] = $request->boolean('invoice_email_enabled');

        // The form never shows the saved password back, so an empty box means
        // "leave it alone", not "clear it".
        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $setting->fill($validated)->save();

        return back()->with('success', 'Mail settings saved.');
    }

    public function test(Request $request): RedirectResponse
    {
        $validated = $request->validate(['test_email' => ['required', 'email', 'max:255']]);

        if (! MailSetting::current()->canSend()) {
            return back()->with('error', 'Turn mail on and fill in the From Address before sending a test.');
        }

        try {
            $this->mail->sendOrFail(new TestMail, $validated['test_email']);
        } catch (Throwable $failure) {
            return back()->with('error', 'Test email failed: '.$failure->getMessage());
        }

        return back()->with('success', 'Test email sent to '.$validated['test_email'].'.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'mailer' => ['required', 'string', Rule::in(array_keys(MailSetting::MAILERS))],
            'host' => ['nullable', 'string', 'max:191', 'required_if:mailer,smtp'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:191'],
            'password' => ['nullable', 'string', 'max:191'],
            'encryption' => ['required', 'string', Rule::in(array_keys(MailSetting::ENCRYPTIONS))],
            'from_address' => ['required', 'email', 'max:191'],
            'from_name' => ['nullable', 'string', 'max:191'],
            'reply_to_address' => ['nullable', 'email', 'max:191'],
        ];
    }
}
