<?php

namespace App\Services;

use App\Models\VisitDaily;
use App\Models\VisitPlan;
use Illuminate\Support\Carbon;

class VisitPlanningService
{
    public function regenerateForFournisseur(int $frsId, ?Carbon $from = null, ?Carbon $to = null): int
    {
        $plans = VisitPlan::query()
            ->with(['days', 'client:id,prevendeur_id'])
            ->where('id_frs', $frsId)
            ->get();

        foreach ($plans as $plan) {
            $this->regenerateForPlan($plan, $from, $to);
        }

        return $plans->count();
    }

    public function regenerateForPlan(VisitPlan $plan, ?Carbon $from = null, ?Carbon $to = null): void
    {
        $plan->loadMissing(['days', 'client:id,prevendeur_id']);

        $from = ($from ?? Carbon::today())->copy()->startOfDay();
        $to = ($to ?? Carbon::today()->addDays(60))->copy()->startOfDay();

        VisitDaily::query()
            ->where('visit_plan_id', $plan->id)
            ->where('source', 'generated')
            ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
            ->delete();

        if (! $plan->is_active) {
            return;
        }

        $prevendeurId = (int) ($plan->client?->prevendeur_id ?? $plan->prevendeur_id ?? 0);
        if ($prevendeurId <= 0) {
            return;
        }

        if ((int) ($plan->prevendeur_id ?? 0) !== $prevendeurId) {
            $plan->forceFill(['prevendeur_id' => $prevendeurId])->save();
        }

        $startDate = Carbon::parse($plan->start_date)->startOfDay();
        $endDate = $plan->end_date ? Carbon::parse($plan->end_date)->startOfDay() : null;

        $windowStart = $from->greaterThan($startDate) ? $from->copy() : $startDate->copy();
        $windowEnd = $endDate && $endDate->lessThan($to) ? $endDate->copy() : $to->copy();

        if ($windowStart->gt($windowEnd)) {
            return;
        }

        foreach ($this->generateDates($plan, $windowStart, $windowEnd) as $date) {
            $existing = VisitDaily::query()
                ->whereDate('visit_date', $date->toDateString())
                ->where('client_id', $plan->client_id)
                ->where('id_frs', $plan->id_frs)
                ->first();

            if ($existing && $existing->source === 'manual') {
                continue;
            }

            VisitDaily::query()->updateOrCreate(
                [
                    'visit_date' => $date->toDateString(),
                    'client_id' => $plan->client_id,
                    'id_frs' => $plan->id_frs,
                ],
                [
                    'prevendeur_id' => $prevendeurId,
                    'visit_plan_id' => $plan->id,
                    'status' => 'planned',
                    'source' => 'generated',
                ]
            );
        }
    }

    protected function generateDates(VisitPlan $plan, Carbon $from, Carbon $to): array
    {
        $dates = [];
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            if ($this->matches($plan, $cursor)) {
                $dates[] = $cursor->copy();
            }

            $cursor->addDay();
        }

        return $dates;
    }

    protected function matches(VisitPlan $plan, Carbon $date): bool
    {
        $startDate = Carbon::parse($plan->start_date)->startOfDay();

        if ($date->lt($startDate)) {
            return false;
        }

        if ($plan->end_date && $date->gt(Carbon::parse($plan->end_date)->startOfDay())) {
            return false;
        }

        return match ($plan->frequency_type) {
            'daily' => $this->matchesDaily($plan, $date, $startDate),
            'weekly' => $this->matchesWeekly($plan, $date, $startDate),
            'monthly' => $this->matchesMonthly($plan, $date, $startDate),
            default => false,
        };
    }

    protected function matchesDaily(VisitPlan $plan, Carbon $date, Carbon $startDate): bool
    {
        $interval = max(1, (int) $plan->interval_value);

        return $startDate->diffInDays($date) % $interval === 0;
    }

    protected function matchesWeekly(VisitPlan $plan, Carbon $date, Carbon $startDate): bool
    {
        $days = $plan->days->pluck('day_of_week')->map(fn ($value) => (int) $value)->all();

        if (! in_array($date->dayOfWeek, $days, true)) {
            return false;
        }

        $interval = max(1, (int) $plan->interval_value);
        $anchorWeek = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeek = $date->copy()->startOfWeek(Carbon::SUNDAY);

        return $anchorWeek->diffInWeeks($currentWeek) % $interval === 0;
    }

    protected function matchesMonthly(VisitPlan $plan, Carbon $date, Carbon $startDate): bool
    {
        $dayOfWeek = $plan->days->first()?->day_of_week;

        if ($dayOfWeek === null || $date->dayOfWeek !== (int) $dayOfWeek) {
            return false;
        }

        $interval = max(1, (int) $plan->interval_value);
        $monthDiff = $startDate->copy()->startOfMonth()->diffInMonths($date->copy()->startOfMonth());

        if ($monthDiff % $interval !== 0) {
            return false;
        }

        return $this->matchesMonthOccurrence((string) $plan->month_occurrence, $date);
    }

    protected function matchesMonthOccurrence(string $occurrence, Carbon $date): bool
    {
        if ($occurrence === 'last') {
            return $date->copy()->addWeek()->month !== $date->month;
        }

        $position = intdiv($date->day - 1, 7) + 1;

        return match ($occurrence) {
            'first' => $position === 1,
            'second' => $position === 2,
            'third' => $position === 3,
            'fourth' => $position === 4,
            default => false,
        };
    }
}
