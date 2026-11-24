<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Database Backup</h5>
                    <small class="text-muted">
                        <i class="ti ti-database"></i> 
                        Connection: <strong>{{ ucfirst($dbConnection) }}</strong> | 
                        Database: <strong>{{ $dbName }}</strong>
                    </small>
                </div>
                <form action="{{ route('admin.database-backup.create') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Create a new database backup?');">
                        <i class="ti ti-database-export"></i> Create New Backup
                    </button>
                </form>
            </div>

            

            <!-- Info Card -->
            <div class="card mb-4 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info"><i class="ti ti-info-circle me-2"></i>About Database Backups</h6>
                    <ul class="mb-0 small">
                        <li>Backups are stored securely on the server in <code>storage/app/backups/</code></li>
                        <li>For MySQL/PostgreSQL databases, backups are compressed (gzip) to save space</li>
                        <li>SQLite databases are backed up as a direct copy of the database file</li>
                        <li><strong>Recommended:</strong> Download backups regularly and store them in a safe location off-server</li>
                        <li>To restore a backup, you'll need server access. See the restoration instructions below.</li>
                    </ul>
                </div>
            </div>

            <!-- Backups Table -->
            <div class="card tbl-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-files me-2"></i>Available Backups ({{ count($backups) }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Filename</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $index => $backup)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <code>{{ $backup['filename'] }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $backup['type'] }}</span>
                                        </td>
                                        <td>{{ $backup['size'] }}</td>
                                        <td>
                                            @if($backup['created_at'])
                                                {{ $backup['created_at']->format('M d, Y H:i:s') }}
                                                <br>
                                                <small class="text-muted">{{ $backup['created_at']->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <x-admin.table-actions :actions="[
                                                [
                                                    'type' => 'link',
                                                    'label' => 'Download',
                                                    'icon' => 'ti ti-download',
                                                    'url' => route('admin.database-backup.download', $backup['filename']),
                                                ],
                                                [
                                                    'type' => 'form',
                                                    'label' => 'Delete',
                                                    'icon' => 'ti ti-trash',
                                                    'url' => route('admin.database-backup.destroy', $backup['filename']),
                                                    'method' => 'DELETE',
                                                    'confirm' => 'Are you sure you want to delete this backup? This action cannot be undone.',
                                                    'danger' => true,
                                                ],
                                            ]" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="ti ti-database-off" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="text-muted mt-2 mb-0">No backups found. Click "Create New Backup" to create your first backup.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Restoration Instructions -->
            <div class="card mt-4">
                <div class="card-header bg-warning-subtle">
                    <h5 class="mb-0"><i class="ti ti-arrow-back-up me-2"></i>How to Restore a Backup</h5>
                </div>
                <div class="card-body">
                    @if($dbConnection === 'sqlite')
                        <h6 class="text-primary">SQLite Restoration</h6>
                        <ol class="mb-0">
                            <li>Download the backup file (e.g., <code>backup_2025-01-01_12-00-00.sqlite</code>)</li>
                            <li>Access your server via SSH or FTP</li>
                            <li>Stop your application temporarily (optional but recommended)</li>
                            <li>Navigate to your database directory (usually <code>database/</code>)</li>
                            <li>Rename the current database file as a safety measure:
                                <pre class="bg-light p-2 mt-1"><code>mv database.sqlite database.sqlite.old</code></pre>
                            </li>
                            <li>Copy the backup file and rename it to <code>database.sqlite</code>:
                                <pre class="bg-light p-2 mt-1"><code>cp /path/to/backup_2025-01-01_12-00-00.sqlite database/database.sqlite</code></pre>
                            </li>
                            <li>Restart your application</li>
                        </ol>
                    @elseif($dbConnection === 'mysql' || $dbConnection === 'mariadb')
                        <h6 class="text-primary">MySQL/MariaDB Restoration</h6>
                        <ol class="mb-0">
                            <li>Download the backup file (e.g., <code>backup_2025-01-01_12-00-00.sql.gz</code>)</li>
                            <li>Access your server via SSH</li>
                            <li>Decompress the backup file:
                                <pre class="bg-light p-2 mt-1"><code>gunzip backup_2025-01-01_12-00-00.sql.gz</code></pre>
                            </li>
                            <li>Import the SQL file into your database:
                                <pre class="bg-light p-2 mt-1"><code>mysql -u YOUR_USERNAME -p YOUR_DATABASE < backup_2025-01-01_12-00-00.sql</code></pre>
                            </li>
                            <li>You will be prompted to enter your MySQL password</li>
                        </ol>
                    @elseif($dbConnection === 'pgsql')
                        <h6 class="text-primary">PostgreSQL Restoration</h6>
                        <ol class="mb-0">
                            <li>Download the backup file (e.g., <code>backup_2025-01-01_12-00-00.sql.gz</code>)</li>
                            <li>Access your server via SSH</li>
                            <li>Decompress the backup file:
                                <pre class="bg-light p-2 mt-1"><code>gunzip backup_2025-01-01_12-00-00.sql.gz</code></pre>
                            </li>
                            <li>Import the SQL file into your database:
                                <pre class="bg-light p-2 mt-1"><code>psql -U YOUR_USERNAME -d YOUR_DATABASE -f backup_2025-01-01_12-00-00.sql</code></pre>
                            </li>
                        </ol>
                    @else
                        <p class="text-muted mb-0">Restoration instructions for {{ ucfirst($dbConnection) }} database are not available. Please consult your database documentation.</p>
                    @endif
                </div>
            </div>

            <!-- Best Practices -->
            <div class="card mt-4">
                <div class="card-header bg-success-subtle">
                    <h5 class="mb-0"><i class="ti ti-bulb me-2"></i>Best Practices</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="ti ti-calendar-event me-2 text-primary"></i>Regular Backups</h6>
                            <p class="small text-muted">Create backups regularly based on how often your data changes. For active systems, daily or weekly backups are recommended.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="ti ti-cloud-download me-2 text-primary"></i>Off-site Storage</h6>
                            <p class="small text-muted">Download backups and store them in a separate location (cloud storage, external drive) in case of server failure.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="ti ti-test-pipe me-2 text-primary"></i>Test Restores</h6>
                            <p class="small text-muted">Periodically test your backup restoration process on a staging environment to ensure backups are working correctly.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="ti ti-trash me-2 text-primary"></i>Clean Up Old Backups</h6>
                            <p class="small text-muted">Remove old backups you no longer need to free up server storage space. Keep at least the last 3-5 backups.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-admin-layout>

