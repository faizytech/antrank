<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Keyphrase;
use Maatwebsite\Excel\Concerns\FromCollection;

class KeyPhraseSheetController extends Controller implements FromCollection, WithTitle, WithHeadings, WithMapping
{
    private $project_name;
    private $keyphrases;

    public function __construct($project_name,$keyphrases)
    {
        $this->project_name = $project_name;
        $this->keyphrases = $keyphrases;
    }

    public function collection()
    {
        return collect($this->keyphrases);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->project_name;
    }


    public function headings(): array
    {
        return [
            'Date',
            'keyphrase',
            'rank',
        ];
    }

    /**
    * @var Invoice $invoice
    */
    public function map($keyphrase): array
    {
        return [
            $keyphrase['date'],
            $keyphrase['keyphrase'],
            $keyphrase['rank']
        ];
    }
}
