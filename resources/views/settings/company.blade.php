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
                                <div class="mb-2">
                                    @if($appSettings['company_logo'])
                                        <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="Company Logo" style="max-height: 100px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                    @else
                                        <p class="text-muted">No logo uploaded</p>
                                    @endif
                                </div>
                                <input type="file" name="company_logo" class="form-control" accept="image/jpg,image/jpeg,image/png,image/svg+xml">
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
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="company_address" class="form-control" rows="3">{{ $appSettings['company_address'] }}</textarea>
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
