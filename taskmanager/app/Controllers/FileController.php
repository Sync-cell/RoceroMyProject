<?php

namespace App\Controllers;

use App\Models\UploadModel;

class FileController extends BaseController
{
    public function upload()
    {
        helper('activity');

        $file = $this->request->getFile('upload_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return redirect()->back()->with('error', 'Invalid file type.');
            }

            // Move file to writable/uploads
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);

            // Save record to database
            $model = new UploadModel();
            $model->save([
                'user_id'   => session()->get('user_id'),
                'file_name' => $newName,
                'file_type' => $file->getClientMimeType(),
            ]);

            activity_log('Upload File', 'Uploaded file: ' . $newName);

            return redirect()->back()->with('success', 'File uploaded successfully.');
        }

        return redirect()->back()->with('error', 'File upload failed.');
    }
}