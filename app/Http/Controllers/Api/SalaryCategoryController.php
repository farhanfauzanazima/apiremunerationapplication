<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalaryCategory\StoreSalaryCategoryRequest;
use App\Http\Requests\SalaryCategory\UpdateSalaryCategoryRequest;
use App\Models\SalaryCategory;
use Illuminate\Http\JsonResponse;

class SalaryCategoryController extends Controller
{
    // GET /api/salary-categories
    public function index(): JsonResponse
    {
        $categories = SalaryCategory::with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($category) {
                return [
                    'id'            => $category->id,
                    'category_name' => $category->category_name,
                    'base_salary'   => $category->base_salary,
                    'allowance'     => $category->allowance,
                    'overtime_rate' => $category->overtime_rate,
                    'late_penalty'  => $category->late_penalty,
                    'description'   => $category->description,
                    'is_active'     => $category->is_active,
                    'created_by'    => $category->creator->name ?? null,
                    'created_at'    => $category->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data kategori gaji berhasil diambil.',
            'data'    => $categories,
        ], 200);
    }

    // POST /api/salary-categories
    public function store(StoreSalaryCategoryRequest $request): JsonResponse
    {
        $category = SalaryCategory::create([
            'category_name' => $request->category_name,
            'base_salary'   => $request->base_salary,
            'allowance'     => $request->allowance ?? 0,
            'overtime_rate' => $request->overtime_rate ?? 0,
            'late_penalty'  => $request->late_penalty ?? 0,
            'description'   => $request->description,
            'created_by'    => auth()->id(),
            'is_active'     => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori gaji berhasil ditambahkan.',
            'data'    => $category,
        ], 201);
    }

    // GET /api/salary-categories/{salary_category}
    public function show(SalaryCategory $salary_category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail kategori gaji berhasil diambil.',
            'data'    => [
                'id'            => $salary_category->id,
                'category_name' => $salary_category->category_name,
                'base_salary'   => $salary_category->base_salary,
                'allowance'     => $salary_category->allowance,
                'overtime_rate' => $salary_category->overtime_rate,
                'late_penalty'  => $salary_category->late_penalty,
                'description'   => $salary_category->description,
                'is_active'     => $salary_category->is_active,
                'created_by'    => $salary_category->creator->name ?? null,
                'created_at'    => $salary_category->created_at,
                'updated_at'    => $salary_category->updated_at,
            ],
        ], 200);
    }

    // PUT /api/salary-categories/{salary_category}
    public function update(UpdateSalaryCategoryRequest $request, SalaryCategory $salary_category): JsonResponse
    {
        $salary_category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kategori gaji berhasil diperbarui.',
            'data'    => $salary_category,
        ], 200);
    }

    // DELETE /api/salary-categories/{salary_category}
    public function destroy(SalaryCategory $salary_category): JsonResponse
    {
        // Cek apakah kategori sedang digunakan oleh karyawan
        // if ($salary_category->employees()->count() > 0) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Kategori tidak dapat dihapus karena sedang digunakan oleh karyawan.',
        //     ], 422);
        // }

        $salary_category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori gaji berhasil dihapus.',
        ], 200);
    }
}