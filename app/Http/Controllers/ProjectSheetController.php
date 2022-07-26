<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Project;
use App\Http\Controllers\KeyphraseSheetController;
use Maatwebsite\Excel\Concerns\FromCollection;


class ProjectSheetController extends Controller implements  WithMultipleSheets
{
    //
    use Exportable;
    
    protected $projects;
    
    public function __construct($projects)
    {
        $this->projects = $projects;
    }
    
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        foreach($this->projects as $project)
        {
            $sheets[] = new KeyphraseSheetController($project['project_name'], $project['keyphrases']);
        }
        return $sheets;
    }
}
