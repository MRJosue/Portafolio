<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinancialEntryController extends Controller
{
    public function calendar(Request $request): View
    {
        $month = $this->monthFromRequest($request);
        $startOfCalendar = $month->startOfMonth()->startOfWeek();
        $endOfCalendar = $month->endOfMonth()->endOfWeek();

        $dailyEntries = FinancialEntry::query()
            ->active()
            ->whereIn('type', [FinancialEntry::TYPE_EXPENSE, FinancialEntry::TYPE_INCOME])
            ->whereDate('entry_date', '>=', $startOfCalendar->toDateString())
            ->whereDate('entry_date', '<=', $endOfCalendar->toDateString())
            ->get()
            ->groupBy(fn (FinancialEntry $entry) => $entry->entry_date->toDateString());

        $calendarDays = collect();
        for ($day = $startOfCalendar; $day->lte($endOfCalendar); $day = $day->addDay()) {
            $entries = $dailyEntries->get($day->toDateString(), collect());

            $calendarDays->push([
                'date' => $day,
                'is_current_month' => $day->month === $month->month,
                'expense_total' => $entries->where('type', FinancialEntry::TYPE_EXPENSE)->sum('amount'),
                'income_total' => $entries->where('type', FinancialEntry::TYPE_INCOME)->sum('amount'),
            ]);
        }

        return view('admin.finances.calendar', [
            'month' => $month,
            'previousMonth' => $month->subMonth(),
            'nextMonth' => $month->addMonth(),
            'calendarDays' => $calendarDays,
        ]);
    }

    public function day(string $date): View
    {
        $selectedDate = $this->dateFromRoute($date);

        $entries = FinancialEntry::query()
            ->forDayDetail($selectedDate->toDateString())
            ->orderByRaw("case type when 'fixed_expense' then 1 when 'expense' then 2 when 'fixed_asset' then 3 when 'income' then 4 else 5 end")
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        $fixedExpenses = $entries->get(FinancialEntry::TYPE_FIXED_EXPENSE, collect());
        $dayExpenses = $entries->get(FinancialEntry::TYPE_EXPENSE, collect());
        $fixedAssets = $entries->get(FinancialEntry::TYPE_FIXED_ASSET, collect());
        $dayIncomes = $entries->get(FinancialEntry::TYPE_INCOME, collect());

        $totals = [
            'fixed_expenses' => $this->activeTotal($fixedExpenses),
            'day_expenses' => $this->activeTotal($dayExpenses),
            'fixed_assets' => $this->activeTotal($fixedAssets),
            'day_incomes' => $this->activeTotal($dayIncomes),
        ];
        $totals['expenses'] = $totals['fixed_expenses'] + $totals['day_expenses'];
        $totals['assets'] = $totals['fixed_assets'] + $totals['day_incomes'];
        $totals['balance'] = $totals['assets'] - $totals['expenses'];

        return view('admin.finances.day', [
            'selectedDate' => $selectedDate,
            'typeLabels' => FinancialEntry::TYPE_LABELS,
            'fixedExpenses' => $fixedExpenses,
            'dayExpenses' => $dayExpenses,
            'fixedAssets' => $fixedAssets,
            'dayIncomes' => $dayIncomes,
            'totals' => $totals,
        ]);
    }

    public function store(Request $request, string $date): RedirectResponse
    {
        $selectedDate = $this->dateFromRoute($date);
        $validated = $this->validatedEntry($request);

        FinancialEntry::create($this->payloadForDate($validated, $selectedDate));

        return back()->with('finance_status', 'Entrada financiera creada.');
    }

    public function update(Request $request, string $date, FinancialEntry $entry): RedirectResponse
    {
        $selectedDate = $this->dateFromRoute($date);
        $validated = $this->validatedEntry($request);

        $entry->update($this->payloadForDate($validated, $selectedDate));

        return back()->with('finance_status', 'Entrada financiera actualizada.');
    }

    public function destroy(string $date, FinancialEntry $entry): RedirectResponse
    {
        $this->dateFromRoute($date);

        $entry->delete();

        return back()->with('finance_status', 'Entrada financiera eliminada.');
    }

    private function monthFromRequest(Request $request): CarbonImmutable
    {
        $month = $request->string('month')->toString();

        if (! $month) {
            return CarbonImmutable::now()->startOfMonth();
        }

        return CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth();
    }

    private function dateFromRoute(string $date): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $date)->startOfDay();
    }

    private function validatedEntry(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'string', Rule::in(FinancialEntry::TYPES)],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function payloadForDate(array $validated, CarbonImmutable $selectedDate): array
    {
        $isFixed = in_array($validated['type'], [FinancialEntry::TYPE_FIXED_EXPENSE, FinancialEntry::TYPE_FIXED_ASSET], true);

        return [
            ...$validated,
            'entry_date' => $isFixed ? null : $selectedDate->toDateString(),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    private function activeTotal($entries): float
    {
        return (float) $entries
            ->where('is_active', true)
            ->sum('amount');
    }
}
