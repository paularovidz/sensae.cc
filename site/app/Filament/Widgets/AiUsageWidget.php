<?php

namespace App\Filament\Widgets;

use App\Models\AiGeneration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AiUsageWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $stats = AiGeneration::thisMonth()
            ->select([
                DB::raw('COUNT(*) as total_count'),
                DB::raw('COALESCE(SUM(total_tokens), 0) as total_tokens'),
                DB::raw('COALESCE(SUM(cost_estimate), 0) as total_cost'),
            ])
            ->first();

        return [
            Stat::make('Generations IA (ce mois)', $stats->total_count)
                ->icon('heroicon-o-sparkles'),
            Stat::make('Tokens utilises', number_format($stats->total_tokens))
                ->icon('heroicon-o-cpu-chip'),
            Stat::make('Cout estime', '$' . number_format($stats->total_cost, 4))
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
