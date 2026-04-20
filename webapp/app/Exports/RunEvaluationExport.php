<?php

namespace App\Exports;

use App\Models\RunParticipation;
use App\Models\Sponsor;
use App\Models\SponsoredRun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class RunEvaluationExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly SponsoredRun $sponrun) {}

    public function headings(): array
    {
        $h = ['Läufernr', 'Projekt'];

        if ($this->sponrun->with_tshirt) {
            $h[] = 'T-Shirt-Größe';
        }

        array_push($h,
            'L.Optigem PersNr.', 'L.Name', 'L.Straße Nr.', 'L.PLZ', 'L.Stadt',
            'L.E-Mail', 'L.Telefon'
        );

        if (config('app.newsletter_optional')) {
            $h[] = 'L.Newsletter';
        }

        array_push($h,
            'Sponsorennr.', 'S.Optigem PersNr.', 'S.Name', 'S.Straße Nr.', 'S.PLZ',
            'S.Stadt', 'S.E-Mail', 'S.Telefon'
        );

        if (config('app.newsletter_optional')) {
            $h[] = 'S.Newsletter';
        }

        array_push($h,
            'Name des Läufers', 'Spende pro Runde', 'Maximal- oder Festbetrag',
            'gelaufene Runden', 'Endbetrag', 'Erhalten am', 'Betrag'
        );

        return $h;
    }

    public function collection(): Collection
    {
        $this->sponrun->load('runParticipations.user', 'runParticipations.sponsors');

        $rows = collect();

        foreach ($this->sponrun->runParticipations as $runpart) {
            foreach ($runpart->sponsors as $sponsor) {
                $rows->push($this->buildRow($runpart, $sponsor));
            }
        }

        return $rows;
    }

    private function buildRow(RunParticipation $runpart, Sponsor $sponsor): array
    {
        $user = $runpart->user;
        $row  = [
            $user->id,
            (string) $runpart->project_id,
        ];

        if ($this->sponrun->with_tshirt) {
            $row[] = (string) $runpart->tshirt_size;
        }

        $row[] = (int) $user->ext_personnel_no;
        $row[] = $user->lastname . ', ' . $user->firstname;
        $row[] = $user->street . ' ' . $user->housenumber;
        $row[] = $user->postcode;
        $row[] = $user->city;
        $row[] = $user->email;
        $row[] = $user->phone ?? '';

        if (config('app.newsletter_optional')) {
            $row[] = $user->wants_newsletter ? 'Ja' : 'Nein';
        }

        $row[] = $sponsor->id;
        $row[] = (int) $sponsor->ext_personnel_no;
        $row[] = $sponsor->lastname . ', ' . $sponsor->firstname;
        $row[] = $sponsor->street . ' ' . $sponsor->housenumber;
        $row[] = $sponsor->postcode;
        $row[] = $sponsor->city;
        $row[] = $sponsor->email ?? '';
        $row[] = $sponsor->phone ?? '';

        if (config('app.newsletter_optional')) {
            $row[] = $sponsor->wants_newsletter ? 'Ja' : 'Nein';
        }

        $row[] = $user->lastname . ', ' . $user->firstname;
        $row[] = number_format((float) $sponsor->donation_per_lap, 2, ',', '');
        $row[] = number_format((float) $sponsor->donation_static_max, 2, ',', '');
        $row[] = $runpart->laps;
        $row[] = number_format($sponsor->calculateDonationSum($runpart->laps), 2, ',', '');
        $row[] = ''; // Erhalten am
        $row[] = ''; // Betrag

        return $row;
    }
}
