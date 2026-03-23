<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('super-admin') || $user->hasPermissionTo('edit-projects'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'integer|exists:users,id',
            'developer_ids' => 'required|array|min:1',
            'developer_ids.*' => 'integer|exists:users,id',
        ];
    }
}
