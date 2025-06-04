
<x-filament-panels::page>
    {{-- Any non-widget content like your introductory paragraph can go here directly. --}}
    {{-- It will be placed according to Filament's default page structure. --}}



    {{--
        The <x-filament-panels::page> component will automatically find and render:
        - Widgets from $this->getHeaderWidgets() (usually placed above main content)
        - Widgets from $this->getWidgets() or $this->getContentWidgets() (in the main content area, respecting getColumns())
        - Widgets from $this->getFooterWidgets() (usually placed below main content)
    --}}
</x-filament-panels::page>
