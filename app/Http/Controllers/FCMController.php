<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FCMController extends Controller
{
    private $credentialPath = 'firebase_credentials.json';

    public function index()
    {
        $hasCredentials = File::exists(public_path($this->credentialPath));
        $projectInfo = null;
        
        if ($hasCredentials) {
            try {
                $content = File::get(public_path($this->credentialPath));
                $credentials = json_decode($content, true);
                $projectInfo = [
                    'project_id' => $credentials['project_id'] ?? 'Unknown',
                    'client_email' => $credentials['client_email'] ?? 'Unknown',
                    'type' => $credentials['type'] ?? 'Unknown',
                ];
            } catch (Exception $e) {
                $projectInfo = null;
            }
        }
        
        return view('fcm', [
            'hasCredentials' => $hasCredentials,
            'projectInfo' => $projectInfo,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'firebase_credentials' => 'required|file|mimes:json|max:2048',
        ]);

        try {
            $file = $request->file('firebase_credentials');
            
            // Validate it's a valid Firebase service account JSON
            $content = file_get_contents($file->getRealPath());
            $credentials = json_decode($content, true);
            
            if (!$credentials) {
                return back()->with('error', __('Invalid JSON file'));
            }
            
            // Check required fields for Firebase service account
            $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
            foreach ($requiredFields as $field) {
                if (!isset($credentials[$field])) {
                    return back()->with('error', __('Invalid Firebase service account file. Missing field: ') . $field);
                }
            }
            
            if ($credentials['type'] !== 'service_account') {
                return back()->with('error', __('Invalid Firebase file. Must be a service account JSON.'));
            }
            
            // Save the file to public directory
            $file->move(public_path(), $this->credentialPath);
            
            return back()->with('success', __('Firebase credentials uploaded successfully! Project: ') . $credentials['project_id']);
            
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            if (File::exists(public_path($this->credentialPath))) {
                File::delete(public_path($this->credentialPath));
                return back()->with('success', __('Firebase credentials deleted successfully'));
            }
            return back()->with('error', __('No credentials file found'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download()
    {
        if (File::exists(public_path($this->credentialPath))) {
            return response()->download(public_path($this->credentialPath));
        }
        return back()->with('error', __('No credentials file found'));
    }
}
