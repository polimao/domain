<?php

namespace Domain\Console\Commands;

use Illuminate\Console\Command;
use Curl;
use Pinyin;
use Domain\Body;
use Domain\Domain;
use Domain\Suffix;


class VerifyRegisterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yep:verify {source=aaw8 : Default aaw8}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get domain verify status.';

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
        $source = $this->argument('source');
        $method = 'deal' . ucfirst($source);
        if(method_exists($this,$method))
            $this->$method();
        else
            $this->error('Method no exist!');
    }

    public function dealXinwang()
    {
        $suffixs = Suffix::get(['name','id'])->toArray();
        $suffixs = array_pluck($suffixs, 'name','id');

        $offset = 1000;
        do{
            // $bodies = Body::where('register_status',0)->offset($offset)->limit(200)->orderBy('id','desc')->get();
            $domain = Domain::where('register_status',0)->offset($offset)->orderBy('id','desc')->first();
// dd($domain);
            $this->requireXinwang($domain,$suffixs[$domain->suffix_id]);

            sleep(1);
        } while (true);

    }

    private function requireXinwang($domain,$suffix)
    {
        $body = $domain->body->name;
        $url = "http://checkdomain.xinnet.com/domainCheck?callbackparam=jQuery17208138300830344336_1480265083858&searchRandom=8&prefix={$body}&suffix=.{$suffix}&_=1480265126741";

        $this->comment($url);
        $curl = new Curl();
        $result = $curl->get($url);
        // dd($result);
        $result = str_replace(['jQuery17208138300830344336_1480265083858(',')'], '', $result);
        $data = json_decode($result,true);

// [{"searchRandom":8,"result":[{"yes":[],"no":[{"price":55,"flag":false,"productCode":"AEPDxin000201","prefix":"testtest","originalNewPrice":128,"domainName":"testtest.net","goodsCode":"GDxin000201","feeFlag":false,"suffix":".net","timeAmount":1}]}]}])

        if(isset($data[0]['result'][0]['yes'])){
            $register_status = -1;
            if(!empty($data[0]['result'][0]['yes'])){
                $register_status = -8;
                $this->info('   可以注册');
            }else{
                $register_status = 8;
                $this->error('   已经注册');
            }
            $domain->register_status = $register_status;
            $domain->verify_at = date('Y-m-d H:i:s');
            $domain->save();
            // dd($domain->toArray(),$register_status,12,date('Y-m-d H:i:s'));
        }else{
            $this->error(' Web Error!');
            dd();
        }

        // dd($data[0]['result'][0]['yes'],$data[0]['result' ][0]['no']);
    }

    public function dealAaw8()
    {
        $suffixs = Suffix::get(['name','id'])->toArray();
        $suffixs = array_pluck($suffixs, 'name','id');

        $suffixs = array_diff($suffixs,['aero','be','ca','ch','fr','hk','ie','im','in','io','la','nu','se','tw','us','ws']);

        // dd(array_keys($suffixs));

        $offset = 100;
        do{
            // $bodies = Body::where('register_status',0)->offset($offset)->limit(200)->orderBy('id','desc')->get();
            $domain = Domain::where('register_status',0)->offset($offset)->whereIn('suffix_id',array_keys($suffixs))->first();
// dd($domain);
            $this->requireAAW8($domain,$suffixs);

            // sleep(1);
        } while (true);
    }

    private function requireAAW8($domain,$suffixs)
    {

        $curl = new Curl();

        // $url = "http://www.yumingco.com/api";
        $url = "http://www.aaw8.com/Api/DomainApi.aspx";

        // $parma = http_build_query(['domain' => $body->name,'suffix'=>$suffixs[$domain->suffix_id]]);
        $result = $curl->get($url . '?domain=' . $domain->body->name . '.' . $suffixs[$domain->suffix_id] );
        $this->comment($url . '?domain=' . $domain->body->name . '.' . $suffixs[$domain->suffix_id] );
        // echo $result->body;
        preg_match('/StateID"\:(\d{3})/i', $result->body,$match);
        if(isset($match[1])){
            $register_status = -1;

            if($match[1] == 210) //可以注册
            {
                $register_status = -8;
                $this->info('   可以注册');
            }else if($match[1] == 211) //已经注册
            {
                $register_status = 8;
                $this->error('   已经注册');
            }else{
                $this->error('   other ' . $match[1]);
            }
            if ($domain->register_status <> $register_status){
                $domain->register_status = $register_status;
                $domain->verify_at = date('Y-m-d H:i:s');
                $domain->save();
                // dd($domain->toArray(),$register_status,12,date('Y-m-d H:i:s'));
            }
            // dd($domain->toArray(),$register_status);
        }else{
            $this->error('web error!');
            // dd($result);
        }
        sleep(0.5);
    }

    private function requireYuming($body,$suffixs)
    {
        // todo
    }

}
