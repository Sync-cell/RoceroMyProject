<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class StartSession implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $session = Services::session();

            if (session_status() === PHP_SESSION_NONE) {  
                $session->start();
            }
        } catch (\Throwable $e) {
            log_message('error', 'StartSession error: ' . $e->getMessage());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
