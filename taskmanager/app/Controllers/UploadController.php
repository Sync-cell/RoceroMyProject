<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class UploadController extends BaseController
{
    public function index()
    {
        return view('dashboard'); // Assuming you placed the form in dashboard
    }

    public function upload()
    {
        $file = $this->request->getFile('file');

        if (!$file->isValid()) {
            return redirect()->back()->with('error', $file->getErrorString());
        }

        // Validate file
        $validated = $this->validate([
            'file' => [
                'uploaded[file]',
                'mime_in[file,image/jpg,image/jpeg,image/png,application/pdf]',
                'max_size[file,2048]' // 2MB
            ]
        ]);

        if (!$validated) {
            return redirect()->back()->with('error', 'Invalid file format or size.');
        }

        // Move to writable/uploads
        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $newName);

        return redirect()->back()->with('success', 'File uploaded successfully as ' . $newName);
    }
}
