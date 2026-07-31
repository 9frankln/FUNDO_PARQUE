<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AsignacionFamiliar;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\Movimiento;
use App\Models\Ordeno;
use App\Models\OrdenoDetalle;
use App\Models\Parto;
use App\Models\PesajeEngorde;
use App\Models\ProduccionQueso;
use App\Models\ProfilaxisRegistro;
use App\Models\SanidadRegistro;
use App\Models\User;
use Tests\TestCase;

class UppercasePersistenceTest extends TestCase
{
    public function test_suite_uses_isolated_in_memory_database(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_business_text_fields_are_normalized_to_uppercase(): void
    {
        $fieldsByModel = [
            User::class => ['name'],
            Fundo::class => ['nombre'],
            Animal::class => ['arete', 'nombre', 'observaciones'],
            LoteEngorde::class => ['codigo', 'nombre', 'observaciones'],
            PesajeEngorde::class => ['observaciones'],
            Ordeno::class => ['observaciones'],
            OrdenoDetalle::class => ['justificacion_otros'],
            ProduccionQueso::class => ['observaciones'],
            Movimiento::class => ['descripcion'],
            AsignacionFamiliar::class => ['beneficiario', 'descripcion'],
            ProfilaxisRegistro::class => ['proposito', 'producto_marca', 'dosis', 'responsable', 'observaciones'],
            Parto::class => ['observaciones'],
        ];

        foreach ($fieldsByModel as $modelClass => $fields) {
            $model = new $modelClass;

            foreach ($fields as $field) {
                $model->{$field} = 'diagnóstico de cría';
                $this->assertSame('DIAGNÓSTICO DE CRÍA', $model->{$field}, $modelClass.'::'.$field);
            }
        }
    }

    public function test_credentials_paths_and_enum_keys_keep_original_case(): void
    {
        $user = new User;
        $user->email = 'Person@example.com';
        $animal = new Animal;
        $animal->foto_ruta = 'fotos/animales/Animal.webp';
        $animal->genero = 'hembra';
        $animal->nombre = null;

        $this->assertSame('Person@example.com', $user->email);
        $this->assertSame('fotos/animales/Animal.webp', $animal->foto_ruta);
        $this->assertSame('hembra', $animal->genero);
        $this->assertNull($animal->nombre);
    }

    public function test_sanidad_clinical_fields_are_normalized_to_lowercase(): void
    {
        $sanidad = new SanidadRegistro;
        $sanidad->sintomas_diagnostico = 'FIEBRE ALTA CON TOS';
        $sanidad->tratamiento = 'ANTIBIÓTICO ORAL';
        $sanidad->dosis_via = '5 ML VÍA ORAL';

        $this->assertSame('fiebre alta con tos', $sanidad->sintomas_diagnostico);
        $this->assertSame('antibiótico oral', $sanidad->tratamiento);
        $this->assertSame('5 ml vía oral', $sanidad->dosis_via);
    }
}
