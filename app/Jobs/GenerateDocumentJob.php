<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\ServiceSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

class GenerateDocumentJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $serviceId;
    protected $submissionId;

    public function __construct($serviceId, $submissionId)
    {
        $this->serviceId = $serviceId;
        $this->submissionId = $submissionId;
    }

    public function handle()
    {
        $service = Service::findOrFail($this->serviceId);
        $submission = ServiceSubmission::findOrFail($this->submissionId);

        $templatePath = storage_path('app/public/' . $service->document);

        if (!file_exists($templatePath)) {
            return;
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        foreach ($submission->data as $key => $value) {

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $templateProcessor->setValue($key, $value ?? '');
        }

        $fileName = 'doc_' . time() . '_' . $submission->id . '.docx';
        $newPath = 'customer_documents/' . $fileName;
        $fullPath = storage_path('app/public/' . $newPath);

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0775, true);
        }

        $templateProcessor->saveAs($fullPath);

        $submission->update([
            'document' => $newPath
        ]);
    }
}
