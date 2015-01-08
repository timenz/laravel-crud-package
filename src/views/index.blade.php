
@extends($crud['master_blade'])

<?php
$x = $crud['lists']['from'];
?>

@section('konten')
    <div class="row">
        <div class="col-md-12">


            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $crud['title'] }}</h3>
                </div>
                <div class="panel-body">
                    @if($crud['message'])
                        <div class="alert alert-success fade in">
                            <a href="#" class="close" data-dismiss="alert">&times;</a>
                            <strong>Selamat!</strong> {{ $crud['message'] }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-xs-7">
                            @if($crud['allow_create'])
                                <a href="{{ url('crud_create/'.$crud['model_name'].'/'.$crud['method_name']) }}" class="btn btn-success">{{ $crud['list_create_text'] }}</a>
                            @endif

                            @if($crud['allow_mass_delete'])
                                <a href="{{ url('crud_mass_delete/'.$crud['model_name'].'/'.$crud['method_name']) }}" class="btn btn-danger sel-many" style="display: none;">{{ $crud['list_mass_delete_text'] }}</a>
                            @endif

                            @if($crud['allow_read'])
                                <a href="#" id="list-btn-read" class="btn btn-default sel-one" style="display: none;">{{ $crud['list_read_text'] }}</a>
                            @endif
                            @if($crud['allow_edit'])
                                <a href="#" id="list-btn-edit" class="btn btn-primary sel-one" style="display: none;">{{ $crud['list_edit_text'] }}</a>
                            @endif
                            @if($crud['allow_delete'])
                                <a href="#" id="list-btn-delete" class="btn btn-warning sel-one" style="display: none;">{{ $crud['list_delete_text'] }}</a>
                            @endif
                            <span id="action_lists"></span>

                        </div>
                        <div class="col-xs-5 text-right">
                            @foreach($crud['external_link'] as $item)
                                @if($item['show_at_index'] == true)
                                    <a href="{{ $item['url'] }}" class="{{ $item['class'] }}" target="{{ $item['target'] }}">{{ $item['title'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="clearfix" style="height: 10px;"></div>

                    <div class="list-table ">
                        <table class="table table-striped">
                            <thead>

                            <tr>
                                <th><input type="checkbox" class="" name="cb-all" id="cb-all"></th>
                                @foreach($crud['columns'] as $item)
                                    <th>{{ $crud['data_type'][$item]['column_text'] }}</th>
                                @endforeach


                                {{--@if($allow_create or $allow_edit or $allow_delete)--}}
                                    {{--<th>Action</th>--}}
                                {{--@endif--}}
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($crud['lists']['data'] as $item)

                                <?php $item = (array)$item;  ?>
                                <tr>
                                    <td><input type="checkbox" class="cb-list" name="" id="cb-list-{{ $item['id'] }}" data-id="{{ $item['id'] }}">
                                    <span style="display: none" id="action-{{ $item['id'] }}">@if(isset($crud['action_lists'][$item['id']]))
                                            {{ json_encode($crud['action_lists'][$item['id']]) }}@endif</span>
                                    </td>
                                    <?php $x++; ?>
                                    @foreach($item as $key=>$subitem)

                                        @if(!in_array($key, $crud['columns']))
                                            @continue
                                        @endif

                                        @if($crud['data_type'][$key]['input_type'] == 'money')
                                            <td class="align-right">{{ number_format((float)$subitem, 2) }}</td>
                                        @elseif($crud['data_type'][$key]['input_type'] == 'join')
                                            <td>{{ $item[$crud['data_type'][$key]['related_field']] }}</td>
                                        @else
                                            <td>{{ $subitem }}</td>
                                        @endif
                                    @endforeach


                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>

                    <div>{{ $crud['paging_links'] }}</div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-confirm-hapus" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Konfirmasi Hapus Data</h4>
                </div>
                <div class="modal-body">
                    <p>Yakin untuk menghapus data yang telah dipilih ?</p>
                </div>
                <div class="modal-footer">

                    <form method="post" action="#" id="form-delete" accept-charset="UTF-8">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        <input type="submit" class="btn btn-primary" value="Hapus" />
                    </form>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


@endsection

@section('js')
<script>
    $(function(){
        cbListAction();

        $('#modal-confirm-hapus').modal({
            show: false,
            backdrop: false
        });

        $('#cb-all').click(function(){
            var prop = $(this).is(':checked');

            $('.cb-list').prop("checked", prop);
            cbListAction();
        });

        $('.cb-list').click(function(){
            var all = $('.cb-list').length;
            var checked = $('.cb-list:checked').length;
            var not_checked = all - checked;
            document.getElementById('cb-all').indeterminate = false;



            if(not_checked == all){
                $('#cb-all').prop("checked", false);
            }else if(all == checked){
                $('#cb-all').prop("checked", true);

            }else{
                document.getElementById('cb-all').indeterminate = true;
            }

            cbListAction();
        });

        $('#list-btn-delete').click(function(e){
            e.preventDefault();
            $('#modal-confirm-hapus').modal('show');
        });

        function cbListAction(){

            var checked = $('.cb-list:checked');
            var id = checked.attr('data-id');
            var act_lists = $('#action-' + id).html();

            //act_lists = JSON.parse(act_lists);

            $('#action_lists').html('');

            if(checked.length == 1){

                $('.sel-one').show();
                $('#list-btn-read').attr('href', '{{ url('crud_read/'.$crud['model_name'].'/'.$crud['method_name']) }}/' + id);
                $('#list-btn-edit').attr('href', '{{ url('crud_edit/'.$crud['model_name'].'/'.$crud['method_name']) }}/' + id);
                $('#form-delete').attr('action', '{{ url('crud_delete/'.$crud['model_name'].'/'.$crud['method_name']) }}/' + id);

                act_lists = JSON.parse(act_lists);

                if(act_lists.length > 0){
                    var str = '';
                    for(i in act_lists){
                        var row = act_lists[i];

                        str += '<a href="'+row.url+'" class="'+row.class+'">'+row.title+'</a>';
                    }
                    $('#action_lists').html(str);
                }
            }else{
                $('.sel-one').hide();
                $('#list-btn-read').attr('href', '#');
                $('#list-btn-edit').attr('href', '#');
                $('#list-btn-delete').attr('href', '#');
            }

            if(checked.length > 0){
                $('.sel-many').show();
            }else{
                $('.sel-many').hide();
            }
        }
    });
</script>
@endsection