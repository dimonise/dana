<?php

namespace App\Console\Commands;

use App\Models\Details;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GetDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-details';

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
        set_time_limit(0);
        echo 'Start downloading details'.PHP_EOL;
        $client = new Client();
        $res = $client->request('GET',
            'http://194.15.54.191/test/hs/api/FullPrice/fxGXX13iRkE5y0f0NvQAz9mjrAFtF4sRT9QRqZXhifgypLGAF',
            ['auth' => ['1c', 'z8anfaoq']]);
        $result = json_decode($res->getBody());
        echo 'Count details - '.count($result).PHP_EOL;
        echo 'Updating table'.PHP_EOL;
        $details = new Details();
        $details->UploadDetails($result);
        echo 'Downloading finished'.PHP_EOL;
    }
}
