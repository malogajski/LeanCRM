<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'title'               => $this->title,
            'description'         => $this->description,
            'amount'              => $this->amount,
            'stage'               => $this->stage,
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'company'             => $this->whenLoaded('company', function () {
                return [
                    'id'   => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),
            'contact'             => $this->whenLoaded('contact', function () {
                return [
                    'id'    => $this->contact->id,
                    'name'  => $this->contact->first_name . ' ' . $this->contact->last_name,
                    'email' => $this->contact->email,
                ];
            }),
            'user'                => $this->whenLoaded('user', function () {
                return [
                    'id'   => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'activities_count'    => $this->whenCounted('activities'),
            'notes_count'         => $this->whenCounted('notes'),
            'is_won'              => $this->isWon(),
            'is_lost'             => $this->isLost(),
            'is_closed'           => $this->isClosed(),
            'created_at'          => $this->created_at->toISOString(),
            'updated_at'          => $this->updated_at->toISOString(),
        ];
    }
}
