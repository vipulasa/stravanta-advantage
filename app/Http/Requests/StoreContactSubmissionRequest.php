<?php

namespace App\Http\Requests;

use App\Enums\ServiceInterest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The honeypot fields are deliberately absent: their name is randomised per
     * render, so they cannot be listed here. Using `validated()` in the
     * controller strips them from the data that reaches the model.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'service_interest' => ['required', Rule::enum(ServiceInterest::class)],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_interest.required' => 'Please choose the engagement you are interested in.',
            'message.min' => 'Please tell us a little more about what you need.',
        ];
    }

    /**
     * Get the custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_interest' => 'service interest',
        ];
    }
}
