<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DealStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Deal::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'          => 'nullable|exists:companies,id',
            'contact_id'          => 'nullable|exists:contacts,id',
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'amount'              => 'required|numeric|min:0',
            'stage'               => 'required|in:prospect,qualified,proposal,won,lost',
            'expected_close_date' => 'nullable|date|after:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'            => 'Deal title is required',
            'amount.required'           => 'Deal amount is required',
            'amount.min'                => 'Deal amount must be greater than or equal to 0',
            'stage.in'                  => 'Invalid deal stage. Must be one of: prospect, qualified, proposal, won, lost',
            'expected_close_date.after' => 'Expected close date must be in the future',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'team_id' => $this->user()->team_id,
            'user_id' => $this->user()->id,
        ]);
    }
}
