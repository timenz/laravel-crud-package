@extends($crud['master_blade'])

@section('crud_konten')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $crud['title'] }}</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 text-right">
                            @foreach($crud['external_link'] as $item)
                                @if($item['show_at_read'] == true)
                                    <a href="{{ $item['url'] }}" class="{{ $item['class'] }}" target="{{ $item['target'] }}">{{ $item['title'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="clearfix" style="height: 10px;"></div>
                    <form class="form-horizontal" role="form">

                        @foreach($crud['read_fields'] as $key)
                            <?php
                            $item = $crud['data_type'][$key];
                            $value = $item['value'];
                            ?>

                            @if($key == 'id')
                                    <?php continue; ?>
                            @endif

                            <div class="form-group">
                                <label for="id-{{ $item['column_name'] }}" class="col-lg-2 control-label">{{ $item['column_text'] }}</label>
                                <div class="col-lg-10">
                                    @if($item['input_type'] == 'join')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @endif

                                    @if($item['input_type'] == 'image')
                                            @if($item['value'] == null or $item['value'] == '' or !file_exists(public_path($item['target_dir'].'/'.$item['value']))) <p><em>null</em></p> @else
                                            <p>Download : <a target="_blank" href="{{ asset($item['target_dir'].'/'.$item['value']) }}">{{ $value }}</a></p>
                                            <p class="form-control-static"><img class="img-responsive" src="{{ ImageSrc::path('/'.$item['target_dir'].'/'.$item['value'], 'resize', 1000) }}" /></p>
                                        @endif
                                    @endif

                                    @if($item['input_type'] == 'file')
                                        @if($item['value'] !== null and $item['value'] !== '' and file_exists(public_path($item['target_dir'].'/'.$item['value'])))
                                            <p>Download : <a target="_blank" href="{{ asset($item['target_dir'].'/'.$item['value']) }}">{{ $value }}</a></p>
                                        @else
                                            <p>File <strong>{{ $item['value'] }}</strong> doesn't exist.</p>
                                        @endif
                                    @endif

                                    @if($item['input_type'] == 'select')
                                        <?php
                                        $value = '';
                                        foreach($item['options'] as $key=>$opt){

                                            if($key == $item['value']){
                                                $value = $opt;
                                                break;
                                            }
                                        }
                                        ?>
                                            <p class="form-control-static">{{ $value }}</p>

                                    @endif

                                    @if($item['input_type'] == 'textarea')
                                        <p class="form-control-static">{{ $item['value'] }}</p>


                                    @endif

                                    @if($item['input_type'] == 'join_nn')
                                        <p class="form-control-static">{{ $item['value'] }}</p>

                                    @endif

                                    @if($item['input_type'] == 'numeric')
                                        <p class="form-control-static">{{ $item['value'] }}</p>

                                    @endif

                                    @if($item['input_type'] == 'decimal')
                                        <p class="form-control-static">{{ $item['value'] }}</p>

                                    @endif

                                    @if($item['input_type'] == 'text')
                                        <p class="form-control-static">{{ $item['value'] }}</p>

                                    @endif

                                    @if($item['input_type'] == 'enum')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @endif

                                    @if($item['input_type'] == 'date')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @endif

                                    @if($item['input_type'] == 'datetime')
                                        <p class="form-control-static">{{ date('d F Y H:i:s', strtotime($item['value'])) }}</p>
                                    @endif

                                    @if($item['input_type'] == 'richarea')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @endif

                                    @if($item['input_type'] == 'hidden')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @endif

                                    @if($item['input_type'] == 'location')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                        <div class="map-field" id="map-{{ $item['column_name'] }}" data-location="{{ $item['value'] }}" style="height: 300px;"></div>
                                    @endif

                                </div>
                            </div>
                        @endforeach



                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <a href="{{ url($crud['uri']) }}" class="btn btn-default">{{ $crud['back_btn_text'] }}</a>
                            </div>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>


@stop

@section('crud_js')
    <script src="{{ asset('vendor/timenz/crud/js/crud.js') }}"></script>

    @if($crud['is_load_map_libs'])
        <script src="http://maps.google.com/maps/api/js?sensor=false&libraries=geometry&v=3.7"></script>
        <script type="text/javascript" src="{{ asset('vendor/timenz/crud/js/maplace.min.js') }}"></script>

        <script>
            $(function(){
                $('.map-field').each(function(){
                    var id = $(this).attr('id');
                    var loc = $(this).attr('data-location');

                    var lat = 0, lon = 0;
                    if(loc.isValidLocation()){
                        loc = loc.split(',');
                        lat = loc[0];
                        lon = loc[1];
                    }

                    new Maplace({
                        map_options: {
                            zoom: 10,
                            scrollwheel: false
                        },
                        locations: [{
                            lat: lat,
                            lon: lon
                        }],
                        map_div: '#' + id,
                        controls_on_map: false
                    }).Load();
                });
            });

        </script>
    @endif

@endsection
