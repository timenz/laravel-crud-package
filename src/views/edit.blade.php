
@extends($crud['master_blade'])

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
                                @if($item['show_at_edit'] == true)
                                    <a href="{{ $item['url'] }}" class="{{ $item['class'] }}" target="{{ $item['target'] }}">{{ $item['title'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="clearfix" style="height: 10px;"></div>
                    <form
                            class="form-horizontal"
                            role="form" method="post"
                            action="{{ url($crud['uri'].'/'.$crud['id']) }}"
                            accept-charset="UTF-8"
                            enctype="multipart/form-data">

                        <input name="_method" type="hidden" value="PUT">
                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                        <?php
                            $input_old = Input::old();
                            $old_input_exist = false;
                            if(count($input_old) > 0){
                                $old_input_exist = true;
                            }
                        ?>
                        @foreach($crud['edit_fields'] as $key)

                            <?php
                                    $item = $crud['data_type'][$key];
                                    $value = $item['value'];
                                    if($old_input_exist){
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
                                        <?php if(isset($item['renew_on_update'])){
                                            if($item['renew_on_update']){
                                                $value = $item['default_value'];
                                            }
                                        } ?>
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

                                    @if($item['input_type'] == 'image')
                                        <p class="form-control-static">
                                            <p>Download : <a target="_blank" href="{{ asset($item['target_dir'].'/'.$item['value']) }}">{{ $value }}</a></p>
                                            <img src="{{ ImageSrc::path('/'.$item['target_dir'].'/'.$item['value'], 'resize', 400) }}" /></p>
                                        <input type="file"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control col-md-6"
                                               id="id-{{ $item['column_name'] }}"
                                               value="" />
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

                                    @if($item['input_type'] == 'enum')
                                        <?php
                                            $option = '<option></option>';
                                            foreach($item['options'] as $opt){
                                                $selected = '';
                                                if($opt == $item['value']){
                                                    $selected = 'selected="selected"';
                                                }
                                                $option .= '<option '.$selected.' value="'.$opt.'">'.$opt.'</option>';
                                            }
                                        ?>

                                        <select
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" >{{ $option }}</select>
                                    @endif

                                    @if($item['input_type'] == 'select')
                                        <?php
                                            $option = '<option></option>';
                                            foreach($item['options'] as $key=>$opt){
                                                $selected = '';
                                                if($key == $item['value']){
                                                    $selected = 'selected="selected"';
                                                }
                                                $option .= '<option '.$selected.' value="'.$key.'">'.$opt.'</option>';
                                            }
                                        ?>

                                        <select
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control"
                                               id="id-{{ $item['column_name'] }}"
                                               value="{{ $value }}" >{{ $option }}</select>
                                    @endif

                                    @if($item['input_type'] == 'join')
                                        <?php
                                        $arr_option = $item['options'];
                                        $str_option = '<option>Pilih</option>';

                                        foreach($arr_option as $option){
                                            $selected = '';
                                            if($option->id == $value){$selected = 'selected="selected"';}
                                            $str_option .= '<option value="'.$option->id.'" '.$selected.'>'.$option->{$item['related_field']}.'</option>';
                                        }
                                        ?>
                                        <select name="{{ $item['column_name'] }}"
                                                class="cl-{{ $item['column_name'] }} form-control"
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
                                <button type="submit" class="btn btn-primary">{{ $crud['edit_btn_text'] }}</button>
                            </div>
                        </div>



                        @foreach($crud['edit_fields'] as $key)

                            <?php $item = $crud['data_type'][$key]; ?>

                            @if($item['input_type'] == 'hidden')
                                <?php
                                    $value = $item['value'];
                                    if($item['renew_on_update']){
                                        $value = $item['default_value'];
                                    } ?>
                                <input type="hidden"
                                       name="{{ $item['column_name'] }}"
                                       class="cl-{{ $item['column_name'] }}"
                                       id="id-{{ $item['column_name'] }}"
                                       value="{{ $value }}" />
                            @endif

                        @endforeach
                    </form>


                </div>
            </div>
        </div>
    </div>


@endsection

@section('js')
    @if($load_mce)<script src="{{ asset('libs/tinymce/tinymce.min.js') }}"></script>@endif
    <script>
        @if($load_mce)
        tinymce.init({
            selector: "textarea.richarea",
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
        });
        @endif
    </script>
@endsection