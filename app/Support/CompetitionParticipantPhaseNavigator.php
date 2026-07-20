<?php

namespace App\Support;

use App\Models\Competition;
use App\Models\CompetitionPhase;

final class CompetitionParticipantPhaseNavigator
{
    /**
     * @return array{
     *     accessiblePhaseKeys: list<string>,
     *     selectedPhaseKey: string|null,
     *     defaultPhaseKey: string|null,
     *     selectedPhase: CompetitionPhase|null
     * }
     */
    public static function resolve(
        Competition $competition,
        bool $registrationApproved,
        bool $ideaSubmitted,
        bool $ideaShortlisted,
        bool $prototypeSubmitted,
        bool $prototypeShortlisted,
        bool $modificationSubmitted,
        ?string $requestedPhaseKey = null,
    ): array {
        $gates = self::phaseVisibilityGates(
            $competition,
            $registrationApproved,
            $ideaSubmitted,
            $ideaShortlisted,
            $prototypeSubmitted,
            $prototypeShortlisted,
            $modificationSubmitted,
        );

        $accessible = $competition->phases
            ->filter(fn (CompetitionPhase $phase) => ($gates[$phase->phase_key] ?? false)())
            ->values();

        $accessiblePhaseKeys = $accessible->pluck('phase_key')->all();
        $defaultPhaseKey = $accessible->last()?->phase_key;

        $selectedPhaseKey = $defaultPhaseKey;
        if ($requestedPhaseKey && in_array($requestedPhaseKey, $accessiblePhaseKeys, true)) {
            $selectedPhaseKey = $requestedPhaseKey;
        }

        return [
            'accessiblePhaseKeys' => $accessiblePhaseKeys,
            'selectedPhaseKey' => $selectedPhaseKey,
            'defaultPhaseKey' => $defaultPhaseKey,
            'selectedPhase' => $selectedPhaseKey
                ? $competition->phases->firstWhere('phase_key', $selectedPhaseKey)
                : null,
        ];
    }

    public static function normalizeRequestedPhaseKey(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $normalized = str_replace('-', '_', trim($value));

        // Legacy hash targets (e.g. #idea-submission) map to phase keys.
        if ($normalized === 'idea_submission' || $value === 'idea-submission') {
            return 'idea_submission';
        }

        return $normalized;
    }

    /**
     * @return array<string, callable(): bool>
     */
    private static function phaseVisibilityGates(
        Competition $competition,
        bool $registrationApproved,
        bool $ideaSubmitted,
        bool $ideaShortlisted,
        bool $prototypeSubmitted,
        bool $prototypeShortlisted,
        bool $modificationSubmitted,
    ): array {
        return [
            'registration' => fn (): bool => $competition->isPhaseOpen('registration'),
            'idea_submission' => fn (): bool => $competition->isPhaseOpen('idea_submission') && $registrationApproved,
            'idea_shortlisting' => fn (): bool => $competition->isPhaseOpen('idea_shortlisting') && $registrationApproved && $ideaSubmitted,
            'prototype_development' => fn (): bool => $competition->isPhaseOpen('prototype_development') && $registrationApproved && $ideaSubmitted && $ideaShortlisted,
            'prototype_shortlisting' => fn (): bool => $competition->isPhaseOpen('prototype_shortlisting') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted,
            'mentor_onboarding' => fn (): bool => $competition->isPhaseOpen('mentor_onboarding') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted && $prototypeShortlisted,
            'prototype_modification' => fn (): bool => $competition->isPhaseOpen('prototype_modification') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted && $prototypeShortlisted,
            'pitch_winner' => fn (): bool => $competition->isPhaseOpen('pitch_winner') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted && $prototypeShortlisted && $modificationSubmitted,
        ];
    }
}
