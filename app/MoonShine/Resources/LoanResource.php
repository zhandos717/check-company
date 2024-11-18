<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Loan;

use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Text;
use MoonShine\Handlers\ExportHandler;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\ID;
use MoonShine\Fields\Field;
use MoonShine\Components\MoonShineComponent;

/**
 * @extends ModelResource<Loan>
 */
class LoanResource extends ModelResource
{
    protected string $model = Loan::class;

    protected string $title = 'Займы';

    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        return [
            Block::make([
                ID::make()->sortable(),
                BelongsTo::make('Компания', 'company', resource: new CompanyResource()),
                BelongsTo::make('Платформа', 'platform', resource: new PlatformResource()),
                Number::make('Сумма', 'sum',
                    formatted: fn(Loan $loan) => number_format($loan->sum, 2, '.', ' '))
                    ->sortable(),
            ]),
        ];
    }

    /**
     * @param  Loan  $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }

    public function search(): array
    {
        return ['company.name','company.bin','platform.name'];
    }

    public function export(): ?ExportHandler
    {
        return null;
    }
}
