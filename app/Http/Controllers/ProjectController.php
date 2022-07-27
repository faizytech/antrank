<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Mail\DailyReports;
use App\Models\Keyphrase;
use Illuminate\Support\Facades\Mail;


class ProjectController extends Controller
{
    //
    protected $data = [];
    protected $dateBegin = '';
    protected $dateEnd = '';

    public function index()
    {
        $this->dateBegin = date('Y-m-d');
        $this->dateEnd = date('Y-m-d');
        $response = Http::withBasicAuth(config('ant_rank.apikey'), '')->get('https://api.antranks.com/v1/projects?limit=1000');
        foreach($response->json()['data'] as $project)
        {
           $this->data[$project['id']] = [
               'project_name' => $project['name'],
               'project_domain' => $project['domain'],
               'keyphrases' => $this->getKeyPhrases($project['id'])
           ];
        }
        $url = $this->directExport();
        Mail::to(config('ant_rank.mail_to'))->cc(explode(',',config('ant_rank.mail_cc')))->send(new DailyReports($url));
        $this->saveInDB();
    }

    public function getKeyPhrases($projectId)
    {
        $keyph = '';
        $response = Http::withBasicAuth(config('ant_rank.apikey'), '')->get('https://api.antranks.com/v1/projects/'.$projectId.'/keyphrases?limit=1000');
        foreach($response->json()['data'] as $values){
            $keyph .= implode(',',array_keys($values['keyphrases'])).',';
            foreach($values["keyphrases"] as $key => $keyphrase)
            {
                $keyphrases[$key] = $keyphrase;
            }
        }
        $keyph = substr($keyph,0,-1);
        $ranks = [];
        $rankings = $this->getRankings($projectId,$keyph,$this->dateBegin,$this->dateEnd);
        foreach($rankings["data"] as $ranking){
            foreach($ranking as $dat){
                $ranks[] = [
                    'id' => $dat[0],
                    'keyphrase' => $keyphrases[$dat[0]],
                    'rank' => $dat[1],
                    'date' => $this->dateBegin,
                ];

            }
        }
        return $ranks;
    }

    public function getKeyPh(Request $request)
    {
        $projectId = $request->projectId;
        $keyph = '';
        $response = Http::withBasicAuth(config('ant_rank.apikey'), '')->get('https://api.antranks.com/v1/projects/'.$projectId.'/keyphrases?limit=1000');
        foreach($response->json()['data'] as $values){
            $keyph .= implode(',',array_keys($values['keyphrases'])).',';
            foreach($values["keyphrases"] as $key => $keyphrase)
            {
                $keyphrases[$key] = $keyphrase;
            }
        }
        $ranks = [];
        //dd($keyphrases);
        //dd(explode(',',substr($keyph,0,-1)));
        $keyph = substr($keyph,0,-1);
        $rankings = $this->getRankings($projectId,$keyph);
        //dd($rankings["data"]);
        foreach($rankings["data"] as $ranking){
            foreach($ranking as $dat){
                $ranks[] = [
                    'id' => $dat[0],
                    'keyphrase' => $keyphrases[$dat[0]],
                    'rank' => $dat[1]
                ];

            }
        }
        dd($ranks);
    }

    public function getSearchProfiles($projectId)
    {
        $response = Http::withBasicAuth(config('ant_rank.apikey'), '')->get('https://api.antranks.com/v1/projects/'.$projectId.'/search_profiles?limit=1000');
        return $firstSearchProfile = $response["data"][0];
    }

    public function getRankings($projectId, $keyPhraseIds, $dateBegin, $dateEnd)
    {
        $searchProfile = $this->getSearchProfiles($projectId);
        $response = Http::withBasicAuth(config('ant_rank.apikey'), '')->get('https://api.antranks.com/v1/projects/'.$projectId.'/ranks?limit=1000',[
            'search_profiles_id' => $searchProfile["id"],
            'keyphrases_ids' => $keyPhraseIds,
            'datebegin' => $dateBegin,
            'dateend' => $dateEnd
        ]);
        return $response->json();
    }

    public function getRanking(Request $request)
    {
        $dateBegin = date('Y-m-d');
        $dateEnd = date('Y-m-d');
        $projectId = $request->projectId;
        $searchProfile = $this->getSearchProfiles($projectId);
        $response = Http::withBasicAuth(config('ant_rank.apikey'), '')->get('https://api.antranks.com/v1/projects/'.$projectId.'/ranks?limit=1000',[
            'search_profiles_id' => $searchProfile["id"],
            'keyphrases_ids' => "4789848,4789850,4789852,4789851,4789857,4789855,4789859,4789858,5595059,5512286,5512287,5595060,5595062,5595061,5595063,5595064,5767374,5767375,5767376,5767377,5767378,5767379,5767380,5767381,5767382,5767383,5767384,5767385,5767386,5767387,5767388,5767389,5767390,5767391,5767392,5767393,5767394,5767395,5767396,5767397,5380149,5380150,5380152,5380151,5380165,5380164,5380167,5380166,5875908,4324148,5875909,5875910,5875911,5875912,5875913,5875914,5875915,5875916,5875917,5875918,5875919,5875920,5875921,58759225875923,5875924,5875925,5875926,5875927,5875928,5875929,5875930",
            'datebegin' => $dateBegin,
            'dateend' => $dateEnd
        ]);
        dd($response->json());
    }

    private function saveInDB()
    {
        //dd($this->data);
        try{
            DB::beginTransaction();

            if(count($this->data)>0)
            {
                foreach($this->data as $key => $data){
                   $project = Project::updateOrCreate(
                    [
                        'project_id' => $key
                    ],
                    [
                        'project_id' => $key,
                        'project_name' => $data["project_name"],
                        'project_domain' => $data["project_domain"]
                    ]);

                    foreach($data["keyphrases"] as $keyphrase)
                    {
                        Keyphrase::create([
                            'project_id' => $project->id,
                            'keyphrase_id' => $keyphrase['id'],
                            'keyphrase_name' => $keyphrase['keyphrase'],
                            'rank' => $keyphrase["rank"],
                            'created_date' => $keyphrase["date"]
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (Exception $e){
            log($e->getMessage());
            DB::rollback();
        }
    }

    private function directExport(){
        $data = collect($this->data);
        $fileName = date('Y-m-d').'-'.uniqid().'-keyReports.xlsx';
        \Excel::store(new ProjectSheetController($data), $fileName,'public');
        return \Storage::url($fileName);
    }
}
