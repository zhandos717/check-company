<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\CompanyResource;
use App\MoonShine\Resources\LoanResource;
use App\MoonShine\Resources\PlatformResource;
use MoonShine\Providers\MoonShineApplicationServiceProvider;
use MoonShine\MoonShine;
use MoonShine\Menu\MenuGroup;
use MoonShine\Menu\MenuItem;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Menu\MenuElement;
use MoonShine\Pages\Page;
use Closure;

class MoonShineServiceProvider extends MoonShineApplicationServiceProvider
{
    /**
     * @return list<ResourceContract>
     */
    protected function resources(): array
    {
        return [];
    }

    /**
     * @return list<Page>
     */
    protected function pages(): array
    {
        return [];
    }


    protected function menu(): array
    {
        return [
            MenuItem::make(
                static fn() => 'Займы',
                new LoanResource()
            ),


            MenuGroup::make(static fn() => 'Справочники', [

                MenuItem::make(
                    static fn() => 'Компании',
                    new CompanyResource()
                ),
                MenuItem::make(
                    static fn() => 'Платформы',
                    new PlatformResource()
                ),
            ]),

            MenuGroup::make(static fn() => __('moonshine::ui.resource.system'), [
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.admins_title'),
                    new MoonShineUserResource()
                ),
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.role_title'),
                    new MoonShineUserRoleResource()
                ),
            ]),
        ];
    }

    /**
     * @return Closure|array{css: string, colors: array, darkColors: array}
     */
    protected function theme(): array
    {
        return [];
    }
}
