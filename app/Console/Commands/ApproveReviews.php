<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\ReviewRepo;

class ApproveReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'review:approve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically approve reviews older than 1 minute';

    /**
     * Execute the console command.
     */
    public function handle(ReviewRepo $reviewRepo)
    {
        $reviews = $reviewRepo->Unapproved();
        $count = $reviews->count();
        if ($count > 0) {
            foreach ($reviews as $review) {
                $reviewRepo->automatic_update_review($review->id);
            }
            $this->info("Successfully approved {$count} pending reviews.");
        } else {
            $this->info("No pending reviews older than 1 minute found.");
        }
    }
}
