@extends('web.layouts.app')
@section('style')
    <style>
        .img-container {
            display: table;
            margin: 0 auto;
        }

        .img-container label {
            cursor: pointer;
        }
    </style>
@endsection
@section('content')
    <h1 class="text-center">last {{$count}} articles</h1>
    <table id="tbArticles" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Id</th>
            <th>Title</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div id="mdDelete" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog  modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Delete modal</h4>
                </div>
                <div class="modal-body text-center">
                    <p>Delete an article?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success pull-left" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger pull-right btn-destroy"
                            data-loading-text='<img height="18" src="{{asset('images/loading.gif')}}">'>Delete
                    </button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div id="mdShow" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <blockquote class="article">
                        <h1 class="title"></h1>
                        <br>
                        <img class="local_img_url img-responsive" src="">
                        <br>
                        <span class="description text-justify"></span>
                        <br>
                        <br>
                        <code class="date"></code>
                        <br>
                        <a target="_blank" class="article_url"></a>
                    </blockquote>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success pull-right" data-dismiss="modal">Close</button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div id="mdEdit" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <br>
                    <div class="img-container">
                        <p class="text-center">click on a picture to change image</p>
                        <label for="file">
                            <img class="local_img_url imgUp img-responsive" src="">
                        </label>
                        <input type="file" id="file" class="hidden image">
                    </div>
                    <form class="fm-edit">
                        <input type="hidden" name="id" class="id"/>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control title" id="title" name="title" placeholder="Title">
                        </div>
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="text" class="form-control date" id="date" name="date" placeholder="Date">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputPassword1">Description</label>
                            <textarea class="form-control description" id="description" name="description"
                                      rows="30" placeholder="Description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success pull-left" data-dismiss="modal">Cancel</button>
                    <button data-loading-text='<img height="18" src="{{asset('images/loading.gif')}}">'
                            type="button" class="btn btn-warning pull-right btn-update">Save changes
                    </button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let url = "{{action('Web\Pages\ArticlesController@getAll')}}";
        (function ($, url) {
            "use strict";
            var InitPageScripts = function () {
                this.initialize();
            };

            let p = InitPageScripts.prototype;
            let table = null;

            p.initialize = function () {
                this._initDataTable();
                this._update();
                this._changeImg();
                this._destroy();
                this._onHideBsModal();
            };

            p._initDataTable = function () {
                let _this = this;
                table = $('#tbArticles')
                    .DataTable({
                        processing: true,
                        serverSide: true,
                        searching: false,
                        stateSave: true,
                        ajax: url,
                        columns: [
                            {data: 'id', name: 'id'},
                            {data: 'title', name: 'title'},
                            {
                                width: 138,
                                data: 'actions',
                                name: 'actions',
                                sortable: false,
                                searchable: false,
                                className: 'text-center'
                            },
                        ]
                    })
                    .on('click', '.btn-delete', function (e) {
                        e.preventDefault();

                        $(this).button('loading');

                        $('.btn-destroy').data('url', $(this).data('url'));
                        $('#mdDelete').modal();
                    })
                    .on('click', '.btn-show', function (e) {
                        e.preventDefault();

                        $(this).button('loading');
                        _this._show($(this).data('url'), $(this));
                    })
                    .on('click', '.btn-edit', function (e) {
                        e.preventDefault();

                        $(this).button('loading');
                        _this._edit($(this).data('url'), $(this));
                    });
            };

            p._show = function (url, btnShow) {
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function (data) {
                        for (let key in data) {
                            if (data.hasOwnProperty(key)) {
                                let elem = $('.article').find('.' + key);

                                if (elem.length) {
                                    if (elem.is('a')) {
                                        elem.attr('href', data[key])
                                    }
                                    if (elem.is('img')) {
                                        elem.attr('src', data[key])
                                    }
                                    elem.text(data[key])
                                }
                            }
                        }
                        $('#mdShow').modal();
                    },

                    error: function (jqXhr, json, errorThrown) {
                        let errors = jqXhr.responseJSON;

                        if (errors) {
                            let errorString = "";
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    errorString += " " + errors[key] + " ";
                                }
                            }

                            alert(errorString);
                            console.error(errorString)
                        } else {
                            alert('something went wrong');
                            console.error('something went wrong')
                        }

                        btnShow.button('reset');
                    }
                });
            };

            p._edit = function (url, btnEdit) {
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function (data) {
                        for (let key in data) {
                            if (data.hasOwnProperty(key)) {
                                let elem = $('.fm-edit').find('.' + key);

                                if (elem.length) {
                                    if (elem.is('img')) {
                                        elem.attr('src', data[key])
                                    }
                                    elem.val(data[key])
                                }
                            }
                        }

                        $('.image').data('id', data['id']);
                        $('.local_img_url').attr('src', data['local_img_url']);
                        $('.btn-update').data('update', btnEdit.data('update'));
                        $('#mdEdit').modal();
                    },

                    error: function (jqXhr, json, errorThrown) {
                        let errors = jqXhr.responseJSON;

                        if (errors) {
                            let errorString = "";
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    errorString += " " + errors[key] + " ";
                                }
                            }

                            alert(errorString);
                            console.error(errorString)
                        } else {
                            alert('something went wrong');
                            console.error('something went wrong')
                        }

                        btnEdit.button('reset');
                    }
                });
            };

            p._update = function () {
                $('.btn-update').on('click', function (e) {
                    e.preventDefault();

                    let _this = $(this);
                    _this.button('loading');

                    let formData = new FormData($('.fm-edit')[0]);

                    $.ajax({
                        url: _this.data('update'),
                        type: "POST",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                        },
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            $('#mdEdit').modal('hide');
                            table.ajax.reload();

                            console.info(data.message);
                        },
                        error: function (jqXhr, json, errorThrown) {
                            let errors = jqXhr.responseJSON.errors;

                            _this.button('reset');

                            if (errors) {
                                let errorString = "";

                                for (let key in errors) {
                                    if (errors.hasOwnProperty(key)) {
                                        errorString += " " + errors[key] + " ";
                                    }
                                }

                                alert(errorString);
                                console.error(errorString)
                            } else {
                                alert('something went wrong');
                                console.error('something went wrong')
                            }
                        }
                    });

                })
            };

            p._changeImg = function () {
                $('input[type=file]').on('change', function () {
                    if ($(this).val()) {
                        let formData = new FormData();

                        formData.append('image', $(this).prop('files')[0]);
                        formData.append('id', $(this).data('id'));

                        $.ajax({
                            url: '{{ action('Web\Pages\ArticlesController@changeImg') }}',
                            type: 'POST',
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                            },
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            data: formData,
                            success: function (data) {
                                $('.imgUp').attr('src', data['image_path']);
                            },
                            error: function (jqXhr, json, errorThrown) {
                                let errors = jqXhr.responseJSON.errors;
                                if (errors) {
                                    console.info(errors)
                                    let errorString = "";
                                    for (let key in errors) {
                                        if (errors.hasOwnProperty(key)) {
                                            errorString += " " + errors[key] + " ";
                                        }
                                    }
                                    alert(errorString);
                                    console.error(errorString);
                                } else {
                                    alert('Something went wrong');
                                    console.error('Something went wrong');
                                }
                            }

                        });
                    }
                });
            };

            p._destroy = function () {
                $('.btn-destroy').on('click', function () {
                    let _this = $(this);
                    _this.button('loading');

                    $.ajax({
                        url: _this.data('url'),
                        type: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                        },
                        success: function (data) {
                            $('#mdDelete').modal('hide');
                            table.ajax.reload();

                            console.log(data.message)
                        },

                        error: function (jqXhr, json, errorThrown) {
                            let errors = jqXhr.responseJSON;

                            if (errors) {
                                let errorString = "";
                                for (let key in errors) {
                                    if (errors.hasOwnProperty(key)) {
                                        errorString += " " + errors[key] + " ";
                                    }
                                }

                                alert(errorString);
                                console.error(errorString)
                            } else {
                                alert('something went wrong');
                                console.error('something went wrong')
                            }

                            _this.button('reset');
                            $('#mdDelete').modal('hide');
                        }
                    });

                })
            };

            p._onHideBsModal = function () {
                $('.modal').on('hide.bs.modal', function () {
                    $('.btn').button('reset');
                    $('.btn-destroy').data('url', '');

                    $('.article').children().each(function () {
                        $(this).text('')
                    });

                    $('.fm-edit').children().each(function () {
                        $(this).val('')
                    });

                    $('.image').data('id', '');
                    $('.local_img_url').attr('src', '');
                })
            };

            window.app = new InitPageScripts;
        }(jQuery, url))
    </script>
@endsection