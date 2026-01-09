<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\FeeManagement\FeeType;
use App\Models\FeeManagement\FeeStructure;
use App\Models\FeeManagement\FeeInvoice;
use App\Models\FeeManagement\FeePayment;
use App\Models\FeeManagement\FeeWaiver;
use App\Models\SchoolManagement\Student;
use Carbon\Carbon;

class FeeManagementService
{
    public function createFeeType(array $data): FeeType
    {
        return FeeType::create($data);
    }

    public function updateFeeType(string $id, array $data): FeeType
    {
        $feeType = FeeType::findOrFail($id);
        $feeType->update($data);
        return $feeType;
    }

    public function deleteFeeType(string $id): bool
    {
        $feeType = FeeType::findOrFail($id);
        return $feeType->delete();
    }

    public function createFeeStructure(array $data): FeeStructure
    {
        return FeeStructure::create($data);
    }

    public function updateFeeStructure(string $id, array $data): FeeStructure
    {
        $structure = FeeStructure::findOrFail($id);
        $structure->update($data);
        return $structure;
    }

    public function deleteFeeStructure(string $id): bool
    {
        $structure = FeeStructure::findOrFail($id);
        return $structure->delete();
    }

    public function generateInvoice(array $data): FeeInvoice
    {
        $student = Student::findOrFail($data['student_id']);
        $structure = FeeStructure::findOrFail($data['fee_structure_id']);

        $subtotal = $structure->amount;
        $tax = 0;
        $discount = 0;

        if (isset($data['apply_waivers']) && $data['apply_waivers']) {
            $waivers = FeeWaiver::forStudent($student->id)->active()->get();
            foreach ($waivers as $waiver) {
                $discount += $waiver->calculateDiscount($subtotal);
            }
        }

        $invoiceNumber = $this->generateInvoiceNumber($student->id);

        $invoice = FeeInvoice::create([
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => Carbon::parse($data['issue_date'] ?? now()),
            'due_date' => $structure->due_date ?? Carbon::parse($data['due_date'])->addDays(30),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total_amount' => $subtotal + $tax - $discount,
            'balance_amount' => $subtotal + $tax - $discount,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        if ($discount > 0) {
            FeeWaiver::create([
                'student_id' => $student->id,
                'invoice_id' => $invoice->id,
                'waiver_type' => 'invoice_discount',
                'waiver_code' => 'AUTO-' . strtoupper(substr(uniqid(), -6)),
                'discount_amount' => $discount,
                'reason' => 'Auto-applied waiver',
                'valid_from' => now(),
                'status' => 'active',
            ]);
        }

        return $invoice;
    }

    public function generateInvoicesForGrade(string $grade, string $academicYear): array
    {
        $students = Student::whereHas('class', function ($q) use ($grade) {
            $q->where('grade_level', $grade);
        })->get();

        $structures = FeeStructure::forGrade($grade)->forAcademicYear($academicYear)->active()->get();
        $invoices = [];

        foreach ($students as $student) {
            foreach ($structures as $structure) {
                $invoices[] = $this->generateInvoice([
                    'student_id' => $student->id,
                    'fee_structure_id' => $structure->id,
                    'issue_date' => now(),
                    'apply_waivers' => true,
                ]);
            }
        }

        return $invoices;
    }

    public function createWaiver(array $data): FeeWaiver
    {
        return FeeWaiver::create($data);
    }

    public function createPayment(array $data): FeePayment
    {
        $invoice = FeeInvoice::findOrFail($data['invoice_id']);

        $payment = FeePayment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $data['user_id'],
            'payment_method' => $data['payment_method'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'payment_gateway_response' => $data['payment_gateway_response'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($payment->status === 'completed') {
            $payment->markAsCompleted();
        }

        return $payment;
    }

    public function processPayment(string $paymentId, string $gatewayResponse): FeePayment
    {
        $payment = FeePayment::findOrFail($paymentId);
        $response = json_decode($gatewayResponse, true);

        if ($response['status'] === 'success') {
            $payment->payment_gateway_response = $response;
            $payment->markAsCompleted();
        } else {
            $payment->payment_gateway_response = $response;
            $payment->markAsFailed($response['message'] ?? 'Payment failed');
        }

        return $payment;
    }

    public function calculateLateFees(): int
    {
        $invoices = FeeInvoice::where('status', '!=', 'paid')
                             ->where('due_date', '<', now())
                             ->get();

        $updatedCount = 0;

        foreach ($invoices as $invoice) {
            $daysLate = now()->diffInDays($invoice->due_date);
            $structure = $invoice->feeStructure;

            if ($structure && $structure->late_fee_percentage > 0) {
                $lateFee = $structure->calculateLateFee($daysLate);

                if ($lateFee > $invoice->late_fee) {
                    $invoice->late_fee = $lateFee;
                    $invoice->total_amount = $invoice->subtotal + $invoice->tax - $invoice->discount + $lateFee;
                    $invoice->balance_amount = $invoice->total_amount - $invoice->paid_amount;
                    $invoice->save();
                    $updatedCount++;
                }
            }
        }

        return $updatedCount;
    }

    public function getStudentOutstandingBalance(string $studentId): array
    {
        $invoices = FeeInvoice::byStudent($studentId)
                             ->whereIn('status', ['pending', 'partially_paid'])
                             ->get();

        $totalOutstanding = 0;
        $overdueCount = 0;

        foreach ($invoices as $invoice) {
            $totalOutstanding += $invoice->balance_amount;
            if ($invoice->isOverdue()) {
                $overdueCount++;
            }
        }

        return [
            'total_outstanding' => $totalOutstanding,
            'overdue_count' => $overdueCount,
            'invoices' => $invoices,
        ];
    }

    public function getFinancialReport(array $filters = []): array
    {
        $query = FeeInvoice::query();

        if (isset($filters['from_date'])) {
            $query->where('issue_date', '>=', Carbon::parse($filters['from_date']));
        }

        if (isset($filters['to_date'])) {
            $query->where('issue_date', '<=', Carbon::parse($filters['to_date']));
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $invoices = $query->get();

        $totalBilled = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $totalPending = $invoices->sum('balance_amount');
        $paymentRate = $totalBilled > 0 ? ($totalPaid / $totalBilled) * 100 : 0;

        $paymentStats = FeePayment::completed()
                                ->whereBetween('created_at', [
                                    $filters['from_date'] ?? '1970-01-01',
                                    $filters['to_date'] ?? now()
                                ])
                                ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                                ->groupBy('payment_method')
                                ->get();

        return [
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'payment_rate' => round($paymentRate, 2),
            'payment_statistics' => $paymentStats,
            'invoice_count' => $invoices->count(),
            'paid_count' => $invoices->where('status', 'paid')->count(),
            'pending_count' => $invoices->whereIn('status', ['pending', 'partially_paid'])->count(),
        ];
    }

    private function generateInvoiceNumber(string $studentId): string
    {
        $date = now()->format('Ymd');
        $prefix = 'INV-' . $date . '-';

        $lastInvoice = FeeInvoice::where('invoice_number', 'like', $prefix . '%')
                                ->orderBy('created_at', 'desc')
                                ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
