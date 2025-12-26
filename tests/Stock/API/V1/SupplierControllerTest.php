<?php

namespace Arkenstone\Core\Tests\Stock\API\V1;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_suppliers()
    {
        Supplier::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/suppliers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'email', 'supplier_code', 'status']
                    ],
                    'meta',
                    'links'
                ]
            ]);

        $this->assertCount(5, $response->json('data.data'));
    }

    /** @test */
    public function it_can_show_a_single_supplier()
    {
        $supplier = Supplier::factory()->active()->create();

        $response = $this->getJson('/api/v1/suppliers/' . $supplier->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'email' => $supplier->email,
                    'supplier_code' => $supplier->supplier_code,
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_supplier()
    {
        $response = $this->getJson('/api/v1/suppliers/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_supplier()
    {
        $data = [
            'name' => 'ABC Suppliers Ltd',
            'contact_person' => 'John Doe',
            'email' => 'contact@abcsuppliers.com',
            'phone' => '+1234567890',
            'address' => '123 Supply Street',
            'city' => 'Supply City',
            'state' => 'Supply State',
            'country' => 'Supply Country',
            'postal_code' => '12345',
            'supplier_code' => 'SUP-ABC-001',
            'status' => 'active',
            'notes' => 'Reliable supplier',
        ];

        $response = $this->postJson('/api/v1/suppliers', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'ABC Suppliers Ltd',
                    'supplier_code' => 'SUP-ABC-001',
                    'email' => 'contact@abcsuppliers.com',
                ]
            ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'ABC Suppliers Ltd',
            'supplier_code' => 'SUP-ABC-001',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_supplier()
    {
        $response = $this->postJson('/api/v1/suppliers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'supplier_code']);
    }

    /** @test */
    public function it_validates_unique_supplier_code()
    {
        Supplier::factory()->create(['supplier_code' => 'SUP-UNIQUE-001']);

        $response = $this->postJson('/api/v1/suppliers', [
            'name' => 'Test Supplier',
            'supplier_code' => 'SUP-UNIQUE-001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_code']);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $response = $this->postJson('/api/v1/suppliers', [
            'name' => 'Test Supplier',
            'supplier_code' => 'SUP-001',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_update_a_supplier()
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Old Name',
            'status' => 'active',
        ]);

        $response = $this->putJson('/api/v1/suppliers/' . $supplier->id, [
            'name' => 'Updated Name',
            'status' => 'inactive',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Updated Name',
                    'status' => 'inactive',
                ]
            ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_supplier()
    {
        $response = $this->putJson('/api/v1/suppliers/999', ['name' => 'Test']);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_delete_a_supplier()
    {
        $supplier = Supplier::factory()->create();

        $response = $this->deleteJson('/api/v1/suppliers/' . $supplier->id);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_supplier()
    {
        $response = $this->deleteJson('/api/v1/suppliers/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_search_suppliers()
    {
        Supplier::factory()->create(['name' => 'ABC Suppliers']);
        Supplier::factory()->create(['name' => 'XYZ Suppliers']);
        Supplier::factory()->create(['supplier_code' => 'SUP-ABC-001']);

        $response = $this->getJson('/api/v1/suppliers/search?search=ABC');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    /** @test */
    public function it_can_filter_suppliers_by_status()
    {
        Supplier::factory()->active()->count(3)->create();
        Supplier::factory()->inactive()->count(2)->create();

        $response = $this->getJson('/api/v1/suppliers?status=active');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }
}
