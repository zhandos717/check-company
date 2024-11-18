<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Fields\Text;
use MoonShine\Fields\Url;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\ID;
use MoonShine\Fields\Field;
use MoonShine\Components\MoonShineComponent;

/**
 * @extends ModelResource<Platform>
 */
class PlatformResource extends ModelResource
{
    protected string $model = Platform::class;

    protected string $title = 'Платформы';

    protected string $column = 'name';

    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        return [
            Block::make([
                ID::make()->sortable(),
                Text::make('Наименование', 'name')->sortable(),
                Url::make('Ссылка', 'link'),
            ]),
        ];
    }

    /**
     * @param  Platform  $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }
}
