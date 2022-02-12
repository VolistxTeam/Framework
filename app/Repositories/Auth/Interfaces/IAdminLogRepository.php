<?php
namespace App\Repositories\Auth\Interfaces;

interface IAdminLogRepository
{
    public function Create(array $inputs);

    public function Find($log_id);

    public function FindAll($needle, $page, $limit);
}