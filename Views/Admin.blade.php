@extends('gp247-admin::layouts.admin')

@section('main')
<div class="row">
    <div class="col-md-12">
        {{-- Core 2.0 fallback view. The real admin screen is the Livewire component
             (App\GP247\Plugins\CheckIP\Livewire\AdminLivewire); this static stub only
             renders if that class is unavailable. No jQuery / AdminLTE widgets. --}}
        <x-gp247::card :title="gp247_language_render('Plugins/CheckIP::lang.admin.list')">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {!! gp247_language_render('Plugins/CheckIP::lang.ip_help') !!}
            </p>
        </x-gp247::card>
    </div>
</div>
@endsection
