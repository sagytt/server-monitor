<?php

namespace App\Jobs;

use App\Notifications\ServerUnhealthyNotification;
use App\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class MonitorServers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $servers = Server::all();

        foreach ($servers as $server) {
            $checkResult = $this->checkServer($server);

            // Save the request results
            $server->requests()->create([
                'status' => $checkResult['status'] ? 'healthy' : 'unhealthy',
                'latency' => $checkResult['latency'],
            ]);

            // Check if the server became unhealthy
            $recentRequests = $server->requests()->latest()->take(3)->get();
            $unhealthy = $recentRequests->count() === 3 && $recentRequests->every(function ($req) {
                    return $req->status === 'unhealthy';
                });

            if ($unhealthy && !$server->notified_unhealthy) {
                // Send notification
                $adminEmail = config('mail.smtp'); // Pre-defined email in config
                print_r('sending mail');
                Notification::route('mail', $adminEmail)
                    ->notify(new ServerUnhealthyNotification($server));

                // Mark as notified
                $server->update(['notified_unhealthy' => true]);
            }

            // Reset the notification flag if the server becomes healthy
            $healthy = $server->requests()->latest()->take(5)->get()->every(function ($req) {
                return $req->status === 'healthy';
            });
            if ($healthy && $server->notified_unhealthy) {
                $server->update(['notified_unhealthy' => false]);
            }
        }
    }


    public function updateServerHealth($server)
    {
        // Get last 5 requests
        $lastRequests = DB::table('monitor_requests')
            ->where('server_id', $server->id)
            ->orderBy('timestamp', 'desc')
            ->limit(5)
            ->get();

        $successCount = $lastRequests->where('status', 'success')->count();
        $failureCount = $lastRequests->where('status', 'failure')->count();

        // If 5 consecutive successes, mark server as healthy
        if ($successCount == 5) {
            $server->status = 'Healthy';
        }

        // If 3 consecutive failures, mark server as unhealthy
        if ($failureCount == 3) {
            $server->status = 'Unhealthy';
        }

        $server->save();
    }

    public function checkServer($server, $user = null, $password = null)
    {
        $startTime = microtime(true);

        try {
            switch ($server->protocol) {
                case 'http':
                case 'https':
                    $response = Http::timeout(45)->get($server->url);
                    $status = $response->successful(); // Checks for 2xx status codes
                    break;
                case 'ftp':
                    $status = $this->checkFTP($server->url, $user, $password);
                    break;
                case 'ssh':
                    $status = $this->checkSSH($server->url, $user, $password);
                    break;
                default:
                    $status = false;
            }

            $latency = microtime(true) - $startTime;

            return [
                'status' => $status && $latency < 45, // AND condition
                'latency' => $latency,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'latency' => 45, // Consider max latency for failed requests
            ];
        }
    }


    private function checkFTP($url, $username, $password)
    {
        // Connect to FTP server
        $connection = ftp_connect($url);
        if (!$connection) return false;

        // Attempt to login
        $login = @ftp_login($connection, $username, $password);

        // Close connection
        ftp_close($connection);

        return $login !== false;
    }


    private function checkSSH($url, $username, $password)
    {
        // Example SSH connection check
        $connection = ssh2_connect($url, 22);
        if (!$connection) return false;

        // Authenticate with username and password (or you could use a private key)
        $auth = ssh2_auth_password($connection, $username, $password);

        return $auth !== false;
    }

}
