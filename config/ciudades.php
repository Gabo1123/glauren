<?php

/*
| Páginas "Chaquetas de prom en {ciudad}" (/chaquetas-de-prom/{slug}).
| local = true → Bogotá y Cundinamarca: presentación y toma de medidas en el colegio,
|                entrega con globos y fotografía. Fuera de ahí: reunión virtual, tallas
|                estándar con tabla y guía, y envío del pedido.
| intro → texto propio de cada ciudad (evita páginas repetidas).
*/

return [
    'bogota' => ['name' => 'Bogotá', 'region' => 'Bogotá D.C.', 'local' => true,
        'intro' => 'Nuestro taller está en Bogotá, así que vamos a tu colegio en cualquier localidad —Suba, Kennedy, Engativá, Usaquén, Bosa, Chapinero— a presentar muestras y a tomar las medidas de cada estudiante.'],
    'chia' => ['name' => 'Chía', 'region' => 'Cundinamarca', 'local' => true,
        'intro' => 'Atendemos colegios de Chía y la Sabana Centro: vamos al colegio a presentarnos con muestras, tomamos medidas y entregamos en el colegio.'],
    'soacha' => ['name' => 'Soacha', 'region' => 'Cundinamarca', 'local' => true,
        'intro' => 'Muy cerca de nuestro taller del sur de Bogotá: visitamos tu colegio en Soacha para presentar el diseño y tomar las medidas sin costo adicional.'],
    'zipaquira' => ['name' => 'Zipaquirá', 'region' => 'Cundinamarca', 'local' => true,
        'intro' => 'Promociones de Zipaquirá, Cajicá, Cogua y alrededores: presentación en el colegio, toma de medidas y entrega en el mismo colegio.'],
    'facatativa' => ['name' => 'Facatativá', 'region' => 'Cundinamarca', 'local' => true,
        'intro' => 'Atendemos Facatativá, Funza, Mosquera y Madrid: vamos al colegio con muestras de telas y bordados y tomamos las medidas allí.'],
    'fusagasuga' => ['name' => 'Fusagasugá', 'region' => 'Cundinamarca', 'local' => true,
        'intro' => 'Promociones de Fusagasugá y el Sumapaz: presentación en el colegio, toma de medidas y entrega con globos y fotografía.'],
    'girardot' => ['name' => 'Girardot', 'region' => 'Cundinamarca', 'local' => true,
        'intro' => 'Colegios de Girardot y el Alto Magdalena: vamos a presentarnos y a tomar medidas, y entregamos en el colegio.'],
    'medellin' => ['name' => 'Medellín', 'region' => 'Antioquia', 'local' => false,
        'intro' => 'Hacemos chaquetas de prom para colegios de Medellín, Envigado, Itagüí y Bello: nos reunimos por videollamada, cada estudiante elige su talla con nuestra tabla y te enviamos el pedido empacado por nombre.'],
    'cali' => ['name' => 'Cali', 'region' => 'Valle del Cauca', 'local' => false,
        'intro' => 'Promociones de Cali, Jamundí y Palmira: diseño y aprobación en reunión virtual, tallas estándar con guía y envío a tu colegio.'],
    'barranquilla' => ['name' => 'Barranquilla', 'region' => 'Atlántico', 'local' => false,
        'intro' => 'Para el clima de Barranquilla recomendamos busos y chaquetas un faz más livianos. Todo el proceso es virtual y enviamos el pedido a tu colegio.'],
    'bucaramanga' => ['name' => 'Bucaramanga', 'region' => 'Santander', 'local' => false,
        'intro' => 'Atendemos colegios de Bucaramanga, Floridablanca y Girón con reunión virtual, tabla de tallas y envío del pedido completo.'],
    'cartagena' => ['name' => 'Cartagena', 'region' => 'Bolívar', 'local' => false,
        'intro' => 'Promociones de Cartagena: busos y camisetas de grado frescas, chaquetas un faz y todo el proceso en línea con envío al colegio.'],
    'pereira' => ['name' => 'Pereira', 'region' => 'Risaralda', 'local' => false,
        'intro' => 'Colegios de Pereira y Dosquebradas: diseñamos contigo por videollamada y enviamos las prendas empacadas con el nombre de cada estudiante.'],
    'manizales' => ['name' => 'Manizales', 'region' => 'Caldas', 'local' => false,
        'intro' => 'Para el clima de Manizales, la chaqueta estructurada o doble faz es la favorita. Proceso virtual con tallas estándar y envío al colegio.'],
    'ibague' => ['name' => 'Ibagué', 'region' => 'Tolima', 'local' => false,
        'intro' => 'Promociones de Ibagué: cotización en línea, reunión virtual, tallas con guía y envío del pedido a tu colegio.'],
    'villavicencio' => ['name' => 'Villavicencio', 'region' => 'Meta', 'local' => false,
        'intro' => 'Colegios de Villavicencio y el Meta: diseño a la medida de tu promoción, proceso virtual y envío del pedido completo.'],
    'tunja' => ['name' => 'Tunja', 'region' => 'Boyacá', 'local' => false,
        'intro' => 'En Tunja y Boyacá el frío pide chaqueta estructurada o con forro. Te acompañamos por videollamada y enviamos el pedido a tu colegio.'],
    'neiva' => ['name' => 'Neiva', 'region' => 'Huila', 'local' => false,
        'intro' => 'Promociones de Neiva: busos, polos y chaquetas un faz con tus bordados, con todo el proceso en línea y envío al colegio.'],
];
