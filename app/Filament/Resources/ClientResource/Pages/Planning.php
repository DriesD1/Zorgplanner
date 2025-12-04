<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\House;
use App\Models\Visit;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;

class Planning extends Page
{
    protected static string $resource = ClientResource::class;

    protected static string $view = 'filament.resources.client-resource.pages.planning';

    protected static ?string $title = 'Planning Matrix';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public $selectedHouseId;
    public $viewMode = 'week'; 
    public $headers = []; 
    public $clients = [];

    public function mount()
    {
        $firstHouse = House::where('user_id', auth()->id())->first();
        if ($firstHouse) {
            $this->selectedHouseId = $firstHouse->id;
            // Dit zorgt dat bij het laden direct de juiste modus wordt gekozen
            $this->updatedSelectedHouseId(); 
        } else {
            $this->loadData();
        }
    }

    //De Back Button logic
    public function back()
    {
        return redirect()->to(ClientResource::getUrl('index'));
    }

    //Automatische modus wissel op basis van huis instelling
    public function updatedSelectedHouseId() 
    { 
        if ($this->selectedHouseId) {
            $house = House::find($this->selectedHouseId);
            
            // Als has_custom_schedule TRUE is -> Dag modus (Agenda)
            // Als FALSE -> Week modus (Matrix)
            if ($house && $house->has_custom_schedule) {
                $this->viewMode = 'day';
            } else {
                $this->viewMode = 'week';
            }
        }
        
        $this->loadData(); 
    }

    // De viewMode mag niet handmatig gewisseld worden als we strikt per huis werken,
    // maar voor de zekerheid laten we deze staan:
    public function updatedViewMode() { $this->loadData(); }

    public function loadData()
    {
        $this->headers = [];
        $startDate = Carbon::now();

        if ($this->viewMode === 'week') {
            $startDate = $startDate->startOfWeek();
            for ($i = 0; $i < 12; $i++) {
                $date = $startDate->copy()->addWeeks($i);
                $this->headers[] = [
                    'date_obj' => $date, // Bewaren voor berekening
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('d/m'),
                    'sub' => 'Wk ' . $date->weekOfYear,
                ];
            }
        } else {
            for ($i = 0; $i < 14; $i++) {
                $date = $startDate->copy()->addDays($i);
                $this->headers[] = [
                    'date_obj' => $date,
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('d/m'),
                    'sub' => substr($date->locale('nl')->dayName, 0, 2),
                ];
            }
        }

        if ($this->selectedHouseId) {
            $this->clients = Client::where('house_id', $this->selectedHouseId)
                ->orderBy('room_number')
                ->with(['visits']) // We laden nog steeds de handmatige visits
                ->get();
        } else {
            $this->clients = [];
        }
    }

    public function toggleVisit($clientId, $dateString)
    {
        $existing = Visit::where('client_id', $clientId)
            ->where('date', $dateString)
            ->first();

        if ($existing) {
            $existing->delete(); 
        } else {
            Visit::create([
                'client_id' => $clientId,
                'date' => $dateString,
                'is_planned' => true,
            ]); 
        }
        
        $this->loadData(); 
    }

    // FOUT 1: De logica voor de automatische kruisjes
    public function isVisitDue($client, $headerDateString)
    {
        // Alleen berekenen in weekmodus en als er data is
        if ($this->viewMode !== 'week' || !$client->next_planned_date || !$client->frequency_weeks) {
            return false;
        }

        $plannedDate = Carbon::parse($client->next_planned_date)->startOfWeek();
        $columnDate = Carbon::parse($headerDateString)->startOfWeek();

        // Verschil in weken
        $diffInWeeks = $plannedDate->diffInWeeks($columnDate, false);

        // Modulo: Als het verschil precies deelbaar is door de frequentie
        return ($diffInWeeks % $client->frequency_weeks) === 0;
    }
}