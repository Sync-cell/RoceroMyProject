<?php


namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['form', 'url'];
    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Ensure session is available for csrf_field() and session()->get()
        $this->session = Services::session();

        try {
            // Start session — safely call start() inside try/catch to support different CI versions
            if (is_callable([$this->session, 'start'])) {
                $this->session->start();
            }
        } catch (\Throwable $e) {
            // ignore session start errors (will surface on POST CSRF if misconfigured)
        }

        // Ensure security helper is available and touch CSRF so token + cookie are generated.
        if (! function_exists('csrf_hash')) {
            helper('security');
        }
        try {
            csrf_hash();
        } catch (\Throwable $e) {
            // ignore
        }
    }
}