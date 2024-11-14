<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;

class AdminController extends Controller
{
    /**
     * Repository for handling dashboard-related data operations.
     *
     * @var \App\Repositories\Eloquent\DashboardRepositoryInterface 
     */
    protected $dashboardRepo;

    /**
     * Repository for handling contact data operations.
     *
     * @var \App\Repositories\Eloquent\ContactRepositoryInterface 
     */
    protected $contactRepo;

    /**
     * Repository for handling order data operations.
     *
     * @var \App\Repositories\Contracts\OrderRepositoryInterface
     */
    protected $orderRepo;

    /**
     * Create a new controller instance and inject dependencies.
     *
     * @param \App\Repositories\Eloquent\DashboardRepositoryInterface $dashboardRepo
     * @param \App\Repositories\Eloquent\ContactRepositoryInterface $contactRepo
     * @param \App\Repositories\Eloquent\OrderRepositoryInterface $orderRepo
     */
    public function __construct(
        DashboardRepositoryInterface $dashboardRepo,
        ContactRepositoryInterface $contactRepo,
        OrderRepositoryInterface $orderRepo
    ) 
    {
        $this->dashboardRepo = $dashboardRepo;
        $this->contactRepo = $contactRepo;
        $this->orderRepo = $orderRepo;
    }

    /**
     * Display the dashboard view with recent orders and statistical data.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $orders = $this->orderRepo->getForAdminPage();

        // Retrieve general dashboard and monthly data 
        $dashboardDatas = $this->dashboardRepo->getDashboardData();
        $monthlyDatas = $this->dashboardRepo->getMonthlyData();

        // Define a helper function to get a comma-separated string of values for a specific column in monthly data
        function getMonthlyDataString($monthlyDatas, $column)
        {
            return implode(',', collect($monthlyDatas)->pluck($column)->toArray());
        }

        // Prepare monthly data strings for different metrics
        $AmountMonthly = getMonthlyDataString($monthlyDatas, 'TotalAmount');
        $AmountOrderedMonthly = getMonthlyDataString($monthlyDatas, 'TotalOrderedAmount');
        $AmountDeliveredMonthly = getMonthlyDataString($monthlyDatas, 'TotalDeliveredAmount');
        $AmountCanceledMonthly = getMonthlyDataString($monthlyDatas, 'TotalCanceledAmount');

        // Calculate totals for each metric across all months
        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        return view('admin.index', compact(
            'orders',
            'dashboardDatas',
            'AmountMonthly',
            'AmountOrderedMonthly',
            'AmountDeliveredMonthly',
            'AmountCanceledMonthly',
            'TotalAmount',
            'TotalOrderedAmount',
            'TotalDeliveredAmount',
            'TotalCanceledAmount'
        ));
    }

    /**
     * Display a listing of all contacts in the admin view.
     *
     * @return \Illuminate\View\View
     */
    public function contacts()
    {
        $contacts = $this->contactRepo->getAll();

        return view('admin.contacts', compact('contacts'));
    }

    /**
     * Delete a contact by id.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteContact($id)
    {
        $this->contactRepo->deleteContact($id);

        return redirect()->route('admin.contacts.index')->with('status', 'Contact message has deleted successfully!');
    }
}
