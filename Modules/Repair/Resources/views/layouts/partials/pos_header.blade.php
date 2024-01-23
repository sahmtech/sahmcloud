@if ($__is_repair_enabled)
    @can('repair.create')
        <a style=" justify-content: center;
		align-items: center;
		display: flex;width:85px;padding:8px"
            href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) . '?sub_type=repair' }}"
            title="{{ __('repair::lang.add_repair') }}" data-toggle="tooltip" data-placement="bottom"
            class="btn bg-purple btn-flat m-6 btn-xs m-5 pull-right">
            <strong><i class="fa fa-wrench fa-lg"></i> &nbsp;@lang('repair::lang.repair')</strong>
        </a>
    @endcan
@endif
