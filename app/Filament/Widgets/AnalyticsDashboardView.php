<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AnalyticsDashboardView extends Widget
{
    protected static string $view = 'filament.widgets.analytics-dashboard-view';

    protected int | string | array $columnSpan = 'full';
}
