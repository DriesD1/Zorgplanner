<x-filament-panels::page>

    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-4">
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
        
        @if($viewMode === 'week')
            <div class="flex items-center gap-2 sm:order-3">
                <button 
                    wire:click="goToCommunicationSheet"
                    class="px-4 py-2 text-sm font-semibold rounded-lg border shadow-sm transition flex items-center gap-2 bg-primary-500 text-white border-primary-600 hover:bg-primary-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7.5h6M9 11.5h6" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 4.5h7a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5l2 2m-2-2l-2 2" />
                    </svg>
                    Communicatieblad
                </button>
            </div>
        @endif
        
        @if($viewMode === 'agenda')
            <div class="flex items-center justify-center bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm h-10 px-1" style="min-width: fit-content;">
                <button wire:click="previousPeriod" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-500 dark:text-gray-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                
                <div class="px-4 text-center min-w-[160px]">
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Week {{ \Carbon\Carbon::parse($agendaStartDate)->weekOfYear }}</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($agendaStartDate)->startOfWeek()->format('d M') }} - {{ \Carbon\Carbon::parse($agendaStartDate)->endOfWeek()->format('d M') }}
                    </div>
                </div>

                <button wire:click="nextPeriod" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-gray-500 dark:text-gray-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        @endif
    </div>

    @if($viewMode === 'agenda')
        <div class="isolate flex flex-col h-[calc(100vh-240px)] bg-white dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            
            <div x-data="{ openPopover: null }" @click="openPopover = null" class="flex-1 overflow-auto relative scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-800">
                <table class="w-full border-collapse border-spacing-0 min-w-[800px] table-fixed">
                    <thead class="sticky top-0 z-20 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                        <tr>
                            <th class="sticky left-0 top-0 z-30 w-20 p-3 border-b border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs font-bold uppercase text-gray-400 tracking-wider">
                                Tijd
                            </th>
                            @foreach($daysHeader as $day)
                                <th class="relative w-[14%] p-2 border-b border-r border-gray-200 dark:border-gray-700 text-center {{ $day['isToday'] ? 'bg-blue-50/50 dark:bg-blue-900/20' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-semibold uppercase {{ $day['isToday'] ? 'text-primary-600' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ \Carbon\Carbon::parse($day['date'])->locale('nl')->isoFormat('ddd') }}
                                        </span>
                                        <span class="text-lg font-bold {{ $day['isToday'] ? 'text-primary-600' : 'text-gray-900 dark:text-white' }}">
                                            {{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}
                                        </span>
                                    </div>
                                    @if($day['isToday']) <div class="h-0.5 w-full bg-primary-500 absolute bottom-0 left-0"></div> @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @foreach($times as $time)
                            <tr wire:key="row-{{ $time }}">
                                <td class="sticky left-0 z-10 w-20 p-0 border-r border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 align-middle">
                                    <div class="w-full h-full flex flex-col items-center justify-center">
                                        <div class="text-xs font-mono text-gray-400 text-center">{{ $time }}</div>
                                    </div>
                                </td>
                                @foreach($daysHeader as $day)
                                    @php
                                        $dateStr = $day['date'];
                                        $appointment = $grid[$time][$dateStr] ?? null; 
                                        $isToday = $day['isToday'];
                                    @endphp
                                    <td wire:key="cell-{{ $dateStr }}-{{ $time }}" 
                                        class="p-0 border-r border-b border-gray-100 dark:border-gray-700 h-16 relative align-top group transition-colors hover:bg-gray-50 dark:hover:bg-white/5 {{ $isToday ? 'bg-blue-50/10 dark:bg-blue-900/10' : '' }}"
                                        style="height: 60px;">
                                        <div class="w-full h-full flex flex-col items-center justify-center relative">
                                            @if($appointment)
                                                <div class="absolute inset-0 border-l-4 shadow-sm flex flex-col items-center justify-center gap-1 cursor-pointer transition-transform hover:scale-[1.01]
                                                    {{ $appointment->is_planned ? 'border-sky-500 dark:border-sky-400' : 'bg-green-100/90 border-green-500 dark:bg-green-900/60 dark:border-green-400' }}"
                                                    @if($appointment->is_planned) style="background: rgba(56, 189, 248, 0.16); border-left: solid rgba(80, 189, 248, 0.16); border-left-width: 0.2rem;" @endif
                                                    @click.stop="openPopover = openPopover === '{{ $dateStr }}-{{ $time }}' ? null : '{{ $dateStr }}-{{ $time }}'">
                                                    
                                                    <span class="text-xs font-bold {{ $appointment->is_planned ? 'text-sky-900 dark:text-sky-100' : 'text-green-900 dark:text-green-100' }} truncate w-full px-2 text-center select-none border-l-amber-50 border-l-4">
                                                        {{ $appointment->client->name }}
                                                    </span>

                                                    <div x-cloak x-show="openPopover === '{{ $dateStr }}-{{ $time }}'" @click.outside="openPopover = null" 
                                                         class="absolute top-full mt-2 left-1/2 -translate-x-1/2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg w-56 p-3 z-50">
                                                        <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $appointment->client->name }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ \Carbon\Carbon::parse($dateStr)->format('d/m/Y') }} · {{ $time }}</div>
                                                        <div class="flex gap-2">
                                                            <button type="button" @click.stop="openPopover = null" class="flex-1 px-3 py-2 text-xs font-medium rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition">Sluiten</button>
                                                            <button type="button" wire:click.stop="removeAppointment('{{ $dateStr }}', '{{ $time }}')" class="px-3 py-2 text-xs font-semibold rounded-md bg-red-500 hover:bg-red-600 text-white transition flex items-center justify-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div wire:click="openPlanModal('{{ $dateStr }}', '{{ $time }}')" class="w-full h-full cursor-pointer transition-colors duration-100 flex flex-col items-center justify-center opacity-0 hover:opacity-100 bg-gray-100/50 dark:bg-white/5" title="Klik om te plannen">
                                                    <span class="text-gray-400 text-xl font-light select-none">+</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>


    @elseif($viewMode === 'week')
        <div class="isolate relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col h-[calc(100vh-240px)]">
            
            <div class="overflow-auto flex-1 w-full">
                <table class="w-full text-sm text-left border-separate border-spacing-0">
                    <thead class="sticky top-0 z-20 text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold">
                        <tr>
                            <th class="sticky left-0 top-0 z-50 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-r border-gray-200 dark:border-gray-700 min-w-[220px] shadow-sm">
                                Bewoner
                            </th>
                            <th class="sticky top-0 z-20 px-2 py-3 text-center bg-gray-50 dark:bg-gray-800 border-b border-r border-gray-200 dark:border-gray-700 w-16">
                                Freq
                            </th>
                            
                            @foreach($matrixHeaders as $h)
                                @php
                                    $headerDate = \Carbon\Carbon::parse($h['date']);
                                    $isCurrentWeek = $headerDate->isSameWeek(now());
                                @endphp
                                
                                <th class="sticky top-0 z-20 px-1 py-2 text-center border-b border-r border-gray-200 dark:border-gray-700 min-w-[85px]
                                    {{ $isCurrentWeek ? 'bg-primary-50 dark:bg-primary-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                                    
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="text-xs font-bold {{ $isCurrentWeek ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $h['label'] }}
                                        </span>
                                        <span class="text-[10px] uppercase mt-0.5 {{ $isCurrentWeek ? 'text-primary-500 dark:text-primary-300' : 'text-gray-400' }}">
                                            W{{ $h['week'] }}
                                        </span>
                                    </div>
                                    @if($isCurrentWeek)
                                        <div class="absolute bottom-0 left-0 w-full h-[2px] bg-primary-500"></div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($matrixRows as $index => $client)
                            <tr class="group transition duration-75 {{ $loop->even ? 'bg-gray-50/50 dark:bg-white/[0.02]' : 'bg-white dark:bg-gray-900' }} hover:bg-gray-100 dark:hover:bg-white/5">
                                
                                <td class="sticky left-0 z-10 px-4 py-3 border-r border-b border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-white shadow-sm
                                    {{ $loop->even ? 'bg-gray-50/95 dark:bg-gray-900' : 'bg-white dark:bg-gray-900' }} group-hover:bg-gray-100 dark:group-hover:bg-gray-800">
                                    <div class="flex flex-col">
                                        <span class="truncate">{{ $client->name }}</span>
                                        <span class="text-xs text-gray-400 font-normal">{{ $client->room_number }}</span>
                                    </div>
                                </td>

                                <td class="text-center border-r border-b border-gray-200 dark:border-gray-700 text-xs text-gray-500">
                                    {{ $client->frequency_weeks }}
                                </td>

                                @foreach($matrixHeaders as $h)
                                    @php
                                        $date = $h['date'];
                                        $visit = $client->visits->where('date', $date)->first();
                                        $isDue = $this->isVisitDue($client, $date); 
                                        $isCurrentWeek = \Carbon\Carbon::parse($date)->isSameWeek(now());
                                        
                                        $bgClass = $isCurrentWeek ? 'bg-primary-50/30 dark:bg-primary-900/10' : '';
                                    @endphp

                                    <td 
                                        wire:click="toggleVisit({{ $client->id }}, '{{ $date }}')"
                                        class="p-0 border-r border-b border-gray-200 dark:border-gray-700 h-14 cursor-pointer transition-colors {{ $bgClass }} hover:bg-gray-200 dark:hover:bg-white/10"
                                    >
                                        <div class="w-full h-full flex items-center justify-center">
                                            @if($visit)
                                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            @elseif($isDue)
                                                <div class="w-4 h-4 rounded-full border-2 
                                                    {{ $isCurrentWeek ? 'border-primary-500 bg-primary-500 shadow-sm animate-pulse' : 'border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800' }}">
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>