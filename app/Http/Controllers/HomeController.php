<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
        public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        return view("landing");
    }

    public function search(Request $request)
    {
        $firstname = $request->input('firstname');
        $lastname = $request->input('lastname');
        $neename = $request->input('neename');
        $county = $request->input('county');
        $town = $request->input('town');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        
        if ( $firstname != "" || $lastname !="" || $neename != "" || $county != "" || $town !="" 
        || $date_from !="" || $date_to != ""){
            if($date_from == ""){
                $date_from = Carbon::createFromTimestamp(0);
            }
            if($date_to == ""){
                $date_to = Carbon::now()->addYears(50);
            }
              $estates = \DB::table('anothertable')->where("firstname","LIKE", "%" . $firstname . "%")
                ->where("lastname","LIKE", "%" . $lastname . "%")->where("neename","LIKE", "%" . $neename . "%")
                ->where("county","LIKE", "%" . $county . "%")->where("town","LIKE", "%" . $town . "%")
                ->whereBetween("published", [$date_from, $date_to]);
                $estatesdatas = $estates->get();

            if(count($estatesdatas) > 0){
                $items = $estates->paginate(20)->appends(Input::except('page'));
                return ['status' => $items];
            } else {
                $estates = [];
                $data['estates'] = $estates;
                return ['status' => $estates];
            }

        }  else {
                $estates = \DB::table('anothertable');
                $items = $estates->paginate(20)->appends(Input::except('page'));
                return ['status' => $items];
        }
        

    }

    public function replace(){

        $query = DB::table('anothertable')->select('published');

        $getpublished = $query->get();
        $i = 17;// start id will be replaced
        foreach($getpublished as $getpublish){
            
            DB::table('anothertable')->where('id', $i)->update(['published' => substr_replace($getpublish->published, '20', 6, 0)]);
            $i = $i + 1;

        }
    }

}
