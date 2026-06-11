@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                @if(Auth::check() && Auth::user()->usertype === 'admin')
                <div class="page-title">
                    <h4>Distributor Sales Management</h4>
                    <h6>Manage Distributor Sales Efficiently</h6>
                </div>
                @elseif(Auth::check() && Auth::user()->usertype === 'distributor')
                <div class="page-title">
                    <h4>Distributor Purchased Management</h4>
                </div>
                @endif
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success">
                        <strong>Success!</strong> {{ session('success') }}.
                    </div>
                    @endif

                    @if (session()->has('error'))
                    <div class="alert alert-danger">
                        <strong>Error!</strong> {{ session('error') }}.
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer | Owner</th>
                                    <th>City | Area</th>
                                    <th>Address | Phone</th>
                                    <th>Booker</th>
                                    <th>Saleman</th>
                                    <th>Assigned To</th>
                                    <th>Category</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($Sales as $sale)
                                <tr class="{{ $sale->cancel_status == 1 ? 'table-secondary' : '' }}">
                                    <td>{{ $sale->invoice_number }}
                                        @if($sale->return_status == 1)
                                        <span class="badge bg-danger text-white ms-2">Returned</span>
                                        @endif
                                        @if($sale->cancel_status == 1)
                                        <span class="badge bg-dark text-white ms-2">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>{{ $sale->Date }}</td>
                                    <td>{{ $sale->distributor->Customer ?? 'N/A' }} <br> {{ $sale->distributor->Owner ?? 'N/A' }}</td>
                                    <td>{{ $sale->distributor_city }} <br> {{ $sale->distributor_area }}</td>
                                    <td>{{ $sale->distributor_address }} <br> {{ $sale->distributor_phone }}</td>
                                    <td>{{ $sale->Booker }}</td>
                                    <td>{{ $sale->Saleman }}</td>
                                    <td>
                                        @if($sale->assignedSalesman)
                                            <span class="badge bg-info text-white">{{ $sale->assignedSalesman->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                        $categories = json_decode($sale->category, true);
                                        @endphp
                                        {{ is_array($categories) ? implode(', ', $categories) : $categories }}
                                    </td>
                                    <td>
                                        @php
                                        $items = json_decode($sale->item, true);
                                        @endphp
                                        {{ is_array($items) ? implode(', ', $items) : $items }}
                                    </td>
                                    <td>{{ number_format($sale->net_amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('sale.invoice', $sale->id) }}" class="btn btn-dark btn-sm text-white">
                                            Invoice
                                        </a>

                                        @if(Auth::check() && Auth::user()->usertype === 'admin')

                                        @if($sale->cancel_status == 0)
                                        {{-- Assign Button --}}
                                        <button class="btn btn-success btn-sm btn-assign"
                                            data-id="{{ $sale->id }}"
                                            data-assigned="{{ $sale->assigned_salesman_id ?? '' }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#assignModal">
                                            Assign
                                        </button>

                                        {{-- Cancel Button --}}
                                        <button class="btn btn-warning btn-sm text-dark btn-cancel-sale" data-id="{{ $sale->id }}">
                                            Cancel
                                        </button>

                                        <a href="{{ route('sale.edit', $sale->id) }}" class="btn btn-primary btn-sm text-white">
                                            Edit
                                        </a>
                                        @endif

                                        <button class="btn btn-danger btn-sm delete-sale" data-id="{{ $sale->id }}">
                                            Delete
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Assign Salesman Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">Assign Salesman to Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_salesman_id" class="form-label fw-semibold">Select Salesman</label>
                        <select name="assigned_salesman_id" id="assigned_salesman_id" class="form-select" required>
                            <option value="">-- Select Salesman --</option>
                            @foreach($Staffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ---- Delete Sale ----
    const deleteRoute = "{{ route('sale.delete', ['id' => '__id__']) }}";

    $('.delete-sale').on('click', function() {
        let saleId = $(this).data('id');
        let finalRoute = deleteRoute.replace('__id__', saleId);

        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the invoice and update ledger!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = finalRoute;
            }
        });
    });

    // ---- Cancel Sale ----
    const cancelRoute = "{{ route('sale.cancel', ['id' => '__id__']) }}";

    $('.btn-cancel-sale').on('click', function() {
        let saleId = $(this).data('id');
        let finalRoute = cancelRoute.replace('__id__', saleId);

        Swal.fire({
            title: 'Cancel this Sale?',
            text: "Stock will be restored and ledger will be reversed. The sale record will remain.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f0ad4e',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = finalRoute;
            }
        });
    });

    // ---- Assign Salesman ----
    const assignRouteBase = "{{ route('sale.assign', ['id' => '__id__']) }}";

    $('.btn-assign').on('click', function() {
        let saleId    = $(this).data('id');
        let assigned  = $(this).data('assigned');
        let formRoute = assignRouteBase.replace('__id__', saleId);

        $('#assignForm').attr('action', formRoute);

        // Pre-select assigned salesman if already set
        if (assigned) {
            $('#assigned_salesman_id').val(assigned);
        } else {
            $('#assigned_salesman_id').val('');
        }
    });
</script>