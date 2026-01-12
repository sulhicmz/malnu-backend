<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cafeteria\MealPlan;
use App\Models\Cafeteria\StudentMealPreference;
use App\Models\Cafeteria\CafeteriaInventory;
use App\Models\Cafeteria\MealPayment;
use App\Models\Cafeteria\Vendor;
use App\Models\SchoolManagement\Student;
use Exception;

class CafeteriaManagementService
{
    public function createMealPlan(array $data): MealPlan
    {
        return MealPlan::create($data);
    }

    public function getMealPlan(string $id): ?MealPlan
    {
        return MealPlan::with('studentPreferences')->find($id);
    }

    public function updateMealPlan(string $id, array $data): bool
    {
        $mealPlan = MealPlan::find($id);
        if (!$mealPlan) {
            return false;
        }
        return $mealPlan->update($data);
    }

    public function deleteMealPlan(string $id): bool
    {
        $mealPlan = MealPlan::find($id);
        if (!$mealPlan) {
            return false;
        }
        return $mealPlan->delete();
    }

    public function getAllMealPlans()
    {
        return MealPlan::with('studentPreferences')->get();
    }

    public function createStudentPreference(array $data): StudentMealPreference
    {
        return StudentMealPreference::create($data);
    }

    public function getStudentPreference(string $id): ?StudentMealPreference
    {
        return StudentMealPreference::with(['student', 'mealPlan'])->find($id);
    }

    public function updateStudentPreference(string $id, array $data): bool
    {
        $preference = StudentMealPreference::find($id);
        if (!$preference) {
            return false;
        }
        return $preference->update($data);
    }

    public function deleteStudentPreference(string $id): bool
    {
        $preference = StudentMealPreference::find($id);
        if (!$preference) {
            return false;
        }
        return $preference->delete();
    }

    public function getStudentPreferences(string $studentId)
    {
        return StudentMealPreference::where('student_id', $studentId)
            ->with('mealPlan')
            ->get();
    }

    public function createInventoryItem(array $data): CafeteriaInventory
    {
        return CafeteriaInventory::create($data);
    }

    public function getInventoryItem(string $id): ?CafeteriaInventory
    {
        return CafeteriaInventory::with('vendor')->find($id);
    }

    public function updateInventoryItem(string $id, array $data): bool
    {
        $inventory = CafeteriaInventory::find($id);
        if (!$inventory) {
            return false;
        }
        return $inventory->update($data);
    }

    public function deleteInventoryItem(string $id): bool
    {
        $inventory = CafeteriaInventory::find($id);
        if (!$inventory) {
            return false;
        }
        return $inventory->delete();
    }

    public function getAllInventory()
    {
        return CafeteriaInventory::with('vendor')->get();
    }

    public function getLowStockItems(int $threshold = 10)
    {
        return CafeteriaInventory::where('quantity', '<=', $threshold)
            ->with('vendor')
            ->get();
    }

    public function createMealPayment(array $data): MealPayment
    {
        return MealPayment::create($data);
    }

    public function getMealPayment(string $id): ?MealPayment
    {
        return MealPayment::with('student')->find($id);
    }

    public function updateMealPayment(string $id, array $data): bool
    {
        $payment = MealPayment::find($id);
        if (!$payment) {
            return false;
        }
        return $payment->update($data);
    }

    public function deleteMealPayment(string $id): bool
    {
        $payment = MealPayment::find($id);
        if (!$payment) {
            return false;
        }
        return $payment->delete();
    }

    public function getStudentPayments(string $studentId)
    {
        return MealPayment::where('student_id', $studentId)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function createVendor(array $data): Vendor
    {
        return Vendor::create($data);
    }

    public function getVendor(string $id): ?Vendor
    {
        return Vendor::with('cafeteriaInventories')->find($id);
    }

    public function updateVendor(string $id, array $data): bool
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return false;
        }
        return $vendor->update($data);
    }

    public function deleteVendor(string $id): bool
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return false;
        }
        return $vendor->delete();
    }

    public function getAllVendors()
    {
        return Vendor::with('cafeteriaInventories')->get();
    }

    public function getActiveVendors()
    {
        return Vendor::where('status', 'active')
            ->with('cafeteriaInventories')
            ->get();
    }
}
