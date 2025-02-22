<x-filament-panels::page>
    <livewire:contador-chart/>

    <livewire:equipment-check-stats/>

    <livewire:area-reports-chart/>

    @livewire(App\Filament\Widgets\EquipmentAlertsWidget::class)

    @livewire(App\Filament\Widgets\EquipmentAlertsChart::class)
</x-filament-panels::page>
