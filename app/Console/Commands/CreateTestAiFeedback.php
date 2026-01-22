<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiFeedback;

class CreateTestAiFeedback extends Command
{
    protected $signature = 'ai:seed-feedback {--user=0}';
    protected $description = 'Create a test AI feedback record (for development)';

    public function handle()
    {
        $userId = (int) $this->option('user') ?: null;
        $fb = AiFeedback::create([
            'feedback_id' => 'test-' . time(),
            'user_id' => $userId,
            'action' => 'accepted',
            'object_type' => 'transaction',
            'object_id' => 1,
            'meta' => ['note' => 'seeded for test']
        ]);

        $this->info('Created AiFeedback id=' . $fb->id . ' feedback_id=' . $fb->feedback_id);
        return 0;
    }
}
