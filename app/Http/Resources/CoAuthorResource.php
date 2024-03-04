<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoAuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $coAuthor = User::find($this->user_id);

        return [
            'fullname' => "$coAuthor->first_name $coAuthor->last_name",
            'email'     => $coAuthor->email,
            'type'      => 'co-author',
            'code'      => 200,
        ];
    }
}
