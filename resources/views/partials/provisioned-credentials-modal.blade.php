@php
    $credentials = session('provisioned_credentials', []);
@endphp

@if(is_array($credentials) && $credentials !== [])
    <div class="modal fade" id="provisionedCredentialsModal" tabindex="-1" aria-labelledby="provisionedCredentialsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="provisionedCredentialsModalLabel">Student login details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="provisioned-credentials-print">
                    <p class="small text-muted mb-3">Write these down now. The PIN will not be shown again.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Admission number</th>
                                    <th>PIN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($credentials as $credential)
                                    <tr>
                                        <td>{{ $credential['name'] ?? '' }}</td>
                                        <td><code>{{ $credential['login_id'] ?? '' }}</code></td>
                                        <td><code>{{ $credential['pin'] ?? '' }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Print</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        @media print {
            body * { visibility: hidden; }
            #provisioned-credentials-print, #provisioned-credentials-print * { visibility: visible; }
            #provisioned-credentials-print { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('provisionedCredentialsModal');
            if (el && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        });
    </script>
@endif
