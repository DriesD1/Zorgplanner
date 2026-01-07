<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class UpcomingVisitsTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full'; 

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Agenda Vandaag';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Visit::query()
                    ->where('organization_id', auth()->user()?->organization_id)
                    ->whereDate('date', Carbon::today())
                    ->orderBy('time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('time')
                    ->label('Tijd')
                    ->time('H:i')
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Bewoner')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.room_number')
                    ->label('Kamer')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('client.house.name')
                    ->label('Huis / Afdeling'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->state(function (Visit $record) {
                        if ($record->is_done) {
                            return 'Gedaan';
                        }

                        // FIX: Veilige manier om datum en tijd samen te voegen
                        $visitDateTime = Carbon::parse($record->date)->setTimeFromTimeString($record->time);
                        
                        if ($visitDateTime->isPast()) {
                            return 'Gedaan'; // Tijd is voorbij, dus we noemen het "Gedaan" (of Te Laat)
                        }
                        
                        return 'Todo';
                    })
                    ->colors([
                        'success' => 'Gedaan',
                        'gray' => 'Todo',
                    ]),
            ]);
    }
}