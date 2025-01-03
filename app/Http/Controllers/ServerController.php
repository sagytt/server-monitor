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
        // Preprocess the URL to add a default scheme if missing
        $request->merge([
            'url' => $this->addDefaultScheme($request->input('url')),
        ]);

        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'protocol' => 'required|string|in:http,https,ftp,ssh',
            'ftp_username' => 'nullable|required_if:protocol,ftp|string|max:255',
            'ftp_password' => 'nullable|required_if:protocol,ftp|string|max:255',
        ], [
            'name.required' => 'The server name is required.',
            'url.required' => 'The server URL is required.',
            'url.url' => 'Please provide a valid URL.',
            'protocol.required' => 'The server protocol is required.',
            'protocol.in' => 'The protocol must be one of the following: http, https, ftp, ssh.',
            'ftp_username.required_if' => 'FTP username is required when protocol is FTP.',
            'ftp_password.required_if' => 'FTP password is required when protocol is FTP.',
        ]);

        // Store the server data
        $server = Server::create($validated);

        return response()->json($server, 201);
    }

    private function addDefaultScheme($url)
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            // Prepend 'http://' if no scheme is present
            $url = 'http://' . $url;
        }

        return $url;
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
