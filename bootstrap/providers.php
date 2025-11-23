<?php

return [
    App\Providers\AppServiceProvider::class,
    // Only load Telescope in local environment
    ...app()->environment('local') ? [App\Providers\TelescopeServiceProvider::class] : [],
];
