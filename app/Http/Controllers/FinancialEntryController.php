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

        $monthlyEntries = FinancialEntry::query()
            ->active()
            ->whereIn('type', [FinancialEntry::TYPE_EXPENSE, FinancialEntry::TYPE_INCOME])
            ->whereDate('entry_date', '>=', $month->startOfMonth()->toDateString())
            ->whereDate('entry_date', '<=', $month->endOfMonth()->toDateString())
            ->get();

        $fixedEntries = FinancialEntry::query()
            ->active()
            ->whereIn('type', [FinancialEntry::TYPE_FIXED_EXPENSE, FinancialEntry::TYPE_FIXED_ASSET])
            ->get();

        $monthlySummary = $this->monthlySummary($fixedEntries, $monthlyEntries);
        $fortnightSummaries = $this->fortnightSummaries($month, $monthlyEntries);

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
            'monthlySummary' => $monthlySummary,
            'fortnightSummaries' => $fortnightSummaries,
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

        $activeFixedEntries = $fixedExpenses
            ->concat($fixedAssets)
            ->where('is_active', true);
        $monthlyEntries = FinancialEntry::query()
            ->active()
            ->whereIn('type', [FinancialEntry::TYPE_EXPENSE, FinancialEntry::TYPE_INCOME])
            ->whereDate('entry_date', '>=', $selectedDate->startOfMonth()->toDateString())
            ->whereDate('entry_date', '<=', $selectedDate->endOfMonth()->toDateString())
            ->get();
        $periodBalances = $this->periodBalances($selectedDate, $activeFixedEntries, $monthlyEntries);

        return view('admin.finances.day', [
            'selectedDate' => $selectedDate,
            'typeLabels' => FinancialEntry::TYPE_LABELS,
            'entryNameOptions' => [
                'expense' => FinancialEntry::EXPENSE_NAMES,
                'income' => FinancialEntry::INCOME_NAMES,
            ],
            'fixedExpenses' => $fixedExpenses,
            'dayExpenses' => $dayExpenses,
            'fixedAssets' => $fixedAssets,
            'dayIncomes' => $dayIncomes,
            'totals' => $totals,
            'periodBalances' => $periodBalances,
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
            'name' => ['required', 'string', 'max:120', Rule::in(FinancialEntry::namesForType($request->input('type')))],
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

    private function monthlySummary($fixedEntries, $monthlyEntries): array
    {
        $summary = [
            'fixed_expenses' => $fixedEntries->where('type', FinancialEntry::TYPE_FIXED_EXPENSE)->sum('amount'),
            'variable_expenses' => $monthlyEntries->where('type', FinancialEntry::TYPE_EXPENSE)->sum('amount'),
            'fixed_assets' => $fixedEntries->where('type', FinancialEntry::TYPE_FIXED_ASSET)->sum('amount'),
            'variable_incomes' => $monthlyEntries->where('type', FinancialEntry::TYPE_INCOME)->sum('amount'),
        ];

        $summary['expenses'] = $summary['fixed_expenses'] + $summary['variable_expenses'];
        $summary['assets'] = $summary['fixed_assets'] + $summary['variable_incomes'];
        $summary['balance'] = $summary['assets'] - $summary['expenses'];

        return $summary;
    }

    private function fortnightSummaries(CarbonImmutable $month, $monthlyEntries)
    {
        $periods = [
            [
                'label' => 'Primera quincena',
                'start' => $month->startOfMonth(),
                'end' => $month->startOfMonth()->setDay(15),
            ],
            [
                'label' => 'Segunda quincena',
                'start' => $month->startOfMonth()->setDay(16),
                'end' => $month->endOfMonth(),
            ],
        ];

        return collect($periods)->map(function (array $period) use ($monthlyEntries) {
            $entries = $monthlyEntries->filter(function (FinancialEntry $entry) use ($period) {
                return $entry->entry_date->betweenIncluded($period['start'], $period['end']);
            });

            $expenses = (float) $entries->where('type', FinancialEntry::TYPE_EXPENSE)->sum('amount');
            $incomes = (float) $entries->where('type', FinancialEntry::TYPE_INCOME)->sum('amount');

            return [
                ...$period,
                'expenses' => $expenses,
                'incomes' => $incomes,
                'balance' => $incomes - $expenses,
            ];
        });
    }

    private function periodBalances(CarbonImmutable $selectedDate, $fixedEntries, $monthlyEntries): array
    {
        $fortnightStart = $selectedDate->day <= 15
            ? $selectedDate->startOfMonth()
            : $selectedDate->startOfMonth()->setDay(16);
        $fortnightEnd = $selectedDate->day <= 15
            ? $selectedDate->startOfMonth()->setDay(15)
            : $selectedDate->endOfMonth();
        $fortnightEntries = $monthlyEntries->filter(function (FinancialEntry $entry) use ($fortnightStart, $fortnightEnd) {
            return $entry->entry_date->betweenIncluded($fortnightStart, $fortnightEnd);
        });

        $monthly = $this->periodBalance($fixedEntries, $monthlyEntries, 1);
        $fortnight = $this->periodBalance($fixedEntries, $fortnightEntries, 2);

        return [
            'fortnight' => [
                ...$fortnight,
                'label' => $selectedDate->day <= 15 ? 'Primera quincena' : 'Segunda quincena',
                'range' => $fortnightStart->format('d/m/Y').' - '.$fortnightEnd->format('d/m/Y'),
            ],
            'monthly' => [
                ...$monthly,
                'label' => $selectedDate->locale('es')->translatedFormat('F Y'),
                'range' => $selectedDate->startOfMonth()->format('d/m/Y').' - '.$selectedDate->endOfMonth()->format('d/m/Y'),
            ],
        ];
    }

    private function periodBalance($fixedEntries, $variableEntries, int $fixedDivider): array
    {
        $fixedExpenses = ((float) $fixedEntries->where('type', FinancialEntry::TYPE_FIXED_EXPENSE)->sum('amount')) / $fixedDivider;
        $fixedAssets = ((float) $fixedEntries->where('type', FinancialEntry::TYPE_FIXED_ASSET)->sum('amount')) / $fixedDivider;
        $variableExpenses = (float) $variableEntries->where('type', FinancialEntry::TYPE_EXPENSE)->sum('amount');
        $variableIncomes = (float) $variableEntries->where('type', FinancialEntry::TYPE_INCOME)->sum('amount');
        $expenses = $fixedExpenses + $variableExpenses;
        $assets = $fixedAssets + $variableIncomes;

        return [
            'fixed_expenses' => $fixedExpenses,
            'variable_expenses' => $variableExpenses,
            'expenses' => $expenses,
            'fixed_assets' => $fixedAssets,
            'variable_incomes' => $variableIncomes,
            'assets' => $assets,
            'balance' => $assets - $expenses,
        ];
    }
}
