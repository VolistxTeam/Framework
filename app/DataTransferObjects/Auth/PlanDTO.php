<?php

namespace App\DataTransferObjects\Auth;

use App\DataTransferObjects\DataTransferObjectBase;

class PlanDTO extends DataTransferObjectBase
{
    public string $id;
    public string $name;
    public ?string $description;
    public array $data;

    public static function fromModel($plan): self
    {
        return new self($plan);
    }

    public function GetDTO(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'data' => $this->data,
        ];
    }
}