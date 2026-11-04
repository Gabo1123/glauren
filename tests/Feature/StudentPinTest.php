<?php

namespace Tests\Feature;

use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentPinTest extends TestCase
{
    use RefreshDatabase;

    private function student(): Student
    {
        $school = School::create(['name' => 'Colegio X', 'slug' => 'cx', 'active' => true, 'payment_mode' => 'estudiante']);
        $c = Contract::create(['school_id' => $school->id, 'code' => 'CTR-P', 'year' => 2027, 'production_status' => 'aprobado', 'terms' => 'x']);

        return Student::create(['contract_id' => $c->id, 'name' => 'Ana', 'document' => '1001', 'phone' => '3001234567', 'pin' => Hash::make('1111')]);
    }

    public function test_forgot_pin_flow_works_and_pages_can_be_reloaded(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $sent = [];
        $wa = \Mockery::mock(WhatsAppService::class);
        $wa->shouldReceive('normalize')->andReturn('573001234567');
        $wa->shouldReceive('configured')->andReturn(true);
        $wa->shouldReceive('sendText')->andReturnUsing(function ($to, $msg) use (&$sent) { $sent[] = $msg; return ["ok" => true]; });
        $this->app->instance(WhatsAppService::class, $wa);

        $this->student();
        $this->post(route('student.doc'), ['document' => '1001'])->assertOk();

        // Abrir la URL directamente ya no da error
        $this->get('/estudiante/olvide-pin')->assertRedirect(route('student.resume'));

        $this->post(route('student.forgot'))->assertRedirect(route('student.reset.show'));
        $this->get(route('student.reset.show'))->assertOk()->assertSee('Restablecer PIN')->assertSee('•••4567', false);
        $this->get('/estudiante/olvide-pin')->assertOk()->assertSee('Restablecer PIN');

        preg_match('/\b(\d{4})\b/', $sent[0], $m);
        // Código malo → vuelve a la pantalla con el error
        $this->post(route('student.reset'), ['code' => $m[1] === '0000' ? '0001' : '0000', 'pin' => '2222', 'pin2' => '2222'])
            ->assertRedirect(route('student.reset.show'));
        // PIN que no coincide → vuelve a la misma pantalla (GET), no a un error
        $this->from(route('student.reset.show'))->post(route('student.reset'), ['code' => $m[1], 'pin' => '2222', 'pin2' => '3333'])
            ->assertRedirect(route('student.reset.show'));
        $this->post(route('student.reset'), ['code' => $m[1], 'pin' => '2222', 'pin2' => '2222'])->assertRedirect(route('student.home'));
        $this->assertTrue(Hash::check('2222', Student::first()->pin));
    }

    public function test_without_whatsapp_shows_message_instead_of_error(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $wa = \Mockery::mock(WhatsAppService::class);
        $wa->shouldReceive('normalize')->andReturn(null);
        $wa->shouldReceive('configured')->andReturn(false);
        $this->app->instance(WhatsAppService::class, $wa);

        $this->student();
        $this->post(route('student.doc'), ['document' => '1001']);
        $this->post(route('student.forgot'))->assertRedirect(route('student.resume'));
        $this->get(route('student.resume'))->assertOk()->assertSee('No tienes un WhatsApp registrado');
    }
}
