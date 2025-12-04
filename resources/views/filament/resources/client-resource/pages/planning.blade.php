<x-filament-panels::page>
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-4">
        
        <div class="w-full sm:w-1/2 flex items-end gap-4">
            <button wire:click="back" class="mb-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Terug
            </button>

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Locatie</label>
                <select wire:model.live="selectedHouseId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600">
                    <option value="">Kies een huis...</option>
                    @foreach(\App\Models\House::where('user_id', auth()->id())->get() as $house)
                        <option value="{{ $house->id }}">
                            {{ $house->name }} 
                            ({{ $house->has_custom_schedule ? 'Dagplanning' : 'Weekplanning' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-sm">
            Huidige weergave: 
            <span class="font-bold text-primary-600">
                {{ $viewMode === 'week' ? 'Week Matrix' : 'Dag Agenda' }}
            </span>
        </div>
    </div>

    @if($selectedHouseId && count($clients) > 0)
        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left border-collapse">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 sticky left-0 z-20 bg-gray-50 dark:bg-gray-800 border-b border-r dark:border-gray-700 min-w-[150px]">Bewoner</th>
                        <th class="px-2 py-3 text-center border-b border-r dark:border-gray-700 w-12 bg-gray-50 dark:bg-gray-800">Freq</th>
                        
                        @foreach($headers as $header)
                            <th class="px-2 py-2 text-center border-b border-r dark:border-gray-700 min-w-[60px]">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $header['label'] }}</div>
                                <div class="text-[10px] text-gray-500">{{ $header['sub'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                
                <tbody>
                    @foreach($clients as $client)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition group">
                            
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white sticky left-0 z-10 bg-white dark:bg-gray-900 border-r dark:border-gray-700 group-hover:bg-gray-50 dark:group-hover:bg-gray-800">
                                <div class="flex flex-col">
                                    <span class="font-bold">{{ $client->name }}</span>
                                    <span class="text-xs text-gray-500">Kmr {{ $client->room_number }}</span>
                                </div>
                            </td>

                            <td class="text-center border-r dark:border-gray-700 text-gray-400 text-xs">
                                {{ $client->frequency_weeks ?? '-' }}
                            </td>

                            @foreach($headers as $header)
                                @php
                                    // Check 1: Staat er een handmatig opgeslagen kruisje in de database?
                                    $manualVisit = $client->visits->where('date', $header['date'])->first();
                                    
                                    // Check 2 (FOUT 1): Is het wiskundig tijd voor een bezoek?
                                    $isCalculated = $this->isVisitDue($client, $header['date']);
                                    
                                    // Bepaal kleur/icoon
                                    $showCross = $manualVisit || $isCalculated;
                                    $isConfirmed = $manualVisit ? true : false;
                                @endphp
                                
                                <td 
                                    wire:click="toggleVisit({{ $client->id }}, '{{ $header['date'] }}')"
                                    class="p-0 border-r border-b dark:border-gray-700 cursor-pointer text-center align-middle hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors h-12"
                                >
                                    @if($showCross)
                                        @if($isConfirmed)
                                            <span class="text-green-600 dark:text-green-400 font-bold text-xl block">X</span>
                                        @else
                                            <span class="text-gray-400 font-bold text-xl block opacity-50">x</span>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($selectedHouseId)
        <div class="flex flex-col items-center justify-center p-12 text-gray-500 bg-white dark:bg-gray-900 rounded-xl border border-dashed border-gray-300">
            <x-heroicon-o-users class="w-12 h-12 mb-2 text-gray-300"/>
            <p>Geen bewoners gevonden in deze afdeling.</p>
            <a href="{{ \App\Filament\Resources\ClientResource::getUrl('create') }}" class="text-primary-600 hover:underline mt-2">
                + Voeg eerste bewoner toe
            </a>
        </div>
    @endif

</x-filament-panels::page>