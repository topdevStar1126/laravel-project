@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            @if (@systemDetails()['version'] <= 1.1)
            <div class="form-group col-md-12">
                <div class="alert alert-danger p-3">
                    @lang("Please be very careful about changing the File Upload Server. If you change the setting of File Upload Server, make sure to copy all product files to your new server. Otherwise, anything related to the product won't be shown on the site.")
                </div>
            </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Site Title')</label>
                                    <input class="form-control" type="text" name="site_name" required value="{{ $general->site_name }}">
                                </div>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 position-relative">
                                <label> @lang('Timezone')</label>
                                <select class="select2-basic" name="timezone">
                                    @foreach ($timezones as $key => $timezone)
                                        <option value="{{ @$key }}">{{ __($timezone) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label> @lang('Site Base Color')</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0 border-0">
                                        <input type='text' class="form-control colorPicker" value="{{ $general->base_color }}" />
                                    </span>
                                    <input type="text" class="form-control colorCode" name="base_color" value="{{ $general->base_color }}" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group ">
                                    <label>@lang('Currency')</label>
                                    <input class="form-control" type="text" name="cur_text" required value="{{ $general->cur_text }}">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group ">
                                    <label>@lang('Currency Symbol')</label>
                                    <input class="form-control" type="text" name="cur_sym" required value="{{ $general->cur_sym }}">
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="form-group">
                                    <label>@lang('File Upload Server')</label>
                                    <select class="form-control" name="file_server">
                                        <option value="{{ Status::SERVER_CURRENT }}" @selected($general->file_server == Status::SERVER_CURRENT)>
                                            @lang('Current Server')
                                        </option>
                                        <option value="{{ Status::SERVER_FTP }}" @selected($general->file_server == Status::SERVER_FTP)>
                                            @lang('FTP')
                                        </option>
                                        <option value="{{ Status::SERVER_WASABI }}" @selected($general->file_server == Status::SERVER_WASABI)>
                                            @lang('Wasabi')
                                        </option>
                                        <option value="{{ Status::SERVER_DIGITAL_OCEAN }}" @selected($general->file_server == Status::SERVER_DIGITAL_OCEAN)>
                                            @lang('Digital Ocean')
                                        </option>
                                    </select>
                                    <span class="text--small text--info d-none storage--info"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .select2-container {
            z-index: 0 !important;
        }
    </style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/viseradmin/js/spectrum.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/viseradmin/css/spectrum.css') }}">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.colorPicker').spectrum({
                color: $(this).data('color'),
                change: function(color) {
                    $(this).parent().siblings('.colorCode').val(color.toHexString().replace(/^#?/, ''));
                }
            });

            $('.colorCode').on('input', function() {
                var clr = $(this).val();
                $(this).parents('.input-group').find('.colorPicker').spectrum({
                    color: clr,
                });
            });

            $('select[name=timezone]').val("{{ $currentTimezone }}").select2();
            $('.select2-basic').select2({
                dropdownParent: $('.position-relative')
            });

            $("[name=file_server]").on("change", function(e) {
                let value = $(this).val();
                let text = $(this).find("option:selected").text();
                if (value == 1) {
                    $('.storage--info').addClass("d-none");
                } else {
                    $('.storage--info').text(`Only the item's main file will be stored in the ${text}`);
                    $('.storage--info').removeClass("d-none");
                };
            }).change();
        })(jQuery);
    </script>
@endpush
