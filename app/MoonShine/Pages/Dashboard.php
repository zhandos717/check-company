<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Company;
use App\Models\Loan;
use App\Models\Platform;
use MoonShine\Decorations\Grid;
use MoonShine\Metrics\DonutChartMetric;
use MoonShine\Metrics\LineChartMetric;
use MoonShine\Metrics\ValueMetric;
use MoonShine\Pages\Page;
use MoonShine\Components\MoonShineComponent;

class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function breadcrumbs(): array
    {
        return [
            '#' => $this->title(),
        ];
    }

    public function title(): string
    {
        return $this->title ?: 'Аналитика';
    }

    public function components(): array
    {
        return [
            Grid::make([
                ValueMetric::make('Количество займов')
                    ->value(Loan::count())->columnSpan(6),
                ValueMetric::make('Платформы')
                    ->value(Platform::count())->columnSpan(6),
                ValueMetric::make('Количество компании')
                    ->value(Company::count())
                    ->columnSpan(12),
                ValueMetric::make('Сумма')
                    ->value(number_format(Loan::sum('sum'), 2, '.', ' '))
                    ->columnSpan(12),

            ]),

        ];
    }

}
