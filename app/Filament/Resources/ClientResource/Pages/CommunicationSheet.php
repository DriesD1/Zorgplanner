<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\House;
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
    public $weekLabel;
    public $weekNumber;
    
    public $headerDate; 
    public $headerMoment;
    public $headerRecipient = 'Hoofdverpleging'; 

    public $rows = [];
    public $previewPdfData = null; 

    public function mount(): void
    {
        $this->houseId = request()->get('house');
        $dateParam = request()->get('date');
        $anchorDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();
        
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
        $this->buildSheet($house, $anchorDate);
    }

    protected function buildSheet(House $house, Carbon $anchorDate): void
    {
        $weekStart = $anchorDate->copy()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $this->weekNumber = $weekStart->weekOfYear;
        $this->weekLabel = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m');

        $clients = Client::query()
            ->where('house_id', $house->id)
            ->orderByRaw('LENGTH(room_number), room_number')
            ->with(['visits' => fn($query) => $query
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->orderBy('date')
            ])
            ->get();

        $rows = [];
        foreach ($clients as $client) {
            $visitForWeek = $client->visits->first();
            $isDue = $this->isVisitDue($client, $weekStart->toDateString());
            $hasVisit = (bool) $visitForWeek;

            if (! $hasVisit && ! $isDue) continue;

            $rows[] = [
                'name' => $client->name,
                'room_number' => $client->room_number,
                'date' => $visitForWeek ? $visitForWeek->date->format('Y-m-d') : null, 
                'note' => '',
            ];
        }
        $this->rows = $rows;
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
        $data = [
            'houseName' => $this->houseName,
            'weekLabel' => $this->weekLabel,
            'weekNumber' => $this->weekNumber,
            'headerRecipient' => $this->headerRecipient,
            'headerDate' => $this->headerDate,
            'headerMoment' => $this->headerMoment,
            'rows' => $this->rows,
        ];

        $pdfContent = Pdf::loadView('pdf.communication-sheet', $data)
                  ->setPaper('a4', 'portrait')
                  ->output();

        // AANPASSING: Gewoon 'kale' base64, geen #view parameters hier!
        $this->previewPdfData = 'data:application/pdf;base64,' . base64_encode($pdfContent);
        
        $this->dispatch('open-modal', id: 'pdf-preview-modal');
    }

    public function exportPdf()
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

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'communicatieblad-week-' . $this->weekNumber . '.pdf');
    }
}