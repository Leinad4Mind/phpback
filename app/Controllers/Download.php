<?php

namespace App\Controllers;

use App\Models\AttachmentModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Download extends BaseController
{
    /**
     * Streams an idea attachment from writable/uploads (outside the web root)
     * as a forced download, so uploaded files are never executed inline.
     */
    public function attachment($id)
    {
        $attachment = model(AttachmentModel::class)->find((int) $id);
        if ($attachment === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $attachment->file_path;
        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($path, null)->setFileName($attachment->file_name);
    }
}
