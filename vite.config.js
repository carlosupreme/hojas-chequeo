import {defineConfig} from 'vite'
import laravel, {refreshPaths} from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [laravel({
        input: ['resources/css/filament/operador/theme.css', 'resources/css/filament/admin/theme.css', 'resources/css/app.css', 'resources/js/app.js'],
        refresh: [...refreshPaths, 'app/Livewire/**', 'app/Forms/Components/**', 'app/Livewire/**', 'app/Infolists/Components/**', 'app/Providers/Filament/**', 'app/Tables/Columns/**', 'resources/views/**/*.blade.php'],
    }),],
})
