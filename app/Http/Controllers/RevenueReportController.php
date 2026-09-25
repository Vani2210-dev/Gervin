<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\RevenueTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RevenueReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || (!$user->can('view revenue report') && !$user->hasRole('Admin') && !$user->can('view order'))) {
                abort(403, 'Bạn không có quyền truy cập Báo cáo doanh thu.');
            }
            return $next($request);
        });
    }

    /**
     * Báo cáo doanh thu theo ngày - Bám sát 100% cấu trúc bảng Excel
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && $currentUser->hasRole('Admin');

        // 1. Chế độ xem: 'day' (theo từng ngày), 'range' (khoảng ngày từ... đến...), hoặc 'month' (tổng của tháng)
        $viewMode = $request->input('view_mode', 'day');
        $todayDate = now()->toDateString();
        $selectedDate = $request->input('date', $todayDate);
        if ($selectedDate > $todayDate) {
            $selectedDate = $todayDate;
        }

        if ($request->filled('date_mode')) {
            $fMode = $request->input('date_mode');
            $fVal  = $request->input('date_val');
            if ($fMode === 'month') {
                $viewMode = 'month';
                if ($fVal && strlen($fVal) >= 7) {
                    $request->merge([
                        'year' => (int) substr($fVal, 0, 4),
                        'month' => (int) substr($fVal, 5, 2),
                    ]);
                }
            } elseif ($fMode === 'day') {
                $viewMode = 'day';
                if ($fVal) {
                    $selectedDate = $fVal;
                }
            }
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        if (!$fromDate || !$toDate) {
            $fromDate = Carbon::today()->startOfMonth()->toDateString();
            $toDate = $todayDate;
        }

        if ($viewMode === 'month') {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('month', now()->month);
            $selectedCarbon = Carbon::createFromDate($year, $month, 1);
            $selectedDate = $selectedCarbon->toDateString();
            $prevDate = $selectedCarbon->copy()->subMonth()->format('Y-m-d');
            $nextDate = $selectedCarbon->copy()->addMonth()->format('Y-m-d');
            $daysInRange = $selectedCarbon->daysInMonth;
        } elseif ($viewMode === 'range') {
            $fromCarbon = Carbon::parse($fromDate);
            $toCarbon = Carbon::parse($toDate);

            // Giới hạn khoảng ngày nằm trong cùng một tháng của ngày bắt đầu (giống như xuất Excel)
            if ($fromCarbon->year !== $toCarbon->year || $fromCarbon->month !== $toCarbon->month) {
                $toCarbon = $fromCarbon->copy()->endOfMonth();
            }
            if ($fromCarbon->gt($toCarbon)) {
                $tmp = $fromCarbon;
                $fromCarbon = $toCarbon;
                $toCarbon = $tmp;
            }
            if ($toCarbon->gt(Carbon::today())) {
                $toCarbon = Carbon::today();
            }

            $fromDate = $fromCarbon->toDateString();
            $toDate = $toCarbon->toDateString();
            $selectedCarbon = $fromCarbon;
            $year = $fromCarbon->year;
            $month = $fromCarbon->month;
            $prevDate = $fromCarbon->copy()->subDay()->toDateString();
            $nextDate = $toCarbon->copy()->addDay()->toDateString();
            $daysInRange = $fromCarbon->diffInDays($toCarbon) + 1;
        } else {
            $selectedCarbon = Carbon::parse($selectedDate);
            $year = $selectedCarbon->year;
            $month = $selectedCarbon->month;
            $prevDate = $selectedCarbon->copy()->subDay()->toDateString();
            $nextDate = $selectedCarbon->copy()->addDay()->toDateString();
            $daysInRange = 1;
        }

        // 2. Mục tiêu doanh thu tháng & ngày từ CSDL (bảng revenue_targets)
        $targetRecord = RevenueTarget::where('year', $year)->where('month', $month)->first();
        $targetMonth = $targetRecord ? (float) $targetRecord->target_amount : 500000000;
        $daysInMonth = $selectedCarbon->daysInMonth;
        if ($targetRecord && (float) $targetRecord->target_day_amount > 0) {
            $targetDay = (float) $targetRecord->target_day_amount;
        } else {
            $targetDay = $daysInMonth > 0 ? round($targetMonth / $daysInMonth) : 0;
        }

        $lostMonths = $targetRecord ? (int) ($targetRecord->lost_months ?? 6) : 6;
        if ($lostMonths <= 0) $lostMonths = 6;
        $returningMonths = $targetRecord ? (int) ($targetRecord->returning_months ?? 6) : 6;
        if ($returningMonths <= 0) $returningMonths = 6;

        $targetThresholdDate = ($viewMode === 'month') ? $selectedCarbon->copy()->endOfMonth() : (($viewMode === 'range') ? Carbon::parse($toDate) : $selectedCarbon);
        $lostThresholdDate = $targetThresholdDate->copy()->subMonths($lostMonths)->toDateString();
        $returningThresholdDate = $targetThresholdDate->copy()->subMonths($returningMonths)->toDateString();

        // 3. Query danh sách Khách hàng có phân quyền
        $customerQuery = Customer::with('users')->orderBy('customer_code');

        $filterUserId = null;
        if ($isAdmin) {
            $filterUserId = $request->input('user_id');
            if (!empty($filterUserId)) {
                $customerQuery->whereHas('users', function ($q) use ($filterUserId) {
                    $q->where('users.id', $filterUserId);
                });
            }
            $staffUsers = User::orderBy('name')->get(['id', 'name', 'user_code']);
        } else {
            if ($currentUser) {
                $userMarketGroupIds = $currentUser->marketGroups()->pluck('market_groups.id');
                $customerQuery->whereIn('market_group_id', $userMarketGroupIds);
            }
            $staffUsers = collect();
        }

        // Tìm kiếm khách hàng theo tên hoặc mã
        $search = $request->input('search');
        if (!empty($search)) {
            $customerQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $customerQuery->get();
        $customerIds = $customers->pluck('id')->toArray();
        $startOfMonth = ($viewMode === 'range')
            ? Carbon::parse($fromDate)->startOfMonth()->toDateString()
            : $selectedCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = ($viewMode === 'range')
            ? Carbon::parse($toDate)->endOfMonth()->toDateString()
            : $selectedCarbon->copy()->endOfMonth()->toDateString();

        // 4. Lấy đơn hàng và thanh toán từ đầu tháng được chọn trở đi (startOfMonth)
        $monthOrders = Order::whereIn('customer_id', $customerIds)
            ->whereNotIn('status', ['draft', 'pending', 'cancelled'])
            ->whereDate('order_date', '>=', $startOfMonth)
            ->get(['id', 'customer_id', 'total_amount', 'type', 'relation_type', 'board_return_status', 'order_date']);

        $monthPayments = CustomerPayment::whereIn('customer_id', $customerIds)
            ->whereDate('payment_date', '>=', $startOfMonth)
            ->get(['id', 'customer_id', 'amount', 'payment_date']);

        // Lấy tất cả đơn hàng trong quá khứ tính đến ngày/tháng được chọn để thống kê trạng thái khách
        $pastLimitDate = ($viewMode === 'month') ? $endOfMonth : (($viewMode === 'range') ? $toDate : $selectedDate);
        $pastOrders = Order::whereIn('customer_id', $customerIds)
            ->whereNotIn('status', ['draft', 'pending', 'cancelled'])
            ->whereDate('order_date', '<=', $pastLimitDate)
            ->orderBy('order_date', 'desc')
            ->get(['id', 'customer_id', 'order_date']);
        $pastOrdersByCustomer = $pastOrders->groupBy('customer_id');

        // Đơn hàng và thanh toán trong chu kỳ được chọn (Theo ngày, Khoảng ngày hoặc Theo tháng)
        if ($viewMode === 'month') {
            $periodOrders = $monthOrders->filter(fn($o) => Carbon::parse($o->order_date)->month == $month && Carbon::parse($o->order_date)->year == $year);
            $periodPayments = $monthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->month == $month && Carbon::parse($p->payment_date)->year == $year);
        } elseif ($viewMode === 'range') {
            $periodOrders = $monthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() >= $fromDate && Carbon::parse($o->order_date)->toDateString() <= $toDate);
            $periodPayments = $monthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() >= $fromDate && Carbon::parse($p->payment_date)->toDateString() <= $toDate);
        } else {
            $periodOrders = $monthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() === $selectedDate);
            $periodPayments = $monthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() === $selectedDate);
        }

        // 5. Thống kê chi tiết từng dòng Khách hàng cho Bảng
        $tableRows = [];
        $totalStartMonthDebt = 0;
        $totalPolicyDebt = 0;
        $totalLostCust = 0;
        $totalNewCust = 0;
        $totalReturningCust = 0;
        $totalAcrylicMain = 0;
        $totalAcrylicWarranty = 0;
        $totalGlass = 0;
        $totalMinLate = 0;
        $totalLaminateCommercial = 0;
        $totalPlywoodCommercial = 0;
        $totalPaidInDay = 0;
        $totalEndDayDebt = 0;
        $totalDebtVsPolicy = 0;

        foreach ($customers as $cust) {
            $currentDebt = (float) ($cust->debt ?? 0);
            $cMonthOrders = $monthOrders->where('customer_id', $cust->id);
            $cMonthPayments = $monthPayments->where('customer_id', $cust->id);

            if ($viewMode === 'month') {
                $cPeriodOrders = $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->month == $month && Carbon::parse($o->order_date)->year == $year);
                $cPeriodPayments = $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->month == $month && Carbon::parse($p->payment_date)->year == $year);
            } elseif ($viewMode === 'range') {
                $cPeriodOrders = $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() >= $fromDate && Carbon::parse($o->order_date)->toDateString() <= $toDate);
                $cPeriodPayments = $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() >= $fromDate && Carbon::parse($p->payment_date)->toDateString() <= $toDate);
            } else {
                $cPeriodOrders = $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() === $selectedDate);
                $cPeriodPayments = $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() === $selectedDate);
            }

            // Lịch sử đơn hàng để xét 3 trạng thái KH
            $custOrders = $pastOrdersByCustomer->get($cust->id, collect());
            $priorLimitDate = ($viewMode === 'month') ? $startOfMonth : (($viewMode === 'range') ? $fromDate : $selectedDate);
            $cPriorOrders = $custOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() < $priorLimitDate);

            // 1. Khách mở mới
            if ($viewMode === 'month') {
                $cNewCust = (Carbon::parse($cust->created_at)->month == $month && Carbon::parse($cust->created_at)->year == $year) ? 1 : 0;
            } elseif ($viewMode === 'range') {
                $cNewCust = (Carbon::parse($cust->created_at)->toDateString() >= $fromDate && Carbon::parse($cust->created_at)->toDateString() <= $toDate) ? 1 : 0;
            } else {
                $cNewCust = Carbon::parse($cust->created_at)->toDateString() === $selectedDate ? 1 : 0;
            }

            // 2. Khách quay lại
            $cReturningCust = 0;
            if ($cPeriodOrders->count() > 0 && $cPriorOrders->count() > 0) {
                $latestPriorOrder = $cPriorOrders->first();
                if (Carbon::parse($latestPriorOrder->order_date)->toDateString() <= $returningThresholdDate) {
                    $cReturningCust = 1;
                }
            }

            // 3. Khách mất đi
            $cLostCust = 0;
            if ($custOrders->count() > 0) {
                $latestOrder = $custOrders->first();
                if (Carbon::parse($latestOrder->order_date)->toDateString() <= $lostThresholdDate) {
                    $cLostCust = 1;
                }
            }

            // Phân loại doanh số Acrylic & Số đơn bảo hành (Không tính đơn bảo hành và đơn bổ sung đã trả ván/không nợ)
            $cAcrylicMain = (float) $cPeriodOrders->where('type', 'acrylic')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
            $cAcrylicWarranty = (int) $cPeriodOrders->where('relation_type', 'warranty')->count();
            
            // Cánh kính & Min Late (Không tính đơn bảo hành và đơn bổ sung đã trả ván/không nợ)
            $cGlass = (float) $cPeriodOrders->where('type', 'glass')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
            $cMinLate = (float) $cPeriodOrders->where('type', 'min_late')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
            
            // Thương mại
            $cLaminateCommercial = 0;
            $cPlywoodCommercial = 0;

            // Tiền về
            $cPaidInDay = (float) $cPeriodPayments->sum('amount');

            // Công nợ đầu tháng (chỉ trừ những đơn có tính nợ)
            $ordersFromMonthToNow = (float) $cMonthOrders->filter(fn($o) => $o->board_return_status !== 'returned')->sum('total_amount');
            $paymentsFromMonthToNow = (float) $cMonthPayments->sum('amount');
            $cStartMonthDebt = $currentDebt - ($ordersFromMonthToNow - $paymentsFromMonthToNow);

            // Công nợ cuối kỳ (cuối ngày, cuối khoảng ngày hoặc cuối tháng - chỉ tính đơn có nợ)
            if ($viewMode === 'month') {
                $ordersAfterPeriod = (float) $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() > $endOfMonth && $o->board_return_status !== 'returned')->sum('total_amount');
                $paymentsAfterPeriod = (float) $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() > $endOfMonth)->sum('amount');
            } elseif ($viewMode === 'range') {
                $ordersAfterPeriod = (float) $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() > $toDate && $o->board_return_status !== 'returned')->sum('total_amount');
                $paymentsAfterPeriod = (float) $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() > $toDate)->sum('amount');
            } else {
                $ordersAfterPeriod = (float) $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() > $selectedDate && $o->board_return_status !== 'returned')->sum('total_amount');
                $paymentsAfterPeriod = (float) $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() > $selectedDate)->sum('amount');
            }
            $cEndDayDebt = $currentDebt - ($ordersAfterPeriod - $paymentsAfterPeriod);
            
            // Định mức công nợ
            $cPolicyDebt = (float) ($cust->debt_limit ?? 0);
            if ($cPolicyDebt == 0 && is_numeric($cust->policy)) {
                $cPolicyDebt = (float) $cust->policy;
            }
            $cDebtVsPolicy = $cPolicyDebt - $cEndDayDebt;

            $tableRows[] = [
                'customer_id'          => $cust->id,
                'customer_code'        => $cust->customer_code ?? '—',
                'customer_name'        => $cust->name,
                'customer_phone'       => $cust->phone,
                'customer_address'     => $cust->address,
                'users'                => $cust->users,
                'start_month_debt'     => $cStartMonthDebt,
                'policy_debt'          => $cPolicyDebt,
                'lost_cust'            => $cLostCust,
                'new_cust'             => $cNewCust,
                'returning_cust'       => $cReturningCust,
                'care_count'           => 0,
                'miss_count'           => 0,
                'acrylic_main'         => $cAcrylicMain,
                'acrylic_warranty'     => $cAcrylicWarranty,
                'glass'                => $cGlass,
                'min_late'             => $cMinLate,
                'paid_in_day'          => $cPaidInDay,
                'end_day_debt'         => $cEndDayDebt,
                'debt_vs_policy'       => $cDebtVsPolicy,
                'laminate_commercial'  => $cLaminateCommercial,
                'plywood_commercial'   => $cPlywoodCommercial,
                'has_activity'         => ($cPeriodOrders->count() > 0 || $cPeriodPayments->count() > 0),
            ];

            // Cộng dồn dòng Tổng cộng
            $totalStartMonthDebt += $cStartMonthDebt;
            $totalPolicyDebt += $cPolicyDebt;
            $totalLostCust += $cLostCust;
            $totalNewCust += $cNewCust;
            $totalReturningCust += $cReturningCust;
            $totalAcrylicMain += $cAcrylicMain;
            $totalAcrylicWarranty += $cAcrylicWarranty;
            $totalGlass += $cGlass;
            $totalMinLate += $cMinLate;
            $totalLaminateCommercial += $cLaminateCommercial;
            $totalPlywoodCommercial += $cPlywoodCommercial;
            $totalPaidInDay += $cPaidInDay;
            $totalEndDayDebt += $cEndDayDebt;
            $totalDebtVsPolicy += $cDebtVsPolicy;
        }

        // 6. Phân đoạn nhóm khách hàng (Segment Quick Filter qua Request Server-side)
        $segment = $request->input('segment', 'all');
        if (!in_array($segment, ['all', 'lost', 'new', 'returning', 'active'])) {
            $segment = 'all';
        }

        $allRowsCount = count($tableRows);
        $activeCustCount = collect($tableRows)->where('has_activity', true)->count();

        // Lọc danh sách hiển thị trên bảng nếu có chọn phân đoạn
        $displayRows = $tableRows;
        if ($segment === 'lost') {
            $displayRows = array_values(array_filter($tableRows, fn($r) => !empty($r['lost_cust'])));
        } elseif ($segment === 'new') {
            $displayRows = array_values(array_filter($tableRows, fn($r) => !empty($r['new_cust'])));
        } elseif ($segment === 'returning') {
            $displayRows = array_values(array_filter($tableRows, fn($r) => !empty($r['returning_cust'])));
        } elseif ($segment === 'active') {
            $displayRows = array_values(array_filter($tableRows, fn($r) => !empty($r['has_activity'])));
        }

        // Tính dòng tổng cộng cho dữ liệu hiển thị trên bảng
        $displayStartMonthDebt = array_sum(array_column($displayRows, 'start_month_debt'));
        $displayPolicyDebt = array_sum(array_column($displayRows, 'policy_debt'));
        $displayAcrylicMain = array_sum(array_column($displayRows, 'acrylic_main'));
        $displayAcrylicWarranty = array_sum(array_column($displayRows, 'acrylic_warranty'));
        $displayGlass = array_sum(array_column($displayRows, 'glass'));
        $displayMinLate = array_sum(array_column($displayRows, 'min_late'));
        $displayLaminateCommercial = array_sum(array_column($displayRows, 'laminate_commercial'));
        $displayPlywoodCommercial = array_sum(array_column($displayRows, 'plywood_commercial'));
        $displayPaidInDay = array_sum(array_column($displayRows, 'paid_in_day'));
        $displayEndDayDebt = array_sum(array_column($displayRows, 'end_day_debt'));
        $displayDebtVsPolicy = array_sum(array_column($displayRows, 'debt_vs_policy'));

        // 7. Các chỉ số Khối KPI
        $dayTotalRevenue = $totalAcrylicMain + $totalGlass + $totalMinLate + $totalLaminateCommercial + $totalPlywoodCommercial;
        $compareTarget = ($viewMode === 'day') ? $targetDay : $targetMonth;
        $dayDiff = $dayTotalRevenue - $compareTarget;
        $dayPercent = $compareTarget > 0 ? ($dayTotalRevenue / $compareTarget) * 100 : 0;
        $dayOrdersCount = $periodOrders->count();
        $dayCustomersCount = $periodOrders->pluck('customer_id')->unique()->filter()->count();
        $dayOrdersMoreThan2Count = $periodOrders->groupBy('customer_id')->filter(fn($g) => $g->count() >= 2)->count();

        // Danh sách nhân viên
        $staffUsers = User::whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Admin');
            })->where(function ($q) {
                $q->whereNull('role_id')
                  ->orWhereHas('role', function ($rq) {
                      $rq->where('name', '!=', 'Admin');
                  });
            })->where('email', '!=', 'admin@kbtech.com')
              ->where('id', '!=', 1)
              ->orderBy('name')
              ->get();

        // % So sánh công nợ so với Định mức công nợ
        $debtVsPolicyPercent = $displayPolicyDebt > 0 ? ($displayEndDayDebt / $displayPolicyDebt) * 100 : 0;

        // Đếm số lượng bộ lọc đang kích hoạt
        $activeFilterCount = 0;
        if ($viewMode === 'month' || $viewMode === 'range') $activeFilterCount++;
        if ($segment !== 'all') $activeFilterCount++;
        if (!empty($filterUserId)) $activeFilterCount++;
        if (!empty($search)) $activeFilterCount++;
        if ($viewMode === 'day' && $request->has('date') && $selectedDate !== $todayDate) $activeFilterCount++;

        // Nhân viên phụ trách được chọn (chỉ khi Admin chủ động lọc theo một nhân viên cụ thể)
        $selectedStaffUser = null;
        if ($isAdmin && !empty($filterUserId)) {
            $selectedStaffUser = User::find($filterUserId);
        }

        $isFiltered = $activeFilterCount > 0;
        $tableRows = $displayRows;
        $dateMode = $viewMode === 'month' ? 'month' : 'day';
        $dateVal = $dateMode === 'month' ? sprintf('%04d-%02d', $year, $month) : $selectedDate;

        return view('reports.revenue', compact(
            'viewMode', 'segment', 'allRowsCount', 'activeCustCount', 'activeFilterCount', 'isFiltered',
            'selectedDate', 'selectedCarbon', 'year', 'month', 'fromDate', 'toDate', 'daysInRange', 'compareTarget',
            'prevDate', 'nextDate', 'todayDate',
            'targetMonth', 'daysInMonth', 'targetDay', 'lostMonths', 'returningMonths',
            'dayTotalRevenue', 'dayDiff', 'dayPercent',
            'dayOrdersCount', 'dayCustomersCount', 'dayOrdersMoreThan2Count',
            'tableRows',
            'displayStartMonthDebt', 'displayPolicyDebt', 'totalLostCust', 'totalNewCust', 'totalReturningCust',
            'displayAcrylicMain', 'displayAcrylicWarranty',
            'displayGlass', 'displayMinLate', 'displayPaidInDay', 'displayEndDayDebt',
            'displayDebtVsPolicy', 'debtVsPolicyPercent', 'displayLaminateCommercial', 'displayPlywoodCommercial',
            'staffUsers', 'filterUserId', 'selectedStaffUser', 'search',
            'dateMode', 'dateVal'
        ));
    }

    /**
     * Xuất file Excel Báo cáo doanh thu theo ngày
     */
    public function export(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && $currentUser->hasRole('Admin');

        $todayDate = now()->toDateString();
        // 1. Xác định chế độ xuất và danh sách các ngày cần xuất
        $exportType = $request->input('export_type', 'single');
        $todayDate = Carbon::today()->toDateString();

        if ($exportType === 'range') {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
            if (!$fromDate || !$toDate) {
                $fromDate = Carbon::today()->startOfMonth()->toDateString();
                $toDate = Carbon::today()->toDateString();
            }
            $fromCarbon = Carbon::parse($fromDate);
            $toCarbon = Carbon::parse($toDate);

            // Giới hạn trong cùng một tháng của ngày bắt đầu
            if ($fromCarbon->year !== $toCarbon->year || $fromCarbon->month !== $toCarbon->month) {
                $toCarbon = $fromCarbon->copy()->endOfMonth();
            }
            if ($fromCarbon->gt($toCarbon)) {
                $tmp = $fromCarbon;
                $fromCarbon = $toCarbon;
                $toCarbon = $tmp;
            }
            if ($toCarbon->gt(Carbon::today())) {
                $toCarbon = Carbon::today();
            }
            $dates = [];
            $cur = $fromCarbon->copy();
            while ($cur->lte($toCarbon)) {
                $dates[] = $cur->copy();
                $cur->addDay();
            }
            $selectedCarbon = $fromCarbon;
            $month = $selectedCarbon->month;
            $filename = 'Bao_Cao_Doanh_Thu_' . $fromCarbon->format('Ymd') . '_den_' . $toCarbon->format('Ymd') . '.xlsx';
        } elseif ($exportType === 'month') {
            $expMonth = (int) $request->input('export_month', Carbon::today()->month);
            $expYear = (int) $request->input('export_year', Carbon::today()->year);
            $startMonth = Carbon::createFromDate($expYear, $expMonth, 1);
            $endMonth = $startMonth->copy()->endOfMonth();
            $dates = [];
            $cur = $startMonth->copy();
            while ($cur->lte($endMonth)) {
                $dates[] = $cur->copy();
                $cur->addDay();
            }
            $selectedCarbon = $startMonth;
            $month = $expMonth;
            $filename = 'Bao_Cao_Doanh_Thu_Thang_' . sprintf('%02d', $expMonth) . '_' . $expYear . '.xlsx';
        } else {
            // single day (mặc định xuất 1 ngày lẻ)
            $selectedDate = $request->input('date', $todayDate);
            if ($selectedDate > $todayDate) {
                $selectedDate = $todayDate;
            }
            $selectedCarbon = Carbon::parse($selectedDate);
            $dates = [$selectedCarbon];
            $month = $selectedCarbon->month;
            $filename = 'Bao_Cao_Doanh_Thu_' . $selectedCarbon->format('Y-m-d') . '.xlsx';
        }

        // 2. Query danh sách Khách hàng theo bộ lọc
        $customerQuery = Customer::with('users')->orderBy('customer_code');
        $filterUserId = null;
        if ($isAdmin) {
            $filterUserId = $request->input('user_id');
            if (!empty($filterUserId)) {
                $customerQuery->whereHas('users', fn($q) => $q->where('users.id', $filterUserId));
            }
        } else {
            if ($currentUser) {
                $userMarketGroupIds = $currentUser->marketGroups()->pluck('market_groups.id');
                $customerQuery->whereIn('market_group_id', $userMarketGroupIds);
            }
        }

        $search = $request->input('search');
        if (!empty($search)) {
            $customerQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $customerQuery->get();
        $customerIds = $customers->pluck('id')->toArray();

        // 3. Lấy dữ liệu Đơn hàng và Thanh toán trong toàn bộ phạm vi ngày
        $firstDateCarbon = $dates[0];
        $lastDateCarbon = end($dates);
        $startOfMonth = $firstDateCarbon->copy()->startOfMonth()->toDateString();
        $latestDateStr = $lastDateCarbon->toDateString();

        $monthOrders = Order::whereIn('customer_id', $customerIds)
            ->whereNotIn('status', ['draft', 'pending', 'cancelled'])
            ->whereDate('order_date', '>=', $startOfMonth)
            ->get(['id', 'customer_id', 'total_amount', 'type', 'relation_type', 'board_return_status', 'order_date']);

        $monthPayments = CustomerPayment::whereIn('customer_id', $customerIds)
            ->whereDate('payment_date', '>=', $startOfMonth)
            ->get(['id', 'customer_id', 'amount', 'payment_date']);

        // Lấy lịch sử đơn hàng trong quá khứ tính đến ngày muộn nhất để xét trạng thái KH
        $pastOrders = Order::whereIn('customer_id', $customerIds)
            ->whereNotIn('status', ['draft', 'pending', 'cancelled'])
            ->whereDate('order_date', '<=', $latestDateStr)
            ->orderBy('order_date', 'desc')
            ->get(['id', 'customer_id', 'order_date']);
        $pastOrdersByCustomer = $pastOrders->groupBy('customer_id');

        // Mục tiêu doanh thu từ bảng CSDL revenue_targets (cache theo year_month)
        $yearsList = collect($dates)->pluck('year')->unique()->toArray();
        $monthsList = collect($dates)->pluck('month')->unique()->toArray();
        $targetsList = RevenueTarget::whereIn('year', $yearsList)->whereIn('month', $monthsList)->get();
        $targetsMap = $targetsList->keyBy(fn($t) => $t->year . '_' . $t->month);

        // 4. Lọc các khách hàng có hoạt động trong các ngày xuất
        $activeCustomers = [];
        foreach ($customers as $cust) {
            $currentDebt = (float) ($cust->debt ?? 0);
            $cPolicyDebt = (float) ($cust->debt_limit ?? 0);
            $cMonthOrders = $monthOrders->where('customer_id', $cust->id);
            $cMonthPayments = $monthPayments->where('customer_id', $cust->id);

            // Kiểm tra công nợ đầu tháng (chỉ trừ những đơn có tính nợ)
            $ordersFromMonthToNow = (float) $cMonthOrders->filter(fn($o) => $o->board_return_status !== 'returned')->sum('total_amount');
            $paymentsFromMonthToNow = (float) $cMonthPayments->sum('amount');
            $cStartMonthDebt = $currentDebt - ($ordersFromMonthToNow - $paymentsFromMonthToNow);

            // Kiểm tra xem khách có hoạt động ở bất kỳ ngày nào trong danh sách xuất không
            $hasAnyActivity = $cStartMonthDebt > 0 || $cPolicyDebt > 0 || $cMonthOrders->count() > 0 || $cMonthPayments->count() > 0;
            if ($hasAnyActivity) {
                $activeCustomers[] = [
                    'customer' => $cust,
                    'start_month_debt' => $cStartMonthDebt,
                    'policy_debt' => $cPolicyDebt,
                ];
            }
        }

        // 5. Khởi tạo Spreadsheet và Cấu hình Chung
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bao_Cao_Doanh_Thu');

        // Style Định Nghĩa Sẵn
        $headerBaseStyle = [
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 9.5, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
        ];

        $khakiFillStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D4CEBA']
            ]
        ];

        $kpiOrangeStyle = [
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 9, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F79646']
            ]
        ];

        $totalRowStyle = [
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 10, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00']
            ]
        ];

        $numFormat = '#,##0;#,##0;"-"';

        // Thiết lập chiều cao các dòng Header
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(38);
        $sheet->getRowDimension(3)->setRowHeight(38);
        $sheet->getRowDimension(4)->setRowHeight(42);
        $sheet->getRowDimension(5)->setRowHeight(26);
        $sheet->getRowDimension(6)->setRowHeight(22);

        // 6. Header 5 Cột Cố Định A-E (Dòng 1 đến Dòng 5)
        $prevMonthNum = $month - 1 > 0 ? $month - 1 : 12;

        // Xác định nhân viên phụ trách xuất file:
        // - Admin lọc theo 1 nhân viên cụ thể -> hiển thị nhân viên đó
        // - Nhân viên thường tự xuất -> hiển thị chính nhân viên đó
        $exportStaffUser = null;
        if ($isAdmin && !empty($filterUserId)) {
            $exportStaffUser = User::find($filterUserId);
        } elseif (!$isAdmin && $currentUser) {
            $exportStaffUser = $currentUser;
        }

        if ($exportStaffUser) {
            $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
            $titleRun = $richText->createTextRun("Khách Hàng\n");
            $titleRun->getFont()->setBold(true)->setSize(10)->setName('Times New Roman');

            $staffCode = $exportStaffUser->user_code ? $exportStaffUser->user_code . ' - ' : '';
            $subRun = $richText->createTextRun('(NV: ' . $staffCode . $exportStaffUser->name . ')');
            $subRun->getFont()->setBold(false)->setItalic(true)->setSize(9)->setName('Times New Roman')->getColor()->setRGB('333333');

            $customerHeaderValue = $richText;
        } else {
            $customerHeaderValue = 'Khách Hàng';
        }

        $fixedHeaders = [
            ['STT', 'A1:A5'],
            ['Mã KH', 'B1:B5'],
            [$customerHeaderValue, 'C1:C5'],
            ['Công nợ tháng ' . $prevMonthNum, 'D1:D5'],
            ['Định mức công nợ', 'E1:E5'],
        ];

        foreach ($fixedHeaders as $h) {
            $range = $h[1];
            $firstCell = explode(':', $range)[0];
            $sheet->setCellValue($firstCell, $h[0]);
            $sheet->mergeCells($range);
        }
        $sheet->getStyle('A1:E5')->applyFromArray($headerBaseStyle);
        $sheet->getStyle('A1:E5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);

        // Điền dữ liệu khách hàng vào 5 cột A-E từ Dòng 7 trở đi
        $rowIdx = 7;
        $totalStartMonthDebtSum = 0;
        $totalPolicyDebtSum = 0;
        $stt = 1;

        foreach ($activeCustomers as $item) {
            $cust = $item['customer'];
            $sheet->setCellValue("A{$rowIdx}", $stt++);
            $sheet->setCellValue("B{$rowIdx}", $cust->customer_code ?? '—');
            $sheet->setCellValue("C{$rowIdx}", $cust->name);
            $sheet->setCellValue("D{$rowIdx}", $item['start_month_debt'] > 0 ? $item['start_month_debt'] : '-');
            $sheet->setCellValue("E{$rowIdx}", $item['policy_debt'] > 0 ? $item['policy_debt'] : '-');

            $totalStartMonthDebtSum += $item['start_month_debt'];
            $totalPolicyDebtSum += $item['policy_debt'];
            $rowIdx++;
        }

        $lastRow = max(7, $rowIdx - 1);

        // Dòng Tổng Cộng cho 5 cột đầu A-E (Dòng 6)
        $sheet->setCellValue('A6', '—');
        $sheet->setCellValue('B6', '—');
        $sheet->setCellValue('C6', $exportType === 'month' ? 'TỔNG CỘNG THÁNG' : ($exportType === 'range' ? 'TỔNG CỘNG KỲ' : 'TỔNG CỘNG NGÀY'));
        $sheet->setCellValue('D6', $totalStartMonthDebtSum);
        $sheet->setCellValue('E6', $totalPolicyDebtSum);
        $sheet->getStyle('A6:E6')->applyFromArray($totalRowStyle);

        // Định dạng và canh lề 5 cột đầu A-E
        $sheet->getStyle("A6:B{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C6:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("D6:E{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D6:E{$lastRow}")->getNumberFormat()->setFormatCode($numFormat);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(13);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);

        // Style Màu Xanh Dương Nhạt chuẩn theo file mẫu cho Khối TỔNG THÁNG / TỔNG KỲ
        $monthBlockBlueStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'B7DEE8']
            ]
        ];

        // 7. Khối TỔNG THÁNG / TỔNG KỲ (Đúng 12 Cột F -> Q, Nền Xanh Pastel B7DEE8) - Xuất khi chọn chế độ Cả Tháng hoặc Khoảng Ngày
        if (in_array($exportType, ['month', 'range'])) {
            $tKey = $selectedCarbon->year . '_' . $month;
            $targetRecord = $targetsMap->get($tKey);
            $targetMonth = $targetRecord ? (float) $targetRecord->target_amount : 850000000;

            // Xác định ngày bắt đầu và kết thúc của khối tổng hợp
            if ($exportType === 'range') {
                $periodStartDateStr = $fromCarbon->toDateString();
                $periodEndDateStr = $toCarbon->toDateString();
                $summaryTitle = 'TỔNG KỲ (' . $fromCarbon->format('d/m') . ' - ' . $toCarbon->format('d/m') . ')';
                $paidHeader = 'TỔNG TIỀN VỀ TRONG KỲ';
                $endDebtHeader = 'CÔNG NỢ CUỐI KỲ';
            } else {
                $periodStartDateStr = $startMonth->toDateString();
                $periodEndDateStr = $endMonth->toDateString();
                $summaryTitle = 'TỔNG THÁNG ' . $month;
                $paidHeader = 'TỔNG TIỀN VỀ TRONG THÁNG';
                $endDebtHeader = 'CÔNG NỢ CUỐI THÁNG';
            }

            // Dòng 1: Thanh Tiêu Đề Khối Tổng
            $sheet->mergeCells('F1:Q1');
            $sheet->setCellValue('F1', $summaryTitle);
            $sheet->getStyle('F1')->getFont()->setBold(true)->setSize(11)->setName('Times New Roman');
            $sheet->getStyle('F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F1:Q1')->applyFromArray($monthBlockBlueStyle);

            // Dòng 4 & 5: Header 12 Cột Khối Tổng
            $sheet->mergeCells('F4:F5')->setCellValue('F4', 'SỐ KH MẤT ĐI');
            $sheet->mergeCells('G4:G5')->setCellValue('G4', 'SỐ KH MỞ MỚI');
            $sheet->mergeCells('H4:H5')->setCellValue('H4', 'SỐ KH QUAY LẠI');
            $sheet->mergeCells('I4:J4')->setCellValue('I4', 'TỔNG DOANH SỐ ACRYLIC');
            $sheet->setCellValue('I5', 'ĐƠN CHÍNH, FOIL, CHỈ, BS');
            $sheet->setCellValue('J5', 'ĐƠN BẢO HÀNH');
            $sheet->mergeCells('K4:K5')->setCellValue('K4', 'DOANH SỐ CÁNH KÍNH');
            $sheet->mergeCells('L4:L5')->setCellValue('L4', 'DOANH SỐ ĐƠN MELAMIN, LAMINATE GIA CÔNG');
            $sheet->mergeCells('M4:M5')->setCellValue('M4', $paidHeader);
            $sheet->mergeCells('N4:N5')->setCellValue('N4', $endDebtHeader);
            $sheet->setCellValue('O4', 'SO SÁNH VỚI CÔNG NỢ ĐỊNH MỨC');
            $sheet->getStyle('O4')->getFont()->setSize(8.5);
            $sheet->mergeCells('P4:P5')->setCellValue('P4', 'DOANH THU LAMINATE THƯƠNG MẠI');
            $sheet->mergeCells('Q4:Q5')->setCellValue('Q4', 'DOANH THU PLYWOOD THƯƠNG MẠI');

            $sheet->getStyle('F4:Q5')->applyFromArray($headerBaseStyle);
            $sheet->getStyle('F4:Q5')->applyFromArray($monthBlockBlueStyle);

            // Tính toán số liệu tổng cho từng khách hàng
            $mTotalLostCust = 0;
            $mTotalNewCust = 0;
            $mTotalReturningCust = 0;
            $mTotalAcrylicM = 0;
            $mTotalAcrylicW = 0;
            $mTotalGlass = 0;
            $mTotalMinLate = 0;
            $mTotalPaid = 0;
            $mTotalEndDebt = 0;
            $mTotalDebtVsPolicy = 0;
            $mTotalLaminateTM = 0;
            $mTotalPlywoodTM = 0;

            $cRowIdx = 7;

            foreach ($activeCustomers as $item) {
                $cust = $item['customer'];
                $currentDebt = (float) ($cust->debt ?? 0);
                $cPolicyDebt = $item['policy_debt'];

                $cPeriodOrders = $monthOrders->where('customer_id', $cust->id)
                    ->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() >= $periodStartDateStr && Carbon::parse($o->order_date)->toDateString() <= $periodEndDateStr);
                $cPeriodPayments = $monthPayments->where('customer_id', $cust->id)
                    ->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() >= $periodStartDateStr && Carbon::parse($p->payment_date)->toDateString() <= $periodEndDateStr);

                $cMonthAcrylicM = (float) $cPeriodOrders->where('type', 'acrylic')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
                $cMonthAcrylicW = (int) $cPeriodOrders->where('relation_type', 'warranty')->count();
                $cMonthGlass = (float) $cPeriodOrders->where('type', 'glass')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
                $cMonthMinLate = (float) $cPeriodOrders->where('type', 'min_late')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
                $cMonthLaminateTM = 0;
                $cMonthPlywoodTM = 0;
                $cMonthPaid = (float) $cPeriodPayments->sum('amount');

                // Công nợ sau khoảng thời gian được chọn đến hiện tại
                $cAfterMonthOrders = $monthOrders->where('customer_id', $cust->id)->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() > $periodEndDateStr && $o->board_return_status !== 'returned')->sum('total_amount');
                $cAfterMonthPayments = $monthPayments->where('customer_id', $cust->id)->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() > $periodEndDateStr)->sum('amount');
                $cMonthEndDebt = $currentDebt - ($cAfterMonthOrders - $cAfterMonthPayments);
                $cMonthDebtVsPolicy = $cPolicyDebt > 0 ? ($cPolicyDebt - $cMonthEndDebt) : -$cMonthEndDebt;

                // Điền vào sheet (12 cột F -> Q)
                $sheet->setCellValue("F{$cRowIdx}", '');
                $sheet->setCellValue("G{$cRowIdx}", '');
                $sheet->setCellValue("H{$cRowIdx}", '');
                $sheet->setCellValue("I{$cRowIdx}", $cMonthAcrylicM > 0 ? $cMonthAcrylicM : '-');
                $sheet->setCellValue("J{$cRowIdx}", $cMonthAcrylicW > 0 ? $cMonthAcrylicW : '-');
                $sheet->setCellValue("K{$cRowIdx}", $cMonthGlass > 0 ? $cMonthGlass : '-');
                $sheet->setCellValue("L{$cRowIdx}", $cMonthMinLate > 0 ? $cMonthMinLate : '-');
                $sheet->setCellValue("M{$cRowIdx}", $cMonthPaid > 0 ? $cMonthPaid : '-');
                $sheet->setCellValue("N{$cRowIdx}", $cMonthEndDebt > 0 ? $cMonthEndDebt : '-');
                $sheet->setCellValue("O{$cRowIdx}", $cMonthDebtVsPolicy != 0 ? abs($cMonthDebtVsPolicy) : '-');
                if ($cMonthDebtVsPolicy < 0) {
                    $sheet->getStyle("O{$cRowIdx}")->getFont()->getColor()->setRGB('DC2626');
                }
                $sheet->setCellValue("P{$cRowIdx}", $cMonthLaminateTM > 0 ? $cMonthLaminateTM : '-');
                $sheet->setCellValue("Q{$cRowIdx}", $cMonthPlywoodTM > 0 ? $cMonthPlywoodTM : '-');

                $mTotalAcrylicM += $cMonthAcrylicM;
                $mTotalAcrylicW += $cMonthAcrylicW;
                $mTotalGlass += $cMonthGlass;
                $mTotalMinLate += $cMonthMinLate;
                $mTotalPaid += $cMonthPaid;
                $mTotalEndDebt += $cMonthEndDebt;
                $mTotalDebtVsPolicy += $cMonthDebtVsPolicy;
                $mTotalLaminateTM += $cMonthLaminateTM;
                $mTotalPlywoodTM += $cMonthPlywoodTM;

                $cRowIdx++;
            }

            // Dòng 2 & 3: Khối Cam KPI Tổng (12 cột F -> Q)
            $mTotalActualRevenue = $mTotalAcrylicM + $mTotalGlass + $mTotalMinLate + $mTotalLaminateTM + $mTotalPlywoodTM;
            $mDiff = $mTotalActualRevenue - $targetMonth;
            $mPercent = $targetMonth > 0 ? ($mTotalActualRevenue / $targetMonth) * 100 : 0;

            $mPeriodOrders = $monthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() >= $periodStartDateStr && Carbon::parse($o->order_date)->toDateString() <= $periodEndDateStr);
            $mOrdersCount = $mPeriodOrders->count();
            $mCustomersCount = $mPeriodOrders->pluck('customer_id')->unique()->filter()->count();

            $sheet->mergeCells('F2:G2')->setCellValue('F2', 'MỤC TIÊU DOANH THU THÁNG');
            $sheet->mergeCells('F3:G3')->setCellValue('F3', $targetMonth);

            $sheet->mergeCells('H2:I2')->setCellValue('H2', 'DOANH THU THỰC TẾ ĐẠT ĐƯỢC');
            $sheet->mergeCells('H3:I3')->setCellValue('H3', $mTotalActualRevenue > 0 ? $mTotalActualRevenue : '-');

            $sheet->mergeCells('J2:J3')->setCellValue('J2', "SO SÁNH VỚI\nDOANH THU MỤC\nTIÊU THÁNG");

            $sheet->mergeCells('K2:L2')->setCellValue('K2', "Doanh thu vượt (+)\n/Doanh thu thiếu (-)");
            $sheet->setCellValue('M2', $mDiff);

            $sheet->mergeCells('K3:L3')->setCellValue('K3', '% đạt doanh thu định mức');
            $sheet->setCellValue('M3', number_format($mPercent, 0) . '%');

            $sheet->setCellValue('N2', 'SỐ ĐH LÊN ĐƠN');
            $sheet->setCellValue('O2', $mOrdersCount);

            $sheet->setCellValue('N3', 'SỐ KH CÓ ĐƠN');
            $sheet->setCellValue('O3', $mCustomersCount);

            $sheet->mergeCells('P2:P3')->setCellValue('P2', "TIỀN VỀ\nACRYLIC");
            $sheet->mergeCells('Q2:Q3')->setCellValue('Q2', $mTotalPaid > 0 ? $mTotalPaid : '-');

            $sheet->getStyle('F2:Q3')->applyFromArray($khakiFillStyle);
            $sheet->getStyle('F2:Q3')->getFont()->setBold(true)->setSize(9)->setName('Times New Roman');
            $sheet->getStyle('F2:Q3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setWrapText(true);

            $sheet->getStyle('F3:I3')->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('Q2:Q3')->getNumberFormat()->setFormatCode('#,##0;#,##0;"-"');
            $sheet->getStyle('M2')->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');
            if ($mDiff < 0) {
                $sheet->getStyle('M2')->getFont()->getColor()->setRGB('DC2626');
            }

            // Dòng 6: Dòng Tổng Cộng (Màu Vàng FFFF00, 12 cột F -> Q)
            $sheet->setCellValue('F6', '-');
            $sheet->setCellValue('G6', '-');
            $sheet->setCellValue('H6', '-');
            $sheet->setCellValue('I6', $mTotalAcrylicM);
            $sheet->setCellValue('J6', $mTotalAcrylicW);
            $sheet->setCellValue('K6', $mTotalGlass);
            $sheet->setCellValue('L6', $mTotalMinLate);
            $sheet->setCellValue('M6', $mTotalPaid);
            $sheet->setCellValue('N6', $mTotalEndDebt);
            $sheet->setCellValue('O6', $mTotalDebtVsPolicy != 0 ? abs($mTotalDebtVsPolicy) : '-');
            if ($mTotalDebtVsPolicy < 0) {
                $sheet->getStyle('O6')->getFont()->getColor()->setRGB('DC2626');
            }
            $sheet->setCellValue('P6', $mTotalLaminateTM);
            $sheet->setCellValue('Q6', $mTotalPlywoodTM);

            $monthDebtVsPolicyPercent = $totalPolicyDebtSum > 0 ? ($mTotalEndDebt / $totalPolicyDebtSum) * 100 : 0;
            $sheet->setCellValue('O5', number_format($monthDebtVsPolicyPercent, 0) . '%');
            $sheet->getStyle('O5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('B7DEE8');

            $sheet->getStyle('F6:Q6')->applyFromArray($totalRowStyle);

            // Tô màu nền Xanh Pastel cho dữ liệu khách hàng khối Tổng
            if ($lastRow >= 7) {
                $sheet->getStyle("F7:Q{$lastRow}")->applyFromArray($monthBlockBlueStyle);
            }

            // Format số & Canh lề cho khối Tổng (F..Q)
            $sheet->getStyle("F6:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0;#,##0;"-"');
            $sheet->getStyle("I6:Q{$lastRow}")->getNumberFormat()->setFormatCode($numFormat);

            $sheet->getStyle("F6:H{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I6:Q{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // Độ rộng 12 cột của khối Tổng
            $monthColWidths = [10, 10, 10, 15, 13, 14, 17, 15, 15, 16, 17, 14];
            for ($c = 0; $c < 12; $c++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(6 + $c);
                $sheet->getColumnDimension($colLetter)->setWidth($monthColWidths[$c]);
            }
        }

        // 8. Vòng Lặp Duyệt Từng Ngày Để Tạo Khối 12 Cột Số Liệu
        $dayColWidths = [10, 10, 10, 15, 13, 14, 17, 15, 15, 16, 17, 14];
        $baseDailyColStart = in_array($exportType, ['month', 'range']) ? 18 : 6; // Nếu có Khối Tổng (F..Q, 12 cột) thì ngày bắt đầu từ cột R (18)

        foreach ($dates as $k => $dayCarbon) {
            $curDateStr = $dayCarbon->toDateString();
            $startColIdx = $baseDailyColStart + $k * 12;

            // Tạo các chữ cái tên cột cho ngày hiện tại
            $colF = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx);
            $colG = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 1);
            $colH = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 2);
            $colI = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 3);
            $colJ = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 4);
            $colK = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 5);
            $colL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 6);
            $colM = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 7);
            $colN = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 8);
            $colO = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 9);
            $colP = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 10);
            $colQ = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + 11);

            // Dòng 1: Thanh Ngày Khaki
            $sheet->mergeCells("{$colF}1:{$colQ}1");
            $sheet->setCellValue("{$colF}1", $dayCarbon->format('j/n/Y'));
            $sheet->getStyle("{$colF}1")->getFont()->setBold(true)->setSize(11)->setName('Times New Roman');
            $sheet->getStyle("{$colF}1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$colF}1:{$colQ}1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('D4CEBA');

            // Màu nền xen kẽ giữa các ngày (ngày chẵn: Nâu Khaki D4CEBA, ngày lẻ: Trắng FFFFFF)
            $dayBgColor = ($k % 2 === 0) ? 'D4CEBA' : 'FFFFFF';

            // Dòng 4 & 5: Header 12 Cột Số Liệu
            $sheet->mergeCells("{$colF}4:{$colF}5")->setCellValue("{$colF}4", "SỐ KH MẤT ĐI");
            $sheet->mergeCells("{$colG}4:{$colG}5")->setCellValue("{$colG}4", "SỐ KH MỞ MỚI");
            $sheet->mergeCells("{$colH}4:{$colH}5")->setCellValue("{$colH}4", "SỐ KH QUAY LẠI");
            $sheet->mergeCells("{$colI}4:{$colJ}4")->setCellValue("{$colI}4", "TỔNG DOANH SỐ ACRYLIC");
            $sheet->setCellValue("{$colI}5", "ĐƠN CHÍNH, FOIL, CHỈ, BS");
            $sheet->setCellValue("{$colJ}5", "ĐƠN BẢO HÀNH");
            $sheet->mergeCells("{$colK}4:{$colK}5")->setCellValue("{$colK}4", "DOANH SỐ CÁNH KÍNH");
            $sheet->mergeCells("{$colL}4:{$colL}5")->setCellValue("{$colL}4", "DOANH SỐ ĐƠN MELAMIN, LAMINATE GIA CÔNG");
            $sheet->mergeCells("{$colM}4:{$colM}5")->setCellValue("{$colM}4", "TỔNG TIỀN VỀ TRONG NGÀY");
            $sheet->mergeCells("{$colN}4:{$colN}5")->setCellValue("{$colN}4", "CÔNG NỢ CUỐI NGÀY");
            $sheet->setCellValue("{$colO}4", "SO SÁNH VỚI CÔNG NỢ ĐỊNH MỨC");
            $sheet->getStyle("{$colO}4")->getFont()->setSize(8.5);
            $sheet->mergeCells("{$colP}4:{$colP}5")->setCellValue("{$colP}4", "DOANH THU LAMINATE THƯƠNG MẠI");
            $sheet->mergeCells("{$colQ}4:{$colQ}5")->setCellValue("{$colQ}4", "DOANH THU PLYWOOD THƯƠNG MẠI");

            $sheet->getStyle("{$colF}4:{$colQ}5")->applyFromArray($headerBaseStyle);
            $sheet->getStyle("{$colF}4:{$colQ}5")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($dayBgColor);

            // Thiết lập mục tiêu của ngày
            $tKey = $dayCarbon->year . '_' . $dayCarbon->month;
            $targetRecord = $targetsMap->get($tKey);
            $targetMonth = $targetRecord ? (float) $targetRecord->target_amount : 850000000;
            $daysInMonth = $dayCarbon->daysInMonth;
            if ($targetRecord && (float) $targetRecord->target_day_amount > 0) {
                $targetDay = (float) $targetRecord->target_day_amount;
            } else {
                $targetDay = $daysInMonth > 0 ? round($targetMonth / $daysInMonth) : 0;
            }

            $lostMonths = $targetRecord ? (int) ($targetRecord->lost_months ?? 6) : 6;
            if ($lostMonths <= 0) $lostMonths = 6;
            $returningMonths = $targetRecord ? (int) ($targetRecord->returning_months ?? 6) : 6;
            if ($returningMonths <= 0) $returningMonths = 6;

            $lostThresholdDate = $dayCarbon->copy()->subMonths($lostMonths)->toDateString();
            $returningThresholdDate = $dayCarbon->copy()->subMonths($returningMonths)->toDateString();

            // Tính toán dữ liệu khách hàng cho ngày này
            $dLostCust = 0;
            $dNewCust = 0;
            $dReturningCust = 0;
            $dAcrylicMain = 0;
            $dAcrylicWarranty = 0;
            $dGlass = 0;
            $dMinLate = 0;
            $dPaidInDay = 0;
            $dEndDayDebt = 0;
            $dDebtVsPolicy = 0;
            $dLaminateTM = 0;
            $dPlywoodTM = 0;

            $cRowIdx = 7;
            foreach ($activeCustomers as $item) {
                $cust = $item['customer'];
                $currentDebt = (float) ($cust->debt ?? 0);
                $cPolicyDebt = $item['policy_debt'];
                $cMonthOrders = $monthOrders->where('customer_id', $cust->id);
                $cMonthPayments = $monthPayments->where('customer_id', $cust->id);

                $cDayOrders = $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() === $curDateStr);
                $cDayPayments = $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() === $curDateStr);

                $custOrders = $pastOrdersByCustomer->get($cust->id, collect());
                $cPriorOrders = $custOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() < $curDateStr);

                // Khách mở mới
                $cNew = Carbon::parse($cust->created_at)->toDateString() === $curDateStr ? 1 : 0;

                // Khách quay lại
                $cReturning = 0;
                if ($cDayOrders->count() > 0 && $cPriorOrders->count() > 0) {
                    $latestPrior = $cPriorOrders->first();
                    if (Carbon::parse($latestPrior->order_date)->toDateString() <= $returningThresholdDate) {
                        $cReturning = 1;
                    }
                }

                // Khách mất đi
                $cLost = 0;
                if ($custOrders->count() > 0) {
                    $latestOrder = $custOrders->first();
                    if (Carbon::parse($latestOrder->order_date)->toDateString() <= $lostThresholdDate) {
                        $cLost = 1;
                    }
                }

                $cAcrylicM = (float) $cDayOrders->where('type', 'acrylic')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
                $cAcrylicW = (int) $cDayOrders->where('relation_type', 'warranty')->count();
                $cGla = (float) $cDayOrders->where('type', 'glass')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
                $cMin = (float) $cDayOrders->where('type', 'min_late')->filter(fn($o) => $o->relation_type !== 'warranty' && $o->board_return_status !== 'returned')->sum('total_amount');
                $cLam = 0;
                $cPly = 0;

                $cPaid = (float) $cDayPayments->sum('amount');

                // Công nợ từ sau ngày này đến hiện tại (chỉ tính đơn có nợ)
                $cAfterOrders = $cMonthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() > $curDateStr && $o->board_return_status !== 'returned')->sum('total_amount');
                $cAfterPayments = $cMonthPayments->filter(fn($p) => Carbon::parse($p->payment_date)->toDateString() > $curDateStr)->sum('amount');
                $cEndDebt = $currentDebt - ($cAfterOrders - $cAfterPayments);

                $cDebtVsPol = $cPolicyDebt > 0 ? ($cPolicyDebt - $cEndDebt) : -$cEndDebt;

                // Điền vào sheet
                $sheet->setCellValue("{$colF}{$cRowIdx}", '');
                $sheet->setCellValue("{$colG}{$cRowIdx}", '');
                $sheet->setCellValue("{$colH}{$cRowIdx}", '');
                $sheet->setCellValue("{$colI}{$cRowIdx}", $cAcrylicM > 0 ? $cAcrylicM : '-');
                $sheet->setCellValue("{$colJ}{$cRowIdx}", $cAcrylicW > 0 ? $cAcrylicW : '-');
                $sheet->setCellValue("{$colK}{$cRowIdx}", $cGla > 0 ? $cGla : '-');
                $sheet->setCellValue("{$colL}{$cRowIdx}", $cMin > 0 ? $cMin : '-');
                $sheet->setCellValue("{$colM}{$cRowIdx}", $cPaid > 0 ? $cPaid : '-');
                $sheet->setCellValue("{$colN}{$cRowIdx}", $cEndDebt > 0 ? $cEndDebt : '-');
                $sheet->setCellValue("{$colO}{$cRowIdx}", $cDebtVsPol != 0 ? abs($cDebtVsPol) : '-');
                if ($cDebtVsPol < 0) {
                    $sheet->getStyle("{$colO}{$cRowIdx}")->getFont()->getColor()->setRGB('DC2626');
                }
                $sheet->setCellValue("{$colP}{$cRowIdx}", $cLam > 0 ? $cLam : '-');
                $sheet->setCellValue("{$colQ}{$cRowIdx}", $cPly > 0 ? $cPly : '-');

                $dLostCust += $cLost;
                $dNewCust += $cNew;
                $dReturningCust += $cReturning;
                $dAcrylicMain += $cAcrylicM;
                $dAcrylicWarranty += $cAcrylicW;
                $dGlass += $cGla;
                $dMinLate += $cMin;
                $dPaidInDay += $cPaid;
                $dEndDayDebt += $cEndDebt;
                $dDebtVsPolicy += $cDebtVsPol;
                $dLaminateTM += $cLam;
                $dPlywoodTM += $cPly;

                $cRowIdx++;
            }

            // Dòng 2 & 3: Khối Cam KPI Ngày
            $dayRevenue = $dAcrylicMain + $dGlass + $dMinLate + $dLaminateTM + $dPlywoodTM;
            $dayDiff = $dayRevenue - $targetDay;
            $dayPercent = $targetDay > 0 ? ($dayRevenue / $targetDay) * 100 : 0;
            $dayOrdersCount = $monthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() === $curDateStr)->count();
            $dayCustomersCount = $monthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() === $curDateStr)->pluck('customer_id')->unique()->filter()->count();
            $dayOrdersMoreThan2Count = $monthOrders->filter(fn($o) => Carbon::parse($o->order_date)->toDateString() === $curDateStr)->groupBy('customer_id')->filter(fn($g) => $g->count() >= 2)->count();

            $sheet->mergeCells("{$colF}2:{$colG}2")->setCellValue("{$colF}2", "MỤC TIÊU DOANH THU THÁNG");
            $sheet->mergeCells("{$colF}3:{$colG}3")->setCellValue("{$colF}3", $targetMonth);

            $sheet->mergeCells("{$colH}2:{$colI}2")->setCellValue("{$colH}2", "MỤC TIÊU DOANH THU NGÀY");
            $sheet->mergeCells("{$colH}3:{$colI}3")->setCellValue("{$colH}3", $targetDay);

            $sheet->mergeCells("{$colJ}2:{$colJ}3")->setCellValue("{$colJ}2", "SO SÁNH VỚI\nDOANH THU\nMỤC TIÊU NGÀY");

            $sheet->mergeCells("{$colK}2:{$colL}2")->setCellValue("{$colK}2", "Doanh thu vượt (+)\n/Doanh thu thiếu (-)");
            $sheet->setCellValue("{$colM}2", $dayDiff);

            $sheet->mergeCells("{$colK}3:{$colL}3")->setCellValue("{$colK}3", "% đạt doanh thu định mức");
            $sheet->setCellValue("{$colM}3", number_format($dayPercent, 0) . '%');

            $sheet->setCellValue("{$colN}2", "SỐ ĐH LÊN ĐƠN");
            $sheet->setCellValue("{$colO}2", $dayOrdersCount);

            $sheet->setCellValue("{$colN}3", "SỐ KH CÓ ĐƠN");
            $sheet->setCellValue("{$colO}3", $dayCustomersCount);

            $sheet->mergeCells("{$colP}2:{$colP}3")->setCellValue("{$colP}2", "SỐ ĐH KHI 1 NGÀY CÓ > 2 ĐƠN HÀNG\n(NHẬP SỐ 1 KHI BẮT ĐẦU TỪ ĐƠN THỨ 2 TRỞ LÊN)");
            $sheet->mergeCells("{$colQ}2:{$colQ}3")->setCellValue("{$colQ}2", $dayOrdersMoreThan2Count);

            $sheet->getStyle("{$colF}2:{$colQ}3")->applyFromArray($kpiOrangeStyle);
            $sheet->getStyle("{$colF}3:{$colI}3")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("{$colM}2")->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');
            if ($dayDiff < 0) {
                $sheet->getStyle("{$colM}2")->getFont()->getColor()->setRGB('DC2626');
            }
            $sheet->getStyle("{$colP}2")->getFont()->setSize(7.8);

            // Dòng 6: Dòng Tổng Cộng Ngày
            $sheet->setCellValue("{$colF}6", $dLostCust > 0 ? $dLostCust : '-');
            $sheet->setCellValue("{$colG}6", $dNewCust > 0 ? $dNewCust : '-');
            $sheet->setCellValue("{$colH}6", $dReturningCust > 0 ? $dReturningCust : '-');
            $sheet->setCellValue("{$colI}6", $dAcrylicMain);
            $sheet->setCellValue("{$colJ}6", $dAcrylicWarranty);
            $sheet->setCellValue("{$colK}6", $dGlass);
            $sheet->setCellValue("{$colL}6", $dMinLate);
            $sheet->setCellValue("{$colM}6", $dPaidInDay);
            $sheet->setCellValue("{$colN}6", $dEndDayDebt);
            $sheet->setCellValue("{$colO}6", $dDebtVsPolicy != 0 ? abs($dDebtVsPolicy) : '-');
            if ($dDebtVsPolicy < 0) {
                $sheet->getStyle("{$colO}6")->getFont()->getColor()->setRGB('DC2626');
            }
            $sheet->setCellValue("{$colP}6", $dLaminateTM);
            $sheet->setCellValue("{$colQ}6", $dPlywoodTM);

            $debtVsPolicyPercent = $totalPolicyDebtSum > 0 ? ($dEndDayDebt / $totalPolicyDebtSum) * 100 : 0;
            $sheet->setCellValue("{$colO}5", number_format($debtVsPolicyPercent, 0) . '%');
            $sheet->getStyle("{$colO}5")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($dayBgColor);

            $sheet->getStyle("{$colF}6:{$colQ}6")->applyFromArray($totalRowStyle);

            // Tô màu nền xen kẽ cho dữ liệu khách hàng từ Dòng 7 trở đi (ngày chẵn: Nâu Khaki D4CEBA, ngày lẻ: Trắng FFFFFF)
            if ($lastRow >= 7) {
                $dayDataBgColor = ($k % 2 === 0) ? 'D4CEBA' : 'FFFFFF';
                $sheet->getStyle("{$colF}7:{$colQ}{$lastRow}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($dayDataBgColor);
            }

            // Định dạng số & Canh lề cho 12 cột của ngày
            $sheet->getStyle("{$colF}6:{$colH}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0;#,##0;"-"');
            $sheet->getStyle("{$colI}6:{$colQ}{$lastRow}")->getNumberFormat()->setFormatCode($numFormat);

            $sheet->getStyle("{$colF}6:{$colH}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$colI}6:{$colQ}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // Độ rộng 12 cột của ngày
            for ($c = 0; $c < 12; $c++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIdx + $c);
                $sheet->getColumnDimension($colLetter)->setWidth($dayColWidths[$c]);
            }
        }

        // 9. Viền bảng và Freeze Pane
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $totalCols = ($exportType === 'month') ? (19 + count($dates) * 12) : (5 + count($dates) * 12);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        if ($lastRow >= 6) {
            $sheet->getStyle("A1:{$lastColLetter}{$lastRow}")->applyFromArray($borderStyle);
        }

        // Cố định cột A-D và Dòng 1-6 khi cuộn ngang/dọc
        $sheet->freezePane('E7');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
        }

        $writer->save('php://output');
        exit;
    }

    /**
     * Lưu thiết lập Báo cáo doanh thu vào Cơ sở dữ liệu (bảng revenue_targets)
     */
    public function saveTarget(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Chỉ quản trị viên (Admin) mới có quyền cập nhật thiết lập báo cáo doanh thu.'], 403);
        }

        $request->validate([
            'year'              => 'required|integer|min:2020|max:2099',
            'month'             => 'required|integer|min:1|max:12',
            'target_amount'     => 'required|numeric|min:0',
            'target_day_amount' => 'nullable|numeric|min:0',
            'lost_months'       => 'nullable|integer|min:1|max:60',
            'returning_months'  => 'nullable|integer|min:1|max:60',
        ]);

        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $targetAmount = (float) $request->input('target_amount');
        $targetDayAmount = (float) $request->input('target_day_amount', 0);
        $lostMonths = (int) $request->input('lost_months', 6);
        if ($lostMonths <= 0) $lostMonths = 6;
        $returningMonths = (int) $request->input('returning_months', 6);
        if ($returningMonths <= 0) $returningMonths = 6;

        $targetRecord = RevenueTarget::updateOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'target_amount'     => $targetAmount,
                'target_day_amount' => $targetDayAmount,
                'lost_months'       => $lostMonths,
                'returning_months'  => $returningMonths,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Đã lưu thiết lập báo cáo doanh thu tháng {$month}/{$year} thành công!",
            'data'    => $targetRecord,
        ]);
    }

    /**
     * Lấy thiết lập mục tiêu báo cáo doanh thu theo tháng/năm từ CSDL
     */
    public function getTarget(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $targetRecord = RevenueTarget::where('year', $year)->where('month', $month)->first();
        $targetAmount = $targetRecord ? (float) $targetRecord->target_amount : 500000000;
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $targetDayAmount = $targetRecord && (float) $targetRecord->target_day_amount > 0 
            ? (float) $targetRecord->target_day_amount 
            : round($targetAmount / $daysInMonth);

        return response()->json([
            'success' => true,
            'data'    => [
                'year'              => $year,
                'month'             => $month,
                'target_amount'     => $targetAmount,
                'target_day_amount' => $targetDayAmount,
                'lost_months'       => $targetRecord ? (int) ($targetRecord->lost_months ?? 6) : 6,
                'returning_months'  => $targetRecord ? (int) ($targetRecord->returning_months ?? 6) : 6,
            ],
        ]);
    }
}
