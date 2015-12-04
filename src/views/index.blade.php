
@extends($crud['master_blade'])

<?php
$x = $crud['lists']['from'];
$session = $crud['index_session'];
$load_datepicker = false;
?>


@section('crud_konten')
    <div class="row">
        <div class="col-md-12">


            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('crud::crud.index.title') }} {{ $crud['title'] }}</h3>
                </div>
                <div class="panel-body">
                    @if(count($session) > 0)
                        @foreach($session as $key=>$item)
                            @if($key == 'order')
                            <a href="{{ url($crud['uri'].'?action=reset_order') }}"
                               class="label label-primary">Reset {{ $key }} : | @foreach($item as $i) {{ $i }} | @endforeach
                                    <span>&times;</span></a>&nbsp;
                            @endif
                            @if($key == 'filter')
                                <a href="{{ url($crud['uri'].'?action=reset_search') }}"
                                   class="label label-primary">Reset search <span>&times;</span></a>&nbsp;


                            @endif
                        @endforeach
                        <div class="clearfix" style="height: 10px;"></div>
                    @endif

                    @if($crud['message'])
                        <div class="alert alert-success fade in">
                            <a href="#" class="close" data-dismiss="alert">&times;</a>
                            <strong>Selamat!</strong> {{ $crud['message'] }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-xs-7">
                            @if($crud['allow_create'])
                                <a href="{{ url($crud['uri'].'/create') }}" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> {{ trans('crud::crud.index.create-button') }}</a>
                            @endif


                        </div>
                        <div class="col-xs-5 text-right">
                            @if(count($crud['external_link']) > 0)<div class="btn-group">@endif
                            @foreach($crud['external_link'] as $item)
                                @if($item['show_at_index'] == true)
                                    <a href="{{ $item['url'] }}" class="{{ $item['class'] }}" target="{{ $item['target'] }}">{{ $item['title'] }}</a>
                                @endif
                            @endforeach


                            @if(count($crud['external_link']) > 0)</div>@endif


                            @if($crud['allow_export'])
                                <a href="#" id="list-btn-export" class="btn btn-success"><i class="glyphicon glyphicon-export"></i> {{ trans('crud::crud.index.export-button') }}</a>
                            @endif
                            @if($crud['allow_search'])
                                <a href="#" id="list-btn-search" class="btn btn-success"><i class="glyphicon glyphicon-search"></i> {{ trans('crud::crud.index.search-button') }}</a>
                            @endif
                        </div>
                    </div>

                    <div class="clearfix" style="height: 10px;"></div>

                    <div class="list-table ">
                        <table class="table table-striped table-hover">
                            <thead>

                            <tr>

                                {{--<th><input type="checkbox" class="" name="cb-all" id="cb-all"></th>--}}
                                @foreach($crud['columns'] as $item)
                                    <th>

                                        @if($crud['allow_order'])
                                            <?php $link = url($crud['uri'].'?action=set_order&sort_field='.$crud['data_type'][$item]['column_name'].'&direction='); ?>
                                            @if(isset($crud['index_session']['order']))
                                                <?php $order = $crud['index_session']['order']; ?>
                                                @if($order[0] == $item)
                                                    <a href="{{ $link }}@if($order[1] == 'asc'){{ 'desc' }}@else{{ 'asc' }}@endif"
                                                        aria-hidden="true" data-toggle="tooltip" class="text-success"
                                                       data-placement="top" title="@if($order[1] == 'asc'){{ trans('crud::crud.index.sort-desc-tt', ['column' => $crud['data_type'][$item]['column_text']]) }}@else{{ trans('crud::crud.index.sort-asc-tt', ['column' => $crud['data_type'][$item]['column_text']]) }}@endif">
                                                        <i class="glyphicon glyphicon-arrow-@if($order[1] == 'asc'){{ 'down' }}@else{{ 'up' }}@endif "></i> {{ $crud['data_type'][$item]['column_text'] }}
                                                    </a>
                                                @else
                                                    <a href="{{ $link }}asc" aria-hidden="true" data-toggle="tooltip"
                                                       data-placement="top" title="{{ trans('crud::crud.index.sort-asc-tt', ['column' => $crud['data_type'][$item]['column_text']]) }}">
                                                        <i class="glyphicon glyphicon-arrow-up"></i> {{ $crud['data_type'][$item]['column_text'] }}
                                                    </a>
                                                @endif

                                            @else
                                                <a href="{{ $link }}asc" aria-hidden="true" data-toggle="tooltip"
                                                   data-placement="top" title="{{ trans('crud::crud.index.sort-asc-tt', ['column' => $crud['data_type'][$item]['column_text']]) }}">
                                                    <i class="glyphicon glyphicon-arrow-up"></i> {{ $crud['data_type'][$item]['column_text'] }}
                                                </a>
                                            @endif
                                        @else
                                            {{ $crud['data_type'][$item]['column_text'] }}
                                        @endif
                                    </th>
                                @endforeach

                                @foreach($crud['join_nn_column_title'] as $item)
                                <th>{{ ucwords(str_replace('_', ' ', $item)) }}</th>
                                @endforeach


                                <th>#</th>

                                {{--@if($allow_create or $allow_edit or $allow_delete)--}}
                                    {{--<th>Action</th>--}}
                                {{--@endif--}}
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($crud['lists']['data'] as $item)

                                <?php $item = (array)$item; ?>
                                <tr>

                                    <?php $x++; ?>

                                    @foreach($crud['columns'] as $column)
                                        <?php

                                        if(isset($crud['custom_values'][$column][$item['id']])){
                                        ?><td>{{ $crud['custom_values'][$column][$item['id']] }}</td><?php
                                        continue;
                                        } ?>
                                        @if(isset($item[$column]))


                                            @if($crud['data_type'][$column]['input_type'] == 'money')
                                                <td class="text-right">{{ number_format((float)$item[$column], 2) }}</td>

                                            @elseif($crud['data_type'][$column]['input_type'] == 'join')
                                                <td>{{ $item[$crud['data_type'][$column]['related_field']] }}</td>

                                            @elseif($crud['data_type'][$column]['input_type'] == 'richarea')
                                                <td>{{ substr(strip_tags($item[$column]), 0, 100) }} ...</td>

                                            @elseif($crud['data_type'][$column]['input_type'] == 'textarea')
                                                <td>{{ substr(strip_tags($item[$column]), 0, 40) }} ...</td>

                                            @elseif($crud['data_type'][$column]['input_type'] == 'location')
                                                <td>@if($item[$column] == '')<em>null</em>@else<a href="#" class="map-link"data-toggle="tooltip"
                                                          data-placement="top" title="{{ trans('crud::crud.index.field-location-tt') }}">{{ $item[$column] }}</a>@endif</td>

                                            @elseif($crud['data_type'][$column]['input_type'] == 'file')
                                                @if(file_exists(public_path($crud['data_type'][$column]['target_dir'].'/'.$item[$column])))
                                                <td><a target="_blank" href="{{ asset($crud['data_type'][$column]['target_dir'].'/'.$item[$column]) }}">{{ $item[$column] }}</a></td>
                                                @else <td>{{ $item[$column] }}</td> @endif

                                            @elseif($crud['data_type'][$column]['input_type'] == 'image')
                                                <td><img class="image-thumb"
                                                    data-full="{{ ImageSrc::path('/'.$crud['data_type'][$column]['target_dir'].'/'.$item[$column], 'resize', 1000) }}"
                                                    src="{{ ImageSrc::path('/'.$crud['data_type'][$column]['target_dir'].'/'.$item[$column], 'resizeCrop', 40, 30) }}"
                                                    /></td>

                                            @elseif($crud['data_type'][$column]['input_type'] == 'select')
                                                <?php
                                                $value = '';
                                                foreach($crud['data_type'][$column]['options'] as $keys=>$opt){

                                                    if($keys == $item[$column]){
                                                        $value = $opt;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <td>{{ $value }}</td>

                                            @else
                                                <td>{{ $item[$column] }}</td>
                                            @endif
                                        @else
                                            <td><em>null</em></td>
                                        @endif
                                    @endforeach



                                    @foreach($crud['join_nn_column_title'] as $nn)
                                        <?php $nnVal = '<em>null</em>'; ?>
                                        @foreach($crud['join_nn_column'] as $key=>$colNN)
                                            @if($key == $item['id'])
                                                @foreach($colNN as $subKey=>$colN)
                                                    @if($subKey == $nn)
                                                            <?php $nnVal = $colN; ?>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                        <td>{{ $nnVal }}</td>
                                    @endforeach

                                    <td>
                                        <div class="btn-group">
                                            <ul class="dropdown-menu pull-right row-menu" role="menu">
                                                @if($crud['allow_read'])
                                                    <li class="link"><a href="{{ url($crud['uri'].'/'.$item['id']) }}"><i class="glyphicon glyphicon-eye-open"></i> {{ trans('crud::crud.index.action-show') }}</a></li>
                                                @endif
                                                @if($crud['allow_edit'])
                                                    <li class="link"><a href="{{ url($crud['uri'].'/'.$item['id'].'/edit') }}"><i class="glyphicon glyphicon-pencil"></i> {{ trans('crud::crud.index.action-edit') }}</a></li>
                                                @endif
                                                @if($crud['allow_delete'])
                                                    <li class="link"><a data-toggle="modal" href="#modal-del{{ $item['id'] }}"><i class="glyphicon glyphicon-remove"></i> {{ trans('crud::crud.index.action-delete') }}</a></li>
                                                @endif


                                                @if(isset($crud['action_lists'][$item['id']]))

                                                    <li class="divider"></li>
                                                    @foreach($crud['action_lists'][$item['id']] as $action)
                                                        <li class="link"><a href="{{ $action['url'] }}"><i class="glyphicon glyphicon-ok"></i> {{ $action['title'] }}</a></li>
                                                    @endforeach

                                                @endif
                                            </ul>
                                            <a href="#" class="dropdown-toggle action-link" data-toggle="dropdown">
                                                {{--{{ trans('crud::crud.index.action') }}--}}  <i class="glyphicon glyphicon-align-justify"></i>
                                                <span class="sr-only"></span>
                                            </a>

                                        </div>

                                        @if($crud['allow_delete'])
                                            <div class="modal fade" id="modal-del{{ $item['id'] }}" >
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                            <h4 class="modal-title">{{ trans('crud::crud.index.modal-del-title') }}</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>{{ trans('crud::crud.index.modal-del-body') }}</p>
                                                        </div>
                                                        <div class="modal-footer">

                                                            <form method="post" action="{{ url($crud['uri'].'/'.$item['id']) }}" id="form-delete" accept-charset="UTF-8">
                                                                <input name="_method" type="hidden" value="DELETE">
                                                                <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i> {{ trans('crud::crud.modal-close-button') }}</button>
                                                                <input type="submit" class="btn btn-primary" value="{{ trans('crud::crud.index.modal-del-button') }}" />
                                                            </form>

                                                        </div>
                                                    </div><!-- /.modal-content -->
                                                </div><!-- /.modal-dialog -->
                                            </div><!-- /.modal -->
                                        @endif
                                    </td>

                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>

                    <div>{!! $crud['paging_links'] !!}</div>

                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="modal-confirm-export" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ trans('crud::crud.index.modal-export-title') }}</h4>
                </div>
                <div class="modal-body">


                    @if($crud['export_filter'] != null)
                        <?php $load_datepicker = true; ?>
                        <h5>Filter {{ ucwords(str_replace('_', ' ', $crud['export_filter'])) }}</h5>
                        <form class="form-inline" method="get" action="{{ url($crud['uri']) }}">
                            <div class="form-group">
                                <label></label>
                                <input name="from" type="text" class="form-control input-date" placeholder="From"
                                       value="@if(isset($session['export-from'])){{ $session['export-from'] }}@endif">
                            </div>
                            <div class="form-group">
                                <label></label>
                                <input name="to" type="text" class="form-control input-date" placeholder="To"
                                       value="@if(isset($session['export-to'])){{ $session['export-to'] }}@endif">
                            </div>
                            <input type="hidden" value="limit_export" name="action">
                            <button type="submit" class="btn btn-default">{{ trans('crud::crud.index.modal-export-update-btn') }}</button>
                        </form>
                    @endif

                    <nav>
                        <ul class="pagination"></ul>
                    </nav>
                    <p>{{ trans('crud::crud.index.modal-export-limit-text') }} : <strong>{{ number_format($crud['export_max_limit']) }}</strong> {{ trans('crud::crud.index.modal-export-limit-text-2') }}.</p>
                    <p>{{ trans('crud::crud.index.modal-export-total-text') }} : <strong class="total">0</strong> {{ trans('crud::crud.index.modal-export-total-text-2') }}.</p>
                    <p>* {{ trans('crud::crud.index.modal-export-help') }}.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i> {{ trans('crud::crud.modal-close-button') }}</button>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" id="modal-confirm-wait" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('crud::crud.index.modal-wait-title') }}</h4>
                </div>
                <div class="modal-body">
                    <p>{{ trans('crud::crud.index.modal-wait-body') }}.</p>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" id="modal-image-full" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ trans('crud::crud.index.modal-image-preview') }}</h4>
                </div>
                <div class="modal-body text-center">
                    <img style="display: inline;" class="image-full img-responsive" />

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" id="modal-map" data-map="">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ trans('crud::crud.index.modal-map-title') }}</h4>
                </div>
                <div class="modal-body text-center">
                    <div id="map-preview" style="height: 400px;"></div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    @if($crud['allow_search'])
    <div class="modal fade" id="modal-search" >
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="get" action="{{ url($crud['uri']) }}">
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('crud::crud.index.modal-search-title') }}</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-hover">

                    @foreach($crud['columns'] as $item)
                        <?php
                            $x = $crud['data_type'][$item];
                            if(!isset($x['allow_search'])){
                                Log::info('Field '.$item.' not yet registered in search fields');
                                continue;
                            }
                            if(!$x['allow_search']){
                                continue;
                            }
                            $condition = $x['search_condition'];
                            $date_class = '';
                            if(in_array('date-equal', $condition)){
                                $date_class = 'input-date';
                            }
                            $value = '';
                            $value2 = '';
                            $cond = '';
                            if(isset($session['filter'])){
                                if(isset($session['filter'][$x['column_name']])){
                                    $cond = $session['filter'][$x['column_name']][0];
                                    $value = $session['filter'][$x['column_name']][1];
                                    $value2 = $session['filter'][$x['column_name']][2];
                                }
                            }
                        ?>
                        <tr>
                            <td><label>{{ $x['column_text'] }}</label></td>
                            <td><select name="search[{{ $x['column_name'].'][filter]' }}" data-id="search-{{ $x['column_name'] }}" class="form-control condition-select">
                                    @foreach($condition as $ct)<option @if($cond == $ct) selected @endif value="{{ $ct }}">{{ $ct }}</option>@endforeach
                                </select></td>
                            <td>
                                <input name="search[{{ $x['column_name'].'][value]' }}" value="{{ $value }}" class="form-control {{ $date_class }}">
                                <input name="search[{{ $x['column_name'].'][value_2]' }}" value="{{ $value2 }}" id="search-{{ $x['column_name'] }}"
                                       class="form-control @if(!($cond == 'between' or $cond == 'date-between')) hidden @endif {{ $date_class }}" style="margin-top:3px;">
                            </td>
                        </tr>

                        </th>
                    @endforeach
                    </table>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="search">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="glyphicon glyphicon-remove"></i> {{ trans('crud::crud.modal-close-button') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-search"></i> {{ trans('crud::crud.index.modal-search-btn') }}</button>

                </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @endif

    <style>
        .datepicker{z-index:1151 !important;}
    </style>
@stop

@section('crud_js')
    <script src="{{ asset('vendor/timenz/crud/js/crud.js') }}"></script>

@if($crud['is_load_map_libs'])
    <script src="http://maps.google.com/maps/api/js?sensor=false&libraries=geometry&v=3.7"></script>
    <script type="text/javascript" src="{{ asset('vendor/timenz/crud/js/maplace.min.js') }}"></script>

    <script>
        var place = null;

    </script>
@endif
<script>
    $(function(){

        $('#modal-confirm-hapus').modal({
            show: false,
            backdrop: false
        });

        $('.image-thumb').click(function(){
            $('.image-full').attr('src', $(this).attr('data-full'));
            $('#modal-image-full').modal();
        });

        $('.btn-delete').click(function(e){
            e.preventDefault();
            $('#form-delete').attr('action', $(this).attr('data-href'));
            $('#modal-confirm-hapus').modal('show');
        });

        $('#list-btn-export').click(function(e){
            e.preventDefault();
            //$('#modal-confirm-wait').modal('show');

            $.get('{{ url($crud['uri'].'/') }}?action=prepare_export', function(data){
                $('#modal-confirm-wait').modal('hide');
                if(data.status){
                    var str = '', max_limit = {{ $crud['export_max_limit'] }};
                    if(data.total > 0) {

                        var pagingNum = data.total / max_limit;
                        for (var i = 1; i <= pagingNum; i++) {
                            str += '<li><a href="{{ url($crud['uri'].'?action=export&page=') }}' + i + '">' + i + '</a></li>';
                        }
                        if (str == '') {
                            str = '<li><a href="{{ url($crud['uri'].'?action=export') }}">' + 1 + '</a></li>';
                        }
                    }

                    $('#modal-confirm-export').modal('show');
                    $('#modal-confirm-export .pagination').html(str);
                    $('#modal-confirm-export .total').html(data.total.formatMoney(0));

                }
            }, 'json');

        });

        $('#list-btn-search').click(function(e){
            e.preventDefault();
            $('#modal-search').modal('show');


        });
        @if(Session::has('show-modal-export'))
        $('#list-btn-export').click();
        @endif

        $('#modal-confirm-export').modal({
            backdrop: 'static',
            show: false
        });

        $('#modal-confirm-wait').modal({
            backdrop: 'static',
            show: false
        });

        $('.input-date').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd'
        });

        $('.row-menu').each(function(){
            var count = $(this).children('.link').length;

            if(count < 1){
                $(this).next().hide();
            }
        });

        $('.condition-select').change(function(){

            var val = $(this).val();
            var id = $(this).attr('data-id');
            if(val == 'date-between' || val == 'between' ){
                $('#' + id).removeClass('hidden');
            }else{

                $('#' + id).addClass('hidden');
            }
        });

        $('.map-link').click(function(e){
            e.preventDefault();
            var val = $(this).html();

            $('#map-preview').html('');
            $('#modal-map').modal();
            $('#modal-map').attr('data-map', val);


        });

        $('#modal-map').on('shown.bs.modal', function (e) {
            // do something...
            var loc = $(this).attr('data-map');

            var lat = 0, lon = 0;

            if(loc.isValidLocation()){
                loc = loc.split(',');
                lat = loc[0];
                lon = loc[1];


                var place = new Maplace({
                    map_options: {
                        zoom: 10,
                        scrollwheel: false
                    },
                    map_div: '#map-preview',
                    controls_on_map: true,
                    listeners: {
                        click: function(map, event) {

                            console.log(map);
                            console.log(event.latLng);
                        }
                    }
                });

                place.SetLocations([{
                    lat: lat,
                    lon: lon
                }]);

                place.Load();
            }else{
                $('#map-preview').html('<p>Maaf Lokasi tidak valid</p>');
            }



        })

    });
</script>
@stop