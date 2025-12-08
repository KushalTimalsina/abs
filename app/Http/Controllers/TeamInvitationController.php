<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamMemberInvitationRequest;
use App\Models\Organization;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class TeamInvitationController extends Controller
{
    /**
     * Send an invitation.
     */
    public function store(TeamMemberInvitationRequest $request, Organization $organization)
    {
        // 1. Check Capacity
        if (!$organization->canAddTeamMember()) {
            return back()->withErrors(['message' => 'Your plan limit for team members has been reached. Please upgrade your plan.']);
        }

        // 2. Check if user already in organization
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->organizations()->where('organization_id', $organization->id)->exists()) {
             return back()->withErrors(['email' => 'User is already a member of this organization.']);
        }

        // 3. Create Invitation
        $invitation = TeamInvitation::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'email' => $request->email,
            ],
            [
                'role' => $request->role,
                'token' => Str::random(32),
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
            ]
        );

        // 4. Send Email (Placeholder)
        // Mail::to($request->email)->send(new TeamInvitationMail($invitation));

        return back()->with('status', 'Invitation sent successfully!');
    }

    /**
     * Accept an invitation.
     */
    public function accept($token)
    {
        $invitation = TeamInvitation::where('token', $token)
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now())
                        ->firstOrFail();

        $organization = $invitation->organization;

        // Check if user is logged in
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check email match? Optional. 
            // If logged in user email != invite email, usually warn or ask to confirm.
            // For now, assuming simply linking current user.
            
            if (!$user->organizations()->where('organization_id', $organization->id)->exists()) {
                 $organization->users()->attach($user->id, [
                    'role' => $invitation->role,
                    'status' => 'active',
                    'joined_at' => now(),
                    'permissions' => json_encode([]), 
                ]);
            }
            
            $invitation->update(['status' => 'accepted']);

            return redirect()->route('dashboard')->with('status', 'You have joined ' . $organization->name);
        }

        // If not logged in, show register/login page with magic token context
        // return view('auth.register-via-invite', compact('invitation'));
        // For simple flow, just redirect to register with token param
        return redirect()->route('register', ['invitation_token' => $token]);
    }
}
