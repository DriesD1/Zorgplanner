<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\House;
use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OrgStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // We pakken de organisatie ID van de ingelogde gebruiker
        $orgId = auth()->user()?->organization_id;

        // 1. Aantal huizen van de organisatie
        $housesCount = House::where('organization_id', $orgId)->count();

        // 2. Aantal klanten van de organisatie
        $clientsCount = Client::where('organization_id', $orgId)->count();

        // 3. Afspraken VANDAAG voor de hele organisatie
        $visitsToday = Visit::where('organization_id', $orgId)
            ->whereDate('date', Carbon::today())
            ->count();

        return [
            Stat::make('Huizen', $housesCount)
                ->icon('heroicon-o-home'),
            
            Stat::make('Bewoners', $clientsCount)
                ->icon('heroicon-o-users'),

            Stat::make('Afspraken Vandaag', $visitsToday)
                ->description('Op de agenda voor vandaag')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}