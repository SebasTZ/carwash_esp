<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->decimal('impuesto',8,2,true);
            $table->string('numero_comprobante',255);
            $table->decimal('total',8,2,true);
            $table->tinyInteger('estado')->default(1);
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('comprobante_id')->nullable()->constrained('comprobantes')->onDelete('set null');
            $table->text('comentarios')->nullable(); 
            $table->string('medio_pago')->default('efectivo'); 
            $table->decimal('efectivo', 8, 2)->nullable(); 
            $table->decimal('tarjeta_credito', 8, 2)->nullable(); // Renombrado de billetera digital a tarjeta de crédito
            // $table->decimal('billetera_digital', 8, 2)->nullable(); // Renombrado de yape a billetera digital
            // $table->boolean('pago_mixto')->default(false); // Eliminado si existía
            $table->boolean('servicio_lavado')->default(false);
            $table->dateTime('horario_lavado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas');
    }
};