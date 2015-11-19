@extends($crud['master_blade'])
<?php $load_mce = false; ?>
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
                                @if($item['show_at_create'] == true)
                                    <a href="{{ $item['url'] }}" class="{{ $item['class'] }}" target="{{ $item['target'] }}">{{ $item['title'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="clearfix" style="height: 10px;"></div>
                    <form class="form-horizontal" role="form" method="post" action="{{ url($crud['uri']) }}" accept-charset="UTF-8" enctype="multipart/form-data">

                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                        {{--@foreach($data_type as $key=>$item)--}}
                        <?php
                            $input_old = Input::old();

                            $old_input_exist = false;
                            if(count($crud['errors']) > 0){
                                $old_input_exist = true;
                            }
                        ?>
                        @foreach($crud['create_fields'] as $key)

                            <?php
                            $item = $crud['data_type'][$key];
                            $value = '';
                            if($old_input_exist and isset($input_old[$key])){
                                $value = $input_old[$key];
                            }
                            ?>

                            @if($key == 'id')
                                    <?php continue; ?>
                            @endif

                            @if($item['input_type'] == 'hidden')
                                    <?php continue; ?>
                            @endif

                            <div class="form-group @if(isset($crud['errors'][$key])) has-error @endif">
                                <div class="col-md-12">

                                    <label for="id-{{ $item['column_name'] }}" class="control-label">{{ $item['column_text'] }}</label>
                                </div>


                                    @if($item['input_type'] == 'text')
                                    <div class="col-lg-10">
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'location')
                                    <div class="col-lg-10">
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                        <div class="map-field" id="map-{{ $item['column_name'] }}" data-location="{{ $value }}" style="height: 300px;"></div>

                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'readonly')
                                    <div class="col-lg-10">
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'numeric')
                                    <div class="col-lg-10">
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6 numeric"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'decimal')
                                    <div class="col-lg-10">
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6 decimal"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'money')
                                    <div class="col-lg-10">
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6 money"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'image')
                                    <div class="col-lg-10">
                                        <input type="file"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6"
                                               id="id-{{ $item['column_name'] }}"
                                               value="" />
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'file')
                                    <div class="col-lg-10">
                                        <input type="file"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6"
                                               id="id-{{ $item['column_name'] }}"
                                               value="" />
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'enum')
                                        <?php
                                                $arr_option = $item['options'];
                                                $str_option = '<option value="">Pilih</option>';
                                                foreach($arr_option as $option){
                                                    $sel = '';
                                                    if($option == $value){
                                                        $sel = 'selected';
                                                    }
                                                    $str_option .= '<option '.$sel.' value="'.$option.'">'.$option.'</option>';
                                                }
                                            ?>
                                            <div class="col-lg-10">
                                        <select name="{{ $item['column_name'] }}"
                                                class="cl-{{ $item['column_name'] }} form-control chosen-select"
                                                id="id-{{ $item['column_name'] }}">{!! $str_option !!}</select>
                                            </div>

                                    @endif

                                    @if($item['input_type'] == 'select')
                                        <?php
                                                $arr_option = $item['options'];
                                                $str_option = '<option value="">Pilih</option>';
                                                foreach($arr_option as $key=>$option){
                                                    $sel = '';
                                                    if($key == $value){
                                                        $sel = 'selected';
                                                    }
                                                    $str_option .= '<option '.$sel.' value="'.$key.'">'.$option.'</option>';
                                                }
                                            ?>
                                            <div class="col-lg-10">
                                        <select name="{{ $item['column_name'] }}"
                                                class="cl-{{ $item['column_name'] }} form-control chosen-select"
                                                id="id-{{ $item['column_name'] }}">{!! $str_option !!}</select>
                                            </div>

                                    @endif

                                    @if($item['input_type'] == 'join')
                                        <?php
                                                $arr_option = $item['options'];
                                                $str_option = '<option value="">Pilih</option>';
                                                foreach($arr_option as $option){
                                                    $sel = '';
                                                    if($option->id == $value){
                                                        $sel = 'selected';
                                                    }
                                                    $str_option .= '<option '.$sel.' value="'.$option->id.'">'.$option->{$item['related_field']}.'</option>';
                                                }
                                            ?>
                                            <div class="col-lg-10">
                                        <select name="{{ $item['column_name'] }}"
                                                class="cl-{{ $item['column_name'] }} form-control chosen-select"
                                                id="id-{{ $item['column_name'] }}">{!! $str_option !!}</select>
                                            </div>

                                    @endif

                                    @if($item['input_type'] == 'join_nn')
                                        <?php
                                                $arr_option = $item['options'];
                                                $str_option = '<option value="">Pilih</option>';
                                                foreach($arr_option as $option){
                                                    $sel = '';

                                                    if(is_array($value)){
                                                        foreach($value as $opt){
                                                            if($opt == $option->id){
                                                                $sel = 'selected';
                                                            }
                                                        }
                                                    }
                                                    $str_option .= '<option '.$sel.' value="'.$option->id.'">'.$option->option.'</option>';
                                                }
                                            ?>
                                            <div class="col-lg-10">
                                        <select name="{{ $item['column_name'] }}[]"
                                                class="cl-{{ $item['column_name'] }} form-control chosen-select" multiple
                                                id="id-{{ $item['column_name'] }}">{!! $str_option !!}</select>
                                            </div>

                                    @endif

                                    @if($item['input_type'] == 'textarea')
                                    <div class="col-lg-10">
                                        <textarea
                                                name="{{ $item['column_name'] }}"
                                                class=" cl-{{ $item['column_name'] }} form-control"
                                                id="id-{{ $item['column_name'] }}" >{!! $value !!}</textarea>
                                    </div>
                                    @endif

                                    @if($item['input_type'] == 'richarea')
                                        <?php $load_mce = true; ?>
                                            <div class="col-lg-10">
                                        <textarea
                                                name="{{ $item['column_name'] }}"
                                                class=" cl-{{ $item['column_name'] }} form-control richarea"
                                                id="id-{{ $item['column_name'] }}" >{!! $value !!}</textarea>
                                            </div>
                                    @endif

                                    @if($item['input_type'] == 'date')
                                    <div class="col-sm-6 col-md-4">
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control field-date"
                                               size="100"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    </div><div class="col-sm-6 col-md-8"></div>
                                    @endif


                                    @if(isset($crud['errors'][$key]))
                                    <div class="col-lg-10">
                                        <div class="error">
                                            @foreach($crud['errors'][$key] as $err)
                                                * {{ $err }} </br>
                                            @endforeach
                                        </div>
                                    </div>

                                    @endif



                            </div>

                        @endforeach




                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <a href="{{ url($crud['uri']) }}" class="btn btn-default">{{ $crud['back_btn_text'] }}</a>
                                <button type="submit" class="btn btn-primary">{{ $crud['create_btn_text'] }}</button>
                            </div>
                        </div>

                        @foreach($crud['create_fields'] as $key)

                            <?php $item = $crud['data_type'][$key]; ?>

                            @if($item['input_type'] == 'hidden')
                                <input type="hidden"
                                       name="{{ $item['column_name'] }}"
                                       class="cl-{{ $item['column_name'] }}"
                                       id="id-{{ $item['column_name'] }}"
                                       value="{{ $item['default_value'] }}" />
                            @endif

                        @endforeach

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
                    var input  = id.replace('map-', 'id-');
                    var loc = $(this).attr('data-location');
                    var lat = 0, lon = 0;
                    if(loc.isValidLocation()){
                        loc = loc.split(',');
                        lat = loc[0];
                        lon = loc[1];
                    }


                    var place = new Maplace({
                        map_options: {
                            zoom: 10,
                            scrollwheel: false
                        },
                        locations: [{
                            lat: lat,
                            lon: lon
                        }],
                        map_div: '#' + id,
                        controls_on_map: false,
                        listeners: {
                            click: function(map, event) {

                                var location = event.latLng.G.toFixed(6) + ', ' + event.latLng.K.toFixed(6);

                                $('#' + input).val(location);


                                place.SetLocations([{
                                    lat: event.latLng.G,
                                    lon: event.latLng.K
                                }]);
                                place.Load();
                            }
                        }
                    });
                    place.Load();

                    $('#' + input).keyup(function(){

                        var val0 = $(this).val();

                        setTimeout(function(){
                            var val = $('#' + input).val();

                            if(val0 != val){
                                return false;
                            }

                            if(!val.isValidLocation()){
                                return false;
                            }
                            loc = val.split(',');

                            place.SetLocations([{
                                lat: loc[0],
                                lon: loc[1]
                            }]);
                            place.Load();
                        }, 2000);


                    });


                });

            });

        </script>
    @endif
    @if($crud['is_load_mce_libs'])
        <script type="text/javascript" src="{{ asset('vendor/timenz/filemanager-laravel/tinymce/tinymce.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('vendor/timenz/filemanager-laravel/tinymce/tinymce_editor.js') }}"></script>
        <script type="text/javascript">
            editor_config.selector = "textarea.richarea";
            tinymce.init(editor_config);
        </script>
    @endif
<script>
    $(function(){
        $('.chosen-select').chosen();
        $('.field-date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });


</script>
@stop