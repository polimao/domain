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
    protected $signature = 'yep:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $suffixs = Suffix::get(['name','id'])->toArray();
        $suffixs = array_pluck($suffixs, 'name','id');

        $offset = 0;
        do{
            $bodies = Body::offset($offset)->limit(200)->orderBy('id')->get();
            foreach ($bodies as $key => $body) {
                $this->comment($key . '\\' . $offset);
                // echo $body->id,PHP_EOL;
                $this->requireYuming($body,$suffixs);
            }
            $offset += 200;
            sleep(5);
        } while ($bodies);
    }

    private function requireYuming($body,$suffixs)
    {
        $curl = new Curl();

        $url = "http://www.yumingco.com/api";
        $url = "http://www.aaw8.com/Api/DomainApi.aspx";

        foreach ($suffixs as $suffix_id => $suffix) {
            // $parma = http_build_query(['domain' => $body->name,'suffix'=>$suffix]);
            $result = $curl->get($url . '?domain=' . $body->name . '.' . $suffix );
            $this->comment($url . '?domain=' . $body->name . '.' . $suffix );
            // echo $result->body;
            preg_match('/StateID"\:(\d{3})/i', $result->body,$match);
            if(isset($match[1])){
                $register_status = 0;

                if($match[1] == 210) //可以注册
                {
                    $register_status = -8;
                    $this->info('   可以注册');
                }else if($match[1] == 211) //已经注册
                {
                    $register_status = 8;
                    $this->error('   已经注册');
                }

                $data = [
                        'body_id' => $body->id,
                        'suffix_id' => $suffix_id,
                        'register_status' => $register_status,
                        'verify_at' => date('Y-m-d H:i:s')
                    ];
                $domain = Domain::where('body_id',$body->id)->where('suffix_id',$suffix_id)->first();
                if(!$domain)
                {
                    Domain::create($data);
                }elseif ($domain->register_status <> $register_status){
                    $domain->register_status = $register_status;
                    $domain->verify_at = date('Y-m-d H:i:s');
                    $domain->save();
                }
            }
            sleep(3);
        }
    }

}
