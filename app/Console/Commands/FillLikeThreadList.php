<?php

namespace App\Console\Commands;

use App\UserLike;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class FillLikeThreadList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:like-thread-list {uid}';

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
        $uid = $this->argument('uid');
        $tid = 100;

        while ($tid < 10000) {
            $item = [
                'id' => (string)$tid,
                'time' => Carbon::now()->toDateTimeString()
            ];
            $json = json_encode($item);

            UserLike::where('user_id', $uid)->update(['like_thread_list' => DB::raw("JSON_ARRAY_APPEND(like_thread_list,'$',CAST('{$json}' as JSON))")]);
            $tid++;
            $this->info($tid);
        }
    }
}
