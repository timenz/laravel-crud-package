@extends($crud['master_blade'])
<?php $load_mce = false; ?>
@section('konten')
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
                                <label for="id-{{ $item['column_name'] }}" class="col-lg-2 control-label">{{ $item['column_text'] }}</label>
                                <div class="col-lg-10">

                                    @if($item['input_type'] == 'text')
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    @endif

                                    @if($item['input_type'] == 'numeric')
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6 numeric"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    @endif

                                    @if($item['input_type'] == 'decimal')
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6 decimal"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    @endif

                                    @if($item['input_type'] == 'money')
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6 money"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    @endif

                                    @if($item['input_type'] == 'image')
                                        <input type="file"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6"
                                               id="id-{{ $item['column_name'] }}"
                                               value="" />
                                    @endif

                                    @if($item['input_type'] == 'file')
                                        <input type="file"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6"
                                               id="id-{{ $item['column_name'] }}"
                                               value="" />
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
                                        <select name="{{ $item['column_name'] }}"
                                                class="cl-{{ $item['column_name'] }} form-control chosen-select"
                                                id="id-{{ $item['column_name'] }}">{{ $str_option }}</select>

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
                                        <select name="{{ $item['column_name'] }}"
                                                class="cl-{{ $item['column_name'] }} form-control"
                                                id="id-{{ $item['column_name'] }}">{{ $str_option }}</select>

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
                                        <select name="{{ $item['column_name'] }}"
                                                class="cl-{{ $item['column_name'] }} form-control"
                                                id="id-{{ $item['column_name'] }}">{{ $str_option }}</select>

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
                                        <select name="{{ $item['column_name'] }}[]"
                                                class="cl-{{ $item['column_name'] }} form-control chosen-select" multiple
                                                id="id-{{ $item['column_name'] }}">{{ $str_option }}</select>

                                    @endif

                                    @if($item['input_type'] == 'textarea')
                                        <textarea
                                                name="{{ $item['column_name'] }}"
                                                class=" cl-{{ $item['column_name'] }} form-control"
                                                id="id-{{ $item['column_name'] }}" >{{ $value }}</textarea>
                                    @endif

                                    @if($item['input_type'] == 'richarea')
                                        <?php $load_mce = true; ?>
                                        <textarea
                                                name="{{ $item['column_name'] }}"
                                                class=" cl-{{ $item['column_name'] }} form-control richarea"
                                                id="id-{{ $item['column_name'] }}" >{{ $value }}</textarea>
                                    @endif

                                    @if($item['input_type'] == 'date')
                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6 field-date"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" />
                                    @endif


                                    @if(isset($crud['errors'][$key]))
                                        <div class="error">
                                            @foreach($crud['errors'][$key] as $err)
                                                * {{ $err }} </br>
                                            @endforeach
                                        </div>
                                    @endif


                                </div>
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


@endsection

@section('js')
    @if($crud['action'] == 'create' or $crud['action'] == 'edit')
        @if($crud['is_load_mce_libs'])
            <script type="text/javascript" src="{{ url('') }}/tinymce/tinymce.min.js"></script>
            <script type="text/javascript" src="{{ url('') }}/tinymce/tinymce_editor.js"></script>
            <script type="text/javascript">
                editor_config.selector = "textarea.richarea";
                tinymce.init(editor_config);
            </script>
        @endif
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
@endsection