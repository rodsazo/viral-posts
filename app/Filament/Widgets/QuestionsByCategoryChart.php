<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class QuestionsByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Preguntas por categoría';

    protected function getData(): array
    {
        /** @var Account $account */
        $account = Filament::getTenant();

        $categories = $account->categories()->withCount('questions')->get();

        $labels = $categories->pluck('name')->all();
        $data = $categories->pluck('questions_count')->map(fn ($n) => (int) $n)->all();
        $colors = $categories->pluck('color')->map(fn ($c) => $c ?: '#9ca3af')->all();

        $uncategorized = $account->questions()->whereNull('category_id')->count();
        if ($uncategorized > 0) {
            $labels[] = 'Sin categoría';
            $data[] = $uncategorized;
            $colors[] = '#d1d5db';
        }

        return [
            'datasets' => [[
                'label' => 'Preguntas',
                'data' => $data,
                'backgroundColor' => $colors,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
