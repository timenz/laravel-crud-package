@extends($master_blade)

@section('konten')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $title }}</h3>
                </div>
                <div class="panel-body">

                    <form class="form-horizontal" role="form">

                        @foreach($data_type as $key=>$item)
                            @if($key == 'id')
                                @continue
                            @endif

                            <div class="form-group">
                                <label for="id-{{ $item['column_name'] }}" class="col-lg-2 control-label">{{ $item['column_text'] }}</label>
                                <div class="col-lg-10">
                                    @if($item['input_type'] == 'join')
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @else
                                        <p class="form-control-static">{{ $item['value'] }}</p>
                                    @endif

                                </div>
                            </div>

                        @endforeach


                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <a href="{{ URL::previous() }}" class="btn btn-default">{{ $back_btn_text }}</a>
                            </div>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>


@endsection
