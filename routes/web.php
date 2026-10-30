<?php

use App\Modules\Chaquetas\Http\Controllers\Admin\AuthController;
use App\Modules\Chaquetas\Http\Controllers\Admin\DashboardController;
use App\Modules\Chaquetas\Http\Controllers\Admin\JacketTypeController;
use App\Modules\Chaquetas\Http\Controllers\Admin\QuoteController;
use App\Modules\Chaquetas\Http\Controllers\Admin\MenuController;
use App\Modules\Chaquetas\Http\Controllers\Admin\PageController as AdminPageController;
use App\Modules\Chaquetas\Http\Controllers\Admin\HomeBlockController;
use App\Modules\Chaquetas\Http\Controllers\Admin\PaymentApprovalController;
use App\Modules\Chaquetas\Http\Controllers\Admin\PaymentReminderController;
use App\Modules\Chaquetas\Http\Controllers\Admin\PostController as AdminPostController;
use App\Modules\Chaquetas\Http\Controllers\Admin\CollectionController;
use App\Modules\Chaquetas\Http\Controllers\Admin\ContractController;
use App\Modules\Chaquetas\Http\Controllers\Admin\ExpenseController;
use App\Modules\Chaquetas\Http\Controllers\Admin\ContractPhotoController;
use App\Modules\Chaquetas\Http\Controllers\Admin\FaqController;
use App\Modules\Chaquetas\Http\Controllers\Admin\SchoolController;
use App\Modules\Chaquetas\Http\Controllers\Admin\SeoController;
use App\Modules\Chaquetas\Http\Controllers\Admin\SettingController;
use App\Modules\Chaquetas\Http\Controllers\Admin\ShowcaseController;
use App\Modules\Chaquetas\Http\Controllers\Admin\StudentController;
use App\Modules\Chaquetas\Http\Controllers\CollectionController as PublicCollectionController;
use App\Modules\Chaquetas\Http\Controllers\ConfiguradorController;
use App\Modules\Chaquetas\Http\Controllers\HomeController;
use App\Modules\Chaquetas\Http\Controllers\LeadController;
use App\Modules\Chaquetas\Http\Controllers\NewsController;
use App\Modules\Chaquetas\Http\Controllers\PageController;
use App\Modules\Chaquetas\Http\Controllers\PhotographyController;
use App\Modules\Chaquetas\Http\Controllers\Foto\AuthController as FotoAuthController;
use App\Modules\Chaquetas\Http\Controllers\Foto\PanelController as FotoPanelController;
use App\Modules\Chaquetas\Http\Controllers\PaymentController;
use App\Modules\Chaquetas\Http\Controllers\Portal\AuthController as PortalAuthController;
use App\Modules\Chaquetas\Http\Controllers\Portal\PortalController;
use App\Modules\Chaquetas\Http\Controllers\SeguimientoController;
use App\Modules\Chaquetas\Http\Controllers\SignController;
use App\Modules\Chaquetas\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Público — Home premium + configurador
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/mi-chaqueta', [SeguimientoController::class, 'index'])->name('seguimiento');
Route::post('/mi-chaqueta', [SeguimientoController::class, 'search'])->middleware('throttle:15,1')->name('seguimiento.search');
Route::post('/mi-chaqueta/pago', [SeguimientoController::class, 'declarePayment'])->middleware('throttle:10,10')->name('seguimiento.pay');
Route::post('/mi-chaqueta/telefono', [SeguimientoController::class, 'savePhone'])->middleware('throttle:10,10')->name('seguimiento.phone');
Route::get('/colegios', [HomeController::class, 'colegios'])->name('colegios');
Route::get('/contacto', [\App\Modules\Chaquetas\Http\Controllers\ContactController::class, 'show'])->name('contacto');
Route::post('/contacto', [\App\Modules\Chaquetas\Http\Controllers\ContactController::class, 'submit'])->name('contacto.submit');
Route::get('/contacto/horarios', [\App\Modules\Chaquetas\Http\Controllers\ContactController::class, 'slots'])->name('contacto.slots');

