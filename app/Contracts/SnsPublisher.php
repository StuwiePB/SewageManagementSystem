<?php

namespace App\Contracts;

interface SnsPublisher
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function publish(string $event, string $subject, array $payload, string $severity = 'info'): void;
}
