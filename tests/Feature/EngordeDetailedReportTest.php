<?php

namespace Tests\Feature;

use App\Livewire\Engorde\Index;
use App\Livewire\Engorde\Show;
use App\Models\Animal;
use App\Models\EngordeAnimal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\PesajeEngorde;
use App\Models\Raza;
use App\Models\User;
use App\Support\EngordeReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EngordeDetailedReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_lot_table_shows_animal_photo_and_latest_control_metrics(): void
    {
        [$user, $fundo, $animal, $lot, $engorde] = $this->scenario();
        PesajeEngorde::create(['engorde_animal_id' => $engorde->id, 'fecha' => '2026-01-10', 'peso_kg' => 120]);
        PesajeEngorde::create(['engorde_animal_id' => $engorde->id, 'fecha' => '2026-01-05', 'peso_kg' => 130]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $loaded = EngordeReport::loadLots($fundo->id, [$lot->id])->first()->animales->first();
        $metrics = $loaded->reportMetrics();
        $this->assertSame('120.00', $loaded->ultimoPesaje->peso_kg);
        $this->assertSame(20.0, $metrics['gain_kg']);
        $this->assertSame(20.0, $metrics['gain_percentage']);

        $this->get(route('engorde.lote.show', $lot))
            ->assertOk()
            ->assertSee('src="/storage/fotos/animales/reporte.webp"', false)
            ->assertSee('Último control')
            ->assertSee('GMD:');
    }

    public function test_individual_and_general_detailed_reports_download_and_reject_foreign_lots(): void
    {
        [$user, $fundo, , $lot] = $this->scenario();
        $otherFundo = Fundo::create(['nombre' => 'Otro fundo', 'activo' => true]);
        $foreignLot = LoteEngorde::create([
            'fundo_id' => $otherFundo->id,
            'codigo' => 'LOT26-999',
            'fecha_inicio' => '2026-01-01',
            'estado' => 'activo',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Show::class, ['id' => $lot->id])
            ->call('exportDetailedReport', ['codigo', 'nombre', 'peso_inicial', 'ganancia_kg'])
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();

        Livewire::test(Index::class)
            ->call('exportDetailedReport', 'selected', [(string) $lot->id], ['codigo', 'nombre', 'ganancia_kg'])
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();

        Livewire::test(Index::class)
            ->call('exportDetailedReport', 'selected', [(string) $foreignLot->id], ['codigo'])
            ->assertHasErrors('detailedReportLotIds');
    }

    public function test_detailed_pdf_is_horizontal_grouped_and_contains_no_real_images(): void
    {
        [$user, $fundo, , $lot] = $this->scenario();
        $lots = EngordeReport::loadLots($fundo->id, [$lot->id]);
        $summary = EngordeReport::summarize($lots);
        $data = [
            'lots' => $lots,
            'selectedColumns' => ['codigo', 'nombre', 'foto_registrada', 'especie_raza', 'ganancia_kg'],
            'summary' => $summary,
            'fundo' => $fundo,
            'generatedBy' => $user->name,
            'generatedAt' => now(),
            'administrators' => $user->name,
            'reportSummary' => '1 lote.',
            'filterSummary' => 'Lote seleccionado',
            'title' => 'Reporte detallado',
        ];
        $html = view('pdf.engorde-detallado', $data)->render();
        $this->assertStringContainsString($lot->codigo, $html);
        $this->assertStringContainsString('Foto registrada', $html);
        $this->assertStringNotContainsString('<img', $html);

        $pdf = Pdf::loadView('pdf.engorde-detallado', $data)->setPaper('a4', 'landscape');
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $this->assertGreaterThan($canvas->get_height(), $canvas->get_width());
    }

    private function scenario(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Holstein', 'activo' => true]);
        $animal = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-001',
            'nombre' => 'Animal reporte',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'estado_reproductivo' => 'vacia',
            'tipo_alta' => 'compra',
            'fecha_alta' => '2025-01-01',
            'foto_ruta' => 'fotos/animales/reporte.webp',
            'activo' => true,
        ]);
        $lot = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT26-001',
            'nombre' => 'Lote reporte',
            'fecha_inicio' => '2026-01-01',
            'estado' => 'activo',
        ]);
        $engorde = EngordeAnimal::create([
            'lote_id' => $lot->id,
            'animal_id' => $animal->id,
            'peso_inicial' => 100,
            'peso_actual' => 100,
            'estado' => 'engorde_activo',
            'fecha_ingreso' => '2026-01-01',
        ]);

        return [$user, $fundo, $animal, $lot, $engorde];
    }
}
