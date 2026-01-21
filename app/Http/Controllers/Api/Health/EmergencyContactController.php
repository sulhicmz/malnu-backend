<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\EmergencyContact;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class EmergencyContactController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = EmergencyContact::class;
    protected string $resourceName = 'Emergency Contact';
    protected array $relationships = ['student', 'healthRecord'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'contact_type', 'is_primary'];
    protected array $searchFields = ['name', 'relationship', 'phone', 'email'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'name', 'relationship', 'phone'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'boolean' => ['is_primary'],
        'in' => ['contact_type' => 'parent,guardian,relative,friend,other'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function getByStudent(string $studentId)
    {
        try {
            $contacts = EmergencyContact::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('is_primary', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($contacts, 'Emergency contacts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getPrimary(string $studentId)
    {
        try {
            $primaryContact = EmergencyContact::with($this->relationships)
                ->where('student_id', $studentId)
                ->where('is_primary', true)
                ->first();

            if (!$primaryContact) {
                return $this->successResponse(null, 'No primary emergency contact found for this student', 200);
            }

            return $this->successResponse($primaryContact, 'Primary emergency contact retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}