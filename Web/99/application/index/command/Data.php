<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\index\controller\Api;

class Data extends Command
{
    protected function configure()
    {
        $this->setName('data')->setDescription('Here is the getdata');
    }

    protected function execute(Input $input, Output $output)
    {
        $api = new Api();
        $output->writeln(date("Y-m-d H:i:s") . " Data Command run!");
        while(true){
            $t = time();
            if($t % 5 == 0){
                $api->order();
                $output->writeln(date("Y-m-d H:i:s") . " order run!");
                $api->allotorder();
                $output->writeln(date("Y-m-d H:i:s") . " allotorder run!");
            }
            if($t % 30 == 0){
                $api->getdata();
                $output->writeln(date("Y-m-d H:i:s") . " getdata run!");
                $api->checkbal();
                $output->writeln(date("Y-m-d H:i:s") . " checkbal run!");
            }
            if($t % 60 == 0){
                $api->interest();
                $output->writeln(date("Y-m-d H:i:s") . " interest run!");
            }
            sleep(1);
        }
    }
}