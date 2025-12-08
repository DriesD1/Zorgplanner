<x-filament-panels::page>
    
    <!-- HEADER MET NAVIGATIE -->
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-2">
        <div class="flex gap-3 items-center w-full sm:w-auto">
            <button wire:click="back" class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm">
                &larr; Terug
            </button>
            
            <div class="flex-1 sm:min-w-[250px]">
                <select wire:model.live="selectedHouseId" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-sm text-sm">
                    @foreach(\App\Models\House::where('user_id', auth()->id())->get() as $h)
                        <option value="{{ $h->id }}">
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="flex items-center justify-center bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm p-1">
            <button wire:click="previousPeriod" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-500 dark:text-gray-400 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            
            <div class="px-4 text-center min-w-[140px]">
                @if($viewMode === 'agenda')
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Week {{ \Carbon\Carbon::parse($agendaStartDate)->weekOfYear }}</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($agendaStartDate)->startOfWeek()->format('d M') }} - {{ \Carbon\Carbon::parse($agendaStartDate)->endOfWeek()->format('d M') }}
                    </div>
                @else
                    <span class="text-sm font-bold text-gray-900 dark:text-white">Weekmatrix</span>
                @endif
            </div>

            <button wire:click="nextPeriod" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-500 dark:text-gray-400 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    <!-- AGENDA GRID VIEW -->
    @if($viewMode === 'agenda')
        <div class="flex flex-col h-[calc(100vh-220px)] bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            
            <!-- SCROLLABLE CONTAINER -->
            <div class="flex-1 overflow-auto relative scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-800">
                <table class="w-full border-collapse border-spacing-0 min-w-[800px] table-fixed">
                    
                    <!-- HEADER (STICKY) -->
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 sticky top-0 z-30 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                        <tr>
                            <th class="w-20 p-3 border-b border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 sticky left-0 top-0 z-40 text-xs font-bold uppercase text-gray-400 tracking-wider">
                                Tijd
                            </th>
                            
                            @foreach($daysHeader as $day)
                                <th class="w-[14%] p-2 border-b border-r border-gray-200 dark:border-gray-700 text-center {{ $day['isToday'] ? 'bg-blue-50/50 dark:bg-blue-900/20' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-semibold uppercase {{ $day['isToday'] ? 'text-primary-600' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ \Carbon\Carbon::parse($day['date'])->locale('nl')->isoFormat('ddd') }}
                                        </span>
                                        <span class="text-lg font-bold {{ $day['isToday'] ? 'text-primary-600' : 'text-gray-900 dark:text-white' }}">
                                            {{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}
                                        </span>
                                    </div>
                                    @if($day['isToday'])
                                        <div class="h-0.5 w-full bg-primary-500 absolute bottom-0 left-0"></div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <!-- BODY -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @foreach($times as $time)
                            <tr wire:key="row-{{ $time }}">
                                
                                <!-- Tijd Kolom -->
                                <td class="p-0 border-r border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900  left-0 z-20 w-20 align-middle">
                                    <div class="text-xs font-mono text-gray-400 text-center">
                                        {{ $time }}
                                    </div>
                                </td>

                                <!-- De Cellen -->
                                @foreach($daysHeader as $day)
                                    @php
                                        $dateStr = $day['date'];
                                        $appointment = $grid[$time][$dateStr] ?? null; 
                                        $isToday = $day['isToday'];
                                    @endphp

                                    <td 
                                        wire:key="cell-{{ $dateStr }}-{{ $time }}" 
                                        class="flex items-center justify-centerp-0 border-r border-b border-gray-100 dark:border-gray-700 h-16 relative align-top group transition-colors 
                                               {{ $isToday ? 'bg-blue-50/10' : '' }} hover:bg-gray-50 dark:hover:bg-white/5"
                                        style="height: 60px;"
                                    >
                                        @if($appointment)
                                            <!-- --- AFSPRAAK BLOK (GECENTREERD) --- -->
                                            <!-- items-center en justify-center zorgen dat de tekst in het midden staat -->
                                            <div class="absolute inset-0.5 rounded border-l-4 shadow-sm flex items-center justify-center cursor-default z-10 transition-transform hover:scale-[1.01]
                                                {{ $appointment->is_planned ? 'bg-blue-100/90 border-blue-500 dark:bg-blue-900/60 dark:border-blue-400' : 'bg-green-100/90 border-green-500 dark:bg-green-900/60 dark:border-green-400' }}">
                                                
                                                <!-- De Naam -->
                                                <span class="text-xs font-bold text-blue-900 dark:text-blue-100 truncate w-full px-2 text-center select-none">
                                                    {{ $appointment->client->name }}
                                                </span>

                                                <!-- De Verwijder Knop (ABSOLUTE gepositioneerd in de hoek) -->
                                                <!-- Hierdoor duwt hij de tekst niet weg -->
                                                <button 
                                                    wire:click.stop="removeAppointment('{{ $dateStr }}', '{{ $time }}')"
                                                    wire:confirm="Wil je de afspraak van {{ $appointment->client->name }} annuleren?"
                                                    class="absolute top-0.5 right-0.5 p-1 text-blue-800/40 hover:text-red-600 hover:bg-white/50 dark:text-blue-100/40 dark:hover:bg-black/20 rounded transition-all opacity-0 group-hover:opacity-100 z-20"
                                                    title="Verwijderen"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                                      <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @else
                                            <!-- --- LEEG VAK (Klikbaar) --- -->
                                            <div 
                                                wire:click="openPlanModal('{{ $dateStr }}', '{{ $time }}')"
                                                class="w-full h-full cursor-pointer transition-colors duration-100 flex items-center justify-center opacity-0 hover:opacity-100 bg-gray-100/50 dark:bg-white/5"
                                                title="Klik om te plannen"
                                            >
                                                <span class="text-gray-400 text-xl font-light select-none">+</span>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- WEEKMATRIX (Oud) -->
    @if($viewMode === 'week')
        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold sticky top-0 z-20">
                    <tr>
                        <th class="px-4 py-3 sticky left-0 bg-gray-50 dark:bg-gray-800 z-30 border-b dark:border-gray-700 border-r dark:border-gray-700 shadow-sm">Bewoner</th>
                        <th class="px-2 py-3 text-center border-b dark:border-gray-700 border-r dark:border-gray-700 w-16">Freq</th>
                        @foreach($matrixHeaders as $h)
                            <th class="px-2 text-center border-b dark:border-gray-700 min-w-[70px] border-r dark:border-gray-700/50">
                                <div class="font-bold">{{ $h['label'] }}</div>
                                <div class="text-[10px] text-gray-400">W{{ $h['week'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($matrixRows as $client)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition duration-75">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-900 border-r dark:border-gray-700 shadow-sm z-10">
                                {{ $client->name }}
                                <div class="text-xs text-gray-400 font-normal">{{ $client->room_number }}</div>
                            </td>
                            <td class="text-center border-r dark:border-gray-700 text-xs">{{ $client->frequency_weeks }}</td>
                            @foreach($matrixHeaders as $h)
                                @php
                                    $hasVisit = $client->visits->where('date', $h['date'])->first();
                                    $due = $this->isVisitDue($client, $h['date']);
                                @endphp
                                <td wire:click="toggleVisit({{ $client->id }}, '{{ $h['date'] }}')" class="text-center align-middle cursor-pointer border-r dark:border-gray-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                    @if($hasVisit) <span class="text-green-600 font-bold text-lg">✓</span>
                                    @elseif($due) <span class="text-gray-300">•</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</x-filament-panels::page>
