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
                                    @elseif($item['input_type'] == 'image')
                                        <p>Download : <a target="_blank" href="{{ asset($item['target_dir'].'/'.$item['value']) }}">{{ $value }}</a></p>
                                        <p class="form-control-static"><img src="{{ ImageSrc::path('/'.$item['target_dir'].'/'.$item['value'], 'resize', 400) }}" /></p>
                                    @elseif($item['input_type'] == 'select')
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

                                    @elseif($item['input_type'] == 'textarea')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @else
                                        <p class="form-control-static">{{ $item['value'] }}</p>
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


@endsection
