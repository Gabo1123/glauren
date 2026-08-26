<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\HomeBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeBlockController extends Controller
{
    public function index(Request $request)
    {
        $pageId = $request->integer('page') ?: null;
        if ($pageId) {
            $page = \App\Modules\Chaquetas\Models\Page::findOrFail($pageId);
            $blocks = HomeBlock::forPage($pageId)->orderBy('sort')->orderBy('id')->get();

            return view('admin.cms.blocks.page', compact('page', 'blocks'));
        }

        $blocks = HomeBlock::home()->orderBy('position')->orderBy('sort')->orderBy('id')->get()->groupBy('position');

        return view('admin.cms.blocks.index', compact('blocks'));
    }

    public function create(Request $request)
    {
        $pageId = $request->integer('page') ?: null;
        $block = new HomeBlock(['type' => 'texto', 'position' => 'before_gallery', 'theme' => 'dark', 'align' => 'center', 'active' => true, 'page_id' => $pageId]);

        return view('admin.cms.blocks.form', compact('block'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort'] = (int) (HomeBlock::where('page_id', $data['page_id'] ?? null)->where('position', $data['position'])->max('sort') + 1);
        $block = HomeBlock::create($data);
        $this->handleImage($request, $block);
        $this->handleGallery($request, $block, []);

        return redirect($this->backTo($block))->with('ok', 'Bloque creado.');
    }

    private function backTo(HomeBlock $block): string
    {
        return $block->page_id
            ? route('admin.blocks.index', ['page' => $block->page_id])
            : route('admin.blocks.index');
    }

    public function edit(HomeBlock $block)
    {
        return view('admin.cms.blocks.form', compact('block'));
    }

    public function update(Request $request, HomeBlock $block)
    {
        $existingGallery = ($block->type === 'galeria' && is_array($block->data)) ? $block->data : [];
        $block->update($this->validated($request));
        $this->handleImage($request, $block);
        $this->handleGallery($request, $block, $existingGallery);

        return redirect($this->backTo($block))->with('ok', 'Bloque actualizado.');
    }

    /** Galería: conserva las fotos actuales, quita las marcadas y agrega las nuevas. */
    private function handleGallery(Request $request, HomeBlock $block, array $existing): void
    {
        if ($block->type !== 'galeria') {
            return;
        }
        $request->validate(['gallery_images.*' => 'nullable|image|max:8192']);
        $remove = (array) $request->input('remove_gallery', []);
        $imgs = array_values(array_filter($existing, fn ($p) => ! in_array($p, $remove, true)));
        foreach ((array) $request->file('gallery_images', []) as $file) {
            if ($file) {
                $imgs[] = $file->store('home-blocks', 'public');
            }
        }
        $block->update(['data' => $imgs]);
    }

    public function destroy(HomeBlock $block)
    {
        $block->delete();

        return back()->with('ok', 'Bloque eliminado.');
    }

    public function toggle(HomeBlock $block)
    {
        $block->update(['active' => ! $block->active]);

        return back()->with('ok', $block->active ? 'Bloque visible.' : 'Bloque oculto.');
    }

    /** Guarda el nuevo orden (AJAX): recibe ids en el orden deseado. */
    public function reorder(Request $request)
    {
        foreach ((array) $request->input('ids', []) as $i => $id) {
            HomeBlock::where('id', $id)->update(['sort' => $i]);
        }

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'page_id'     => 'nullable|integer|exists:pages,id',
            'type'        => 'required|in:'.implode(',', array_keys(HomeBlock::TYPES)),
            'position'    => 'required|in:'.implode(',', array_keys(HomeBlock::POSITIONS)),
            'title'       => 'nullable|string|max:180',
            'subtitle'    => 'nullable|string|max:300',
            'body'        => 'nullable|string',
            'button_text' => 'nullable|string|max:60',
            'button_url'  => 'nullable|string|max:300',
            'video_url'   => 'nullable|string|max:300',
            'theme'       => 'required|in:dark,light,gold',
            'align'       => 'required|in:left,center',
            'columns'     => 'nullable|integer|min:1|max:6',
            'spacing'     => 'nullable|in:compact,normal,large',
            'img_pos'     => 'nullable|in:left,right',
            'bg_color'    => 'nullable|string|max:9',
            'width'       => 'nullable|in:normal,wide,full',
            'active'      => 'nullable|boolean',
        ]) + ['active' => $request->boolean('active'), 'data' => $this->dataFromRequest($request)];
    }

    /** Arma la lista de ítems (data) según el tipo de módulo. */
    private function dataFromRequest(Request $request): ?array
    {
        $type = $request->input('type');
        if (in_array($type, ['features', 'steps'], true)) {
            return collect((array) $request->input('items', []))
                ->map(fn ($r) => ['emoji' => trim($r['emoji'] ?? ''), 'title' => trim($r['title'] ?? ''), 'desc' => trim($r['desc'] ?? '')])
                ->filter(fn ($r) => $r['title'] !== '' || $r['desc'] !== '')
                ->values()->all();
        }
        if ($type === 'stats') {
            return collect((array) $request->input('stats', []))
                ->map(fn ($r) => ['value' => trim($r['value'] ?? ''), 'label' => trim($r['label'] ?? '')])
                ->filter(fn ($r) => $r['value'] !== '' || $r['label'] !== '')
                ->values()->all();
        }

        return null;
    }

    private function handleImage(Request $request, HomeBlock $block): void
    {
        $request->validate([
            'image'    => 'nullable|image|max:8192',
            'bg_image' => 'nullable|image|max:8192',
        ]);
        if ($request->hasFile('image')) {
            $block->update(['image_path' => $request->file('image')->store('home-blocks', 'public')]);
        }
        if ($request->hasFile('bg_image')) {
            $block->update(['bg_image' => $request->file('bg_image')->store('home-blocks', 'public')]);
        }
        if ($request->boolean('remove_bg_image')) {
            $block->update(['bg_image' => null]);
        }
    }
}
