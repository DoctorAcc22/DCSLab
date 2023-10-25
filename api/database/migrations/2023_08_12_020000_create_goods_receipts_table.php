<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->ulid();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('supplier_id')->references('id')->on('suppliers');
            $table->foreignId('warehouse_id')->references('id')->on('warehouses');
            $table->foreignId('purchase_order_id')->references('id')->nullable()->on('purchase_orders');
            $table->foreignId('purchase_id')->references('id')->nullable()->on('purchases');

            $table->string('shipment_code');
            $table->dateTime('reception_date', $precision = 0);

            $table->string('remarks')->nullable();
            $table->integer('post');

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->unsignedBigInteger('deleted_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
