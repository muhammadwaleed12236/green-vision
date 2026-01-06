@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Staff Recoveries</h4>
                    <h6>All Staff Payment Records</h6>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success">
                    <strong>Success!</strong> {{ session('success') }}.
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Staff Name</th>
                                    <th>Amount Paid</th>
                                    <th>Date</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Recoveries as $key => $recovery)
                                    <tr id="recovery-row-{{ $recovery->id }}">
                                        <td>{{ $key + 1 }}</td>
<td>{{ $recovery->person_name }}</td>
                                        <td class="amount_paid">{{ number_format($recovery->amount_paid, 0) }}</td>
                                        <td>{{ $recovery->date }}</td>
                                        <td class="remarks">{{ $recovery->remarks }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary text-white"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRecoveryModal{{ $recovery->id }}">
                                                Edit
                                            </button>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editRecoveryModal{{ $recovery->id }}" tabindex="-1"
                                                aria-labelledby="editRecoveryModalLabel{{ $recovery->id }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST"
                                                            action="{{ route('staff-recovery.update', $recovery->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="editRecoveryModalLabel{{ $recovery->id }}">
                                                                    Edit Staff Recovery
                                                                </h5>
                                                                <button type="button" class="btn-close text-black"
                                                                    data-bs-dismiss="modal" aria-label="Close">X</button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Staff Name</label>
                                                                   <input type="text" class="form-control"
    value="{{ $recovery->person_name }}"
    readonly>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Current Amount Paid</label>
                                                                    <input type="text" class="form-control"
                                                                        value="{{ number_format($recovery->amount_paid, 0) }}"
                                                                        readonly>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Adjustment Type</label>
                                                                    <select name="adjust_type" class="form-select" required>
                                                                        <option value="">Select Type</option>
                                                                        <option value="plus">Plus (+)</option>
                                                                        <option value="minus">Minus (-)</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Adjustment Amount</label>
                                                                    <input type="number" name="adjust_amount"
                                                                        class="form-control" min="0" step="any" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Date</label>
                                                                    <input type="date" name="date" class="form-control"
                                                                        value="{{ $recovery->date }}" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Remarks</label>
                                                                    <textarea name="remarks"
                                                                        class="form-control">{{ $recovery->remarks }}</textarea>
                                                                </div>

                                                                <div class="alert alert-danger d-none"
                                                                    id="editRecoveryError{{ $recovery->id }}"></div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-primary">
                                                                    Update Recovery
                                                                </button>
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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

@include('admin_panel.include.footer_include')
