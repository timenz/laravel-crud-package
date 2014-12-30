
@extends($master_blade)

@section('konten')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $title }}</h3>
                </div>
                <div class="panel-body">

                    <form class="form-horizontal" role="form" method="post" action="{{ url('crud_update/'.$model_name.'/'.$method_name.'/'.$id) }}" accept-charset="UTF-8">

                        <input name="_method" type="hidden" value="PUT">
                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                        <?php
                            $input_old = Input::old();
                            $old_input_exist = false;
                            if(count($input_old) > 0){
                                $old_input_exist = true;
                            }
                        ?>
                        @foreach($edit_fields as $key)

                            <?php
                                    $item = $data_type[$key];
                                    $value = $item['value'];
                                    if($old_input_exist){
                                        $value = $input_old[$key];
                                    }
                            ?>

                            @if($key == 'id')
                                @continue
                            @endif

                            @if($item['input_type'] == 'hidden')
                                @continue
                            @endif

                            <div class="form-group @if(isset($errors[$key])) has-error @endif">
                                <label for="id-{{ $item['column_name'] }}" class="col-lg-2 control-label">{{ $item['column_text'] }}</label>
                                <div class="col-lg-10">
                                    @if($item['input_type'] == 'text')

                                        <input type="text"
                                               name="{{ $item['column_name'] }}"
                                               class="cl-{{ $item['column_name'] }} form-control"
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

                                    @if(isset($errors[$key]))
                                        <div class="error">
                                            @foreach($errors[$key] as $err)
                                                * {{ $err }} </br>
                                            @endforeach
                                        </div>
                                    @endif


                                </div>
                            </div>

                        @endforeach



                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <a href="{{ url('crud/'.$model_name.'/'.$method_name) }}" class="btn btn-default">{{ $back_btn_text }}</a>
                                <button type="submit" class="btn btn-primary">{{ $edit_btn_text }}</button>
                            </div>
                        </div>

                        {{--<input type="hidden"--}}
                               {{--name="id"--}}
                               {{--class="cl-id"--}}
                               {{--id="id-id"--}}
                               {{--value="{{ $data_type['id']['value'] }}" />--}}

                        @foreach($edit_fields as $key)

                            <?php $item = $data_type[$key]; ?>

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