// Portal del estudiante (perfil, fotos y muro del curso) — acceso por documento + PIN
Route::prefix('estudiante')->name('student.')->group(function () {
    Route::get('/', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'showLogin'])->name('login');
    Route::post('/documento', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'checkDocument'])->name('doc');
    Route::get('/documento', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'resume']);
    Route::post('/entrar', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'auth'])->middleware('throttle:10,1')->name('auth');
    Route::get('/entrar', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'resume']);
    Route::post('/olvide-pin', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'forgotPin'])->middleware('throttle:5,10')->name('forgot');
    Route::post('/restablecer-pin', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'resetPin'])->middleware('throttle:10,10')->name('reset');
    Route::post('/salir', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'logout'])->name('logout');
    Route::get('/mi-perfil', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'home'])->name('home');
    Route::post('/perfil', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'updateProfile'])->middleware('throttle:20,1')->name('profile');
    Route::post('/publicar', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'post'])->middleware('throttle:20,1')->name('post');
    Route::post('/publicacion/{post}/like', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'toggleLike'])->middleware('throttle:60,1')->name('post.like');
    Route::post('/votar-diseno/{option}', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'voteDesign'])->name('vote');
    Route::post('/publicacion/{post}/comentar', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'comment'])->middleware('throttle:30,1')->name('comment');
    Route::get('/anuario', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'yearbook'])->name('yearbook');
    Route::delete('/publicacion/{post}', [\App\Modules\Chaquetas\Http\Controllers\StudentPortalController::class, 'deletePost'])->name('post.delete');
});
Route::post('/promo-gratis', [\App\Modules\Chaquetas\Http\Controllers\ContactController::class, 'popup'])->name('contacto.popup');

// Registro público de estudiante por QR (link del contrato)
Route::get('/unirme/{token}', [\App\Modules\Chaquetas\Http\Controllers\JoinController::class, 'show'])->name('join.show');
Route::post('/unirme/{token}', [\App\Modules\Chaquetas\Http\Controllers\JoinController::class, 'store'])->middleware('throttle:20,10')->name('join.store');

Route::get('/coleccion/{slug}', [PublicCollectionController::class, 'show'])->name('collection.show');

Route::get('/personalizar', [ConfiguradorController::class, 'index'])->name('personalizar');
Route::get('/personalizar/{slug}', [ConfiguradorController::class, 'design'])->name('design');
Route::post('/cotizar', [ConfiguradorController::class, 'storeQuote'])->name('quote.store');
Route::get('/cotizacion/{token}', [\App\Modules\Chaquetas\Http\Controllers\QuotePublicController::class, 'show'])->where('token', '[A-Za-z0-9]{20,40}')->name('cotizacion.show');
Route::post('/lead', [LeadController::class, 'store'])->name('lead.store');

// CMS público
Route::get('/ranking', [\App\Modules\Chaquetas\Http\Controllers\RankingController::class, 'index'])->name('ranking');
Route::post('/ranking', [\App\Modules\Chaquetas\Http\Controllers\RankingController::class, 'store'])->name('ranking.store');
Route::post('/ranking/{entry}/votar', [\App\Modules\Chaquetas\Http\Controllers\RankingController::class, 'vote'])->name('ranking.vote');

Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/p/{slug}', [PageController::class, 'show'])->name('page.show');

// Fotografía de grados (página pública independiente del prom)
Route::get('/fotografia-de-grados', [PhotographyController::class, 'index'])->name('fotografia');
Route::post('/fotografia-de-grados/contacto', [PhotographyController::class, 'lead'])->middleware('throttle:10,10')->name('fotografia.lead');

// Firma de contrato (enlace único para el colegio)
Route::get('/contrato/{token}', [SignController::class, 'show'])->name('contrato.firmar');
Route::post('/contrato/{token}', [SignController::class, 'sign'])->name('contrato.sign');

