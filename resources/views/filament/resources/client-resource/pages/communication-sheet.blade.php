<x-filament-panels::page>
    <div class="flex flex-col gap-4">
        
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold text-primary-600 uppercase tracking-widest">Communicatieblad</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white underline underline-offset-4">{{ $houseName }}</div>
                    
                    <div class="flex items-center gap-3 mt-2">
                        <button wire:click="previousWeek" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-800 rounded-full transition text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div class="flex flex-col items-center select-none leading-none">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $weekLabel }}
                            </span>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-0.5">
                                Week {{ $weekNumber }}
                            </span>
                        </div>
            
                        <button wire:click="nextWeek" class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-800 rounded-full transition text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button wire:click="openArchive" class="ml-2 flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            Archief
                        </button>
                    </div>
                </div>

                <div class="flex gap-2 self-start sm:self-center">
                    <a href="{{ \App\Filament\Resources\ClientResource::getUrl('planning') }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Terug
                    </a>
                    
                    <button type="button" 
                            wire:click="openPreview"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 transition">
                        <svg wire:loading wire:target="openPreview" class="animate-spin h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg wire:loading.remove wire:target="openPreview" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <span>Voorbeeld</span>
                    </button>

                    <button type="button" 
                            wire:click="exportPdf"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg bg-primary-500 text-white hover:bg-primary-600 transition shadow-sm">
                        <svg wire:loading wire:target="exportPdf" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <svg wire:loading.remove wire:target="exportPdf" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        <span>Download PDF</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                 <div class="sm:col-span-3">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Instellingen voor PDF</div>
                </div>
                <div>
                    <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Datum</label>
                    <input type="date" wire:model.blur="headerDate" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Moment</label>
                    <input type="text" wire:model.blur="headerMoment" placeholder="bv. namiddag" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <div>
                    <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">T.a.v.</label>
                    <input type="text" wire:model.blur="headerRecipient" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
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
    </div>

    <x-filament::modal id="pdf-preview-modal" width="screen" alignment="center">
    <x-slot name="heading">
        Voorbeeld Communicatieblad
    </x-slot>

    {{-- Modal body --}}
    <div class="h-full flex flex-col">

        {{-- Preview container --}}
        <div
            class="-m-6 w-[calc(100%+3rem)] flex-1 flex bg-gray-100"
            x-data="{ pdfBlobUrl: null }"
            x-effect="
                if ($wire.previewPdfData) {
                    fetch($wire.previewPdfData)
                        .then(res => res.blob())
                        .then(blob => {
                            pdfBlobUrl = URL.createObjectURL(blob) + '#view=FitH';
                        });
                } else {
                    pdfBlobUrl = null;
                }
            "
        >

            {{-- PDF iframe --}}
            <template x-if="pdfBlobUrl">
                <iframe
                    :src="pdfBlobUrl"
                    class="w-full h-full border-0 bg-white"
                ></iframe>
            </template>

            {{-- Loading state --}}
            <template x-if="!pdfBlobUrl">
                <div class="flex flex-col items-center justify-center w-full h-full text-gray-500">
                    <svg
                        class="animate-spin h-10 w-10 text-primary-500 mb-4"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>

                    <span class="text-sm font-medium">
                        PDF wordt gegenereerd…
                    </span>
                </div>
            </template>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex gap-4 justify-end w-full">
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
        <x-slot name="heading">
            Archief / Geschiedenis
        </x-slot>

        <div class="flex flex-col gap-2 max-h-[60vh] overflow-y-auto pr-1">
            @forelse($archiveWeeks as $week)
                <div class="flex items-center gap-2 w-full group">
                    
                    <button wire:click="loadWeekFromArchive('{{ $week['dateStr'] }}')"
                            class="flex flex-col items-start flex-1 p-3 rounded-lg border border-gray-200 dark:border-gray-700 transition text-left
                                   hover:bg-gray-50 dark:hover:bg-white/5">
                        <span class="font-bold text-gray-900 dark:text-white">
                            {{ $week['label'] }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $week['subLabel'] }}
                        </span>
                    </button>

                    <button type="button" 
                            wire:click="deleteArchiveWeek({{ $week['year'] }}, {{ $week['week'] }})"
                            wire:confirm="Ben je zeker dat je de notities van deze week wilt verwijderen? Dit kan niet ongedaan worden gemaakt."
                            class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-200 dark:hover:border-red-800 transition"
                            title="Verwijder week uit archief">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>

                </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    Nog geen opgeslagen bladeren gevonden.
                </div>
            @endforelse
        </div>

        <x-slot name="footer">
            <x-filament::button color="gray" class="w-full" x-on:click="$dispatch('close-modal', { id: 'archive-modal' })">
                Sluiten
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

</x-filament-panels::page>