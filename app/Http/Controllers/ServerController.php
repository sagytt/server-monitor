<?php

namespace App\Http\Controllers;

use App\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index()
    {
        return Server::with('requests')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'url' => 'required',
            'protocol' => 'required|in:http,https,ftp,ssh',
            'ftp_username' => 'required',
            'ftp_password' => 'required',
        ]);

        return Server::create($validated);
    }

    public function update(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required',
            'url' => 'required|url',
            'protocol' => 'required|in:http,https,ftp,ssh',
        ]);

        $server->update($validated);
        return response()->json([
            'message' => 'Server has been updated successfully.'
        ], 200);
    }

    public function destroy(Server $server)
    {
        $server->delete();
        return response()->json([
            'message' => 'Server has been deleted successfully.'
        ], 200);
    }

    public function requestsHistory($id)
    {
        $server = Server::findOrFail($id);

        // Get all requests for this server
        $requests = $server->requests()->get();

        return response()->json($requests);
    }
    public function statusAt(Server $server, Request $request)
    {
        $timestamp = $request->query('timestamp');
        return $server->requests()
            ->where('created_at', '<=', $timestamp)
            ->exists();
    }


    public function healthAtTimestamp($serverId, $timestamp)
    {
        // Find the server by ID
        $server = Server::findOrFail($serverId);

        // Convert the timestamp to a Carbon instance
        try {
            $timestamp = \Carbon\Carbon::parse($timestamp);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid timestamp format'], 400);
        }

        // Find the closest request to the given timestamp
        $request = $server->requests()
            ->where('created_at', '<=', $timestamp) // Use created_at if timestamp is not a column
            ->where('status', '=', 'healthy') // Use created_at if timestamp is not a column
            ->orderByDesc('created_at')
            ->first();

        if (!$request) {
            return response()->json(['message' => 'No request found for this timestamp'], 404);
        }

        // Determine the server status based on the request data
        $status = $request->status;  // Assuming the 'status' field stores 'healthy' or 'unhealthy'

        return response()->json([
            'server_id' => $server->id,
            'status' => $status,
            'timestamp' => $request->created_at, // Assuming created_at is used
            'latency' => $request->latency,
        ]);
    }


}
