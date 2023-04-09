<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderResource extends JsonResource
{
    protected string $type;

    public function type(string $value)
    {
        $this->type = $value;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            'invoice_code' => $this->invoice_code,
            'invoice_date' => $this->invoice_date,
            'shipping_date' => $this->shipping_date,
            'shipping_address' => $this->shipping_address,
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            // $this->mergeWhen($this->relationLoaded('purchaseOrderDiscounts'), [
            //     'global_discounts' => new ChartOfAccountResource($this->whenLoaded('purchaseOrderDiscounts')),
            // ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrderProductUnits'), [
                'product_units' => new PurchaseOrderProductUnitResource($this->whenLoaded('purchaseOrderProductUnits')),
            ]),
            'remarks' => $this->remarks,
            'status' => $this->status,
        ];
    }
}