<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'dob'  => $this->dob,
            'gender'  => $this->gender,
            'phone'  => $this->phone,
            'address'  => $this->address,
            'email'  => $this->email,
            'roles' => $this->getRoleNames(),
            'city'  => $this->city->name,
            'state'  => $this->state->name,
            'country_id'  => $this->country->name,
            'created_at' => $this->created_at
        ];
    }
}
