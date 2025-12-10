<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class QueueController extends Controller
{
    /**
     * Display queue dashboard
     */
    public function index()
    {
        // Get queue statistics
        $stats = [
            'pending' => DB::table('jobs')->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'processed_today' => $this->getProcessedToday(),
        ];

        // Get recent jobs
        $recentJobs = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'attempts' => $job->attempts,
                    'created_at' => $job->created_at,
                    'available_at' => date('Y-m-d H:i:s', $job->available_at),
                    'job_name' => $this->extractJobName($payload),
                ];
            });

        // Get failed jobs
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'failed_at' => $job->failed_at,
                    'exception' => substr($job->exception, 0, 200) . '...',
                    'job_name' => $this->extractJobName($payload),
                ];
            });

        return view('superadmin.queue.index', compact('stats', 'recentJobs', 'failedJobs'));
    }

    /**
     * Retry a failed job
     */
    public function retry($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            
            return redirect()->back()
                ->with('success', 'Job queued for retry successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    /**
     * Retry all failed jobs
     */
    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            
            $count = DB::table('failed_jobs')->count();
            
            return redirect()->back()
                ->with('success', "All {$count} failed jobs queued for retry");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to retry jobs: ' . $e->getMessage());
        }
    }

    /**
     * Delete a failed job
     */
    public function forget($id)
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            
            return redirect()->back()
                ->with('success', 'Failed job deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete job: ' . $e->getMessage());
        }
    }

    /**
     * Clear all jobs from queue
     */
    public function clear()
    {
        try {
            $count = DB::table('jobs')->count();
            DB::table('jobs')->truncate();
            
            return redirect()->back()
                ->with('success', "Cleared {$count} jobs from queue");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to clear queue: ' . $e->getMessage());
        }
    }

    /**
     * Flush all failed jobs
     */
    public function flush()
    {
        try {
            Artisan::call('queue:flush');
            
            return redirect()->back()
                ->with('success', 'All failed jobs deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to flush jobs: ' . $e->getMessage());
        }
    }

    /**
     * Extract job name from payload
     */
    private function extractJobName($payload)
    {
        if (isset($payload['displayName'])) {
            return $payload['displayName'];
        }
        
        if (isset($payload['data']['commandName'])) {
            $parts = explode('\\', $payload['data']['commandName']);
            return end($parts);
        }
        
        return 'Unknown Job';
    }

    /**
     * Get count of jobs processed today
     */
    private function getProcessedToday()
    {
        // This is an estimate based on failed jobs
        // In production, you'd want to track this in a separate table
        $failedToday = DB::table('failed_jobs')
            ->whereDate('failed_at', today())
            ->count();
        
        return $failedToday;
    }
}
