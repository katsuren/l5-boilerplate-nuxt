<?php

Route::get('/{any}', function () {
    $html = file_get_contents(public_path('pages/index.html'));
    return $html;
})->where('any', '.*');
