<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Competition;
use App\Models\School;
use App\Models\TeacherClub;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherClubJoinService
{
    public function __construct(
        private readonly CompetitionParticipantRegistrationService $participantRegistrationService,
    ) {}

    /**
     * @return array{ok: true, teacher_club: TeacherClub, message: string}|array{ok: false, reason: string, message: string}
     */
    public function join(User $user, Club $club, Request $request, ?Competition $competition = null): array
    {
        if ($user->teacherClubs()->where('club_id', $club->id)->exists()) {
            return [
                'ok' => false,
                'reason' => 'already_member',
                'message' => 'You have already submitted a request to join this club.',
            ];
        }

        $existingTeacherClub = $user->teacherClubs()->with('school')->first();
        $existingSchool = $existingTeacherClub?->school;
        $isNewSchool = false;

        if ($existingSchool) {
            $schoolId = $existingSchool->id;
            $teacherClubData = [
                'user_id' => $user->id,
                'club_id' => $club->id,
                'school_id' => $schoolId,
            ];
        } else {
            $isNewSchool = $request->boolean('is_new_school', false);

            if ($isNewSchool) {
                $validated = $request->validate([
                    'school_name' => ['required', 'string', 'max:255', 'unique:schools,name'],
                    'school_address' => ['nullable', 'string', 'max:500'],
                    'school_phone' => ['nullable', 'string', 'max:50'],
                    'school_email' => ['nullable', 'email', 'max:255'],
                    'school_district' => ['nullable', 'string', 'max:255'],
                    'school_ward' => ['nullable', 'string', 'max:255'],
                    'school_region_id' => ['nullable', 'exists:regions,id'],
                ]);

                $school = School::create([
                    'name' => $validated['school_name'],
                    'address' => $validated['school_address'] ?? null,
                    'phone' => $validated['school_phone'] ?? null,
                    'email' => $validated['school_email'] ?? null,
                    'district' => $validated['school_district'] ?? null,
                    'ward' => $validated['school_ward'] ?? null,
                    'region_id' => $validated['school_region_id'] ?? null,
                    'is_active' => true,
                ]);

                $schoolId = $school->id;
                $teacherClubData = [
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'school_id' => $schoolId,
                ];
            } else {
                $validated = $request->validate([
                    'school_id' => ['required', 'exists:schools,id'],
                ]);

                $schoolId = $validated['school_id'];
                $teacherClubData = [
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'school_id' => $schoolId,
                ];
            }
        }

        $hasAnyApprovedTeacherFromSameSchool = TeacherClub::where('club_id', $club->id)
            ->where('school_id', $schoolId)
            ->approved()
            ->exists();

        if (! $hasAnyApprovedTeacherFromSameSchool) {
            $teacherClubData['status'] = 'approved';
            $teacherClubData['is_leader'] = true;
            $message = "Successfully joined {$club->name}! You are now the leader for your school in this club.";
        } else {
            $teacherClubData['status'] = 'pending';
            $teacherClubData['is_leader'] = false;
            $message = "Your request to join {$club->name} has been submitted and is pending approval from your school's leader.";
        }

        if ($isNewSchool) {
            $message .= ' Our team may contact you to verify your school details.';
        }

        $teacherClub = TeacherClub::create($teacherClubData);
        $teacherClub->load(['user', 'school']);

        $this->participantRegistrationService->ensureForClubMembership($teacherClub, $competition);

        return [
            'ok' => true,
            'teacher_club' => $teacherClub,
            'message' => $message,
        ];
    }
}
