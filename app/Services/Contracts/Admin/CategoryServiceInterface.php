<?php

namespace App\Services\Contracts\Admin;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryServiceInterface
{
    public function index(array $filters = []): LengthAwarePaginator;
    public function store(array $data):Category;
    public function show($category): Category;
    public function update($category, array $data): Category;
    public function delete($category): void;


}
