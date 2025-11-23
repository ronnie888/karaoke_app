<?php

namespace App\View\Composers;

use Illuminate\View\View;

class NavigationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        $view->with([
            'playlistsCount' => $user->playlists()->count(),
            'favoritesCount' => $user->favorites()->count(),
            'historyCount' => $user->watchHistory()->count(),
        ]);
    }
}
