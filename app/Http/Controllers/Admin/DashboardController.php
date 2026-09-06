<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\ToolPage;
use App\Support\Province;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $since = now()->subDays(30);

        $counts = AnalyticsEvent::query()
            ->select('name', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $since)
            ->groupBy('name')
            ->pluck('total', 'name');

        $pageViews = (int) ($counts['page_view'] ?? 0);
        $started = (int) ($counts['calculator_started'] ?? 0)
            + (int) ($counts['overtime_calculator_started'] ?? 0)
            + (int) ($counts['bonus_calculator_started'] ?? 0)
            + (int) ($counts['raise_calculator_started'] ?? 0)
            + (int) ($counts['military_salary_calculator_started'] ?? 0);
        $completed = (int) ($counts['calculator_completed'] ?? 0)
            + (int) ($counts['overtime_calculator_completed'] ?? 0)
            + (int) ($counts['bonus_calculator_completed'] ?? 0)
            + (int) ($counts['raise_calculator_completed'] ?? 0)
            + (int) ($counts['military_salary_calculator_completed'] ?? 0);

        $tools = AnalyticsEvent::query()
            ->select('tool_key', DB::raw('count(*) as total'))
            ->whereIn('name', [
                'calculator_completed',
                'overtime_calculator_completed',
                'bonus_calculator_completed',
                'raise_calculator_completed',
                'military_salary_calculator_completed',
            ])
            ->whereNotNull('tool_key')
            ->where('created_at', '>=', $since)
            ->groupBy('tool_key')
            ->orderByDesc('total')
            ->get();

        $provinces = AnalyticsEvent::query()
            ->select('province', DB::raw('count(*) as total'))
            ->whereIn('name', [
                'calculator_completed',
                'overtime_calculator_completed',
                'bonus_calculator_completed',
                'raise_calculator_completed',
                'military_salary_calculator_completed',
            ])
            ->whereNotNull('province')
            ->where('created_at', '>=', $since)
            ->groupBy('province')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => Province::fromCode($row->province)?->name() ?? $row->province,
                'total' => $row->total,
            ]);

        $salaryRanges = AnalyticsEvent::query()
            ->select('salary_range', DB::raw('count(*) as total'))
            ->where('name', 'calculator_completed')
            ->whereNotNull('salary_range')
            ->where('created_at', '>=', $since)
            ->groupBy('salary_range')
            ->orderByDesc('total')
            ->get();

        $frequencies = AnalyticsEvent::query()
            ->select('frequency', DB::raw('count(*) as total'))
            ->where('name', 'calculator_completed')
            ->whereNotNull('frequency')
            ->where('created_at', '>=', $since)
            ->groupBy('frequency')
            ->orderByDesc('total')
            ->get();

        $topPages = AnalyticsEvent::query()
            ->select('path', DB::raw('count(*) as total'))
            ->where('name', 'page_view')
            ->whereNotNull('path')
            ->where('created_at', '>=', $since)
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'toolPages' => ToolPage::query()->orderBy('name')->get(),
            'tools' => $tools,
            'pageViews' => $pageViews,
            'started' => $started,
            'completed' => $completed,
            'completionRate' => $started > 0 ? round(($completed / $started) * 100, 1) : 0,
            'provinces' => $provinces,
            'salaryRanges' => $salaryRanges,
            'frequencies' => $frequencies,
            'topPages' => $topPages,
        ]);
    }
}
