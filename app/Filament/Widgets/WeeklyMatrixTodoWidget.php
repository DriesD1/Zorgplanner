<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\CommunicationEntry;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WeeklyMatrixTodoWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2; 

    protected static ?string $heading = 'Weekmatrix Takenlijst';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $orgId = auth()->user()?->organization_id;
                $now = Carbon::now();

                // STAP 1: Haal alle klanten op om te checken wie aan de beurt is
                // We filteren hier alvast op 'planning_type' = 'week'
                $clients = Client::with('house')
                    ->where('organization_id', $orgId)
                    ->whereNotNull('next_planned_date')
                    ->whereHas('house', function ($q) {
                        $q->where('planning_type', 'week'); // Filter de 'dag agenda' eruit
                    })
                    ->get();

                // STAP 2: Filter in PHP wie er DEZE WEEK aan de beurt is
                $dueClientIds = $clients->filter(function ($client) use ($now) {
                    return $this->isVisitDue($client, $now);
                })->pluck('id')->toArray();

                // STAP 3: Geef alleen deze ID's terug aan de tabel
                // Als er niemand is, geven we 'whereIn' met een leeg array (geeft 0 resultaten)
                return Client::query()
                    ->whereIn('id', $dueClientIds)
                    ->orderBy('house_id')
                    ->orderBy('room_number');
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Bewoner')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('house.name')
                    ->label('Afdeling')
                    ->sortable(),

                Tables\Columns\TextColumn::make('room_number')
                    ->label('Kamer'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(function (Client $record) {
                        $now = Carbon::now();
                        
                        // Checken: Is er al een communicatieblad invulling?
                        $hasEntry = CommunicationEntry::where('client_id', $record->id)
                            ->where('year', $now->year)
                            ->where('week_number', $now->weekOfYear)
                            ->whereNotNull('note')
                            ->where('note', '!=', '')
                            ->exists();

                        return $hasEntry ? 'Gedaan' : 'Nog doen';
                    })
                    ->colors([
                        'success' => 'Gedaan',
                        'warning' => 'Nog doen',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Gedaan',
                        'heroicon-o-exclamation-circle' => 'Nog doen',
                    ]),
            ])
            ->filters([
                // DEZE FILTER WERKT NU WEL ECHT (SQL Query)
                Tables\Filters\Filter::make('to_do_only')
                    ->label('Alleen "Nog doen" tonen')
                    ->query(function (Builder $query) {
                        $now = Carbon::now();
                        // Filter clients die GEEN entry hebben voor deze week
                        return $query->whereNotExists(function ($subQuery) use ($now) {
                            $subQuery->select(DB::raw(1))
                                ->from('communication_entries')
                                ->whereColumn('communication_entries.client_id', 'clients.id')
                                ->where('year', $now->year)
                                ->where('week_number', $now->weekOfYear)
                                ->whereNotNull('note')
                                ->where('note', '!=', '');
                        });
                    })
                    ->default(false)
            ]);
    }

    protected function isVisitDue($client, $currentDate): bool
    {
        if (! $client->next_planned_date || ! $client->frequency_weeks) return false;

        $planned = Carbon::parse($client->next_planned_date)->startOfWeek();
        $current = $currentDate->copy()->startOfWeek();

        if ($current->lt($planned)) return false;

        return ($planned->diffInWeeks($current) % $client->frequency_weeks) === 0;
    }
}