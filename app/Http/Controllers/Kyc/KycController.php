<?php

namespace App\Http\Controllers\Kyc;

use App\Http\Controllers\Controller;
use App\Models\KycRecord;
use App\Services\KYC\DojahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class KycController extends Controller
{
    public function __construct(
        protected DojahService $dojahService
    ) {}

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $kyc = $user->kycRecord;

        return response()->json([
            'kyc_status' => $user->kyc_status,
            'record' => $kyc ? [
                'status' => $kyc->status,
                'id_type' => $kyc->id_type,
                'submitted_at' => $kyc->submitted_at,
                'verified_at' => $kyc->verified_at,
                'rejection_reason' => $kyc->rejection_reason,
            ] : null,
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bvn' => ['nullable', 'string', 'size:11'],
            'nin' => ['nullable', 'string', 'size:11'],
            'id_type' => ['required', 'string', 'in:passport,drivers_license,national_id'],
            'id_number' => ['required', 'string'],
            'selfie' => ['nullable', 'string'],
            'id_document' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
            'dob' => ['nullable', 'date'],
        ]);

        $user = $request->user();

        $kycData = [
            'user_id' => $user->id,
            'id_type' => $validated['id_type'],
            'id_number_encrypted' => Crypt::encryptString($validated['id_number']),
            'status' => 'pending',
            'submitted_at' => now(),
        ];

        if (! empty($validated['bvn'])) {
            $kycData['bvn_encrypted'] = Crypt::encryptString($validated['bvn']);

            $bvnResult = $this->dojahService->verifyBVN(
                $validated['bvn'],
                $validated['dob'] ?? ''
            );

            if (isset($bvnResult['error'])) {
                return response()->json(['error' => 'BVN verification failed'], 422);
            }
        }

        if (! empty($validated['nin'])) {
            $kycData['nin_encrypted'] = Crypt::encryptString($validated['nin']);
        }

        if ($request->hasFile('id_document')) {
            $path = $request->file('id_document')->store('kyc-documents', 'private');
            $kycData['id_document_url'] = $path;
        }

        if (! empty($validated['selfie'])) {
            $livenessResult = $this->dojahService->livenessCheck($validated['selfie']);
            $kycData['selfie_url'] = 'liveness-verified';
        }

        $kyc = KycRecord::updateOrCreate(
            ['user_id' => $user->id],
            $kycData
        );

        $user->update(['kyc_status' => 'pending']);

        return response()->json([
            'message' => 'KYC submitted for verification',
            'kyc' => [
                'status' => $kyc->status,
                'submitted_at' => $kyc->submitted_at,
            ],
        ]);
    }

    public function submitBusiness(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string'],
            'rc_number' => ['required', 'string'],
            'cac_document' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
            'tax_id' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $kycData = [
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'rc_number' => $validated['rc_number'],
            'status' => 'pending',
            'submitted_at' => now(),
        ];

        if (! empty($validated['tax_id'])) {
            $kycData['tax_id_encrypted'] = Crypt::encryptString($validated['tax_id']);
        }

        if ($request->hasFile('cac_document')) {
            $path = $request->file('cac_document')->store('kyc-documents', 'private');
            $kycData['cac_document_url'] = $path;
        }

        $kyc = KycRecord::updateOrCreate(
            ['user_id' => $user->id],
            $kycData
        );

        $user->update(['kyc_status' => 'pending', 'account_type' => 'business']);

        return response()->json([
            'message' => 'Business KYC submitted for verification',
            'kyc' => ['status' => $kyc->status],
        ]);
    }
}
