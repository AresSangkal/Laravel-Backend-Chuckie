<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notices extends Model
{
    //
    protected $table = "anothertable";

    public $timestamps = false;

    protected $fillable = [
        'id', 'firstname', 'town', 'county', 'published', 'finaldatelater','index','chdeathtime','lastname','neename','aheadtowncounty','image','resta','restb','restc',
        'chtype', 'chlocname', 'chlocaltname', 'chzoom', 'chlocaddr', 'chremark', 'chlat', 'chlon', 'chloccounty', 'chloctown', 'chloccatname', 'chloccountyid', 'chloctownid',
        'cetype', 'celocname', 'celocaltname', 'cezoom', 'celocaddr', 'ceremark', 'celat', 'celon', 'celoccounty', 'celoctown', 'celoccatname', 'celoccountyid', 'celoctownid',
        'deathdate', 'description', 'towncounty'
    ];
}
