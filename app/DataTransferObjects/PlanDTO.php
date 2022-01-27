<?php

namespace App\DataTransferObjects;

class PlanDTO extends DataTransferObjectBase
{
    public string $id;
    public string $name;
    public string $description;
    public int $requests;



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
            'requests'=> $this->requests,
        ];
    }
}