<x-filament-panels::page>
    <div class="flex flex-col gap-6">

        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $houseName }}
                </h1>
            </div>

            <div class="flex gap-3">
                <x-filament::button
                    color="gray"
                    tag="a"
                    href="{{ \App\Filament\Resources\ClientResource::getUrl('planning') }}"
                >
                    Terug
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    wire:click="openPreview"
                >
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </x-slot>
                    Voorbeeld
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    wire:click="exportPdf"
                >
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    </x-slot>
                    Download PDF
                </x-filament::button>
            </div>
        </div>

        <div class="w-full lg:w-1/3">
            <div class="w-full flex items-center justify-between bg-gray-50 dark:bg-gray-800 rounded-lg p-1 border border-gray-200 dark:border-gray-700 shadow-sm">
                
                <button wire:click="previousWeek" class="p-2 hover:bg-white dark:hover:bg-gray-700 rounded-md transition text-gray-500 hover:text-primary-600 hover:shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                
                <div class="flex-1 px-2 text-center overflow-hidden">
                    <div class="text-sm font-bold text-gray-900 dark:text-white leading-none truncate">
                        {{ $weekLabel }}
                    </div>
                </div>

                <button wire:click="nextWeek" class="p-2 hover:bg-white dark:hover:bg-gray-700 rounded-md transition text-gray-500 hover:text-primary-600 hover:shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col lg:flex-row items-end justify-between gap-4">
            
            <div class="w-full lg:w-1/3">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Kies Huis / Afdeling</label>
                <div class="mt-1">
                    <select wire:model.live="houseId" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 shadow-sm">
                        @foreach($houseOptions as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="w-full lg:w-1/3 flex justify-end">
                <button wire:click="openArchive" class="w-full lg:w-auto flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <span>Open Archief</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
             <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Datum op blad</label>
                <input type="date" wire:model.blur="headerDate" class="w-full px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-transparent dark:text-white focus:ring-primary-500 focus:border-primary-500" />
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Moment</label>
                <input type="text" wire:model.blur="headerMoment" placeholder="bv. namiddag" class="w-full px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-transparent dark:text-white focus:ring-primary-500 focus:border-primary-500" />
            </div>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 shadow-sm">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">T.a.v.</label>
                <input type="text" wire:model.blur="headerRecipient" class="w-full px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-transparent dark:text-white focus:ring-primary-500 focus:border-primary-500" />
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden p-6 space-y-4">
            @forelse($rows as $index => $row)
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-3 lg:gap-4">
                        <div class="flex items-center gap-2 min-w-[140px] lg:w-[160px]">
                            <span class="text-xs uppercase text-gray-500 dark:text-gray-400">Datum</span>
                            <input type="date" wire:model.blur="rows.{{ $index }}.date" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        </div>
                        
                        <div class="flex items-center gap-2 w-full lg:w-[140px]">
                            <span class="text-xs uppercase text-gray-500 dark:text-gray-400">Kamer</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $row['room_number'] ?? '-' }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2 flex-1 min-w-[220px]">
                            <span class="text-xs uppercase text-gray-500 dark:text-gray-400">Naam</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Notitie (iPad: Scribble zet handschrift om naar tekst)</div>
                        <textarea wire:model.blur="rows.{{ $index }}.note" rows="3" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Typ of schrijf hier de overdracht"></textarea>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">Geen notities of klanten voor deze week.</div>
            @endforelse
        </div>
    </div>

    <x-filament::modal id="pdf-preview-modal" width="screen" alignment="center">
        <x-slot name="heading">
            Voorbeeld Communicatieblad
        </x-slot>

        <div
            class="-m-6 w-[calc(100%+3rem)] flex flex-col h-full bg-gray-100 dark:bg-gray-900"
            x-data="{ pdfBlobUrl: null }"
            x-effect="
                if ($wire.previewPdfData) {
                    pdfBlobUrl = $wire.previewPdfData + '#view=FitH';
                } else {
                    pdfBlobUrl = null;
                }
            "
        >
            <div class="flex-1 relative">
                <template x-if="pdfBlobUrl">
                    <iframe
                        :src="pdfBlobUrl"
                        class="absolute inset-0 w-full h-full border-0 bg-white"
                    ></iframe>
                </template>

                <template x-if="!pdfBlobUrl">
                    <div class="flex flex-col items-center justify-center h-full text-gray-500">
                        <svg class="animate-spin h-8 w-8 text-primary-500 mb-3"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0
                                    C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>PDF genereren…</span>
                    </div>
                </template>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-between w-full">
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'pdf-preview-modal' })"
                >
                    Sluiten
                </x-filament::button>

                <x-filament::button
                    wire:click="exportPdf"
                    icon="heroicon-o-arrow-down-tray"
                >
                    Download Definitief
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>


    <x-filament::modal id="archive-modal" width="md">
        <x-slot name="heading">Archief / Geschiedenis</x-slot>
        <div class="flex flex-col gap-2 max-h-[60vh] overflow-y-auto pr-1">
            @forelse($archiveWeeks as $week)
                <div class="flex items-center gap-2 w-full group">
                    <button wire:click="loadWeekFromArchive('{{ $week['dateStr'] }}')"
                            class="flex flex-col items-start flex-1 p-3 rounded-lg border border-gray-200 dark:border-gray-700 transition text-left hover:bg-gray-50 dark:hover:bg-white/5">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $week['label'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $week['subLabel'] }}</span>
                    </button>
                    <button type="button" 
                            wire:click="deleteArchiveWeek({{ $week['year'] }}, {{ $week['week'] }})"
                            wire:confirm="Zeker verwijderen?"
                            class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-white/5 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            @empty
                <div class="text-center text-gray-500 py-4">Geen historie gevonden.</div>
            @endforelse
        </div>
        <x-slot name="footer">
            <x-filament::button color="gray" class="w-full" x-on:click="$dispatch('close-modal', { id: 'archive-modal' })">Sluiten</x-filament::button>
        </x-slot>
    </x-filament::modal>

</x-filament-panels::page>