// Firma múltiple (varios acudientes, cada uno con su enlace + código)
Route::get('/firmar/{token}', [\App\Modules\Chaquetas\Http\Controllers\MultiSignController::class, 'show'])->name('firmar.multi');
Route::post('/firmar/{token}/codigo', [\App\Modules\Chaquetas\Http\Controllers\MultiSignController::class, 'sendCode'])->middleware('throttle:5,10')->name('firmar.codigo');
Route::post('/firmar/{token}/verificar', [\App\Modules\Chaquetas\Http\Controllers\MultiSignController::class, 'verify'])->middleware('throttle:10,10')->name('firmar.verificar');
Route::post('/firmar/{token}/firmar', [\App\Modules\Chaquetas\Http\Controllers\MultiSignController::class, 'sign'])->middleware('throttle:10,10')->name('firmar.guardar');

// Pagos Rapyd
Route::get('/pagar/{student}', [PaymentController::class, 'start'])->name('pay.start')->middleware('signed');
Route::get('/pago/completado/{student}', [PaymentController::class, 'complete'])->name('pay.complete')->middleware('signed');
Route::get('/pago/cancelado', [PaymentController::class, 'cancel'])->name('pay.cancel');
Route::post('/rapyd/webhook', [PaymentController::class, 'webhook'])->name('rapyd.webhook');

