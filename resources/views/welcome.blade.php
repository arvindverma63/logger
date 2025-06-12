<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Android App Logger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-[#d70007] text-white py-4 shadow-lg">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold">ACE TAXIS Logs (Arvind)</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 flex-grow">
        <!-- Add Log Form -->


        <!-- Filter Section -->
        <section class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Filter Logs</h2>
            <!-- Quick Search Bar -->
            <form action="{{ route('logs.index') }}" method="GET" class="mb-4">
                <div class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                        placeholder="Quick search log message...">
                    <button type="submit"
                        class="bg-[#d70007] text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">
                        Search
                    </button>
                </div>
            </form>

            <!-- Filter Form -->
            <form action="{{ route('logs.index') }}" method="GET">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="logType" class="block text-sm font-medium text-gray-700">Log Type</label>
                        <select id="logType" name="type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                            <option value="">All Types</option>
                            <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Error</option>
                            <option value="warn" {{ request('type') == 'warn' ? 'selected' : '' }}>Warn</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Message</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                            placeholder="Search log message...">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full sm:w-auto bg-[#d70007] text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">
                            Apply Filter
                        </button>
                    </div>
                </div>
            </form>

        </section>

        <!-- Logs DataTable -->
        <section class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Application Logs</h2>
            <div class="overflow-x-auto">
                <table id="logsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Source</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($data as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->timestamp->format('Y-m-d H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $log->type == 'info' ? 'bg-blue-100 text-blue-800' : ($log->type == 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($log->type) }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 truncate max-w-xs"
                                    title="{{ $log->message }}">{{ Str::limit($log->message, 50) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->source ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="view-details text-blue-600 hover:text-blue-800"
                                        data-message="{{ $log->message }}">View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $data->links('vendor.pagination.tailwind') }}
            </div>
        </section>

        <!-- Dialog for Message Details -->
        <div id="messageDialog" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6">
                <h3 class="text-lg font-semibold mb-4">Log Message Details</h3>
                <pre id="dialogMessage" class="bg-gray-100 p-4 rounded-md text-sm overflow-auto max-h-96"></pre>
                <div class="mt-4 flex justify-end">
                    <button id="closeDialog"
                        class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">Close</button>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto px-4 text-center">
            <p>© 2025 Android Logger. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Dialog functionality
        const dialog = document.getElementById('messageDialog');
        const dialogMessage = document.getElementById('dialogMessage');
        const closeDialogButton = document.getElementById('closeDialog');
        const viewButtons = document.querySelectorAll('.view-details');

        viewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const message = button.getAttribute('data-message');
                dialogMessage.textContent = message;
                dialog.classList.remove('hidden');
            });
        });

        closeDialogButton.addEventListener('click', () => {
            dialog.classList.add('hidden');
        });
    </script>
</body>

</html>
