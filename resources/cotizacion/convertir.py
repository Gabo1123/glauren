"""Convierte las páginas del lienzo (.dc.html) en fragmentos HTML estáticos para Laravel.

Uso: python3 resources/cotizacion/convertir.py <carpeta_del_lienzo> resources/cotizacion/paginas
Cada página queda como NN.html (solo el contenido) y todas las reglas CSS,
con alcance .pg-NN, en pages.css.
"""
import re
import sys
from pathlib import Path

SRC, OUT = Path(sys.argv[1]), Path(sys.argv[2])

ORDER = [
    'Main', 'P02-Presentacion', 'P02c-Beneficios', 'P02b-Plataforma', 'P03-Trabajos',
    'P04-Telas', 'P05-Estructurada', 'P06-Busos', 'P07-Beisboleras', 'P08-Precios',
    'P09-Combos1', 'P10-Combos2', 'P12-Proceso', 'P13-Fotografia', 'P11-Adicionales',
]

C = '/img/cotizacion/'
BLOBS = {
    'f4af3e87': 'logo-dark.png', '0c3757b6': 'logo-light.png', 'c9d856f7': 'hero.png',
    '227c807c': 'trabajos/trabajo-1.jpg', '2ebfb8dc': 'trabajos/trabajo-2.jpg', '5aa35704': 'trabajos/trabajo-3.jpg',
    '5e58a6fd': 'trabajos/trabajo-4.jpg', '16afff12': 'trabajos/trabajo-5.jpg', 'c5fb0ff1': 'trabajos/trabajo-6.jpg',
    '74b8b3b9': 'trabajos/trabajo-7.jpg',
    'ebc7c5e9': 'marcas/lafayette.jpg', '3a3668e9': 'marcas/yes.jpg', 'd18f0721': 'marcas/patprimo.jpg',
    '3571771b': 'marcas/laftech-1.jpg', 'f9750bb3': 'marcas/laftech-2.jpg', '2cadcf27': 'marcas/laftech-3.jpg',
    '26d1c46d': 'marcas/laftech-4.jpg', 'c64dcb31': 'marcas/laftech-5.jpg', '2953c70d': 'marcas/laftech-6.jpg',
    'c0d9edfd': 'productos/estructurada.jpg', 'ef798fbb': 'productos/buso.jpg', 'af9a0e09': 'productos/polo.jpg',
    'd115d9f2': 'productos/deportiva.jpg', '86028ba2': 'productos/gorra.jpg',
    '3fc95569': 'beisbolera.jpg', '6e3425e1': 'chaqueta.jpg', 'f07761f9': 'buso-cremallera.jpg', '9daaf1d7': 'buso.jpg',
}
for i, b in enumerate(['dabefeef', '1ef5d176', 'e83d4c46', 'ee0ed6da', 'd421d219', 'd1b94c05', 'ee28a7eb', 'a157bcf4'], 1):
    BLOBS[b] = f'disenos/cortes-{i}.jpg'
for i, b in enumerate(['fd4a7e41', 'fb197646', 'b4b05414', '1a23c746', 'ad31ac45', '052f0b22', '9832f5c9', 'd27ecc65'], 1):
    BLOBS[b] = f'disenos/buso-{i}.jpg'
for i, b in enumerate(['86dd77b0', '28f8cb08', '5a0dfeae', 'b1906155', '882ff11e', '8cc19b6f', 'dfb09496', 'a9dc6907', '61368093'], 1):
    BLOBS[b] = f'disenos/beisbolera-{i}.jpg'

# Pequeñas correcciones de ortografía del lienzo.
FIXES = {'p-rimos': 'primos', 'caquetas': 'chaquetas', 'Fotografia&nbsp;': 'Fotografía', '>Fotografia<': '>Fotografía<'}


def blob(m):
    key = m.group(1)[:8]
    if key not in BLOBS:
        sys.exit(f'Imagen sin equivalencia: {m.group(1)}')
    return C + BLOBS[key]


def scope_css(css, scope):
    css = re.sub(r'/\*.*?\*/', '', css, flags=re.S)
    out = []
    for sel, body in re.findall(r'([^{}]+)\{([^{}]*)\}', css):
        sels = []
        for s in sel.split(','):
            s = s.strip()
            if not s or s in ('body', 'html'):
                continue
            sels.append(f'{scope} {s}')
        if sels:
            out.append(','.join(sels) + '{' + body.strip() + '}')
    return '\n'.join(out)


OUT.mkdir(parents=True, exist_ok=True)
all_css = []
for n, name in enumerate(ORDER, 1):
    src = (SRC / f'{name}.dc.html').read_text()
    style = re.search(r'<helmet>.*?<style>(.*?)</style>.*?</helmet>', src, re.S).group(1)
    body = re.search(r'</helmet>(.*)</x-dc>', src, re.S).group(1).strip()
    for a, b in (('{{yellow}}', '#FFC72C'), ('{{orange}}', '#FF6B1A')):
        body, style = body.replace(a, b), style.replace(a, b)
    body = re.sub(r'/_blob/([0-9a-f]+)', blob, body)
    for a, b in FIXES.items():
        body = body.replace(a, b)
    body = body.replace('>65043<', '>%%NUM%%<')
    if name == 'Main':
        for a, b in (('>Estimados padres de familia<', '>%%PARA_NOMBRE%%<'),
                     ('>Promoción grado 11 · 2026<', '>%%PARA_DETALLE%%<'),
                     ('color: #FFC72C">2027<', 'color: #FFC72C">%%YEAR%%<')):
            assert a in body, a
            body = body.replace(a, b)
    assert '{{' not in body and '/_blob/' not in body, name
    (OUT / f'{n:02d}.html').write_text(body + '\n')
    all_css.append(f'/* {n:02d} · {name} */\n' + scope_css(style, f'.pg-{n:02d}'))

(OUT / 'pages.css').write_text('\n'.join(all_css) + '\n')
print('ok', len(ORDER), 'páginas')
