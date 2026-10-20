<?php

namespace App\Http\Controllers;

use App\Models\WoodBoard;
use App\Models\WoodBoardType;
use App\Models\WoodBoardPrice;
use App\Models\WoodBoardPriceGroup;
use App\Models\WoodBoardPriceGroupPrice;
use Illuminate\Http\Request;

class WoodBoardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view supply',   ['only' => ['index']]);
        $this->middleware('permission:add supply',    ['only' => ['store', 'storeType', 'batchUpdate', 'batchUpdatePriceGroups', 'import']]);
        $this->middleware('permission:edit supply',   ['only' => ['update', 'updateType', 'quickUpdateStock']]);
        $this->middleware('permission:delete supply', ['only' => ['destroy', 'destroyType']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search  = $request->input('search', '');

                // Fetch all active wood board types ordered by display_order
        $boardTypes = WoodBoardType::orderBy('display_order', 'asc')->get();

        // Tự động kiểm tra và tạo WoodBoardPrice còn thiếu cho các WoodBoardType hiện có
        foreach ($boardTypes as $type) {
            $prefix = $type->prefix ?? '';
            $missingBoards = WoodBoard::whereDoesntHave('prices', function ($q) use ($type) {
                $q->where('wood_board_type_id', $type->id);
            })->get();

            foreach ($missingBoards as $b) {
                WoodBoardPrice::create([
                    'wood_board_id'      => $b->id,
                    'wood_board_type_id' => $type->id,
                    'code'               => $b->color_code . $prefix,
                    'name'               => $type->name,
                    'thickness'          => null,
                    'price_board'        => 0,
                    'price_m2'           => 0,
                    'stock'              => 0,
                ]);
            }

            // Đồng thời bù mã số hoặc tên hàng nếu trước đó bị rỗng
            WoodBoardPrice::where('wood_board_type_id', $type->id)
                ->where(function ($q) {
                    $q->whereNull('code')->orWhere('code', '');
                })
                ->with('board')
                ->get()
                ->each(function ($p) use ($prefix) {
                    if ($p->board) {
                        $p->update(['code' => $p->board->color_code . $prefix]);
                    }
                });

            WoodBoardPrice::where('wood_board_type_id', $type->id)
                ->where(function ($q) {
                    $q->whereNull('name')->orWhere('name', '');
                })
                ->update(['name' => $type->name]);
        }

        // Fetch all wood board price groups with their default prices
        $priceGroups = WoodBoardPriceGroup::with('prices')->get();

        $woodBoards = WoodBoard::with(['prices'])
            ->when($search, function ($q) use ($search) {
                $q->where('color_code', 'like', "%$search%")
                  ->orWhere('price_group', 'like', "%$search%")
                  ->orWhereHas('prices', function ($subQ) use ($search) {
                      $subQ->where('code', 'like', "%$search%")
                           ->orWhere('name', 'like', "%$search%")
                           ->orWhere('thickness', 'like', "%$search%");
                  });
            })
            ->when($request->filled('filter_color_code'), function ($q) use ($request) {
                $q->where('color_code', 'like', "%{$request->filter_color_code}%");
            })
            ->when($request->filled('filter_price_group'), function ($q) use ($request) {
                $q->where('price_group', 'like', "%{$request->filter_price_group}%");
            });

        $this->applyDateFilter($woodBoards, $request, 'created_at');

        $woodBoards = $woodBoards->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('wood_boards.index', compact('woodBoards', 'boardTypes', 'perPage', 'search', 'priceGroups'));
    }

    public function store(Request $request)
    {
        $this->cleanRequestPrices($request);

        $request->validate([
            'color_code'  => 'required|string|max:100|unique:wood_boards,color_code',
            'price_group' => 'nullable|string|max:100',
            'prices'      => 'required|array',
        ]);

        $board = WoodBoard::create([
            'color_code'  => $request->color_code,
            'price_group' => $request->price_group,
        ]);

        $pricesData = $request->input('prices', []);
        $boardTypes = WoodBoardType::all();

        foreach ($boardTypes as $type) {
            $typeData = $pricesData[$type->id] ?? [];
            // Compute code = color_code . prefix
            $computedCode = $board->color_code . ($type->prefix ?? '');

            $board->prices()->create([
                'wood_board_type_id' => $type->id,
                'code'               => $computedCode,
                'name'               => !empty($typeData['name']) ? $typeData['name'] : $type->name,
                'thickness'          => $typeData['thickness'] ?? null,
                'price_board'        => floatval($typeData['price_board'] ?? 0),
                'price_m2'           => floatval($typeData['price_m2'] ?? 0),
                'stock'              => floatval($typeData['stock'] ?? 0),
            ]);
        }

        // Sync total stock to board
        $board->update(['current_stock' => $board->prices()->sum('stock')]);

        return redirect()->route('wood_boards.index')->with('success', 'Thêm dòng bảng giá thành công.');
    }

    public function update(Request $request, WoodBoard $woodBoard)
    {
        $this->cleanRequestPrices($request);

        $request->validate([
            'color_code'  => 'required|string|max:100|unique:wood_boards,color_code,' . $woodBoard->id,
            'price_group' => 'nullable|string|max:100',
            'prices'      => 'required|array',
        ]);

        $woodBoard->update([
            'color_code'  => $request->color_code,
            'price_group' => $request->price_group,
        ]);

        $pricesData = $request->input('prices', []);
        $boardTypes = WoodBoardType::all();

        foreach ($boardTypes as $type) {
            $typeData = $pricesData[$type->id] ?? [];
            // Compute code = color_code . prefix
            $computedCode = $woodBoard->color_code . ($type->prefix ?? '');

            $woodBoard->prices()->updateOrCreate(
                ['wood_board_type_id' => $type->id],
                [
                    'code'               => $computedCode,
                    'name'               => !empty($typeData['name']) ? $typeData['name'] : $type->name,
                    'thickness'          => $typeData['thickness'] ?? null,
                    'price_board'        => floatval($typeData['price_board'] ?? 0),
                    'price_m2'           => floatval($typeData['price_m2'] ?? 0),
                    'stock'              => floatval($typeData['stock'] ?? 0),
                ]
            );
        }

        // Sync total stock to board
        $woodBoard->update(['current_stock' => $woodBoard->prices()->sum('stock')]);

        return redirect()->route('wood_boards.index')->with('success', 'Cập nhật dòng bảng giá thành công.');
    }

    public function destroy(WoodBoard $woodBoard)
    {
        $woodBoard->delete();
        return redirect()->route('wood_boards.index')->with('success', 'Xóa dòng bảng giá thành công.');
    }

    // Dynamic Board Type Management
    public function storeType(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'prefix'        => 'nullable|string|max:100',
            'display_order' => 'required|integer',
        ]);

        $type = WoodBoardType::create([
            'name'          => $request->name,
            'prefix'        => $request->prefix,
            'display_order' => $request->display_order,
        ]);

        $this->syncPricesForType($type);

        return redirect()->back()->with('success', 'Thêm loại ván thành công.');
    }

    public function updateType(Request $request, WoodBoardType $type)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'prefix'        => 'nullable|string|max:100',
            'display_order' => 'required|integer',
        ]);

        $oldPrefix = $type->prefix;
        $oldName = $type->name;
        $type->update([
            'name'          => $request->name,
            'prefix'        => $request->prefix,
            'display_order' => $request->display_order,
        ]);

        $this->syncPricesForType($type, $oldPrefix, $oldName);

        return redirect()->back()->with('success', 'Cập nhật loại ván thành công.');
    }

    public function destroyType(WoodBoardType $type)
    {
        WoodBoardPrice::where('wood_board_type_id', $type->id)->delete();
        $type->delete();
        return redirect()->back()->with('success', 'Xóa loại ván thành công.');
    }

    // Batch Update and Batch Insert Board Types
    public function batchUpdate(Request $request)
    {
        $request->validate([
            'types'                     => 'nullable|array',
            'types.*.name'              => 'required|string|max:255',
            'types.*.prefix'            => 'nullable|string|max:100',
            'types.*.display_order'     => 'required|integer',

            'new_types'                 => 'nullable|array',
            'new_types.*.name'          => 'required|string|max:255',
            'new_types.*.prefix'        => 'nullable|string|max:100',
            'new_types.*.display_order' => 'required|integer',

            'deleted_types'             => 'nullable|array',
            'deleted_types.*'           => 'required|integer|exists:wood_board_types,id',
        ]);

        \DB::transaction(function () use ($request) {
            // 1. Delete types
            $deletedIds = $request->input('deleted_types', []);
            if (!empty($deletedIds)) {
                WoodBoardPrice::whereIn('wood_board_type_id', $deletedIds)->delete();
                WoodBoardType::whereIn('id', $deletedIds)->delete();
            }

            // 2. Update existing types
            $existingTypesData = $request->input('types', []);
            foreach ($existingTypesData as $id => $data) {
                if (in_array($id, $deletedIds)) {
                    continue;
                }
                $type = WoodBoardType::find($id);
                if ($type) {
                    $oldPrefix = $type->prefix;
                    $oldName = $type->name;
                    $type->update([
                        'name'          => $data['name'],
                        'prefix'        => $data['prefix'],
                        'display_order' => $data['display_order'],
                    ]);

                    $this->syncPricesForType($type, $oldPrefix, $oldName);
                }
            }

            // 3. Insert new types
            $newTypesData = $request->input('new_types', []);
            foreach ($newTypesData as $data) {
                $newType = WoodBoardType::create([
                    'name'          => $data['name'],
                    'prefix'        => $data['prefix'],
                    'display_order' => $data['display_order'],
                ]);

                $this->syncPricesForType($newType);
            }
        });

        return redirect()->back()->with('success', 'Cập nhật cấu hình loại ván thành công.');
    }

    /**
     * Đồng bộ mã số và tên hàng cho tất cả các mã màu (WoodBoard) của một loại ván (WoodBoardType).
     * Mã số = mã màu + tiền tố loại ván (ví dụ: GV103 + .BA = GV103.BA)
     * Tên hàng = Tên loại ván mới (name)
     */
    private function syncPricesForType(WoodBoardType $type, ?string $oldPrefix = null, ?string $oldName = null)
    {
        $prefix = $type->prefix ?? '';
        $boards = WoodBoard::all();

        foreach ($boards as $board) {
            $computedCode = $board->color_code . $prefix;

            $price = WoodBoardPrice::where('wood_board_id', $board->id)
                ->where('wood_board_type_id', $type->id)
                ->first();

            if (!$price) {
                WoodBoardPrice::create([
                    'wood_board_id'      => $board->id,
                    'wood_board_type_id' => $type->id,
                    'code'               => $computedCode,
                    'name'               => $type->name,
                    'thickness'          => null,
                    'price_board'        => 0,
                    'price_m2'           => 0,
                    'stock'              => 0,
                ]);
            } else {
                $updates = [];
                // Nếu tiền tố thay đổi hoặc mã số hiện tại đang rỗng, cập nhật lại mã số
                if (($oldPrefix !== null && $oldPrefix !== $type->prefix) || empty($price->code)) {
                    $updates['code'] = $computedCode;
                }
                // Nếu tên loại ván thay đổi và tên hàng cũ trùng tên loại ván cũ hoặc đang rỗng, cập nhật tên hàng mới
                if ($oldName !== null && $oldName !== $type->name && (empty($price->name) || $price->name === $oldName)) {
                    $updates['name'] = $type->name;
                }
                if (!empty($updates)) {
                    $price->update($updates);
                }
            }
        }
    }

    // Batch Update and Batch Insert Price Groups
    public function batchUpdatePriceGroups(Request $request)
    {
        $this->cleanRequestPrices($request);

        $request->validate([
            'groups'                              => 'nullable|array',
            'groups.*.name'                       => 'required|string|max:255',
            'groups.*.prices'                     => 'required|array',
            'groups.*.prices.*.name'              => 'nullable|string|max:255',
            'groups.*.prices.*.thickness'         => 'nullable|string|max:100',
            'groups.*.prices.*.price_board'       => 'required|numeric|min:0',
            'groups.*.prices.*.price_m2'          => 'required|numeric|min:0',

            'new_groups'                          => 'nullable|array',
            'new_groups.*.name'                   => 'required|string|max:255',
            'new_groups.*.prices'                 => 'required|array',
            'new_groups.*.prices.*.name'          => 'nullable|string|max:255',
            'new_groups.*.prices.*.thickness'     => 'nullable|string|max:100',
            'new_groups.*.prices.*.price_board'   => 'required|numeric|min:0',
            'new_groups.*.prices.*.price_m2'      => 'required|numeric|min:0',

            'deleted_groups'                      => 'nullable|array',
            'deleted_groups.*'                    => 'required|integer|exists:wood_board_price_groups,id',
        ]);

        \DB::transaction(function () use ($request) {
            // 1. Delete price groups
            $deletedIds = $request->input('deleted_groups', []);
            if (!empty($deletedIds)) {
                WoodBoardPriceGroup::whereIn('id', $deletedIds)->delete();
            }

            // 2. Update existing price groups
            $existingGroupsData = $request->input('groups', []);
            foreach ($existingGroupsData as $id => $data) {
                if (in_array($id, $deletedIds)) {
                    continue;
                }
                $group = WoodBoardPriceGroup::find($id);
                if ($group) {
                    $group->update(['name' => $data['name']]);

                    // Sync/update type prices
                    $pricesData = $data['prices'] ?? [];
                    foreach ($pricesData as $typeId => $priceData) {
                        $group->prices()->updateOrCreate(
                            ['wood_board_type_id' => $typeId],
                            [
                                'name'        => $priceData['name'] ?? null,
                                'thickness'   => $priceData['thickness'] ?? null,
                                'price_board' => floatval($priceData['price_board'] ?? 0),
                                'price_m2'    => floatval($priceData['price_m2'] ?? 0),
                            ]
                        );
                    }
                }
            }

            // 3. Insert new price groups
            $newGroupsData = $request->input('new_groups', []);
            foreach ($newGroupsData as $data) {
                $group = WoodBoardPriceGroup::create(['name' => $data['name']]);

                // Insert type prices
                $pricesData = $data['prices'] ?? [];
                foreach ($pricesData as $typeId => $priceData) {
                    $group->prices()->create([
                        'wood_board_type_id' => $typeId,
                        'name'               => $priceData['name'] ?? null,
                        'thickness'          => $priceData['thickness'] ?? null,
                        'price_board'        => floatval($priceData['price_board'] ?? 0),
                        'price_m2'           => floatval($priceData['price_m2'] ?? 0),
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Cập nhật cấu hình nhóm giá thành công.');
    }

    private function cleanRequestPrices(Request $request)
    {
        // For store and update: $request->prices is an array of board type ID -> prices data
        if ($request->has('prices') && is_array($request->prices)) {
            $prices = $request->prices;
            foreach ($prices as $typeId => $typeData) {
                if (isset($typeData['price_board'])) {
                    $prices[$typeId]['price_board'] = $this->parseVnd($typeData['price_board']);
                }
                if (isset($typeData['price_m2'])) {
                    $prices[$typeId]['price_m2'] = $this->parseVnd($typeData['price_m2']);
                }
            }
            $request->merge(['prices' => $prices]);
        }

        // For batchUpdatePriceGroups: groups and new_groups
        if ($request->has('groups') && is_array($request->groups)) {
            $groups = $request->groups;
            foreach ($groups as $groupId => $groupData) {
                if (isset($groupData['prices']) && is_array($groupData['prices'])) {
                    foreach ($groupData['prices'] as $typeId => $priceData) {
                        if (isset($priceData['price_board'])) {
                            $groups[$groupId]['prices'][$typeId]['price_board'] = $this->parseVnd($priceData['price_board']);
                        }
                        if (isset($priceData['price_m2'])) {
                            $groups[$groupId]['prices'][$typeId]['price_m2'] = $this->parseVnd($priceData['price_m2']);
                        }
                    }
                }
            }
            $request->merge(['groups' => $groups]);
        }

        if ($request->has('new_groups') && is_array($request->new_groups)) {
            $newGroups = $request->new_groups;
            foreach ($newGroups as $groupId => $groupData) {
                if (isset($groupData['prices']) && is_array($groupData['prices'])) {
                    foreach ($groupData['prices'] as $typeId => $priceData) {
                        if (isset($priceData['price_board'])) {
                            $newGroups[$groupId]['prices'][$typeId]['price_board'] = $this->parseVnd($priceData['price_board']);
                        }
                        if (isset($priceData['price_m2'])) {
                            $newGroups[$groupId]['prices'][$typeId]['price_m2'] = $this->parseVnd($priceData['price_m2']);
                        }
                    }
                }
            }
            $request->merge(['new_groups' => $newGroups]);
        }
    }

    private function parseVnd($value)
    {
        if (is_null($value) || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        $value = trim($value);
        
        // If there's both dot and comma (e.g. 1.234.567,89 or 1,234,567.89)
        if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } else {
            // Only dots or only commas
            if (strpos($value, '.') !== false) {
                $dotsCount = substr_count($value, '.');
                if ($dotsCount > 1) {
                    $value = str_replace('.', '', $value);
                } else {
                    $parts = explode('.', $value);
                    if (isset($parts[1]) && strlen($parts[1]) === 3) {
                        $value = str_replace('.', '', $value);
                    }
                }
            }
            if (strpos($value, ',') !== false) {
                $commasCount = substr_count($value, ',');
                if ($commasCount > 1) {
                    $value = str_replace(',', '', $value);
                } else {
                    $parts = explode(',', $value);
                    if (isset($parts[1]) && strlen($parts[1]) === 3) {
                        $value = str_replace(',', '', $value);
                    } else {
                        $value = str_replace(',', '.', $value);
                    }
                }
            }
        }
        
        return floatval($value);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 7) {
                return back()->with('error', 'File Excel không đúng cấu trúc hoặc không có dữ liệu.');
            }

            \DB::transaction(function () use ($rows) {
                \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                \App\Models\WoodBoardPrice::truncate();
                \App\Models\WoodBoard::truncate();
                \App\Models\WoodBoardType::truncate();
                \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                // Create the 6 board types
                $types = [
                    [
                        'id' => 1,
                        'name' => 'Acrylic Foil',
                        'prefix' => '',
                        'display_order' => 1,
                    ],
                    [
                        'id' => 2,
                        'name' => 'MDF 1 mặt Acrylic',
                        'prefix' => '.TP',
                        'display_order' => 2,
                    ],
                    [
                        'id' => 3,
                        'name' => 'MDF 2 mặt Acrylic',
                        'prefix' => '.TP.2M',
                        'display_order' => 3,
                    ],
                    [
                        'id' => 4,
                        'name' => 'Cốt Nhựa 1 mặt Acrylic',
                        'prefix' => '.TP.PVC.1M',
                        'display_order' => 4,
                    ],
                    [
                        'id' => 5,
                        'name' => 'Cốt Nhựa 1 mặt Acrylic chống cong',
                        'prefix' => '.TP.PVC',
                        'display_order' => 5,
                    ],
                    [
                        'id' => 6,
                        'name' => 'Cốt Nhựa 2 mặt Acrylic',
                        'prefix' => '.TP.PVC.2M',
                        'display_order' => 6,
                    ],
                ];

                foreach ($types as $type) {
                    \App\Models\WoodBoardType::create([
                        'id' => $type['id'],
                        'name' => $type['name'],
                        'prefix' => $type['prefix'],
                        'display_order' => $type['display_order'],
                    ]);
                }

                $count = 0;
                for ($i = 6; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Col B is Color Code
                    $colorCode = isset($row[1]) ? trim((string)$row[1]) : '';
                    if (empty($colorCode)) {
                        continue;
                    }

                    $board = \App\Models\WoodBoard::create([
                        'color_code'  => $colorCode,
                        'price_group' => '',
                    ]);

                    // Map columns
                    $priceC = $this->cleanPrice($row[2]);

                    $priceD = $this->cleanPrice($row[3]);
                    $priceE = $this->cleanPrice($row[4]);
                    $priceF = $this->cleanPrice($row[5]);
                    $priceG = $this->cleanPrice($row[6]);

                    $priceH = $this->cleanPrice($row[7]);
                    $priceI = $this->cleanPrice($row[8]);
                    $priceJ = $this->cleanPrice($row[9]);
                    $priceK = $this->cleanPrice($row[10]);
                    $priceL = $this->cleanPrice($row[11]);

                    $pricingData = [
                        1 => [
                            'price_board' => $priceC,
                            'price_m2'    => 0,
                            'thickness'   => '17mm',
                        ],
                        2 => [
                            'price_board' => $priceH,
                            'price_m2'    => $priceD,
                            'thickness'   => '17mm',
                        ],
                        3 => [
                            'price_board' => $priceI,
                            'price_m2'    => $priceE,
                            'thickness'   => '17mm',
                        ],
                        4 => [
                            'price_board' => $priceJ,
                            'price_m2'    => 0,
                            'thickness'   => '17mm',
                        ],
                        5 => [
                            'price_board' => $priceK,
                            'price_m2'    => $priceF,
                            'thickness'   => '17mm',
                        ],
                        6 => [
                            'price_board' => $priceL,
                            'price_m2'    => $priceG,
                            'thickness'   => '17mm',
                        ],
                    ];

                    foreach ($pricingData as $typeId => $priceVal) {
                        $boardType = \App\Models\WoodBoardType::find($typeId);
                        $prefix = $boardType ? ($boardType->prefix ?? '') : '';
                        $board->prices()->create([
                            'wood_board_type_id' => $typeId,
                            'code'               => $board->color_code . $prefix,
                            'name'               => $boardType ? $boardType->name : '',
                            'thickness'          => $priceVal['thickness'],
                            'price_board'        => $priceVal['price_board'],
                            'price_m2'           => $priceVal['price_m2'],
                        ]);
                    }

                    $count++;
                }
            });

            return back()->with('success', 'Nhập bảng giá gỗ Acrylic thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Đã xảy ra lỗi khi nhập file: ' . $e->getMessage());
        }
    }

    private function cleanPrice($val)
    {
        if (is_null($val) || $val === '') {
            return 0;
        }
        if (is_numeric($val)) {
            return floatval($val);
        }
        $cleaned = preg_replace('/[^0-9]/', '', $val);
        return floatval($cleaned);
    }

    /**
     * Tra cứu thông tin chi tiết bảng giá tấm theo mã màu và loại ván
     */
    public function getBoardInfo(Request $request)
    {
        $colorCode = trim((string)$request->input('color_code'));
        $typeId = $request->input('wood_board_type_id');
        $typeName = trim((string)$request->input('board_type'));

        $board = null;
        if ($colorCode !== '') {
            $board = WoodBoard::with(['prices.type'])->where('color_code', $colorCode)->first();
        }

        $allTypes = WoodBoardType::orderBy('display_order')->get();

        $selectedType = null;
        if ($typeId) {
            $selectedType = $allTypes->firstWhere('id', (int)$typeId);
        } elseif ($typeName !== '') {
            $selectedType = $allTypes->first(function ($t) use ($typeName) {
                return mb_strtolower($t->name) === mb_strtolower($typeName)
                    || mb_stripos($t->name, $typeName) !== false
                    || mb_stripos($typeName, $t->name) !== false;
            });
        }

        $priceRecord = null;
        if ($board && $selectedType) {
            $priceRecord = $board->prices->firstWhere('wood_board_type_id', $selectedType->id);
        }

        // Computed values
        $prefix = $selectedType?->prefix ?? '';
        $computedCode = $colorCode !== '' ? ($colorCode . $prefix) : '';
        $computedThickness = $priceRecord?->thickness ?? '17mm';
        $computedName = $priceRecord?->name ?? ($selectedType ? "Tấm {$selectedType->name} {$colorCode}" : "Tấm {$colorCode}");
        $costPrice = $priceRecord?->price_board ?? 0;

        return response()->json([
            'success'            => true,
            'color_code'         => $colorCode,
            'board'              => $board,
            'types'              => $allTypes,
            'selected_type'      => $selectedType,
            'price'              => $priceRecord,
            'computed_code'      => $computedCode,
            'computed_name'      => $computedName,
            'computed_thickness' => $computedThickness,
            'cost_price'         => $costPrice,
            'stock'              => floatval($priceRecord?->stock ?? 0),
            'price_group'        => $board?->price_group ?? '',
        ]);
    }

    /**
     * Cập nhật nhanh số lượng tồn kho trực tiếp từ bảng giá
     */
    public function quickUpdateStock(Request $request)
    {
        $request->validate([
            'price_id' => 'required|integer|exists:wood_board_prices,id',
            'stock'    => 'required|numeric|min:0',
        ]);

        $price = WoodBoardPrice::with(['board', 'type'])->findOrFail($request->price_id);
        $newStock = floatval($request->stock);
        $price->update(['stock' => $newStock]);

        // Keep wood_boards.current_stock in sync
        $board = $price->board;
        $totalStock = 0;
        if ($board) {
            $totalStock = $board->prices()->sum('stock');
            $board->update(['current_stock' => $totalStock]);
        }

        return response()->json([
            'success'     => true,
            'message'     => "Đã cập nhật tồn kho cho {$price->code}: {$newStock} tấm",
            'price_id'    => $price->id,
            'code'        => $price->code,
            'stock'       => $newStock,
            'total_stock' => $totalStock,
        ]);
    }
}
