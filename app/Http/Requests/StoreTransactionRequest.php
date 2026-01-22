<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware handles auth
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:income,expense'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
                'required_without:category'
            ],
            'category' => ['nullable', 'string', 'max:120', 'required_without:category_id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'savings_amount' => ['nullable', 'numeric', 'min:0.01', 'lte:amount', 'max:99999999'],
            'occurred_at' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
            'goal_id' => [
                'nullable',
                Rule::exists('goals', 'id')->where('user_id', $this->user()->id)
            ],
            'is_fixed' => ['nullable', 'boolean'],
        ];
    }
}
