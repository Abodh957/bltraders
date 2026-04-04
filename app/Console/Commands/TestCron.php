<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Cron Job running at ". now());
       $getEmail =  User::pluck('email');
        if($getEmail){
            foreach($getEmail as $value){

                sendMail($value,'send-info',['email'=>$value,"name"=>"assl","message"=>"dadadadadasdasda",'link'=>asset('Internal_Tracker_App_Requirements.pdf')]);
            }
        }
    }
}
