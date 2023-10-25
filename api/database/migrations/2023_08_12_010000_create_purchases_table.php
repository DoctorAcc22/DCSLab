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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->ulid();
            $table->foreignId('company_id')->references('id')->on('companies');
            $table->foreignId('branch_id')->references('id')->on('branches');
            $table->foreignId('supplier_id')->references('id')->on('suppliers');
            $table->foreignId('purchase_order_id')->references('id')->on('purchase_orders');

            $table->string('invoice_code');
            $table->dateTime('invoice_date', $precision = 0);
            $table->string('payment_term_type');
            $table->integer('payment_term')->default(0);

            // $table->string('FAKTURPAJAK_NOFAKTUR');
            // $table->string('FAKTURPAJAK_DPP');
            // $table->string('FAKTURPAJAK_PPN');

            $table->string('remarks')->nullable();
            $table->integer('post');

            $table->decimal('total', 19, 8)->default(0);
            $table->decimal('grand_total', 19, 8)->default(0);

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
        Schema::dropIfExists('purchases');
    }
};

// PRINTED
