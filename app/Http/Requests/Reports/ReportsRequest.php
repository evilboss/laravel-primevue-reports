<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'market_ids' => ['sometimes', 'array'],
            'market_ids.*' => ['integer', 'exists:markets,id'],
            'start_date' => ['sometimes', 'date', 'before_or_equal:end_date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'market_ids.*.exists' => 'One or more selected markets do not exist.',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set default date range to last 30 days if not provided
        if (!$this->has('start_date') && !$this->has('end_date')) {
            $this->merge([
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);
        }

        // Filter market IDs based on user permissions
        $user = auth()->user();
        if ($user && !$user->isAdmin() && $this->has('market_ids')) {
            $accessibleMarketIds = $user->getAccessibleMarketIds();
            $requestedMarketIds = array_intersect($this->input('market_ids'), $accessibleMarketIds);
            $this->merge(['market_ids' => $requestedMarketIds]);
        } elseif ($user && !$user->isAdmin() && !$this->has('market_ids')) {
            // If no markets specified and user is not admin, use their accessible markets
            $this->merge(['market_ids' => $user->getAccessibleMarketIds()]);
        }
    }
}
