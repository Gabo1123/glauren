<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\ContractSigner;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Models\StudentPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalImprovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_adds_phone_and_reminds_each_student(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $user = User::create(['name' => 'Rector', 'email' => 'r@t.co', 'password' => bcrypt('x'), 'role' => 'colegio']);
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'user_id' => $user->id, 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-S', 'year' => 2027, 'production_status' => 'aprobado', 'contract_status' => 'enviado', 'terms' => 'x']);

        $this->actingAs($user)->post(route('portal.students.store', $c), ['name' => 'Ana Ruiz', 'document' => '555', 'phone' => '300 123 4567'])
            ->assertRedirect();
        $ana = Student::firstOrFail();
        $this->assertSame('300 123 4567', $ana->phone);

        $this->actingAs($user)->patch(route('portal.students.phone', [$c, $ana]), ['phone' => '3109998877'])->assertRedirect();
        $this->assertSame('3109998877', $ana->fresh()->phone);
        $this->actingAs($user)->patch(route('portal.students.phone', [$c, $ana]), ['phone' => 'abc'])->assertSessionHasErrors('phone');

        $this->actingAs($user)->get(route('portal.contract', $c))->assertOk()
            ->assertSee('https://wa.me/573109998877?text=', false)->assertSee('Recordar')->assertSee('3109998877');
    }

    public function test_contract_html_is_rendered_and_dates_in_spanish(): void
    {
        $school = School::create(['name' => 'Colegio Sol', 'slug' => 'sol', 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-H', 'year' => 2027, 'production_status' => 'aprobado',
            'terms' => '<p><b>CLÁUSULA 1.</b> Objeto</p><p onclick="x()">Texto<script>alert(1)</script></p>']);
        $sg = ContractSigner::create(['contract_id' => $c->id, 'name' => 'Ana', 'token' => str_repeat('c', 40)]);

        $html = $this->get(route('firmar.multi', $sg->token))->assertOk()->getContent();
        $this->assertStringContainsString('<b>CLÁUSULA 1.</b>', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('onclick="x()"', $html);

        $st = Student::create(['contract_id' => $c->id, 'name' => 'Pedro', 'document' => '9', 'payment_status' => 'pendiente']);
        StudentPost::create(['contract_id' => $c->id, 'student_id' => $st->id, 'body' => 'Hola curso']);
        $this->withSession(['student_id' => $st->id])->get(route('student.home'))->assertOk()
            ->assertSee('hace')->assertDontSee('seconds ago')
            ->assertSee('Pagar mi chaqueta o subir comprobante')->assertSee(route('seguimiento.search'), false);
    }
}
