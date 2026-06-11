<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDocumentJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\ServiceSubmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ServiceSubmitController extends Controller
{

    public function submit(Request $request, $serviceId)
    {
        DB::beginTransaction();

        try {
            $service = Service::with('steps.fields')->findOrFail($serviceId);

            $rules = $this->buildValidationRules($service);

            $validated = $request->validate($rules);

            $effectiveDateKey = null;

            foreach ($service->steps as $step) {
                foreach ($step->fields as $field) {
                    if ($field->type === 'effective_date') {
                        $effectiveDateKey = $field->document_key;
                    }
                }
            }

            if ($effectiveDateKey && isset($validated[$effectiveDateKey])) {

                $effectiveDate = Carbon::parse($validated[$effectiveDateKey]);
                $validated['expiry_date'] = $effectiveDate->copy()->addYear()->toDateString();
            }

            $submission = ServiceSubmission::create([
                'service_id' => $service->id,
                'data' => $validated
            ]);
            GenerateDocumentJob::dispatch($service->id, $submission->id);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Form submitted successfully',
                'submission_id' => $submission->id
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function buildValidationRules($service)
    {
        $rules = [];

        foreach ($service->steps as $step) {
            foreach ($step->fields as $field) {

                $rule = [];

                if ($field->required) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }

                switch ($field->type) {

                    case 'email':
                        $rule[] = 'email';
                        break;

                    case 'number':
                        $rule[] = 'numeric';
                        break;

                    case 'date':
                    case 'effective_date':
                        $rule[] = 'date';
                        break;

                    case 'radio':
                    case 'select':
                        $rule[] = 'string';

                        if (!empty($field->options)) {
                            $rule[] = 'in:' . implode(',', $field->options);
                        }
                        break;

                    case 'rich_text':
                    case 'textarea':
                    case 'text':
                    default:
                        $rule[] = 'string';
                        break;
                }

                $rules[$field->document_key] = $rule;
            }
        }

        return $rules;
    }

    public function getSubmission($id)
    {
        $submission = ServiceSubmission::with('service')->findOrFail($id);
        return response()->json([
            'status' => true,
            'message' => 'Submission retrieved successfully',
            'data' => array_merge(
                [
                    'id' => $submission->id,
                    'service_id' => $submission->service_id,
                ],
                $submission->data ?? []
            )
        ]);
    }

    public function getServiceSubmissions($serviceId)
    {
        $submissions = ServiceSubmission::where('service_id', $serviceId)
            ->latest()
            ->get()
            ->map(function ($submission) {
                return array_merge(
                    [
                        'id' => $submission->id,
                        'service_id' => $submission->service_id,
                    ],
                    $submission->data ?? [],
                    [
                        'document' => $submission->document_url,
                        'created_at' => $submission->created_at,
                    ]
                );
            });

        return response()->json([
            'status' => true,
            'message' => 'Submissions retrieved successfully',
            'data' => $submissions
        ]);
    }

    public function update(Request $request, $serviceId, $submissionId)
    {
        DB::beginTransaction();

        try {
            $service = Service::with('steps.fields')->findOrFail($serviceId);
            $submission = ServiceSubmission::findOrFail($submissionId);

            $rules = $this->buildValidationRules($service);
            $validated = $request->validate($rules);

            $effectiveDateKey = null;

            foreach ($service->steps as $step) {
                foreach ($step->fields as $field) {
                    if ($field->type === 'effective_date') {
                        $effectiveDateKey = $field->document_key;
                    }
                }
            }

            if ($effectiveDateKey && isset($validated[$effectiveDateKey])) {
                $effectiveDate = Carbon::parse($validated[$effectiveDateKey]);

                $validated['expiry_date'] = $effectiveDate
                    ->copy()
                    ->addYear()
                    ->toDateString();
            }

            $updatedData = array_merge($submission->data ?? [], $validated);

            if ($submission->document && Storage::disk('public')->exists($submission->document)) {
                Storage::disk('public')->delete($submission->document);
            }

            $submission->update([
                'data' => $updatedData,
                'document' => null
            ]);

            DB::commit();

            GenerateDocumentJob::dispatch($service->id, $submission->id);

            return response()->json([
                'status' => true,
                'message' => 'Submission updated successfully',
                'submission_id' => $submission->id
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function downloadDocument(Request $request, $id)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This download link has expired or is invalid.');
        }

        $submission = ServiceSubmission::findOrFail($id);

        if (!$submission->document) {
            return response()->json(['message' => 'Document is still being generated. Please try again in a moment.'], 404);
        }

        $filePath = $submission->document;

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }

        abort(404, 'File not found on server.');
    }

    public function getDocument($id)
    {
        $submission = ServiceSubmission::findOrFail($id);

        if (!$submission->document) {
            return response()->json(['message' => 'Document is still being generated. Please try again in a moment.'], 404);
        }

        $filePath = $submission->document;

        if (Storage::disk('public')->exists($filePath)) {
            $url = Storage::disk('public')->url($filePath);
            return response()->json([
                'status' => true,
                'id' => $submission->id,
                'message' => 'Document URL retrieved successfully',
                'data' => ['document_url' => $url]
            ]);
        }

        return response()->json(['message' => 'File not found on server.'], 404);
    }

}
