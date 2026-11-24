<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Sms\CompetitionSmsTemplates;
use App\Services\Sms\SmsNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
    public function index(SmsNotifier $sms): View
    {
        $balance = $sms->balance();
        $senders = $sms->allowedSenderIdsFromApi();
        $allowedSenders = CompetitionSmsTemplates::allowedSenders();
        $defaultSender = CompetitionSmsTemplates::defaultSenderId();

        $templates = [
            'registration_approved' => CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_REGISTRATION_APPROVED),
            'eligibility_reminder' => CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_ELIGIBILITY_REMINDER),
            'idea_submitted' => CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_IDEA_SUBMITTED),
            'idea_submission_reminder' => CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_IDEA_SUBMISSION_REMINDER),
            'idea_shortlisted' => CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_IDEA_SHORTLISTED),
            'idea_shortlist_reminder' => CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_IDEA_SHORTLIST_REMINDER),
        ];

        $placeholders = '{teacher_name}, {school_name}, {competition_name}, {url}, {short_url}';

        return view('admin.notification-settings.index', compact(
            'balance',
            'senders',
            'allowedSenders',
            'defaultSender',
            'templates',
            'placeholders',
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $allowed = CompetitionSmsTemplates::allowedSenders();

        $validated = $request->validate([
            'sms_default_sender_id' => ['required', 'string', Rule::in($allowed)],
            'sms_template_competition_registration_approved' => ['required', 'string', 'max:480'],
            'sms_template_competition_eligibility_reminder' => ['required', 'string', 'max:480'],
            'sms_template_competition_idea_submitted' => ['required', 'string', 'max:480'],
            'sms_template_competition_idea_submission_reminder' => ['required', 'string', 'max:480'],
            'sms_template_competition_idea_shortlisted' => ['required', 'string', 'max:480'],
            'sms_template_competition_idea_shortlist_reminder' => ['required', 'string', 'max:480'],
        ]);

        Setting::set(
            CompetitionSmsTemplates::KEY_DEFAULT_SENDER,
            $validated['sms_default_sender_id'],
            'text',
            CompetitionSmsTemplates::GROUP,
            'Default Soscom sender ID for outbound SMS'
        );

        Setting::set(
            CompetitionSmsTemplates::KEY_REGISTRATION_APPROVED,
            $validated['sms_template_competition_registration_approved'],
            'textarea',
            CompetitionSmsTemplates::GROUP,
            'SMS template: competition registration approved'
        );

        Setting::set(
            CompetitionSmsTemplates::KEY_ELIGIBILITY_REMINDER,
            $validated['sms_template_competition_eligibility_reminder'],
            'textarea',
            CompetitionSmsTemplates::GROUP,
            'SMS template: competition eligibility reminder'
        );

        Setting::set(
            CompetitionSmsTemplates::KEY_IDEA_SUBMITTED,
            $validated['sms_template_competition_idea_submitted'],
            'textarea',
            CompetitionSmsTemplates::GROUP,
            'SMS template: competition idea submitted'
        );

        Setting::set(
            CompetitionSmsTemplates::KEY_IDEA_SUBMISSION_REMINDER,
            $validated['sms_template_competition_idea_submission_reminder'],
            'textarea',
            CompetitionSmsTemplates::GROUP,
            'SMS template: competition idea submission reminder'
        );

        Setting::set(
            CompetitionSmsTemplates::KEY_IDEA_SHORTLISTED,
            $validated['sms_template_competition_idea_shortlisted'],
            'textarea',
            CompetitionSmsTemplates::GROUP,
            'SMS template: competition idea shortlisted'
        );

        Setting::set(
            CompetitionSmsTemplates::KEY_IDEA_SHORTLIST_REMINDER,
            $validated['sms_template_competition_idea_shortlist_reminder'],
            'textarea',
            CompetitionSmsTemplates::GROUP,
            'SMS template: competition idea shortlist reminder'
        );

        return back()->with('status', 'Notification settings saved.');
    }
}
