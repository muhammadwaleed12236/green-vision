@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h4 class="fw-bold">Company Settings</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Company Logo</label>
                                <input type="file" name="company_logo" class="form-control" accept="image/*">
                                @if($appSettings['company_logo'])
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="Logo" style="max-height: 80px;">
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Secondary Logo (e.g. PEC Certified)</label>
                                <input type="file" name="secondary_logo" class="form-control" accept="image/*">
                                @if(!empty($appSettings['secondary_logo']))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $appSettings['secondary_logo']) }}" alt="Secondary Logo" style="max-height: 80px;">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ $appSettings['company_name'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="company_phone" class="form-control" value="{{ $appSettings['company_phone'] }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Address 1 (Branch 1)</label>
                                <textarea name="company_address" class="form-control" rows="3">{{ $appSettings['company_address'] }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Address 2 (Branch 2 - Optional)</label>
                                <textarea name="company_address_2" class="form-control" rows="3">{{ $appSettings['company_address_2'] }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Website Link</label>
                                <input type="text" name="company_website" class="form-control" value="{{ $appSettings['company_website'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Social Media Account (e.g. facebook)</label>
                                <input type="text" name="company_social" class="form-control" value="{{ $appSettings['company_social'] }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Invoice Terms & Conditions (One rule per line)</label>
                                <textarea name="invoice_terms" class="form-control" rows="5">{{ $appSettings['invoice_terms'] }}</textarea>
                                <small class="text-muted">These terms will appear at the bottom of estimates, bookings, and sale invoices. Enter one term per line to display as a bulleted list.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
