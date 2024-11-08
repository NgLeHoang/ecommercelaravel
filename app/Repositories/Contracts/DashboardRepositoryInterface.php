<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function getDashboardData();
    public function getMonthlyData();
}
