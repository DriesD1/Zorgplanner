<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use App\Models\House;
use App\Models\Visit;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class Planning extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static string $resource = ClientResource::class;
    protected static string $view = 'filament.resources.client-resource.pages.planning';
    protected static ?string $title = 'Agenda & Planning';
    
    public $selectedHouseId;
    public $viewMode = 'week'; 
    
    // Tijdelijke opslag voor datum/tijd bij klikken
    public $tempDate;
    public $tempTime;

    // Data containers
    public $matrixHeaders = []; 
    public $matrixRows = []; 
    public $agendaStartDate; 
    public $daysHeader = []; 
    public $grid = []; 
    
    public $times = [
        '00:00', '00:30', '01:00', '01:30', '02:00', '02:30', '03:00', '03:30', '04:00', '04:30', '05:00', '05:30',
        '06:00', '06:30', '07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', 
        '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', 
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
        '20:00', '20:30', '21:00', '21:30', '22:00', '22:30', '23:00', '23:30',  // maak tot 23:30
    ];

    public function mount()
    {
        $this->agendaStartDate = Carbon::now()->startOfWeek()->format('Y-m-d');

        $firstHouse = House::where('user_id', auth()->id())->first();
        if ($firstHouse) {
            $this->selectedHouseId = $firstHouse->id;
            $this->updatedSelectedHouseId(); 
        }
    }

    public function updatedSelectedHouseId() 
    { 
        if ($this->selectedHouseId) {
            $house = House::find($this->selectedHouseId);
            $this->viewMode = ($house && $house->planning_type === 'day') ? 'agenda' : 'week';
        }
        $this->loadData(); 
    }

    public function back()
    {
        return redirect()->to(ClientResource::getUrl('index'));
    }

    public function previousPeriod() {
        if($this->viewMode === 'agenda') {
            $this->agendaStartDate = Carbon::parse($this->agendaStartDate)->subWeek()->format('Y-m-d');
        } 
        $this->loadData();
    }
    
    public function nextPeriod() {
        if($this->viewMode === 'agenda') {
            $this->agendaStartDate = Carbon::parse($this->agendaStartDate)->addWeek()->format('Y-m-d');
        }
        $this->loadData();
    }

    public function loadData()
    {
        $house = House::find($this->selectedHouseId);
        if(!$house) return;

        if ($this->viewMode === 'week') {
            // Matrix Logic
            $this->matrixHeaders = [];
            $start = Carbon::now()->startOfWeek();
            for ($i = 0; $i < 12; $i++) {
                $d = $start->copy()->addWeeks($i);
                $this->matrixHeaders[] = ['date' => $d->format('Y-m-d'), 'label' => $d->format('d/m'), 'week' => $d->weekOfYear];
            }
            $this->matrixRows = Client::where('house_id', $house->id)->orderBy('room_number')->with('visits')->get();
        } else {
            // Agenda Logic
            $this->daysHeader = [];
            $this->grid = []; 

            $startOfWeek = Carbon::parse($this->agendaStartDate)->startOfWeek();
            
            for($i=0; $i<7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                $this->daysHeader[] = [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->locale('nl')->isoFormat('ddd D MMM'), 
                    'isToday' => $date->isToday(),
                ];
            }

            foreach($this->times as $time) {
                foreach($this->daysHeader as $day) {
                    $this->grid[$time][$day['date']] = null;
                }
            }

            $weekStart = $startOfWeek->format('Y-m-d');
            $weekEnd = $startOfWeek->copy()->addDays(6)->format('Y-m-d');

            $visits = Visit::query()
                ->whereHas('client', fn($q) => $q->where('house_id', $house->id))
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->with('client')
                ->get();

            foreach($visits as $visit) {
                if($visit->time && in_array($visit->time, $this->times)) {
                    $this->grid[$visit->time][$visit->date->format('Y-m-d')] = $visit;
                }
            }
        }
    }

    // --- VEILIGE TUSSENSTAP ---
    public function openPlanModal($date, $time)
    {
        // 1. BEVEILIGING: Check eerst of dit slot echt leeg is
        $exists = Visit::whereDate('date', $date)
            ->where('time', $time)
            ->whereHas('client', fn($q) => $q->where('house_id', $this->selectedHouseId))
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Dit tijdstip is al bezet!')
                ->body('Ververs de pagina om de huidige status te zien.')
                ->danger()
                ->send();
            return; // Stop direct, open geen modal
        }

        // 2. Als het leeg is, sla gegevens op en open modal
        $this->tempDate = $date;
        $this->tempTime = $time;
        $this->mountAction('planAppointment');
    }

    public function planAppointmentAction(): Action
    {
        return Action::make('planAppointment')
            ->label('Inplannen')
            ->modalHeading('Consult Inplannen')
            ->modalWidth('sm')
            ->form([
                Select::make('client_id')
                    ->label('Kies Bewoner')
                    ->options(fn() => Client::where('house_id', $this->selectedHouseId)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ])
            ->action(function (array $data) {
                $date = $this->tempDate;
                $time = $this->tempTime;
                
                if (!$date || !$time) {
                    Notification::make()->title('Fout: Geen datum/tijd')->danger()->send();
                    return;
                }

                // 3. EXTRA BEVEILIGING: Check nog een keer vlak voor opslaan (race conditions)
                $exists = Visit::whereDate('date', $date)
                    ->where('time', $time)
                    ->whereHas('client', fn($q) => $q->where('house_id', $this->selectedHouseId))
                    ->exists();

                if ($exists) {
                    Notification::make()->title('Oeps, iemand was je voor!')->danger()->send();
                    return;
                }

                // Opslaan (geen delete meer nodig, want we staan alleen toe op lege plekken)
                Visit::create([
                    'client_id' => $data['client_id'],
                    'date' => $date,
                    'time' => $time,
                    'is_planned' => true,
                ]);

                Notification::make()->title('Succesvol ingepland')->success()->send();
                
                $this->tempDate = null;
                $this->tempTime = null;
                $this->loadData();
            });
    }
    
    public function removeAppointment($date, $time)
    {
        Visit::whereDate('date', $date)->where('time', $time)
             ->whereHas('client', fn($q) => $q->where('house_id', $this->selectedHouseId))
             ->delete();     
        
        Notification::make()->title('Afspraak verwijderd')->success()->send();
        $this->loadData();
    }

    // Helpers (Matrix)
    public function isVisitDue($client, $dateString) { 
         if (!$client->next_planned_date || !$client->frequency_weeks) return false;
         $planned = Carbon::parse($client->next_planned_date)->startOfWeek();
         $current = Carbon::parse($dateString)->startOfWeek();
         return ($planned->diffInWeeks($current) % $client->frequency_weeks) === 0;
    }
    public function toggleVisit($clientId, $date) { 
        $visit = Visit::where('client_id', $clientId)->where('date', $date)->first();
        if($visit) $visit->delete();
        else Visit::create(['client_id' => $clientId, 'date' => $date, 'is_planned' => true]);
        $this->loadData();
    }
}
