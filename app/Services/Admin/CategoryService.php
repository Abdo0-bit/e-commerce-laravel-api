<?php

namespace App\Services\Admin;
use App\Models\Category;
use App\Services\Contracts\Admin\CategoryServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService implements CategoryServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function index(array $filters = []): LengthAwarePaginator
    {
        return Category::paginate($filters['per_page'] ?? 15);

    }
    public function store(array $data): Category
    {
        return Category::create($data);
    }
    public function show($category): Category
    {
        $category->load('products:id,name');
        return $category;
    }
    public function update($category, array $data): Category
    {
        $category->update($data);
        return $category;
    }
    public function delete($category): void
    {
        $category->delete();
        
    }
}
