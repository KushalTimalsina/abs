<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Organization;

class TeamMemberInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Must be admin of the organization context
        $organization = $this->route('organization'); 
        // Logic handled by middleware usually, but extra check if needed.
        // For now, return true (middleware 'can:org.admin' or equiv will guard controller)
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,team_member,frontdesk'],
        ];
    }
}
