<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\LeaveType;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class LeaveTypeController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function index()
    {
        $query = LeaveType::query();

        if ($this->request->has('is_active')) {
            $query->where('is_active', $this->request->input('is_active'));
        }

        if ($this->request->has('is_paid')) {
            $query->where('is_paid', $this->request->input('is_paid'));
        }

        $leaveTypes = $query->orderBy('name')->paginate(15);

        return $this->response->json($leaveTypes);
    }

    public function store()
    {
        $data = $this->request->all();

        $leaveType = LeaveType::create($data);

        return $this->response->json($leaveType, 201);
    }

    public function show(int $id)
    {
        $leaveType = LeaveType::find($id);

        if (! $leaveType) {
            return $this->notFoundResponse('Leave type not found');
        }

        return $this->response->json($leaveType);
    }

    public function update(int $id)
    {
        $leaveType = LeaveType::find($id);

        if (! $leaveType) {
            return $this->notFoundResponse('Leave type not found');
        }

        $leaveType->update($this->request->all());

        return $this->response->json($leaveType);
    }

    public function destroy(int $id)
    {
        $leaveType = LeaveType::find($id);

        if (! $leaveType) {
            return $this->notFoundResponse('Leave type not found');
        }

        $leaveType->delete();

        return $this->response->json(['message' => 'Leave type deleted']);
    }
}
