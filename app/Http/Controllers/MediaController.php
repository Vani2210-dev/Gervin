<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Document;

class MediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view media', ['only' => ['index', 'serve']]);
        $this->middleware('permission:add media', ['only' => ['store', 'createFolder']]);
        $this->middleware('permission:delete media', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $folder = $request->input('folder', '/');
        if (str_contains($folder, '..')) abort(403);
        $diskPath = $folder === '/' ? '' : $folder;

        $files = [];

        // Nút quay lại thư mục cha
        if ($folder !== '/' && $folder !== '') {
            $parent = dirname($folder);
            if ($parent === '\\' || $parent === '.') $parent = '/';
            $files[] = [
                'name'          => '.. (Quay lại)',
                'path'          => $parent,
                'is_dir'        => true,
                'size'          => 0,
                'last_modified' => 0,
                'mime_type'     => 'folder',
                'url'           => route('media.index', ['folder' => $parent]),
                'document'      => null,
            ];
        }

        // Danh sách thư mục
        foreach (Storage::disk('private')->directories($diskPath) as $dir) {
            $files[] = [
                'name'          => basename($dir),
                'path'          => $dir,
                'is_dir'        => true,
                'size'          => 0,
                'last_modified' => Storage::disk('private')->lastModified($dir),
                'mime_type'     => 'folder',
                'url'           => route('media.index', ['folder' => $dir]),
                'document'      => null,
            ];
        }

        // Danh sách file
        $allFiles = Storage::disk('private')->files($diskPath);
        $documentsDb = Document::whereIn('file_path', $allFiles)->get()->keyBy('file_path');

        foreach ($allFiles as $file) {
            if (basename($file) === '.gitignore') continue;
            $doc = $documentsDb->get($file);
            $files[] = [
                'name'          => basename($file),
                'path'          => $file,
                'is_dir'        => false,
                'size'          => Storage::disk('private')->size($file),
                'last_modified' => Storage::disk('private')->lastModified($file),
                'mime_type'     => Storage::disk('private')->mimeType($file),
                'url'           => route('media.serve', ['filename' => $file]),
                'document'      => $doc,
            ];
        }

        // Sắp xếp: thư mục lên trước, sau đó theo ngày sửa đổi
        usort($files, function ($a, $b) {
            if ($a['name'] === '.. (Quay lại)') return -1;
            if ($b['name'] === '.. (Quay lại)') return 1;
            if ($a['is_dir'] !== $b['is_dir']) return $a['is_dir'] ? -1 : 1;
            return $b['last_modified'] <=> $a['last_modified'];
        });

        // Tìm kiếm
        $search = $request->input('search');
        if ($search) {
            $files = array_filter($files, fn($f) => $f['name'] === '.. (Quay lại)' || stripos($f['name'], $search) !== false);
        }

        // Lọc theo loại (dropdown cũ)
        $type = $request->input('type', 'all');
        if ($type && $type !== 'all') {
            $files = array_filter($files, function ($file) use ($type) {
                if ($file['is_dir']) return true;
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                return match ($type) {
                    'word'    => in_array($ext, ['doc', 'docx']),
                    'pdf'     => $ext === 'pdf',
                    'excel'   => in_array($ext, ['xls', 'xlsx', 'csv']),
                    'image'   => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']),
                    'archive' => in_array($ext, ['zip', 'rar', '7z']),
                    default   => true,
                };
            });
        }

        // Lọc theo popup
        if ($request->filled('filter_name')) {
            $files = array_filter($files, fn($f) => $f['name'] === '.. (Quay lại)' || stripos($f['name'], $request->filter_name) !== false);
        }

        if ($request->filled('filter_type') && $request->filter_type !== '') {
            $filterType = $request->filter_type;
            $files = array_filter($files, function ($file) use ($filterType) {
                if ($file['is_dir']) return true;
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                return match ($filterType) {
                    'word'  => in_array($ext, ['doc', 'docx']),
                    'pdf'   => $ext === 'pdf',
                    'excel' => in_array($ext, ['xls', 'xlsx', 'csv']),
                    'image' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']),
                    default => true,
                };
            });
        }

        if ($request->filled('filter_min_size')) {
            $minSizeKB = (int) $request->filter_min_size;
            $minSizeBytes = $minSizeKB * 1024;
            $files = array_filter($files, fn($f) => $f['is_dir'] || $f['size'] >= $minSizeBytes);
        }

        if ($request->filled('filter_max_size')) {
            $maxSizeKB = (int) $request->filter_max_size;
            $maxSizeBytes = $maxSizeKB * 1024;
            $files = array_filter($files, fn($f) => $f['is_dir'] || $f['size'] <= $maxSizeBytes);
        }

        $perPage     = (int) $request->input('per_page', 20);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $allFiles    = array_values($files);
        $pagedItems  = array_slice($allFiles, ($currentPage - 1) * $perPage, $perPage);
        $paginated   = new LengthAwarePaginator($pagedItems, count($allFiles), $perPage, $currentPage, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        return view('media.index', [
            'files'         => $paginated,
            'currentFolder' => $folder,
            'search'        => $search,
            'type'          => $type,
            'perPage'       => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files'   => 'required|array',
            'files.*' => 'file|max:20480',
            'folder'  => 'nullable|string',
            'title'   => 'nullable|string|max:255',
            'notes'   => 'nullable|string',
        ]);

        $folder = $request->input('folder', '/');
        if (str_contains($folder, '..')) abort(403);
        $diskPath = $folder === '/' ? '' : $folder;

        $count = 0;
        foreach ($request->file('files') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension    = strtolower($file->getClientOriginalExtension());
            $cleanName    = Str::slug($originalName) . '_' . time() . '_' . $count . '.' . $extension;
            $filePath     = $file->storeAs($diskPath, $cleanName, 'private');

            Document::create([
                'uploaded_by' => auth()->id(),
                'title'       => $request->input('title') ?: $originalName,
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $filePath,
                'mime_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
                'notes'       => $request->input('notes'),
            ]);
            $count++;
        }

        return redirect()->route('media.index', ['folder' => $folder])
            ->with('success', "Đã tải lên $count tập tin thành công.");
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'folder_name'    => 'required|string|max:255',
            'current_folder' => 'nullable|string',
        ]);

        $current = $request->input('current_folder', '/');
        if (str_contains($current, '..')) abort(403);

        $folderName = Str::slug($request->input('folder_name'));
        $diskPath   = ($current === '/' ? '' : $current . '/') . $folderName;

        if (Storage::disk('private')->exists($diskPath)) {
            return back()->withErrors('Thư mục đã tồn tại.');
        }

        Storage::disk('private')->makeDirectory($diskPath);

        return redirect()->route('media.index', ['folder' => $current])
            ->with('success', 'Tạo thư mục thành công.');
    }

    public function destroy(Request $request, $filename)
    {
        if (str_contains($filename, '..')) abort(403);

        if ($request->boolean('is_folder')) {
            Storage::disk('private')->deleteDirectory($filename);
            Document::where('file_path', 'like', $filename . '/%')->delete();
        } else {
            Storage::disk('private')->delete($filename);
            Document::where('file_path', $filename)->delete();
        }

        return back()->with('success', 'Đã xóa thành công.');
    }

    public function serve($filename)
    {
        if (str_contains($filename, '..')) abort(403);

        if (!Storage::disk('private')->exists($filename)) {
            abort(404);
        }

        $path     = storage_path('app/private/' . $filename);
        $mimeType = Storage::disk('private')->mimeType($filename);

        return Response::file($path, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }

    public function download($filename)
    {
        if (str_contains($filename, '..')) abort(403);

        if (!Storage::disk('private')->exists($filename)) {
            abort(404);
        }

        $path = storage_path('app/private/' . $filename);

        return Response::download($path, basename($filename));
    }
}
