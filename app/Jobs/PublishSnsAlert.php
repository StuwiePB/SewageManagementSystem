<?php

namespace App\Jobs;

use App\Contracts\SnsPublisher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PublishSnsAlert implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $event,
        public string $subject,
        public array $data,
        public string $severity = 'info',
    ) {}

    public function handle(SnsPublisher $publisher): void
    {
        if (! config('sns.enabled')) {
            return;
        }

        $publisher->publish($this->event, $this->subject, $this->data, $this->severity);
    }
}
