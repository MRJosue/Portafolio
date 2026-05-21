<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_calendar_requires_authentication(): void
    {
        $this->get('/admin/finanzas')->assertRedirect('/login');
    }

    public function test_admin_can_create_fixed_and_daily_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/finanzas/2026-05-18/entradas', [
            'name' => 'Renta',
            'description' => 'Pago mensual',
            'amount' => '1200.50',
            'type' => FinancialEntry::TYPE_FIXED_EXPENSE,
            'is_active' => '1',
        ])->assertRedirect();

        $this->actingAs($user)->post('/admin/finanzas/2026-05-18/entradas', [
            'name' => 'Comida',
            'amount' => '45',
            'type' => FinancialEntry::TYPE_EXPENSE,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('financial_entries', [
            'name' => 'Renta',
            'type' => FinancialEntry::TYPE_FIXED_EXPENSE,
            'entry_date' => null,
        ]);

        $this->assertDatabaseHas('financial_entries', [
            'name' => 'Comida',
            'type' => FinancialEntry::TYPE_EXPENSE,
            'entry_date' => '2026-05-18 00:00:00',
        ]);
    }

    public function test_day_detail_shows_fixed_entries_on_any_day_and_daily_entries_only_on_selected_day(): void
    {
        $user = User::factory()->create();

        FinancialEntry::create([
            'name' => 'Servicios',
            'amount' => 300,
            'type' => FinancialEntry::TYPE_FIXED_EXPENSE,
            'is_active' => true,
        ]);

        FinancialEntry::create([
            'name' => 'Ingreso',
            'amount' => 900,
            'type' => FinancialEntry::TYPE_INCOME,
            'entry_date' => '2026-05-18',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin/finanzas/2026-05-18')
            ->assertOk()
            ->assertSee('Servicios')
            ->assertSee('Ingreso')
            ->assertSee('BALANCE QUINCENAL')
            ->assertSee('BALANCE MENSUAL')
            ->assertDontSee('BALANCE DEL DIA')
            ->assertSee('$750.00')
            ->assertSee('$600.00');

        $this->actingAs($user)->get('/admin/finanzas/2026-05-19')
            ->assertOk()
            ->assertSee('Servicios')
            ->assertDontSee('value="900.00"', false);
    }

    public function test_calendar_shows_monthly_and_fortnight_balances(): void
    {
        $user = User::factory()->create();

        FinancialEntry::create([
            'name' => 'Renta',
            'amount' => 1200,
            'type' => FinancialEntry::TYPE_FIXED_EXPENSE,
            'is_active' => true,
        ]);

        FinancialEntry::create([
            'name' => 'Ingreso',
            'amount' => 5000,
            'type' => FinancialEntry::TYPE_FIXED_ASSET,
            'is_active' => true,
        ]);

        FinancialEntry::create([
            'name' => 'Supermercado',
            'amount' => 600,
            'type' => FinancialEntry::TYPE_EXPENSE,
            'entry_date' => '2026-05-10',
            'is_active' => true,
        ]);

        FinancialEntry::create([
            'name' => 'Ingreso',
            'amount' => 900,
            'type' => FinancialEntry::TYPE_INCOME,
            'entry_date' => '2026-05-20',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin/finanzas?month=2026-05')
            ->assertOk()
            ->assertSee('RESUMEN MENSUAL')
            ->assertSee('Balance por quincena')
            ->assertSee('$4,100.00')
            ->assertSee('$-600.00')
            ->assertSee('$900.00');
    }

    public function test_admin_can_update_and_delete_financial_entries(): void
    {
        $user = User::factory()->create();
        $entry = FinancialEntry::create([
            'name' => 'Ingreso',
            'amount' => 100,
            'type' => FinancialEntry::TYPE_INCOME,
            'entry_date' => '2026-05-18',
            'is_active' => true,
        ]);

        $this->actingAs($user)->patch("/admin/finanzas/2026-05-18/entradas/{$entry->id}", [
            'name' => 'Ingreso',
            'amount' => 150,
            'type' => FinancialEntry::TYPE_INCOME,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('financial_entries', [
            'id' => $entry->id,
            'name' => 'Ingreso',
            'amount' => 150,
        ]);

        $this->actingAs($user)
            ->delete("/admin/finanzas/2026-05-18/entradas/{$entry->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('financial_entries', [
            'id' => $entry->id,
        ]);
    }

    public function test_amount_must_be_numeric_and_not_negative(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/finanzas/2026-05-18/entradas', [
            'name' => 'Otros',
            'amount' => '-1',
            'type' => FinancialEntry::TYPE_EXPENSE,
            'is_active' => '1',
        ])->assertSessionHasErrors('amount');
    }

    public function test_income_entries_only_accept_income_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/finanzas/2026-05-18/entradas', [
            'name' => 'Comida',
            'amount' => '100',
            'type' => FinancialEntry::TYPE_INCOME,
            'is_active' => '1',
        ])->assertSessionHasErrors('name');

        $this->actingAs($user)->post('/admin/finanzas/2026-05-18/entradas', [
            'name' => 'Ingreso',
            'amount' => '100',
            'type' => FinancialEntry::TYPE_INCOME,
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();
    }
}
