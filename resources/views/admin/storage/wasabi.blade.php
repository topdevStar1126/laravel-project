@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">

        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Driver Name')</label>
                                    <input class="form-control form-control-lg" name="wasabi[driver]" type="text" value="{{ @$general->wasabi->driver }}" required>
                                    <code>(@lang('s3'))</code>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('API Key')</label>
                                    <input class="form-control form-control-lg" name="wasabi[key]" type="text" value="{{ @$general->wasabi->key }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Secret Key')</label>
                                    <input class="form-control form-control-lg" name="wasabi[secret]" type="text" value="{{ @$general->wasabi->secret }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Region')</label>
                                    <input class="form-control form-control-lg" name="wasabi[region]" type="text" value="{{ @$general->wasabi->region }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Bucket Name')</label>
                                    <input class="form-control form-control-lg" name="wasabi[bucket]" type="text" value="{{ @$general->wasabi->bucket }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('End point')</label>
                                    <input class="form-control form-control-lg" name="wasabi[endpoint]" type="text" value="{{ @$general->wasabi->endpoint }}" required>
                                    <code>(@lang('https://s3.region-name.wasabisys.com'))</code>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Update')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