// Webhook de WhatsApp (auto-respuesta). Verificación GET + mensajes POST.
Route::get('/whatsapp/webhook', [\App\Modules\Chaquetas\Http\Controllers\WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [\App\Modules\Chaquetas\Http\Controllers\WhatsAppWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Portal del colegio
|--------------------------------------------------------------------------
*/
Route::prefix('colegio')->name('portal.')->group(function () {
    Route::get('login', [PortalAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [PortalAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [PortalAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:colegio'])->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('contrato/{contract}', [PortalController::class, 'contract'])->name('contract');
        Route::post('contrato/{contract}/mensaje', [PortalController::class, 'sendMessage'])->name('message');
        Route::post('contrato/{contract}/aprobar-diseno', [PortalController::class, 'approveDesign'])->name('design.approve');
        Route::post('contrato/{contract}/cambio/{change}', [PortalController::class, 'decideChange'])->name('change.decide');
        Route::get('contrato/{contract}/certificado', [PortalController::class, 'certificate'])->name('certificate');
        Route::get('contrato/{contract}/documento', [PortalController::class, 'contractDocument'])->name('contract.doc');
        Route::post('contrato/{contract}/alumnos', [PortalController::class, 'addStudent'])->name('students.store');
        Route::delete('contrato/{contract}/alumnos/{student}', [PortalController::class, 'deleteStudent'])->name('students.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Panel de Fotografía de Grados (rol: foto) — separado del prom
|--------------------------------------------------------------------------
*/
Route::prefix('gfoto')->name('foto.')->group(function () {
    Route::get('login', [FotoAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [FotoAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::post('logout', [FotoAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:foto'])->group(function () {
        Route::get('/', [FotoPanelController::class, 'dashboard'])->name('dashboard');

        // Categorías
        Route::get('categorias/crear', [FotoPanelController::class, 'createCategory'])->name('categories.create');
        Route::post('categorias', [FotoPanelController::class, 'storeCategory'])->name('categories.store');
        Route::get('categorias/{category}', [FotoPanelController::class, 'editCategory'])->name('categories.edit');
        Route::put('categorias/{category}', [FotoPanelController::class, 'updateCategory'])->name('categories.update');
        Route::delete('categorias/{category}', [FotoPanelController::class, 'deleteCategory'])->name('categories.destroy');

        // Servicios
        Route::get('categorias/{category}/servicio', [FotoPanelController::class, 'createService'])->name('services.create');
        Route::post('categorias/{category}/servicio', [FotoPanelController::class, 'storeService'])->name('services.store');
        Route::get('servicio/{service}', [FotoPanelController::class, 'editService'])->name('services.edit');
        Route::put('servicio/{service}', [FotoPanelController::class, 'updateService'])->name('services.update');
        Route::delete('servicio/{service}', [FotoPanelController::class, 'deleteService'])->name('services.destroy');

        // Galería (fotos de ejemplo / muestras)
        Route::post('galeria', [FotoPanelController::class, 'storeGallery'])->name('gallery.store');
        Route::delete('galeria/{image}', [FotoPanelController::class, 'deleteGalleryImage'])->name('gallery.destroy');

        // Paquetes
        Route::get('paquetes/crear', [FotoPanelController::class, 'createPackage'])->name('packages.create');
        Route::post('paquetes', [FotoPanelController::class, 'storePackage'])->name('packages.store');
        Route::get('paquetes/{package}', [FotoPanelController::class, 'editPackage'])->name('packages.edit');
        Route::put('paquetes/{package}', [FotoPanelController::class, 'updatePackage'])->name('packages.update');
        Route::delete('paquetes/{package}', [FotoPanelController::class, 'deletePackage'])->name('packages.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Administrador
|--------------------------------------------------------------------------
*/
// Panel administrativo. La URL es privada (/gtorres); los nombres de ruta siguen siendo admin.*
Route::prefix('gtorres')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Verificación en dos pasos (2FA) por WhatsApp
    Route::get('verificar', [AuthController::class, 'showTwoFactor'])->name('2fa');
    Route::post('verificar', [AuthController::class, 'verifyTwoFactor'])->middleware('throttle:6,1')->name('2fa.verify');
    Route::post('verificar/reenviar', [AuthController::class, 'resendTwoFactor'])->middleware('throttle:3,1')->name('2fa.resend');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('tipos', [JacketTypeController::class, 'index'])->name('types.index');
        Route::get('tipos/crear', [JacketTypeController::class, 'create'])->name('types.create');
        Route::post('tipos', [JacketTypeController::class, 'store'])->name('types.store');
        Route::get('tipos/{type}', [JacketTypeController::class, 'edit'])->name('types.edit');
        Route::put('tipos/{type}', [JacketTypeController::class, 'update'])->name('types.update');
        Route::put('tipos/{type}/materiales', [JacketTypeController::class, 'saveComponents'])->name('types.components');

        Route::get('cotizaciones', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('cotizaciones/crear', [QuoteController::class, 'create'])->name('quotes.create');
        Route::post('cotizaciones', [QuoteController::class, 'store'])->name('quotes.store');
        Route::get('cotizaciones/{quote}/enviar', [QuoteController::class, 'send'])->name('quotes.send');
        Route::post('cotizaciones/{quote}/correo', [QuoteController::class, 'email'])->name('quotes.email');
        Route::post('cotizaciones/{quote}/whatsapp', [QuoteController::class, 'whatsapp'])->name('quotes.whatsapp');
        Route::get('cotizaciones/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
        Route::post('cotizaciones/{quote}/nota', [QuoteController::class, 'note'])->name('quotes.note');
        Route::get('cotizaciones/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
        Route::put('cotizaciones/{quote}', [QuoteController::class, 'update'])->name('quotes.update');

        // Colegios
        Route::get('colegios', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('colegios/crear', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('colegios', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('colegios/{school}', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('colegios/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::delete('colegios/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');

        // Contratos
        Route::get('contratos', [ContractController::class, 'index'])->name('contracts.index');
        Route::get('contratos/crear', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('contratos', [ContractController::class, 'store'])->name('contracts.store');
        Route::get('contratos/{contract}', [ContractController::class, 'show'])->name('contracts.show');
        Route::post('contratos/{contract}/responder', [ContractController::class, 'reply'])->name('contracts.reply');
        Route::post('contratos/{contract}/firmante', [ContractController::class, 'addSigner'])->name('contracts.signer.add');
        Route::delete('contratos/{contract}/firmante/{signer}', [ContractController::class, 'deleteSigner'])->name('contracts.signer.delete');
        Route::put('contratos/{contract}/votacion', [ContractController::class, 'saveVotingSettings'])->name('contracts.voting');
        Route::post('contratos/{contract}/diseno-opcion', [ContractController::class, 'addDesignOption'])->name('contracts.design.add');
        Route::delete('contratos/{contract}/diseno-opcion/{option}', [ContractController::class, 'deleteDesignOption'])->name('contracts.design.delete');
        Route::get('contratos/{contract}/editar', [ContractController::class, 'edit'])->name('contracts.edit');
        Route::put('contratos/{contract}', [ContractController::class, 'update'])->name('contracts.update');
        Route::put('contratos/{contract}/estado', [ContractController::class, 'updateStatus'])->name('contracts.status');
        Route::put('contratos/{contract}/materiales', [ContractController::class, 'saveMaterials'])->name('contracts.materials');
        Route::get('contratos/{contract}/contrato', [ContractController::class, 'contract'])->name('contracts.contract');
        Route::put('contratos/{contract}/contrato', [ContractController::class, 'saveTerms'])->name('contracts.terms');
        Route::post('contratos/{contract}/enviar', [ContractController::class, 'send'])->name('contracts.send');
        Route::post('contratos/{contract}/fotos', [ContractPhotoController::class, 'store'])->name('contracts.photos.store');
        Route::delete('contratos/{contract}/fotos/{photo}', [ContractPhotoController::class, 'destroy'])->name('contracts.photos.destroy');
        Route::delete('contratos/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');

        // Alumnos (dentro de un contrato)
        Route::post('contratos/{contract}/alumnos', [StudentController::class, 'store'])->name('students.store');
        Route::post('contratos/{contract}/alumnos/importar', [StudentController::class, 'import'])->name('students.import');
        Route::get('contratos/{contract}/alumnos/plantilla', [StudentController::class, 'template'])->name('students.template');
        Route::get('contratos/{contract}/alumnos/exportar', [StudentController::class, 'export'])->name('students.export');
        Route::get('contratos/{contract}/alumnos/medidas', [StudentController::class, 'measures'])->name('students.measures');
        Route::put('contratos/{contract}/alumnos/medidas', [StudentController::class, 'saveMeasures'])->name('students.measures.save');
        Route::get('contratos/{contract}/alumnos/{student}', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('contratos/{contract}/alumnos/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('contratos/{contract}/alumnos/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::put('contratos/{contract}/alumnos/{student}/pago', [StudentController::class, 'togglePay'])->name('students.pay');
        Route::put('contratos/{contract}/alumnos/{student}/avisos', [StudentController::class, 'toggleNotify'])->name('students.notify');
        Route::put('contratos/{contract}/alumnos/{student}/pin', [StudentController::class, 'resetPin'])->name('students.pin');

        // Finanzas (panel global)
        Route::get('finanzas', [\App\Modules\Chaquetas\Http\Controllers\Admin\FinanceController::class, 'index'])->name('finance.index');

        // Leads / Contactos (correos capturados antes de diseñar)
        Route::get('leads', [\App\Modules\Chaquetas\Http\Controllers\Admin\LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/{lead}', [\App\Modules\Chaquetas\Http\Controllers\Admin\LeadController::class, 'show'])->name('leads.show');
        Route::put('leads/{lead}/ia', [\App\Modules\Chaquetas\Http\Controllers\Admin\LeadController::class, 'toggleAi'])->name('leads.ai');
        Route::delete('leads/{lead}', [\App\Modules\Chaquetas\Http\Controllers\Admin\LeadController::class, 'destroy'])->name('leads.destroy');

        // Gastos / Facturas (contabilidad)
        Route::get('gastos', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('gastos/exportar', [ExpenseController::class, 'export'])->name('expenses.export');
        Route::get('gastos/crear', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('gastos', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('gastos/{expense}', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('gastos/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('gastos/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // SEO
        Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
        Route::put('seo/global', [SeoController::class, 'updateGlobals'])->name('seo.globals');
        Route::put('seo/pagina/{seoPage}', [SeoController::class, 'updatePage'])->name('seo.page');
        Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
        Route::put('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
        Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

        // CMS
        Route::get('contenido', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('contenido', [SettingController::class, 'update'])->name('settings.update');

        // Citas / Contacto (correo)
        Route::get('citas', [\App\Modules\Chaquetas\Http\Controllers\Admin\AppointmentController::class, 'index'])->name('appointments.index');
        Route::put('citas/config', [\App\Modules\Chaquetas\Http\Controllers\Admin\AppointmentController::class, 'saveConfig'])->name('appointments.config');
        Route::delete('citas/pdf', [\App\Modules\Chaquetas\Http\Controllers\Admin\AppointmentController::class, 'removePdf'])->name('appointments.pdf.remove');
        Route::put('citas/{appointment}/estado', [\App\Modules\Chaquetas\Http\Controllers\Admin\AppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::delete('citas/{appointment}', [\App\Modules\Chaquetas\Http\Controllers\Admin\AppointmentController::class, 'destroy'])->name('appointments.destroy');

        // Difusión / anuncios masivos por WhatsApp
        Route::get('difusion', [\App\Modules\Chaquetas\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('difusion', [\App\Modules\Chaquetas\Http\Controllers\Admin\BroadcastController::class, 'send'])->name('broadcast.send');

        // Configuración de llaves (API)
        Route::get('config-api', [\App\Modules\Chaquetas\Http\Controllers\Admin\ApiKeysController::class, 'edit'])->name('apikeys.edit');
        Route::put('config-api', [\App\Modules\Chaquetas\Http\Controllers\Admin\ApiKeysController::class, 'update'])->name('apikeys.update');

        // Entrenar IA de WhatsApp
        Route::get('ia', [\App\Modules\Chaquetas\Http\Controllers\Admin\AiTrainingController::class, 'edit'])->name('ia.edit');
        Route::put('ia', [\App\Modules\Chaquetas\Http\Controllers\Admin\AiTrainingController::class, 'update'])->name('ia.update');
        Route::match(['POST', 'PUT'], 'ia/probar', [\App\Modules\Chaquetas\Http\Controllers\Admin\AiTrainingController::class, 'test'])->name('ia.test');

        Route::get('colecciones', [CollectionController::class, 'index'])->name('collections.index');
        Route::get('colecciones/crear', [CollectionController::class, 'create'])->name('collections.create');
        Route::post('colecciones', [CollectionController::class, 'store'])->name('collections.store');
        Route::get('colecciones/{collection}', [CollectionController::class, 'edit'])->name('collections.edit');
        Route::put('colecciones/{collection}', [CollectionController::class, 'update'])->name('collections.update');
        Route::delete('colecciones/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');
        Route::delete('colecciones/{collection}/imagenes/{image}', [CollectionController::class, 'destroyImage'])->name('collections.images.destroy');

        Route::get('promociones', [ShowcaseController::class, 'index'])->name('showcase.index');
        Route::post('promociones', [ShowcaseController::class, 'store'])->name('showcase.store');
        Route::put('promociones/{photo}', [ShowcaseController::class, 'update'])->name('showcase.update');
        Route::delete('promociones/{photo}', [ShowcaseController::class, 'destroy'])->name('showcase.destroy');

        Route::get('testimonios', [\App\Modules\Chaquetas\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('testimonials.index');
        Route::post('testimonios', [\App\Modules\Chaquetas\Http\Controllers\Admin\TestimonialController::class, 'store'])->name('testimonials.store');
        Route::put('testimonios/{testimonial}', [\App\Modules\Chaquetas\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('testimonios/{testimonial}', [\App\Modules\Chaquetas\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('testimonials.destroy');

        Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
        Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
        Route::get('menu/{menu}', [MenuController::class, 'edit'])->name('menu.edit');
        Route::put('menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');

        Route::get('paginas', [AdminPageController::class, 'index'])->name('pages.index');
        Route::get('paginas/crear', [AdminPageController::class, 'create'])->name('pages.create');
        Route::post('paginas', [AdminPageController::class, 'store'])->name('pages.store');
        Route::get('paginas/{page}', [AdminPageController::class, 'edit'])->name('pages.edit');
        Route::put('paginas/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        Route::delete('paginas/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');

        Route::get('noticias', [AdminPostController::class, 'index'])->name('posts.index');
        Route::get('noticias/crear', [AdminPostController::class, 'create'])->name('posts.create');
        Route::post('noticias', [AdminPostController::class, 'store'])->name('posts.store');
        Route::get('noticias/{post}', [AdminPostController::class, 'edit'])->name('posts.edit');
        Route::put('noticias/{post}', [AdminPostController::class, 'update'])->name('posts.update');
        Route::delete('noticias/{post}', [AdminPostController::class, 'destroy'])->name('posts.destroy');

        // Ranking de colegios (moderación)
        Route::get('ranking', [\App\Modules\Chaquetas\Http\Controllers\Admin\RankingController::class, 'index'])->name('ranking.index');
        Route::put('ranking/{entry}', [\App\Modules\Chaquetas\Http\Controllers\Admin\RankingController::class, 'update'])->name('ranking.update');
        Route::post('ranking/{entry}/visibilidad', [\App\Modules\Chaquetas\Http\Controllers\Admin\RankingController::class, 'toggle'])->name('ranking.toggle');
        Route::delete('ranking/{entry}', [\App\Modules\Chaquetas\Http\Controllers\Admin\RankingController::class, 'destroy'])->name('ranking.destroy');

        // Pagos por aprobar (comprobantes de estudiantes)
        Route::get('pagos-aprobar', [PaymentApprovalController::class, 'index'])->name('approvals.index');
        Route::post('pagos-aprobar/{student}/aprobar', [PaymentApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('pagos-aprobar/{student}/rechazar', [PaymentApprovalController::class, 'reject'])->name('approvals.reject');

        // Recordatorios de pago
        Route::get('recordatorios', [PaymentReminderController::class, 'index'])->name('reminders.index');
        Route::put('recordatorios/plantilla', [PaymentReminderController::class, 'saveTemplate'])->name('reminders.template');
        Route::post('recordatorios/{contract}/enviado', [PaymentReminderController::class, 'markSent'])->name('reminders.sent');
        Route::post('recordatorios/{contract}/enviar-auto', [PaymentReminderController::class, 'sendAuto'])->name('reminders.auto');
        Route::post('recordatorios/enviar-todos', [PaymentReminderController::class, 'sendAll'])->name('reminders.all');
        Route::post('contratos/{contract}/recordar-abono', [PaymentReminderController::class, 'remindStudents'])->name('reminders.students');

        // Diseño del home (reordenar/ocultar secciones)
        Route::get('diseno-home', [\App\Modules\Chaquetas\Http\Controllers\Admin\HomeDesignController::class, 'index'])->name('homedesign.index');
        Route::post('diseno-home', [\App\Modules\Chaquetas\Http\Controllers\Admin\HomeDesignController::class, 'save'])->name('homedesign.save');

        // Bloques del home (editor modular tipo Elementor)
        Route::get('bloques', [HomeBlockController::class, 'index'])->name('blocks.index');
        Route::get('bloques/crear', [HomeBlockController::class, 'create'])->name('blocks.create');
        Route::post('bloques', [HomeBlockController::class, 'store'])->name('blocks.store');
        Route::post('bloques/reordenar', [HomeBlockController::class, 'reorder'])->name('blocks.reorder');
        Route::get('bloques/{block}', [HomeBlockController::class, 'edit'])->name('blocks.edit');
        Route::put('bloques/{block}', [HomeBlockController::class, 'update'])->name('blocks.update');
        Route::post('bloques/{block}/visibilidad', [HomeBlockController::class, 'toggle'])->name('blocks.toggle');
        Route::delete('bloques/{block}', [HomeBlockController::class, 'destroy'])->name('blocks.destroy');
    });
});
