<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Notices;

class TestCronjob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap the datum from rip.ie';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $mytime = Carbon::now()->format('Y-m-d');
        $today = $mytime->toDateString();
        echo $today;
        $client = new Client();
        $response = $client->request('GET', 'https://rip.ie/deathnotices.php?do=get_deathnotices_pages&sEcho=1&iColumns=5&sColumns=&iDisplayStart=0&iDisplayLength=40&mDataProp_0=0&mDataProp_1=1&mDataProp_2=2&mDataProp_3=3&mDataProp_4=4&iSortingCols=2&iSortCol_0=0&sSortDir_0=desc&iSortCol_1=0&sSortDir_1=asc&bSortable_0=true&bSortable_1=true&bSortable_2=true&bSortable_3=true&bSortable_4=true&iDisplayLength=40&DateFrom=2019-08-17+00%3A00%3A00&DateTo='.$today.'+23%3A59%3A59&NoWhere=y', []);
        $data = (string) $response->getBody();
            $noticeObject = json_decode($data);
            $noticeArrayCount = $noticeObject->iTotalRecords;
        
        
        for($i = 1; $i < $noticeArrayCount+1; $i++){
            $client = new Client();
            $start = ($i-1) * 40;
            $response = $client->request('GET', 'https://rip.ie/deathnotices.php?do=get_deathnotices_pages&sEcho='.$i.'&iColumns=5&sColumns=&iDisplayStart='.$start.'&iDisplayLength=40&mDataProp_0=0&mDataProp_1=1&mDataProp_2=2&mDataProp_3=3&mDataProp_4=4&iSortingCols=2&iSortCol_0=0&sSortDir_0=desc&iSortCol_1=0&sSortDir_1=asc&bSortable_0=true&bSortable_1=true&bSortable_2=true&bSortable_3=true&bSortable_4=true&iDisplayLength=40&DateFrom=2019-08-17+00%3A00%3A00&DateTo=2019-08-25+23%3A59%3A59&NoWhere=y', []);

            $data = (string) $response->getBody();
            $noticeObject = json_decode($data);
            $noticeArray = $noticeObject->aaData;
            //start foreach;
            foreach ($noticeArray as $key => $item) {
                $prior_notices = Notices::where('index','=',$item[5])->first();

                if( !$prior_notices){

                    $notices = new Notices();
                    $notices->firstname = $item[0];//first name
                    $notices->town = $item[1];// town 
                    $notices->county = $item[2]; // county

                    $replace = substr_replace($item[3], '20', 6, 0);
                $replace .= " 00:00:00";
                $format = 'd/m/Y H:i:s';
                $date = DateTime::createFromFormat($format, $replace);
                // dd($date);
                $notices->published = $date;// published date

                    $notices->finaldatelater = $item[4];//value ==1 :announced finaldate later
                    $notices->index = $item[5];// id
                    $notices->chdeathtime = $item[6];//value == 1 :death time changed
                    $notices->lastname = $item[7];//lastname
                    $notices->neename = $item[8];//nee name
                    $notices->aheadtowncounty = $item[9];//the ahead element of town and county in detail page 
                    $notices->image = $item[10];//image
                    $notices->resta = $item[11];
                    $notices->restb = $item[12];
                    $notices->restc= $item[13];
                    

                    //again send the request of detail page
                    $client = new Client();
                    $response = $client->request('GET', 'https://rip.ie/death-notice/'.$notices->lastname.'-'.$notices->firstname.'-'.$notices->town.'-'.$notices->county.'/'.$notices->index, []);
                    $data = (string) $response->getBody();
                    //start: get church name and ceremony name , and then map of these
                    $doc = new \DOMDocument();
                    libxml_use_internal_errors(true);
                    $doc->loadHTML($data);
                    libxml_clear_errors();
                    $dxp = new \DOMXpath($doc);
                    $div = $dxp->query('//script');
                    $description = $div->item(12)->textContent;
                    $html = $description;
                    $needle = "var json";
                    //start this is catching the "var json" 's count
                    $lastPos = 0;
                    $positions = array();
                    while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
                        $positions[] = $lastPos;
                        $lastPos = $lastPos + strlen($needle);
                    }
                    $positionscount = count($positions);
                    //end var json count
                    if($positionscount == 1){
                        $positions_description = "";
                    }
                    else if($positionscount == 4){
                        preg_match('~=(.*?)}~', $html, $output);
                        $positions_description = $output[1];
                        $positions_description .= "}]}";
                        $positions_description = json_decode($positions_description);
                        $type = $positions_description->type;
                        $locname = $positions_description->data[0]->locName;
                        $localtname = $positions_description->data[0]->locAltName;
                        $zoom = $positions_description->data[0]->Zoom;
                        $locaddr = $positions_description->data[0]->locAddr;
                        $remark = $positions_description->data[0]->remark;
                        $lat = $positions_description->data[0]->lat;
                        $lon = $positions_description->data[0]->lon;
                        $loccounty = $positions_description->data[0]->locCounty;
                        $loctown = $positions_description->data[0]->locTown;
                        $loccatname = $positions_description->data[0]->locCatname;
                        $loccountyid = $positions_description->data[0]->locCountyID;
                        $loctownid = $positions_description->data[0]->locTownID;

                        if($type == "church_mapfavourite_map"){
                            $notices->chtype = $type;
                            $notices->chlocname = $locname;
                            $notices->chlocaltname = $localtname;
                            $notices->chzoom = $zoom;
                            $notices->chlocaddr = $locaddr;
                            $notices->chremark = $remark;
                            $notices->chlat = $lat;
                            $notices->chlon = $lon;
                            $notices->chloccounty = $loccounty;
                            $notices->chloctown = $loctown;
                            $notices->chloccatname = $loccatname;
                            $notices->chloccountyid = $loccountyid;
                            $notices->chloctownid = $loctownid;
                        } else{
                            $notices->cetype = $type;
                            $notices->celocname = $locname;
                            $notices->celocaltname = $localtname;
                            $notices->cezoom = $zoom;
                            $notices->celocaddr = $locaddr;
                            $notices->ceremark = $remark;
                            $notices->celat = $lat;
                            $notices->celon = $lon;
                            $notices->celoccounty = $loccounty;
                            $notices->celoctown = $loctown;
                            $notices->celoccatname = $loccatname;
                            $notices->celoccountyid = $loccountyid;
                            $notices->celoctownid = $loctownid;
                        }
                    }
                    else if($positionscount == 7){
                        //first part: the church catching
                        preg_match('~=(.*?)}~', $html, $output);
                        $positions_description = $output[1];
                        $positions_description .= "}]}";
                        $positions_description = json_decode($positions_description);
                        $type = $positions_description->type;
                        $locname = $positions_description->data[0]->locName;
                        $localtname = $positions_description->data[0]->locAltName;
                        $zoom = $positions_description->data[0]->Zoom;
                        $locaddr = $positions_description->data[0]->locAddr;
                        $remark = $positions_description->data[0]->remark;
                        $lat = $positions_description->data[0]->lat;
                        $lon = $positions_description->data[0]->lon;
                        $loccounty = $positions_description->data[0]->locCounty;
                        $loctown = $positions_description->data[0]->locTown;
                        $loccatname = $positions_description->data[0]->locCatname;
                        $loccountyid = $positions_description->data[0]->locCountyID;
                        $loctownid = $positions_description->data[0]->locTownID;

                            $notices->chtype = $type;
                            $notices->chlocname = $locname;
                            $notices->chlocaltname = $localtname;
                            $notices->chzoom = $zoom;
                            $notices->chlocaddr = $locaddr;
                            $notices->chremark = $remark;
                            $notices->chlat = $lat;
                            $notices->chlon = $lon;
                            $notices->chloccounty = $loccounty;
                            $notices->chloctown = $loctown;
                            $notices->chloccatname = $loccatname;
                            $notices->chloccountyid = $loccountyid;
                            $notices->chloctownid = $loctownid;
                        //next part : the ceremony catching
                        $nextdescription = substr($html,$positions[1],$positions[2]);
                        preg_match('~=(.*?)}~', $nextdescription, $output);
                        $positions_description = $output[1];
                        $positions_description .= "}]}";
                        $positions_description = json_decode($positions_description);
                        $type = $positions_description->type;
                        $locname = $positions_description->data[0]->locName;
                        $localtname = $positions_description->data[0]->locAltName;
                        $zoom = $positions_description->data[0]->Zoom;
                        $locaddr = $positions_description->data[0]->locAddr;
                        $remark = $positions_description->data[0]->remark;
                        $lat = $positions_description->data[0]->lat;
                        $lon = $positions_description->data[0]->lon;
                        $loccounty = $positions_description->data[0]->locCounty;
                        $loctown = $positions_description->data[0]->locTown;
                        $loccatname = $positions_description->data[0]->locCatname;
                        $loccountyid = $positions_description->data[0]->locCountyID;
                        $loctownid = $positions_description->data[0]->locTownID;

                            $notices->cetype = $type;
                            $notices->celocname = $locname;
                            $notices->celocaltname = $localtname;
                            $notices->cezoom = $zoom;
                            $notices->celocaddr = $locaddr;
                            $notices->ceremark = $remark;
                            $notices->celat = $lat;
                            $notices->celon = $lon;
                            $notices->celoccounty = $loccounty;
                            $notices->celoctown = $loctown;
                            $notices->celoccatname = $loccatname;
                            $notices->celoccountyid = $loccountyid;
                            $notices->celoctownid = $loctownid;
                    }
                    //end: get church name and ceremony name , and then map of these

                    //start:get deathDate
                    $doc = new \DOMDocument();
                    libxml_use_internal_errors(true);
                    $doc->loadHTML($data);
                    libxml_clear_errors();
                    $dxp = new \DOMXpath($doc);
                    $divlength = $dxp->query('//div[@class="dates ddeath textRight"]')->length;
            
                    if ($divlength == 0){
                        $notices->deathdate = "";
                    } else {
                        $div = $dxp->query('//div[@class="dates ddeath textRight"]');
                        $deathdate = $div->item(0)->textContent;
                        $deathdate = substr($deathdate,14,strlen($deathdate));
                        $notices->deathdate = $deathdate;
                    }
                    //end: get deathdate

                    //start: whole description of death
                    $doc = new \DOMDocument();
                    libxml_use_internal_errors(true);
                    $doc->loadHTML($data);
                    libxml_clear_errors();
                    $dxp = new \DOMXpath($doc);
                    $divlength = $dxp->query('//td[@class="minhide"]')->length;

                    if ($divlength == 0){
                        $notices->description = "";
                    } else {
                        $div = $dxp->query('//td[@class="minhide"]');
                        $description = $div->item(0)->textContent;
                        $notices->description = $description;
                    }
                    //end: whole description of death

                    //start get twon county / city (location)
                    $doc = new \DOMDocument();
                    libxml_use_internal_errors(true);
                    $doc->loadHTML($data);
                    libxml_clear_errors();
                    $dxp = new \DOMXpath($doc);
                    $divlength = $dxp->query('//h3[@class="no-print"]/span[@class="small_addr"]')->length;
                    if ($divlength == 0){
                        $notices->towncounty = "";
                    } else {
                        $div = $dxp->query('//h3[@class="no-print"]/span[@class="small_addr"]');
                        $towncounty = $div->item(0)->textContent;
                        $notices->towncounty = $towncounty;
                    }
                    //end get twon county / city (location)

                    $notices->save();
                }
            }
            //end foreach
        }
        $this->info('TestCronjob has been completed successfully');
    }
}
