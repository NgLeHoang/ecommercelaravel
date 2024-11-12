<?php

namespace App\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Get the dashboard data, including totals for orders and their statuses.
     *
     * This method retrieves the total amount of all orders, as well as the amounts
     * and counts for orders with different statuses ('ordered', 'delivered', 'canceled').
     * It returns a collection with the calculated sums and counts.
     *
     * @return array
     */
    public function getDashboardData()
    {
        $query = "SELECT 
            sum(total) AS TotalAmount,
            sum(if(status='ordered',total,0)) AS TotalOrderedAmount,
            sum(if(status='delivered',total,0)) AS TotalDeliveredAmount,
            sum(if(status='canceled',total,0)) AS TotalCanceledAmount,
            count(*) AS Total,
            sum(if(status='ordered',1,0)) AS TotalOrdered,
            sum(if(status='delivered',1,0)) AS TotalDelivered,
            sum(if(status='canceled',1,0)) AS TotalCanceled
            FROM orders";

        return DB::select($query);
    }

    /**
     * Get monthly data for the current year, including totals for orders by status.
     *
     * This method retrieves the monthly totals for the current year, including
     * the total amounts and counts for each status ('ordered', 'delivered', 'canceled').
     * It joins the `month_names` table to get the names of the months.
     *
     * @return array
     */
    public function getMonthlyData()
    {
        $query = "SELECT 
            M.id AS MonthNo, 
            M.name AS MonthName,
            IFNULL(D.TotalAmount,0) AS TotalAmount,
            IFNULL(D.TotalOrderedAmount,0) AS TotalOrderedAmount,
            IFNULL(D.TotalDeliveredAmount,0) AS TotalDeliveredAmount,
            IFNULL(D.TotalCanceledAmount,0) AS TotalCanceledAmount 
            FROM month_names M
            LEFT JOIN (
            SELECT 
                DATE_FORMAT(created_at, '%b') AS MonthName,
                MONTH(created_at) AS MonthNo,
                sum(total) AS TotalAmount,
                sum(if(status='ordered',total,0)) AS TotalOrderedAmount,
                sum(if(status='delivered',total,0)) AS TotalDeliveredAmount,
                sum(if(status='canceled',total,0)) AS TotalCanceledAmount
            FROM orders 
            WHERE YEAR(created_at) = YEAR(NOW()) 
            GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
            ORDER BY MONTH(created_at)
        ) D ON D.MonthNo = M.id";

        return DB::select($query);
    }
}
