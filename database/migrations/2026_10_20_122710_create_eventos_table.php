public function up(): void
{
    Schema::create('eventos', function (Blueprint $table) {
        $table->id('idEvento');
        $table->string('nombreEvento');
        $table->string('lugar');
        $table->date('fecha');
    });
}