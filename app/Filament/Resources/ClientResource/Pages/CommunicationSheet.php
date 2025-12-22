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

class CommunicationSheet extends Page
{
    protected static string $resource = ClientResource::class;
    protected static string $view = 'filament.resources.client-resource.pages.communication-sheet';
    protected static ?string $title = 'Communicatieblad';

    public $houseId;
    public $houseName;
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

        $house = House::query()
            ->where('id', $this->houseId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $house) {
            Notification::make()->title('Huis niet gevonden.')->danger()->send();
            $this->redirect(ClientResource::getUrl('planning'));
            return;
        }

        $this->houseName = $house->name;
        $this->loadSheetData();
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

    // Aparte functie gemaakt zodat we de lijst kunnen verversen na verwijderen
    protected function fetchArchiveWeeks()
    {
        $this->archiveWeeks = CommunicationEntry::query()
            ->where('house_id', $this->houseId)
            ->select('year', 'week_number')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('week_number', 'desc')
            ->get()
            ->map(function ($entry) {
                $date = Carbon::now()->setISODate($entry->year, $entry->week_number)->startOfWeek();
                return [
                    'year' => $entry->year,          // Nodig voor verwijderen
                    'week' => $entry->week_number,   // Nodig voor verwijderen
                    'label' => $date->format('d/m') . ' - ' . $date->copy()->endOfWeek()->format('d/m'), // DATUM NU EERST
                    'subLabel' => "Week {$entry->week_number} · {$entry->year}", // WEEK NU KLEIN
                    'dateStr' => $date->format('Y-m-d'),
                ];
            })
            ->toArray();
    }

    // NIEUW: Functie om een week te verwijderen
    public function deleteArchiveWeek($year, $weekNumber)
    {
        CommunicationEntry::query()
            ->where('house_id', $this->houseId)
            ->where('year', $year)
            ->where('week_number', $weekNumber)
            ->delete();

        // Herlaad de lijst en geef melding
        $this->fetchArchiveWeeks();
        
        // Als we toevallig naar de verwijderde week keken, herlaad dan de huidige view ook (maakt hem leeg)
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
            ->where('year', $this->year)
            ->where('week_number', $this->weekNumber)
            ->get()
            ->keyBy('client_id');

        $rows = [];
        foreach ($clients as $client) {
            $visitForWeek = $client->visits->first();
            $isDue = $this->isVisitDue($client, $weekStart->toDateString());
            $savedEntry = $savedEntries->get($client->id);

            if (! $visitForWeek && ! $isDue && ! $savedEntry) continue;

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

            CommunicationEntry::updateOrCreate(
                [
                    'house_id' => $this->houseId,
                    'client_id' => $row['client_id'],
                    'year' => $this->year,
                    'week_number' => $this->weekNumber,
                ],
                [
                    'date' => $row['date'] ?: null,
                    'note' => $row['note'],
                ]
            );
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