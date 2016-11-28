<?php

namespace Domain\Console\Commands;

use Illuminate\Console\Command;
use Curl;
use Pinyin;
use Domain\Dick;
use Domain\Body;
use Domain\Library;


class PullWordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yep:pullword';

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
        // dd(scandir(storage_path('/library')));
        foreach (scandir(storage_path('/library')) as $file) {
            $file_path = storage_path('/library') . "/$file";
            if(is_file($file_path)){
                if(!Library::where('name',$file)->first()){
                    $this->comment('====== ' . $file);
                    $this->splitText($file_path);
                    Library::create(['name' => $file]);
                }
            }
        }
    }

    private function splitText($file_path)
    {
        // dd($file_path);
        $text = file_get_contents($file_path);
        $arr = $this->str_split_unicode($text,1000);
        // dd($arr);
        $arr = array_reverse($arr);
        foreach ($arr as $key => $value) {
            // $this->info($value);

            $url = "http://www.pullword.com/process.php";
            $curl = new Curl();
            $param = array(
                    "param1" => "1",
                    "param2" => "0",
                    "source" => $value,
                );
            $this->comment('剩余------------------------'. (count($arr)-$key).'组');
            $res = $curl->post($url, $param);
            // dd($res->body);
            $words = explode("\r\n",$res);
            // $Py = new ChineseSpell();
            sleep(5);
            // var($words);
            foreach ($words as $k => $word) {
                if($k < 16 || !$word)
                    continue;

                if($word >= 'A' && $word <='z')
                    continue;
                // $word_clone = iconv("UTF-8", "GB2312//IGNORE", $word);
                // $word_clone = mb_convert_encoding(trim($word), "gb2312", "UTF-8");
                $pinyin = join(Pinyin::convert(trim($word)));

                $this->info($word . '   --  ' . $pinyin);



                if($dick = Dick::where('name',$word)->first())
                {
                    $dick->appear_cnt = $dick->appear_cnt + 1;
                    $dick->save();
                    $dick->body->appear_cnt+=1;
                    $dick->body->save();
                }else{
                    if($body = Body::where('name',$pinyin)->first()){
                        $body->appear_cnt = $body->appear_cnt + 1;
                        $body->save();
                    }else{
                        $body = Body::create([
                            'name'    => $pinyin,
                            'lengh'   => strlen($pinyin),
                            'appear_cnt' => 1
                            ]);
                    }
                    Dick::create([
                        'name'    => $word,
                        'lengh'   => mb_strlen($word),
                        'body_id' => $body->id,
                        'appear_cnt' => 1
                        ]);
                }

            }
        }
    }

    /**
     * 将unicode字符串按传入长度分割成数组
     * @param  string  $str 传入字符串
     * @param  integer $l   字符串长度
     * @return mixed      数组或false
     */
    public function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
     }
}
