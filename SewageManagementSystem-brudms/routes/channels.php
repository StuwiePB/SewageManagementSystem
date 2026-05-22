<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('support.{userId}', function ($user, $userId) {
    if ((int) $user->id === (int) $userId) {
        return true;
    }

    return $user->hasRole('admin');
});
