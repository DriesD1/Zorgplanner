<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\House;
use App\Models\CommunicationEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class CommunicationSheet extends Page
{
    protected static string $resource = ClientResource::class;
    protected static string $view = 'filament.resources.client-resource.pages.communication-sheet';
    protected static ?string $title = 'Communicatieblad';

    public $houseId;
    public $houseName;
    
    public $houseOptions = []; 
    
    public $currentDate; 
    public $weekLabel;
    public $weekNumber;
    public $year;
    
    public $headerDate; 
    public $headerMoment;
    public $headerRecipient = 'Hoofdverpleging'; 

    public $rows = [];
    public $previewPdfData = null;
    public $archiveWeeks = [];

    public function mount(): void
    {
        $this->houseId = request()->get('house');
        $dateParam = request()->get('date');
        
        $this->currentDate = $dateParam ? Carbon::parse($dateParam)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $this->headerDate = Carbon::now()->format('Y-m-d');
        $this->headerMoment = '';

        // Alleen huizen ophalen van de gebruiker WAAR planning_type op 'week' staat
        $this->houseOptions = House::where('organization_id', auth()->user()?->organization_id)
            ->where('planning_type', 'week')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        // Als er geen houseId is (of de geselecteerde is niet geldig voor deze lijst), pak de eerste
        if ((!$this->houseId || !array_key_exists($this->houseId, $this->houseOptions)) && count($this->houseOptions) > 0) {
            $this->houseId = array_key_first($this->houseOptions);
        }

        // Controleer of we nu een geldig huis hebben
        $house = House::query()
            ->where('id', $this->houseId)
            ->where('organization_id', auth()->user()?->organization_id)
            ->first();

        if (! $house) {
            // Als er helemaal geen 'week' huizen zijn, of ID klopt niet -> terugsturen
            Notification::make()->title('Geen geldig huis gevonden.')->danger()->send();
            $this->redirect(ClientResource::getUrl('planning'));
            return;
        }

        $this->houseName = $house->name;
        $this->loadSheetData();
    }

    // Zorgt dat de pagina ververst als je een ander huis kiest in de dropdown
    public function updatedHouseId()
    {
        $house = House::find($this->houseId);
        if ($house) {
            $this->houseName = $house->name;
            $this->loadSheetData();
        }
    }

    public function previousWeek()
    {
        $this->currentDate = Carbon::parse($this->currentDate)->subWeek()->format('Y-m-d');
        $this->loadSheetData();
    }

    public function nextWeek()
    {
        $this->currentDate = Carbon::parse($this->currentDate)->addWeek()->format('Y-m-d');
        $this->loadSheetData();
    }

    public function openArchive()
    {
        $this->fetchArchiveWeeks();
        $this->dispatch('open-modal', id: 'archive-modal');
    }

    protected function fetchArchiveWeeks()
    {
        $this->archiveWeeks = CommunicationEntry::query()
            ->where('house_id', $this->houseId)
            ->where('organization_id', auth()->user()?->organization_id)
            ->select('year', 'week_number')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('week_number', 'desc')
            ->get()
            ->map(function ($entry) {
                $date = Carbon::now()->setISODate($entry->year, $entry->week_number)->startOfWeek();
                return [
                    'year' => $entry->year,          
                    'week' => $entry->week_number,   
                    'label' => $date->format('d/m') . ' - ' . $date->copy()->endOfWeek()->format('d/m'), 
                    'subLabel' => "Week {$entry->week_number} · {$entry->year}", 
                    'dateStr' => $date->format('Y-m-d'),
                ];
            })
            ->toArray();
    }

    public function deleteArchiveWeek($year, $weekNumber)
    {
        CommunicationEntry::query()
            ->where('house_id', $this->houseId)
            ->where('organization_id', auth()->user()?->organization_id)
            ->where('year', $year)
            ->where('week_number', $weekNumber)
            ->delete();

        $this->fetchArchiveWeeks();
        
        if ($this->year == $year && $this->weekNumber == $weekNumber) {
            $this->loadSheetData();
        }

        Notification::make()->title('Week verwijderd uit archief')->success()->send();
    }

    public function loadWeekFromArchive($dateStr)
    {
        $this->currentDate = $dateStr;
        $this->loadSheetData();
        $this->dispatch('close-modal', id: 'archive-modal');
    }

    protected function loadSheetData(): void
    {
        $anchorDate = Carbon::parse($this->currentDate);
        $weekStart = $anchorDate->copy()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $this->weekNumber = $weekStart->weekOfYear;
        $this->year = $weekStart->year;
        $this->weekLabel = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m');

        $clients = Client::query()
            ->where('house_id', $this->houseId)
            ->orderByRaw('LENGTH(room_number), room_number')
            ->with(['visits' => fn($query) => $query
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->orderBy('date')
            ])
            ->get();

        $savedEntries = CommunicationEntry::query()
            ->where('house_id', $this->houseId)
            ->where('organization_id', auth()->user()?->organization_id)
            ->where('year', $this->year)
            ->where('week_number', $this->weekNumber)
            ->get()
            ->keyBy('client_id');

        $rows = [];
        foreach ($clients as $client) {
            $visitForWeek = $client->visits->first();
            $isDue = $this->isVisitDue($client, $weekStart->toDateString());
            $savedEntry = $savedEntries->get($client->id);

            // Filter: alleen tonen als er inhoud is (tekst), of als er een bezoek/planning is
            $hasContent = $savedEntry && filled($savedEntry->note);

            if (! $visitForWeek && ! $isDue && ! $hasContent) {
                continue;
            }

            $initialDate = null;
            if ($savedEntry && $savedEntry->date) {
                $initialDate = $savedEntry->date; 
            } elseif ($visitForWeek) {
                $initialDate = $visitForWeek->date->format('Y-m-d');
            }

            $initialNote = $savedEntry ? $savedEntry->note : '';

            $rows[] = [
                'client_id' => $client->id,
                'name' => $client->name,
                'room_number' => $client->room_number,
                'date' => $initialDate, 
                'note' => $initialNote,
            ];
        }
        $this->rows = $rows;
    }

    public function updated($name, $value)
    {
        if (str_starts_with($name, 'rows.')) {
            $parts = explode('.', $name);
            $index = $parts[1];
            $row = $this->rows[$index];
            
            if (!isset($row['client_id'])) return;

            // Opschonen als leeg
            if (empty(trim($row['note'])) && empty($row['date'])) {
                CommunicationEntry::where([
                    'house_id' => $this->houseId,
                    'client_id' => $row['client_id'],
                    'year' => $this->year,
                    'week_number' => $this->weekNumber,
                    'organization_id' => auth()->user()?->organization_id,
                ])->delete();
            } else {
                CommunicationEntry::updateOrCreate(
                    [
                        'house_id' => $this->houseId,
                        'client_id' => $row['client_id'],
                        'year' => $this->year,
                        'week_number' => $this->weekNumber,
                        'organization_id' => auth()->user()?->organization_id,
                    ],
                    [
                        'date' => $row['date'] ?: null,
                        'note' => $row['note'],
                        'organization_id' => auth()->user()?->organization_id,
                    ]
                );
            }
        }
    }

    protected function isVisitDue($client, $dateString): bool
    {
        if (! $client->next_planned_date || ! $client->frequency_weeks) return false;
        $planned = Carbon::parse($client->next_planned_date)->startOfWeek();
        $current = Carbon::parse($dateString)->startOfWeek();
        if($current->lt($planned)) return false;
        return ($planned->diffInWeeks($current) % $client->frequency_weeks) === 0;
    }

    // Actie om persoon toe te voegen
    public function addClientAction(): Action
    {
        return Action::make('addClient')
            ->label('Persoon toevoegen')
            ->button()
            ->color('gray')
            ->size('sm')
            ->icon('heroicon-m-plus')
            ->form([
                Select::make('client_id')
                    ->label('Selecteer bewoner')
                    ->options(function () {
                        return Client::where('house_id', $this->houseId)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->action(function (array $data) {
                $client = Client::find($data['client_id']);

                foreach ($this->rows as $row) {
                    if ($row['client_id'] == $client->id) {
                        Notification::make()->title('Deze persoon staat al op de lijst.')->warning()->send();
                        return;
                    }
                }

                $this->rows[] = [
                    'client_id' => $client->id,
                    'name' => $client->name,
                    'room_number' => $client->room_number,
                    'date' => Carbon::now()->format('Y-m-d'), 
                    'note' => '',
                ];
                
                Notification::make()->title('Bewoner toegevoegd')->success()->send();
            });
    }

    // Actie om rij te verwijderen
    public function removeRow($index)
    {
        $row = $this->rows[$index];

        // Verwijder uit database indien aanwezig
        if (isset($row['client_id'])) {
            CommunicationEntry::where([
                'house_id' => $this->houseId,
                'client_id' => $row['client_id'],
                'year' => $this->year,
                'week_number' => $this->weekNumber,
            ])->delete();
        }

        // Verwijder uit de view
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        Notification::make()->title('Persoon verwijderd')->success()->send();
    }

    public function openPreview()
    {
        $this->generatePdfData(true);
    }

    public function exportPdf()
    {
        return $this->generatePdfData(false);
    }

    protected function generatePdfData($isPreview)
    {
        $data = [
            'houseName' => $this->houseName,
            'weekLabel' => $this->weekLabel,
            'weekNumber' => $this->weekNumber,
            'headerRecipient' => $this->headerRecipient,
            'headerDate' => $this->headerDate,
            'headerMoment' => $this->headerMoment,
            'rows' => $this->rows,
        ];

        $pdf = Pdf::loadView('pdf.communication-sheet', $data)
                  ->setPaper('a4', 'portrait');

        if ($isPreview) {
            $pdfContent = $pdf->output();
            $this->previewPdfData = 'data:application/pdf;base64,' . base64_encode($pdfContent);
            $this->dispatch('open-modal', id: 'pdf-preview-modal');
        } else {
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'communicatieblad-week-' . $this->weekNumber . '.pdf');
        }
    }
}