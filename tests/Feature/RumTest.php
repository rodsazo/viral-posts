<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ContentPiece;
use App\Support\Rum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RumTest extends TestCase
{
    use RefreshDatabase;

    private function factors(string $value): array
    {
        return [
            'amplitud' => $value,
            'intensidad' => $value,
            'universalidad' => $value,
            'inmediatez' => $value,
            'independencia' => $value,
        ];
    }

    public function test_compute_product_rounded_to_one_decimal(): void
    {
        $this->assertSame(10.0, Rum::compute($this->factors('1.5848')));
        $this->assertSame(1.0, Rum::compute($this->factors('1')));
        $this->assertSame(4.2, Rum::compute([
            'amplitud' => '1.5848',
            'intensidad' => '1.3',
            'universalidad' => '1.5848',
            'inmediatez' => '1.3',
            'independencia' => '1',
        ]));
    }

    public function test_compute_is_null_when_a_factor_is_missing(): void
    {
        $this->assertNull(Rum::compute(['amplitud' => '1.5848']));
        $this->assertNull(Rum::compute(null));
    }

    public function test_color_thresholds(): void
    {
        $this->assertSame('danger', Rum::color(5.0));   // <= 5 rojo
        $this->assertSame('warning', Rum::color(6.0));  // 5–7 amarillo
        $this->assertSame('warning', Rum::color(7.0));  // límite superior amarillo
        $this->assertSame('success', Rum::color(7.1));  // > 7 verde
        $this->assertSame('gray', Rum::color(null));
    }

    public function test_saving_a_piece_recomputes_rum_from_factors(): void
    {
        $account = Account::factory()->create();

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'rum_factors' => $this->factors('1.5848'),
        ]);

        $this->assertSame(10.0, $piece->refresh()->rum);

        $piece->update(['rum_factors' => $this->factors('1')]);
        $this->assertSame(1.0, $piece->refresh()->rum);
    }
}
