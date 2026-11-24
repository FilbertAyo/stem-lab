<?php

namespace App\Services\Sms;

use App\Models\Setting;

class CompetitionSmsTemplates
{
    public const KEY_REGISTRATION_APPROVED = 'sms_template_competition_registration_approved';

    public const KEY_ELIGIBILITY_REMINDER = 'sms_template_competition_eligibility_reminder';

    public const KEY_IDEA_SUBMITTED = 'sms_template_competition_idea_submitted';

    public const KEY_IDEA_SUBMISSION_REMINDER = 'sms_template_competition_idea_submission_reminder';

    public const KEY_IDEA_SHORTLISTED = 'sms_template_competition_idea_shortlisted';

    public const KEY_IDEA_SHORTLIST_REMINDER = 'sms_template_competition_idea_shortlist_reminder';

    public const KEY_DEFAULT_SENDER = 'sms_default_sender_id';

    public const GROUP = 'sms';

    public static function defaults(): array
    {
        return [
            self::KEY_REGISTRATION_APPROVED => 'Hi {teacher_name}, {school_name} is approved for {competition_name}. Continue to idea submission: {url}',
            self::KEY_ELIGIBILITY_REMINDER => 'Hi {teacher_name}, complete eligibility for {competition_name} ({school_name}): {url}',
            self::KEY_IDEA_SUBMITTED => 'Hi {teacher_name}, idea for {competition_name} ({school_name}) was submitted. View: {url}',
            self::KEY_IDEA_SUBMISSION_REMINDER => 'Hi {teacher_name}, reminder: submit your idea for {competition_name} ({school_name}): {url}',
            self::KEY_IDEA_SHORTLISTED => 'Hi {teacher_name}, {school_name} is shortlisted for {competition_name}. Continue prototype work: {url}',
            self::KEY_IDEA_SHORTLIST_REMINDER => 'Hi {teacher_name}, reminder: {school_name} is shortlisted for {competition_name}. Continue here: {url}',
        ];
    }

    public static function template(string $key): string
    {
        $defaults = self::defaults();
        $default = $defaults[$key] ?? '';

        return (string) Setting::get($key, $default);
    }

    /**
     * @param  array{teacher_name?: string, school_name?: string, competition_name?: string, url?: string, short_url?: string}  $vars
     */
    public static function render(string $template, array $vars): string
    {
        $url = (string) ($vars['url'] ?? '');
        $shortUrl = (string) ($vars['short_url'] ?? $url);

        $replacements = [
            '{teacher_name}' => (string) ($vars['teacher_name'] ?? 'Teacher'),
            '{school_name}' => (string) ($vars['school_name'] ?? 'your school'),
            '{competition_name}' => (string) ($vars['competition_name'] ?? 'the competition'),
            '{url}' => $url,
            '{short_url}' => $shortUrl,
        ];

        return strtr($template, $replacements);
    }

    public static function defaultSenderId(): string
    {
        $allowed = config('sms.allowed_senders', ['ADILISHA', 'ADILISHAXR', 'STEMLABS']);
        $fromSetting = (string) Setting::get(self::KEY_DEFAULT_SENDER, '');
        $fromEnv = (string) config('sms.default_sender', 'ADILISHA');

        $candidate = strtoupper(trim($fromSetting !== '' ? $fromSetting : $fromEnv));

        if (in_array($candidate, array_map('strtoupper', $allowed), true)) {
            foreach ($allowed as $sender) {
                if (strcasecmp((string) $sender, $candidate) === 0) {
                    return (string) $sender;
                }
            }
        }

        return 'ADILISHA';
    }

    /**
     * @return list<string>
     */
    public static function allowedSenders(): array
    {
        return array_values(array_map('strval', config('sms.allowed_senders', ['ADILISHA', 'ADILISHAXR', 'STEMLABS'])));
    }
}
