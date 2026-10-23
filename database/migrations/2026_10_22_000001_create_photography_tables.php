<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('icon', 16)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('photo_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('photo_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->json('features')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $this->seed();
    }

    private function seed(): void
    {
        $cats = [
            ['Fotografía profesional', '📷', 'Retratos, ceremonia y fotos de grupo', 'fotografía de graduación, fotos de grado, fotos de graduación bogotá, retrato de grado, fotografía de ceremonia, mosaico de grado', [
                ['Fotos pre-ceremonia (estudio)', 'Sesión previa con iluminación profesional', 'Fotos previas al grado en estudio, con asesoría para posar y escoger el fondo ideal. Individuales y con acompañantes.'],
                ['Cubrimiento de ceremonia', 'Los momentos más importantes del día', 'Registramos la entrega del diploma, el grado y los momentos con familiares durante toda la ceremonia.'],
                ['Retrato individual', 'Tu mejor foto de grado', 'Retrato personalizado con edición profesional que resalta lo mejor de ti el día de tu graduación.'],
                ['Fotos grupales', 'Con tu promoción y amigos', 'Tomas con el grupo completo y subgrupos por afinidad, en exteriores o el lugar favorito del curso.'],
                ['Mosaico de la promoción', 'Estilo universidad', 'Diseño del mosaico con la foto y el nombre de todos los integrantes de la promoción.'],
            ]],
            ['Anuarios y álbumes', '📕', 'El recuerdo que te acompaña siempre', 'anuario escolar, anuario del colegio, photobook de grado, álbum de graduación, carpetas fotográficas', [
                ['Anuario del colegio', 'Diseño e impresión personalizados', 'Anuario con fotos de graduación, actividades, salidas, dedicatorias y mensajes de compañeros y profesores.'],
                ['Photobook', 'Tu libro de recuerdos', 'Photobook con las mejores fotos de la promoción en impresión de alta calidad.'],
                ['Carpetas fotográficas', 'Infantiles y formales', 'Carpetas abullonadas marcadas con el logo de la institución, repujadas o impresas.'],
                ['Álbum de ceremonia', 'Conserva tus recuerdos', 'Álbum abullonado y marcado donde las fotos de la ceremonia quedan protegidas.'],
            ]],
            ['Togas y birretes', '🎓', 'Alquiler según la talla de cada estudiante', 'alquiler de togas, togas y birretes, estolas de grado, toga negra, toga azul, estola bufanda', [
                ['Alquiler de togas y birretes', 'Negro, azul y vino tinto', 'Alquiler de toga y birrete según la talla de cada estudiante. Diseños americano y cuello en V, para adultos y niños.'],
                ['Estolas', 'Tipo bufanda y tipo V', 'Estolas bordadas con el escudo de la institución y texto personalizado. Unicolor o bicolor.'],
            ]],
            ['Diplomas y actas', '📜', 'Documentos oficiales de grado', 'diplomas de grado, actas de grado, carpetas porta diploma, carpetas de grado', [
                ['Diplomas', 'Título académico', 'Diseño e impresión en papel nacarado o pergamino de alta calidad, con escudo repujado y marcas de seguridad.'],
                ['Actas de grado', 'Diseño institucional', 'Impresas en materiales de alta calidad con el diseño institucional de cada promoción.'],
                ['Carpetas porta-diploma', 'Protege tu diploma', 'Carpetas abullonadas y repujadas con el escudo de la institución, para diploma, actas o fotos.'],
            ]],
            ['Souvenirs y recuerdos', '🎁', 'Detalles personalizados de la promoción', 'souvenirs de grado, mugs personalizados, llaveros de grado, retablos, marquetería', [
                ['Mugs personalizados', 'Un recuerdo especial', 'Mugs de cerámica con nombre, fotografía y diseño de la promoción.'],
                ['Llaveros', 'Para llevar siempre', 'Llaveros con fotografía o detalles de la promoción, en diseños variados.'],
                ['Retablos y marquetería', 'Elegante y duradero', 'Fotografías sobre MDF o enmarcadas con acabados de calidad para exhibir y conservar.'],
            ]],
        ];

        $now = now();
        foreach ($cats as $ci => [$name, $icon, $subtitle, $keywords, $services]) {
            $catId = DB::table('photo_categories')->insertGetId([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'subtitle' => $subtitle,
                'keywords' => $keywords,
                'icon' => $icon,
                'sort' => $ci,
                'active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach ($services as $si => [$title, $sub, $desc]) {
                DB::table('photo_services')->insert([
                    'photo_category_id' => $catId,
                    'title' => $title, 'subtitle' => $sub, 'description' => $desc,
                    'sort' => $si, 'active' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $packages = [
            ['Básico Escolar', 'Lo esencial para un recuerdo seguro', false, [
                'Anuario del colegio personalizado por alumno',
                'Mosaico de la promoción con retratos individuales',
                'Cobertura fotográfica de la ceremonia',
                'Fotos grupales (grupo completo y subgrupos)',
                'Entrega digital en galería online protegida',
                '1 foto impresa 20x30 cm por alumno',
            ]],
            ['Avanzado Graduación', 'Más servicios, más recuerdos', true, [
                'Todo lo del paquete Básico Escolar',
                'Cobertura del prom o fiesta de grado',
                '3 fotos impresas 20x30 cm por alumno',
                'Photobook 20x20 cm con 20 fotos',
            ]],
            ['Premium Generación', 'Tu generación merece lo mejor', false, [
                'Todo lo del paquete Avanzado',
                'Photobook ampliado (20 fotos adicionales)',
                'Galería de descarga digital en alta resolución por alumno',
                'Páginas extra en el anuario para dedicatorias',
            ]],
        ];
        foreach ($packages as $pi => [$pname, $tag, $feat, $features]) {
            DB::table('photo_packages')->insert([
                'name' => $pname, 'tagline' => $tag,
                'features' => json_encode($features),
                'featured' => $feat, 'sort' => $pi, 'active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_services');
        Schema::dropIfExists('photo_packages');
        Schema::dropIfExists('photo_categories');
    }
};
