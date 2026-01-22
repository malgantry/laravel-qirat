<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Keep UUID primary key from Laravel notifications, just align auxiliary columns used by the app
            if (! Schema::hasColumn('notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable()->after('type');
            }

            if (! Schema::hasColumn('notifications', 'body')) {
                $table->text('body')->nullable()->after('title');
            }
        });

        // Backfill user_id and title/body from existing data payload where possible
        $this->backfillNotifications();
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('notifications', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('notifications', 'body')) {
                $table->dropColumn('body');
            }
        });
    }

    private function backfillNotifications(): void
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'notifications';
            public $timestamps = false;
            public $incrementing = false;
            protected $primaryKey = 'id';
            protected $keyType = 'string';
        };

        $model
            ->whereNull('user_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($model) {
                foreach ($rows as $row) {
                    $payload = null;
                    if (isset($row->data)) {
                        $payload = json_decode($row->data, true);
                    }

                    $row->user_id = ($row->notifiable_type ?? null) === 'App\\Models\\User'
                        ? $row->notifiable_id
                        : null;

                    if ($payload && is_array($payload)) {
                        $row->title = $payload['title'] ?? $row->title;
                        $row->body = $payload['body'] ?? $row->body;
                    }

                    $row->save();
                }
            });
    }
};
