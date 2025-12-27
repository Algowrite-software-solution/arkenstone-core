<?php

namespace Arkenstone\Core\Tests\Stock\Unit\Services;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Arkenstone\Core\ECommerce\Stock\Services\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SupplierService $supplierService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supplierService = app()->make('supplier');
    }

    /** @test */
    public function it_can_create_supplier()
    {
        $data = [
            'name' => 'Test Supplier',
            'email' => 'supplier@test.com',
            'phone' => '1234567890',
            'status' => 'active',
        ];

        $supplier = $this->supplierService->createSupplier($data);

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertEquals('Test Supplier', $supplier->name);
        $this->assertNotNull($supplier->supplier_code);
        $this->assertDatabaseHas('suppliers', ['name' => 'Test Supplier']);
    }

    /** @test */
    public function it_can_get_supplier_by_id()
    {
        $supplier = Supplier::factory()->create();

        $result = $this->supplierService->getSupplier($supplier->id);

        $this->assertNotNull($result);
        $this->assertEquals($supplier->id, $result->id);
        $this->assertEquals($supplier->name, $result->name);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_supplier()
    {
        $result = $this->supplierService->getSupplier(999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_update_supplier()
    {
        $supplier = Supplier::factory()->create(['name' => 'Original Name']);

        $updated = $this->supplierService->updateSupplier($supplier->id, [
            'name' => 'Updated Name',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_can_delete_supplier()
    {
        $supplier = Supplier::factory()->create();

        $result = $this->supplierService->deleteSupplier($supplier->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    /** @test */
    public function it_can_search_suppliers()
    {
        Supplier::factory()->create(['name' => 'ABC Suppliers']);
        Supplier::factory()->create(['name' => 'XYZ Corporation']);

        $results = $this->supplierService->searchSuppliers('ABC');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('ABC', $results->first()->name);
    }

    /** @test */
    public function it_can_get_all_suppliers()
    {
        Supplier::factory()->count(3)->create();

        $suppliers = $this->supplierService->getSuppliers([]);

        $this->assertCount(3, $suppliers);
    }

    /** @test */
    public function it_can_filter_active_suppliers()
    {
        Supplier::factory()->create(['status' => 'active']);
        Supplier::factory()->create(['status' => 'active']);
        Supplier::factory()->create(['status' => 'inactive']);

        $suppliers = $this->supplierService->getSuppliers(['active' => true]);

        $this->assertCount(2, $suppliers);
    }
}
