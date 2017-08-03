<?php

namespace App\Console\Commands;

use App\Thread;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class FillLikeUserList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:like-user-list {tid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $tid = $this->argument('tid');
        $uid = 100;

        while ($uid < 1000) {
            $item = [
                'id' => (string)$uid,
                'time' => Carbon::now()->toDateTimeString()
            ];
            $json = json_encode($item);

            Thread::where('id', $tid)->update(['like_user_list' => DB::raw("JSON_ARRAY_APPEND(like_user_list,'$',CAST('{$json}' as JSON))")]);
            $uid++;
            $this->info($uid);
        }
    }
}
