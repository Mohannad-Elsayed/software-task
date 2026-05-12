<?php

require_once __DIR__ . '/../../Services/ReportService.php';

class ReportController
{
    private $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    // GET /api/reports
    public function index()
    {
       // header('Content-Type: application/json');
       //temp remove 
       echo json_encode($this->reportService->getReports());
    }

    // GET /api/reports?id=1 (or /api/reports/1 in better routing)
    public function show()
    {
        $report_id = $_GET['report_id'] ?? null;

        // header('Content-Type: application/json');
        //temp remove
        if (!$report_id) {
            http_response_code(400);
            echo json_encode(["error" => "report_id is required"]);
            return;
        }

        $report = $this->reportService->getReportById($report_id);

        if ($report) {
            echo json_encode($report);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Report not found"]);
        }
    }

    // POST /api/reports
    public function store()
    {
        //$data = json_decode(file_get_contents('php://input'), true);
        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);//remove when finish
        
        $result = $this->reportService->createReport($data);

       // header('Content-Type: application/json');
        //temp remove 
        if (isset($result['error'])) {
            http_response_code(400);
        } else {
            http_response_code(201);
        }

        echo json_encode($result);
    }

    // PUT /api/reports?id=1
    public function update()
    {
        $report_id = $_GET['report_id'] ?? null;
       // $data = json_decode(file_get_contents('php://input'), true);
        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
        // header('Content-Type: application/json');
            //temp remove
        if (!$report_id) {
            http_response_code(400);
            echo json_encode(["error" => "report_id is required"]);
            return;
        }

        $result = $this->reportService->updateReport($report_id, $data);

        if (isset($result['error'])) {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    // DELETE /api/reports?id=1
    public function destroy()
    {
        $report_id = $_GET['report_id'] ?? null;

        //  header('Content-Type: application/json');
        //temp remove
        if (!$report_id) {
            http_response_code(400);
            echo json_encode(["error" => "report_id is required"]);
            return;
        }

        $result = $this->reportService->deleteReport($report_id);

        echo json_encode($result);
    }
}
