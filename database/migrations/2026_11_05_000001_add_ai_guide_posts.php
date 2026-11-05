<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tres guías del blog pensadas para responder lo que la gente le pregunta a Google y a la IA.
 * Se crean como BORRADOR (inactivas): revísalas en Contenido → Noticias y publícalas cuando quieras.
 * Precios tomados del catálogo de la cotización (noviembre de 2026).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $posts = [
            [
                'slug'  => 'cuanto-cuesta-una-chaqueta-de-prom-en-colombia',
                'title' => '¿Cuánto cuesta una chaqueta de prom en Colombia? Precios 2026',
                'excerpt' => 'Precios reales por estudiante de chaquetas beisboleras, estructuradas, doble faz, busos y combos de promoción, con lo que incluye cada uno.',
                'focus_keyword' => 'cuánto cuesta una chaqueta de prom',
                'meta_description' => 'Chaqueta de prom en Colombia: beisbolera $169.900, estructurada $202.900, doble faz $279.900 y combos desde $310.200 por estudiante. Qué incluye el precio.',
                'body' => <<<'HTML'
<p>Si estás organizando la promoción, lo primero que te preguntan los papás es <strong>cuánto cuesta la chaqueta</strong>. Aquí están los precios por estudiante de G'Lauren para 2026, con los bordados incluidos.</p>

<h2>Precios por prenda</h2>
<table>
<thead><tr><th>Prenda</th><th>Bordados</th><th>Precio por estudiante</th></tr></thead>
<tbody>
<tr><td>Chaqueta beisbolera o un faz</td><td>5</td><td>$169.900</td></tr>
<tr><td>Chaqueta estructurada (tipo Fórmula 1)</td><td>5</td><td>$202.900</td></tr>
<tr><td>Chaqueta doble faz</td><td>8 (4 por lado)</td><td>$279.900</td></tr>
<tr><td>Buso canguro, con cuello o cremallera</td><td>5</td><td>$149.900</td></tr>
<tr><td>Camiseta deportiva</td><td>2</td><td>$99.400</td></tr>
<tr><td>Camiseta tipo polo</td><td>2</td><td>$90.000</td></tr>
</tbody>
</table>

<h2>Combos: chaqueta + buso</h2>
<p>La mayoría de promociones pide chaqueta y buso juntos. En combo se descuenta un 3% sobre la suma de las prendas:</p>
<ul>
<li><strong>Beisbolera + buso:</strong> $310.200 por estudiante.</li>
<li><strong>Estructurada + buso:</strong> $342.200 por estudiante.</li>
<li><strong>Beisbolera + buso + polo:</strong> $397.500 por estudiante.</li>
<li><strong>Estructurada + buso + polo:</strong> $429.500 por estudiante.</li>
</ul>

<h2>¿Qué incluye el precio?</h2>
<ul>
<li>Telas Lafayette y cremalleras YES.</li>
<li>Los bordados de cada modelo (nombre, apodo, año, escudo, etc.).</li>
<li>Bandera, globos y tula o termo para la promoción.</li>
<li><strong>1 chaqueta de obsequio por cada 25 unidades</strong>.</li>
<li>10% de descuento para hermanos o primos.</li>
<li>Portal para que el colegio y cada estudiante sigan la producción con fotos reales.</li>
</ul>

<h2>¿Qué hace que el precio suba?</h2>
<p>Bordados adicionales, forro con nombres, capota, paño o telas especiales. Si quieren algo así, lo cotizamos aparte para que sepan el valor exacto antes de firmar.</p>

<h2>¿Cómo se paga?</h2>
<p>50% de anticipo al firmar el contrato (se firma en línea, desde el celular) y 50% al entregar. Cada estudiante puede pagar su parte y subir el comprobante en su portal.</p>

<p><strong>¿Quieres el valor exacto para tu curso?</strong> <a href="/#cotiza">Cotiza en 1 minuto</a> con el número de estudiantes y las prendas que les gustan.</p>
HTML,
            ],
            [
                'slug'  => 'chaqueta-beisbolera-o-estructurada-cual-elegir',
                'title' => '¿Chaqueta beisbolera o estructurada? Cuál elegir para tu promoción',
                'excerpt' => 'Diferencias de estilo, abrigo, bordados y precio entre la chaqueta beisbolera, la estructurada y la doble faz, para que tu curso elija sin dudas.',
                'focus_keyword' => 'chaqueta beisbolera o estructurada',
                'meta_description' => 'Beisbolera ($169.900) vs. estructurada ($202.900) vs. doble faz ($279.900): diferencias de estilo, abrigo y bordados para elegir la chaqueta de prom.',
                'body' => <<<'HTML'
<p>Es la discusión de todos los cursos: <strong>¿beisbolera o estructurada?</strong> Las dos llevan 5 bordados y tela Lafayette. Cambian el estilo, el abrigo y el precio.</p>

<h2>Chaqueta beisbolera (o un faz) — $169.900</h2>
<ul>
<li><strong>Estilo:</strong> universitario, con puños y cuello en resorte.</li>
<li><strong>Abrigo:</strong> liviana; ideal para climas templados o cálidos.</li>
<li><strong>Para quién:</strong> cursos que quieren un look clásico y un precio más bajo, o que la usan con buso debajo.</li>
</ul>

<h2>Chaqueta estructurada (tipo Fórmula 1) — $202.900</h2>
<ul>
<li><strong>Estilo:</strong> más formal y deportiva, con cortes y paneles de colores.</li>
<li><strong>Abrigo:</strong> más gruesa y con mejor forma; recomendada para Bogotá, Tunja, Manizales y climas fríos.</li>
<li><strong>Para quién:</strong> cursos que quieren una prenda que se vea "de equipo" y dure varios años.</li>
</ul>

<h2>Chaqueta doble faz — $279.900</h2>
<ul>
<li><strong>Estilo:</strong> se puede usar por los dos lados, cada uno con su diseño.</li>
<li><strong>Bordados:</strong> 8 (4 por lado).</li>
<li><strong>Para quién:</strong> promociones que quieren dos chaquetas en una.</li>
</ul>

<h2>Comparación rápida</h2>
<table>
<thead><tr><th></th><th>Beisbolera</th><th>Estructurada</th><th>Doble faz</th></tr></thead>
<tbody>
<tr><td>Precio</td><td>$169.900</td><td>$202.900</td><td>$279.900</td></tr>
<tr><td>Bordados</td><td>5</td><td>5</td><td>8</td></tr>
<tr><td>Abrigo</td><td>Liviana</td><td>Media-alta</td><td>Media</td></tr>
<tr><td>Combo con buso</td><td>$310.200</td><td>$342.200</td><td>—</td></tr>
</tbody>
</table>

<h2>Consejo para decidir en el curso</h2>
<p>Pongan las opciones a votación: en el portal de G'Lauren cada estudiante puede votar el diseño desde su celular. Y si no se ponen de acuerdo, el <strong>combo con buso</strong> resuelve el clima: chaqueta para el frío y buso para el día a día.</p>

<p><a href="/personalizar">Diseña tu chaqueta en 3D</a> o <a href="/#cotiza">cotiza en 1 minuto</a>.</p>
HTML,
            ],
            [
                'slug'  => 'cuando-pedir-las-chaquetas-de-grado',
                'title' => '¿Cuándo pedir las chaquetas de grado? Calendario para tu promoción',
                'excerpt' => 'Cuánto se demora una chaqueta de prom, cuándo empezar el proceso y el calendario recomendado para que la promoción las tenga a tiempo.',
                'focus_keyword' => 'cuándo pedir las chaquetas de grado',
                'meta_description' => 'Las chaquetas de prom tardan 25 a 35 días hábiles en fabricarse. Empieza 2 a 3 meses antes: calendario paso a paso para tener las chaquetas de grado a tiempo.',
                'body' => <<<'HTML'
<p>La pregunta que más nos hacen en enero y febrero: <strong>¿todavía estamos a tiempo?</strong> La fabricación toma <strong>25 a 35 días hábiles</strong> después de aprobar el diseño y las tallas. Pero antes de eso hay que cotizar, presentar al colegio y firmar, así que recomendamos empezar <strong>2 a 3 meses antes</strong> de la fecha en que las quieren estrenar.</p>

<h2>Calendario recomendado</h2>
<ol>
<li><strong>Semana 1 — Cotización:</strong> pide la cotización con el número de estudiantes y las prendas que les gustan.</li>
<li><strong>Semanas 1 a 2 — Ajustes y presentación:</strong> definimos diseño, colores y bordados. En Bogotá y Cundinamarca vamos al colegio a presentarnos; en otras ciudades hacemos reunión virtual.</li>
<li><strong>Semana 3 — Contrato y anticipo:</strong> se firma en línea y se paga el 50% para iniciar.</li>
<li><strong>Semanas 3 a 4 — Medidas:</strong> en el colegio (Bogotá y Cundinamarca) o con tallas estándar S, M, L, XL usando nuestra tabla.</li>
<li><strong>Semanas 4 a 10 — Producción:</strong> 25 a 35 días hábiles, con fotos reales del avance en el portal.</li>
<li><strong>Entrega:</strong> en el colegio, cada prenda empacada con el nombre del estudiante. Se paga el 50% restante.</li>
</ol>

<h2>¿Qué retrasa una entrega?</h2>
<ul>
<li>Que el curso tarde en decidir el diseño o en enviar las tallas.</li>
<li>Nombres mal escritos que hay que corregir (revisen el portal: así sale el bordado).</li>
<li>Pagos pendientes del anticipo.</li>
<li>Pedir sobre la fecha: el taller tiene un cupo mensual, así que conviene apartarlo con tiempo.</li>
</ul>

<h2>¿Y si ya estamos encima de la fecha?</h2>
<p>Escríbenos por WhatsApp con la fecha que necesitan. Revisamos la capacidad del taller para ese mes y te decimos con honestidad si alcanzamos.</p>

<p><a href="/#cotiza">Cotiza en 1 minuto</a> y aparta tu cupo.</p>
HTML,
            ],
        ];

        foreach ($posts as $p) {
            if (DB::table('posts')->where('slug', $p['slug'])->exists()) {
                continue;
            }
            DB::table('posts')->insert($p + [
                'author'       => "Equipo G'Lauren",
                'meta_title'   => $p['title'],
                'active'       => false,     // borrador: se publica desde el panel
                'published_at' => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('posts')->whereIn('slug', [
            'cuanto-cuesta-una-chaqueta-de-prom-en-colombia',
            'chaqueta-beisbolera-o-estructurada-cual-elegir',
            'cuando-pedir-las-chaquetas-de-grado',
        ])->where('active', false)->delete();
    }
};
