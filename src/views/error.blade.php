
@extends($crud['master_blade'])

@section('konten')
    <div class="row">
        <div class="col-md-12">


            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Error</h3>
                </div>
                <div class="panel-body">
                    <p>{{ $crud['error_text'] }}</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')

@endsection