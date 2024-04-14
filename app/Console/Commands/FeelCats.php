<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
class FeelCats extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:feel-cats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Заполняем категории для запчастей';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);
        $category = new Category();
        $category->index();
    }
}
