<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
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
    public function getDashboardData();

    /**
     * Get monthly data for the current year, including totals for orders by status.
     *
     * This method retrieves the monthly totals for the current year, including
     * the total amounts and counts for each status ('ordered', 'delivered', 'canceled').
     * It joins the `month_names` table to get the names of the months.
     *
     * @return array
     */
    public function getMonthlyData();
}
