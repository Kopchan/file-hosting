<?php

namespace App\Http\Resources;

use App\Models\Right;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $author = User::find($this->user_id);

        $rights = Right::where('file_id', $this->id)->get();

        return [
            'file_id' => $this->file_id ,
            'name' => $this->name ,
            'code' => 200 ,
            'url' =>  route('download', ['id' => $this->file_id]) ,
            'accesses' => [
                [
                    'fullname' => "$author->first_name $author->last_name",
                    'email'     => $author->email,
                    'type'      => 'author',
                    'code'      => 200,
                ],
                ...CoAuthorResource::collection($rights),
            ]
        ];
    }
}
