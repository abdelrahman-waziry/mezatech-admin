<style>
    .trade-in-report {
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        width: 100%;
    }
    
    @media (min-width: 1024px) {
        .trade-in-report {
            grid-template-columns: 1fr 1fr;
        }
    }

    .report-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .report-card-header {
        padding: 1.25rem 1.5rem;
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .header-icon-wrapper {
        padding: 0.5rem;
        background-color: #e0f2fe; /* blue-100 */
        border-radius: 0.5rem;
        color: #0284c7; /* blue-600 */
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .header-icon-wrapper.purple {
        background-color: #e0e7ff; /* indigo-100 */
        color: #4f46e5; /* indigo-600 */
    }
    
    .header-icon {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    .header-content h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.5;
    }
    
    .header-content p {
        margin: 0.125rem 0 0 0;
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
    }
    
    .report-table-wrapper {
        flex: 1;
        overflow-x: auto;
    }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.875rem;
    }
    
    .report-table th {
        padding: 0.75rem 1.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6b7280;
        background-color: #f9fafb;
        letter-spacing: 0.025em;
    }
    
    .report-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    
    .report-table tr:last-child td {
        border-bottom: none;
    }
    
    .report-table tr:hover {
        background-color: #f9fafb;
    }

    /* Column Specifics */
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .font-bold { font-weight: 700; }
    .font-medium { font-weight: 500; }
    
    /* Text Colors */
    .text-gray-900 { color: #111827; }
    .text-gray-700 { color: #374151; }
    .text-gray-500 { color: #6b7280; }
    .text-gray-300 { color: #d1d5db; }
    
    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.125rem 0.625rem; /* px-2.5 py-0.5 */
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    
    .badge-square {
        border-radius: 0.375rem;
        padding: 0.25rem 0.625rem;
    }
    
    .badge-shadow {
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    /* Badge Variants */
    .badge-success { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; } /* emerald-50 */
    .badge-danger { background-color: #fef2f2; color: #b91c1c; border-color: #fecaca; } /* red-50 */
    .badge-neutral { background-color: #f3f4f6; color: #4b5563; border-color: #e5e7eb; } /* gray-100 */
    
    .badge-danger-outline {
        background-color: white;
        color: #b91c1c;
        border-color: #fecaca;
    }

    /* Price Impacts */
    .impact-value {
        font-weight: 700;
    }
    .impact-negative {
        background-color: #fef2f2;
        color: #dc2626;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        width: fit-content;
        border: 1px solid #fee2e2;
    }
    .impact-neutral {
        color: #d1d5db;
    }

    /* Row Highlights */
    .row-defect {
        background-color: rgba(254, 242, 242, 0.4); /* red-50 with opacity */
    }
    .row-text-defect {
        color: #111827; /* Darker for readability */
    }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        border: 2px dashed #d1d5db;
        border-radius: 1rem;
        padding: 3rem;
        text-align: center;
        background-color: rgba(249, 250, 251, 0.5);
    }
    .empty-icon-wrapper {
        margin: 0 auto 1rem auto;
        height: 4rem;
        width: 4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background-color: #f3f4f6;
    }
    .empty-icon {
        height: 2rem;
        width: 2rem;
        color: #9ca3af;
    }

    /* Dark Mode Overrides (Basic Support via class selector if applicable) */
    .dark .report-card { background-color: #111827; border-color: #1f2937; }
    .dark .report-card-header { background-color: #1f2937; border-color: #374151; }
    .dark .header-content h3 { color: #f9fafb; }
    .dark .header-content p { color: #9ca3af; }
    .dark .report-table th { background-color: #1f2937; color: #9ca3af; }
    .dark .report-table td { border-color: #374151; color: #d1d5db; }
    .dark .report-table tr:hover { background-color: #1f2937; }
    .dark .header-icon-wrapper { background-color: rgba(2, 132, 199, 0.2); color: #7dd3fc; }
    .dark .header-icon-wrapper.purple { background-color: rgba(79, 70, 229, 0.2); color: #a5b4fc; }
    .dark .badge-success { background-color: rgba(6, 95, 70, 0.3); color: #6ee7b7; border-color: rgba(6, 95, 70, 0.5); }
    .dark .badge-danger { background-color: rgba(127, 29, 29, 0.3); color: #fca5a5; border-color: rgba(127, 29, 29, 0.5); }
    .dark .badge-neutral { background-color: #1f2937; color: #9ca3af; border-color: #374151; }
    .dark .badge-danger-outline { background-color: rgba(127, 29, 29, 0.3); color: #fca5a5; border-color: rgba(127, 29, 29, 0.5); }
    .dark .row-defect { background-color: rgba(127, 29, 29, 0.1); }
    .dark .impact-negative { background-color: rgba(127, 29, 29, 0.2); color: #f87171; border-color: rgba(127, 29, 29, 0.4); }
    .dark .empty-state { border-color: #374151; background-color: rgba(17, 24, 39, 0.5); }
    .dark .empty-icon-wrapper { background-color: #1f2937; }
</style>

<div class="trade-in-report">
    {{-- Simplified Answers Section --}}
    @if(isset($report['simplified_report']) && !empty($report['simplified_report']))
        <div class="report-card">
            <div class="report-card-header">
                <div class="header-icon-wrapper">
                    <svg class="header-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                    </svg>
                </div>
                <div class="header-content">
                    <h3>Customer Questionnaire</h3>
                </div>
            </div>
            
            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th scope="col">Question</th>
                            <th scope="col" class="text-right">Answer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['simplified_report'] as $item)
                        <tr>
                            <td class="font-medium text-gray-700">
                                {{ $item['question'] }}
                            </td>
                            <td class="text-right">
                                <span class="badge badge-shadow {{ $item['is_flagged'] ? 'badge-danger' : 'badge-success' }}">
                                    {{ $item['answer'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Technical Details Section --}}
    @if(isset($report['parts_report']) && !empty($report['parts_report']))
        <div class="report-card">
            <div class="report-card-header">
                <div class="header-icon-wrapper purple">
                    <svg class="header-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </div>
                <div class="header-content">
                    <h3>Part Analysis</h3>
                </div>
            </div>

            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th scope="col">Part</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-right">Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['parts_report'] as $item)
                        @php
                            $isDefect = $item['price_modifier'] < 1.0;
                            $rowClass = $isDefect ? 'row-defect' : '';
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="font-medium {{ $isDefect ? 'row-text-defect' : 'text-gray-700' }}">
                                {{ $item['part_name'] }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-square badge-shadow {{ $isDefect ? 'badge-danger-outline' : 'badge-neutral' }}">
                                    @if($isDefect)
                                        <svg style="width: 12px; height: 12px; margin-right: 4px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    @else
                                        <svg style="width: 12px; height: 12px; margin-right: 4px; color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                    {{ $item['condition_name'] }}
                                </span>
                            </td>
                            <td class="text-right font-mono text-sm">
                                @if($isDefect)
                                    <span class="impact-value impact-negative">
                                        {{ $item['input_price_impact'] }}
                                    </span>
                                @else
                                    <span class="impact-neutral">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif(empty($report['simplified_report']))
        <div class="empty-state">
            <div class="empty-icon-wrapper">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-gray-900" style="font-weight: 600; font-size: 1.125rem; margin: 0;">No Evaluation Data</h3>
            <p class="text-gray-500" style="margin-top: 0.5rem; font-size: 0.875rem;">This trade-in request has not been evaluated yet, or the data is missing.</p>
        </div>
    @endif
</div>
